<?php

namespace Controllers\User;

use Controllers\ControllerInterface;
use Models\Database;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class ForgotPasswordPost implements ControllerInterface {

    public const PATH = "/user/forgot-password-post";

    public function control() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
            $email = trim($_POST['email'] ?? '');

            if (empty($email)) {
                $_SESSION['flash'] = [
                    'type' => 'error',
                    'message' => 'Veuillez saisir votre adresse email.'
                ];
                header("Location: /user/forgot-password");
                exit();
            }

            $conn = Database::getConnection();

            // Vérifier si l'email existe
            $stmt = $conn->prepare("SELECT id, nom, prenom FROM users WHERE mail = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 0) {
                // Pour des raisons de sécurité, on affiche le même message
                $_SESSION['flash'] = [
                    'type' => 'success',
                    'message' => 'Si cet email existe, un lien de réinitialisation a été envoyé.'
                ];
                header("Location: /user/login");
                exit();
            }

            $stmt->bind_result($userId, $nom, $prenom);
            $stmt->fetch();
            $stmt->close();

            // Générer un token unique
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Enregistrer le token dans la base de données
            $insertStmt = $conn->prepare("INSERT INTO password_reset_tokens (user_id, token, expiry) VALUES (?, ?, ?)");
            $insertStmt->bind_param("iss", $userId, $token, $expiry);
            $insertStmt->execute();
            $insertStmt->close();

            // Envoyer l'email
            $this->sendResetEmail($email, $token, $nom, $prenom);

            $_SESSION['flash'] = [
                'type' => 'success',
                'message' => 'Un lien de réinitialisation a été envoyé à votre adresse email.'
            ];
            header("Location: /user/login");
            exit();
        } else {
            header("Location: /user/forgot-password");
            exit();
        }
    }

    private function sendResetEmail($email, $token, $nom, $prenom) {
        // URL de réinitialisation
        $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/user/reset-password?token=" . $token;

        // Configuration de l'email (ajustez selon votre configuration SMTP)
        $mail = new PHPMailer(true);

        try {
            // Configuration SMTP - À ADAPTER selon votre serveur
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Remplacez par votre serveur SMTP
            $mail->SMTPAuth = true;
            $mail->Username = 'votre.email@gmail.com'; // Votre email
            $mail->Password = 'votre_mot_de_passe'; // Votre mot de passe
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';

            // Destinataires
            $mail->setFrom('noreply@sae-manager.com', 'SAE Manager');
            $mail->addAddress($email, "$prenom $nom");

            // Contenu
            $mail->isHTML(true);
            $mail->Subject = 'Réinitialisation de votre mot de passe';
            $mail->Body = "
                <html>
                <body>
                    <h2>Réinitialisation de mot de passe</h2>
                    <p>Bonjour $prenom $nom,</p>
                    <p>Vous avez demandé la réinitialisation de votre mot de passe.</p>
                    <p>Cliquez sur le lien ci-dessous pour réinitialiser votre mot de passe :</p>
                    <p><a href='$resetLink'>$resetLink</a></p>
                    <p>Ce lien expirera dans 1 heure.</p>
                    <p>Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.</p>
                </body>
                </html>
            ";
            $mail->AltBody = "Bonjour $prenom $nom,\n\nVous avez demandé la réinitialisation de votre mot de passe.\n\nCopiez ce lien dans votre navigateur : $resetLink\n\nCe lien expirera dans 1 heure.";

            $mail->send();
        } catch (Exception $e) {
            // Log l'erreur (en production, utilisez un système de log approprié)
            error_log("Erreur d'envoi d'email : {$mail->ErrorInfo}");
        }
    }

    static function support(string $chemin, string $method): bool {
        return $chemin === self::PATH && $method === "POST";
    }
}
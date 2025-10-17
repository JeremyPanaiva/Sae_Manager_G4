<?php

namespace Controllers\User;

use Controllers\ControllerInterface;
use Models\Database;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class ForgotPasswordPost implements ControllerInterface {

    public const PATH = "/user/forgot-password-post";

    private const SMTP_HOST_DEFAULT = 'smtp-sae-manager-g4.alwaysdata.net';
    private const SMTP_PORT_DEFAULT = 587;
    private const SMTP_SECURE_DEFAULT = 'tls';
    private const SMTP_USERNAME_DEFAULT = '';
    private const SMTP_PASSWORD_DEFAULT = '';
    private const FROM_EMAIL_DEFAULT = 'sae-manager-g4@alwaysdata.net';
    private const FROM_NAME_DEFAULT = 'SAE Manager';
    private const TOKEN_EXPIRY_HOURS = 1;

    public function control() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
            $email = trim($_POST['email'] ?? '');

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['flash'] = [
                    'type' => 'error',
                    'message' => 'Veuillez saisir une adresse email valide.'
                ];
                header("Location: /user/forgot-password");
                exit();
            }

            $conn = Database::getConnection();

            $stmt = $conn->prepare("SELECT id, nom, prenom FROM users WHERE mail = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 0) {
                $_SESSION['flash'] = [
                    'type' => 'success',
                    'message' => 'Si cet email existe, un lien de réinitialisation a été envoyé.'
                ];
                $stmt->close();
                header("Location: /user/login");
                exit();
            }

            $stmt->bind_result($userId, $nom, $prenom);
            $stmt->fetch();
            $stmt->close();

            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+' . self::TOKEN_EXPIRY_HOURS . ' hour'));

            $insertStmt = $conn->prepare("INSERT INTO password_reset_tokens (user_id, token, expiry, used) VALUES (?, ?, ?, 0)");
            $insertStmt->bind_param("iss", $userId, $token, $expiry);
            if (!$insertStmt->execute()) {
                $_SESSION['flash'] = [
                    'type' => 'error',
                    'message' => 'Une erreur est survenue. Veuillez réessayer.'
                ];
                $insertStmt->close();
                header("Location: /user/forgot-password");
                exit();
            }
            $insertStmt->close();

            $emailSent = $this->sendResetEmail($email, $token, $nom, $prenom);

            if ($emailSent) {
                $_SESSION['flash'] = [
                    'type' => 'success',
                    'message' => 'Un lien de réinitialisation a été envoyé à votre adresse email.'
                ];
            } else {
                $_SESSION['flash'] = [
                    'type' => 'error',
                    'message' => 'Impossible d\'envoyer l\'email. Vérifiez la configuration SMTP.'
                ];
            }

            header("Location: /user/login");
            exit();
        } else {
            header("Location: /user/forgot-password");
            exit();
        }
    }

    private function sendResetEmail(string $email, string $token, string $nom, string $prenom): bool {
        // lire la configuration depuis les variables d'environnement si disponibles (recommandé)
        $smtpHost = getenv('SMTP_HOST') ?: self::SMTP_HOST_DEFAULT;
        $smtpPort = getenv('SMTP_PORT') ?: self::SMTP_PORT_DEFAULT;
        $smtpSecure = getenv('SMTP_SECURE') ?: self::SMTP_SECURE_DEFAULT; // 'tls' or 'ssl'
        $smtpUser = getenv('SMTP_USERNAME') ?: self::SMTP_USERNAME_DEFAULT;
        $smtpPass = getenv('SMTP_PASSWORD') ?: self::SMTP_PASSWORD_DEFAULT;
        $fromEmail = getenv('FROM_EMAIL') ?: self::FROM_EMAIL_DEFAULT;
        $fromName = getenv('FROM_NAME') ?: self::FROM_NAME_DEFAULT;

        // construire le lien de réinitialisation
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $resetLink = $protocol . '://' . $_SERVER['HTTP_HOST'] . '/user/reset-password?token=' . $token;

        $mail = new PHPMailer(true);

        try {
            // Debug: mettre 2 pour afficher les logs SMTP (pendant les tests)
            $mail->SMTPDebug = 0;
            $mail->isSMTP();
            $mail->Host = $smtpHost;
            $mail->SMTPAuth = true;
            $mail->Username = $smtpUser;
            $mail->Password = $smtpPass;

            // utiliser les constantes PHPMailer si possible
            if (strtolower($smtpSecure) === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                // 'tls' ou autre -> STARTTLS
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }

            $mail->Port = (int)$smtpPort;
            $mail->CharSet = 'UTF-8';
            // si le serveur SMTP utilise un certificat auto-signé, décommenter les options ci-dessous (déconseillé en prod)
            /*
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];
            */

            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($email, "$prenom $nom");
            $mail->addReplyTo($fromEmail, $fromName);

            $mail->isHTML(true);
            $mail->Subject = 'Réinitialisation de votre mot de passe - SAE Manager';
            $mail->Body = $this->getEmailHtmlBody($prenom, $nom, $resetLink);
            $mail->AltBody = $this->getEmailTextBody($prenom, $nom, $resetLink);

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('PHPMailer Error: ' . $e->getMessage());
            return false;
        }
    }

    private function getEmailHtmlBody(string $prenom, string $nom, string $resetLink): string {
        return "
        <!DOCTYPE html>
        <html lang='fr'>
        <head><meta charset='utf-8'></head>
        <body style='font-family:Arial,sans-serif;color:#333'>
            <h2>Réinitialisation du mot de passe</h2>
            <p>Bonjour <strong>" . htmlspecialchars($prenom) . " " . htmlspecialchars($nom) . "</strong>,</p>
            <p>Vous avez demandé la réinitialisation de votre mot de passe. Cliquez sur le lien ci-dessous pour définir un nouveau mot de passe :</p>
            <p><a href='" . htmlspecialchars($resetLink) . "'>" . htmlspecialchars($resetLink) . "</a></p>
            <p>Ce lien expirera dans " . self::TOKEN_EXPIRY_HOURS . " heure(s) et ne peut être utilisé qu'une seule fois.</p>
            <p>Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.</p>
            <p>Cordialement,<br/>L'équipe SAE Manager</p>
        </body>
        </html>
        ";
    }

    private function getEmailTextBody(string $prenom, string $nom, string $resetLink): string {
        return "Bonjour $prenom $nom,\n\nVous avez demandé la réinitialisation de votre mot de passe.\n\nOuvrez ce lien pour réinitialiser : $resetLink\n\nCe lien expirera dans " . self::TOKEN_EXPIRY_HOURS . " heure(s).\n\nSi vous n'avez pas demandé cette réinitialisation, ignorez cet email.\n\nCordialement,\nSAE Manager";
    }

    static function support(string $chemin, string $method): bool {
        return $chemin === self::PATH && $method === "POST";
    }
}
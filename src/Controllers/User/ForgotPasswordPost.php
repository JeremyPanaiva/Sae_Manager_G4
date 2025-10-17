<?php

namespace Controllers\User;

use Controllers\ControllerInterface;
use Models\Database;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class ForgotPasswordPost implements ControllerInterface
{
    public const PATH = "/user/forgot-password-post";

    // Défauts sûrs, mais privilégie les variables d'environnement
    private const SMTP_HOST_DEFAULT = 'smtp-alwaysdata.com';
    private const SMTP_PORT_DEFAULT = 587; // STARTTLS
    private const SMTP_SECURE_DEFAULT = 'tls';
    private const SMTP_USERNAME_DEFAULT = 'sae-manager-g4@alwaysdata.net';
    private const SMTP_PASSWORD_DEFAULT = '';
    private const FROM_EMAIL_DEFAULT = 'sae-manager-g4@alwaysdata.net';
    private const FROM_NAME_DEFAULT = 'SAE Manager';
    private const TOKEN_EXPIRY_HOURS = 1;

    public function control()
    {
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

            $_SESSION['flash'] = $emailSent
                ? ['type' => 'success', 'message' => 'Un lien de réinitialisation a été envoyé à votre adresse email.']
                : ['type' => 'error', 'message' => 'Impossible d\'envoyer l\'email. Vérifiez la configuration SMTP.'];

            header("Location: /user/login");
            exit();
        } else {
            header("Location: /user/forgot-password");
            exit();
        }
    }

    private function sendResetEmail(string $email, string $token, string $nom, string $prenom): bool {
        // lire la configuration depuis les variables d'environnement (recommandé)
        $smtpHost   = getenv('SMTP_HOST') ?: 'smtp-alwaysdata.com';
        $smtpPort   = (int)(getenv('SMTP_PORT') ?: 587);
        $smtpSecure = strtolower((string)(getenv('SMTP_SECURE') ?: 'tls')); // 'tls' ou 'ssl'
        $smtpUser   = getenv('SMTP_USERNAME') ?: '';
        $smtpPass   = getenv('SMTP_PASSWORD') ?: '';
        $fromEmail  = getenv('FROM_EMAIL') ?: $smtpUser;
        $fromName   = getenv('FROM_NAME') ?: 'SAE Manager';

        // construire le lien de réinitialisation (APP_URL prioritaire)
        $appUrl = rtrim((string) getenv('APP_URL'), '/');
        if ($appUrl !== '') {
            $resetLink = $appUrl . '/user/reset-password?token=' . urlencode($token);
        } else {
            $proto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http');
            $host  = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $resetLink = $proto . '://' . $host . '/user/reset-password?token=' . urlencode($token);
        }

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        try {
            $mail->SMTPDebug = (int)(getenv('SMTP_DEBUG') ?: 0);
            $mail->isSMTP();
            $mail->Host = $smtpHost;
            $mail->SMTPAuth = true;
            $mail->Username = $smtpUser;
            $mail->Password = $smtpPass;

            if ($smtpSecure === 'ssl') {
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS; // 465
            } else {
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS; // 587
            }

            $mail->Port = $smtpPort;
            $mail->CharSet = 'UTF-8';

            $mail->setFrom($fromEmail, $fromName);
            $mail->addReplyTo($fromEmail, $fromName);
            $mail->addAddress($email, trim("$prenom $nom"));

            $mail->isHTML(true);
            $mail->Subject = 'Réinitialisation de votre mot de passe - SAE Manager';
            $mail->Body    = $this->getEmailHtmlBody($prenom, $nom, $resetLink);
            $mail->AltBody = $this->getEmailTextBody($prenom, $nom, $resetLink);

            $mail->send();
            return true;
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            error_log('PHPMailer Error: ' . $e->getMessage());
            return false;
        }
    }

    private function getEmailHtmlBody(string $prenom, string $nom, string $resetLink): string
    {
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

    private function getEmailTextBody(string $prenom, string $nom, string $resetLink): string
    {
        return "Bonjour $prenom $nom,\n\n"
            . "Vous avez demandé la réinitialisation de votre mot de passe.\n\n"
            . "Ouvrez ce lien pour réinitialiser : $resetLink\n\n"
            . "Ce lien expirera dans " . self::TOKEN_EXPIRY_HOURS . " heure(s) et ne peut être utilisé qu'une seule fois.\n\n"
            . "Si vous n'êtes pas à l'origine de cette demande, vous pouvez ignorer cet email.\n";
    }

    public static function support(string $chemin, string $method): bool
    {
        return $chemin === self::PATH && $method === "POST";
    }
}
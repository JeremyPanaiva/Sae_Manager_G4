<?php

namespace Models\User;

require_once __DIR__ . '/../../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Models\Database;
use Shared\Exceptions\DataBaseException;

class EmailService
{
    private PHPMailer $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->configureMailer();
    }

    private function configureMailer(): void
    {
        try {
            // Configuration SMTP pour AlwaysData
            $this->mailer->isSMTP();
            $this->mailer->Host = Database::parseEnvVar('SMTP_HOST') ?: 'smtp-alwaysdata.com';
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = Database::parseEnvVar('SMTP_USERNAME');
            $this->mailer->Password = Database::parseEnvVar('SMTP_PASSWORD');
            $this->mailer->SMTPSecure = Database::parseEnvVar('SMTP_SECURE') === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
            $this->mailer->Port = (int)(Database::parseEnvVar('SMTP_PORT') ?: 587);
            $this->mailer->CharSet = 'UTF-8';

            // Expéditeur
            $fromEmail = Database::parseEnvVar('FROM_EMAIL');
            $fromName = Database::parseEnvVar('FROM_NAME') ?: 'SAE Manager';
            $this->mailer->setFrom($fromEmail, $fromName);
        } catch (Exception $e) {
            throw new DataBaseException("Erreur de configuration email : " . $e->getMessage());
        }
    }

    /**
     * Envoie un email de réinitialisation de mot de passe
     *
     * @throws DataBaseException
     */
    public function sendPasswordResetEmail(string $email, string $token): bool
    {
        try {
            $this->mailer->addAddress($email);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Réinitialisation de votre mot de passe - SAE Manager';
            
            $resetLink = $this->getBaseUrl() . "/user/reset-password?token=" . $token;
            
            $this->mailer->Body = $this->getPasswordResetEmailBody($resetLink);
            $this->mailer->AltBody = $this->getPasswordResetEmailTextBody($resetLink);

            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            throw new DataBaseException("Erreur d'envoi d'email : " . $e->getMessage());
        }
    }

    private function getBaseUrl(): string
    {
        // Utiliser l'URL de l'application depuis les variables d'environnement
        $appUrl = Database::parseEnvVar('APP_URL');
        if (!empty($appUrl)) {
            return rtrim($appUrl, '/');
        }
        
        // Fallback sur la détection automatique
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $path = dirname($_SERVER['SCRIPT_NAME']);
        return $protocol . '://' . $host . $path;
    }

    private function getPasswordResetEmailBody(string $resetLink): string
    {
        return "
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Réinitialisation de mot de passe</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #2c3e50;'>Réinitialisation de votre mot de passe</h2>
                
                <p>Bonjour,</p>
                
                <p>Vous avez demandé la réinitialisation de votre mot de passe pour votre compte SAE Manager.</p>
                
                <p>Pour réinitialiser votre mot de passe, cliquez sur le lien ci-dessous :</p>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$resetLink}' 
                       style='background-color: #3498db; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                        Réinitialiser mon mot de passe
                    </a>
                </div>
                
                <p><strong>Ce lien est valide pendant 1 heure.</strong></p>
                
                <p>Si vous n'avez pas demandé cette réinitialisation, vous pouvez ignorer cet email.</p>
                
                <hr style='margin: 30px 0; border: none; border-top: 1px solid #eee;'>
                
                <p style='font-size: 12px; color: #666;'>
                    Si le bouton ne fonctionne pas, copiez et collez ce lien dans votre navigateur :<br>
                    <a href='{$resetLink}'>{$resetLink}</a>
                </p>
            </div>
        </body>
        </html>";
    }

    private function getPasswordResetEmailTextBody(string $resetLink): string
    {
        return "
Réinitialisation de votre mot de passe - SAE Manager

Bonjour,

Vous avez demandé la réinitialisation de votre mot de passe pour votre compte SAE Manager.

Pour réinitialiser votre mot de passe, cliquez sur le lien suivant :
{$resetLink}

Ce lien est valide pendant 1 heure.

Si vous n'avez pas demandé cette réinitialisation, vous pouvez ignorer cet email.

Cordialement,
L'équipe SAE Manager
        ";
    }
}

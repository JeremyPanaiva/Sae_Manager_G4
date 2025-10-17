<?php
namespace Controllers\User;

use Controllers\ControllerInterface;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PDO;
use DateTime;
use RuntimeException;
use Views\User\ConnectionView;
use Views\User\ForgotPasswordView;

class ForgotPassword implements ControllerInterface
{    public const PATH = "/user/forgot-password";

    protected string $dsn;
    protected string $dbUser;
    protected string $dbPass;
    protected string $smtpHost;
    protected string $smtpUser;
    protected string $smtpPass;
    protected string $fromEmail;
    protected string $fromName;

    public function __construct(array $config = [])
    {
        $this->dsn      = $config['dsn']      ?? getenv('DB_DSN')      ?? 'mysql:host=127.0.0.1;dbname=your_db;charset=utf8mb4';
        $this->dbUser   = $config['db_user']  ?? getenv('DB_USER')     ?? 'db_user';
        $this->dbPass   = $config['db_pass']  ?? getenv('DB_PASS')     ?? 'db_pass';
        $this->smtpHost = $config['smtp_host']?? getenv('SMTP_HOST')   ?? 'smtp.example.com';
        $this->smtpUser = $config['smtp_user']?? getenv('SMTP_USER')   ?? 'smtp_user';
        $this->smtpPass = $config['smtp_pass']?? getenv('SMTP_PASS')   ?? 'smtp_pass';
        $this->fromEmail= $config['from_email']?? getenv('SMTP_FROM')   ?? 'no-reply@example.com';
        $this->fromName = $config['from_name'] ?? getenv('SMTP_FROM_NAME') ?? 'Support';
    }

    public function handle(array $post): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $email = trim((string)($post['email'] ?? ''));

        if ($email === '') {
            $_SESSION['flash'] = 'Email manquant.';
            header('Location: /forgotPassword.php');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // comportement générique : ne pas révéler l'existence du compte
            $_SESSION['flash'] = 'Si un compte existe, un e‑mail de réinitialisation vous a été envoyé.';
            header('Location: /forgotPassword.php');
            exit;
        }

        $_SESSION['flash'] = 'Si un compte existe, un e‑mail de réinitialisation vous a été envoyé.';

        try {
            $pdo = $this->getPdo();
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                header('Location: /forgotPassword.php');
                exit;
            }

            $token = bin2hex(random_bytes(32));
            $expiresAt = (new DateTime('+1 hour'))->format('Y-m-d H:i:s');

            $uStmt = $pdo->prepare('UPDATE users SET password_reset_token = :token, password_reset_expires = :expires WHERE id = :id');
            $uStmt->execute([
                ':token' => $token,
                ':expires' => $expiresAt,
                ':id' => $user['id'],
            ]);

            $resetUrl = $this->buildResetUrl($token);
            $this->sendResetEmail($email, $resetUrl);

            header('Location: /forgotPassword.php');
            exit;
        } catch (\Throwable $e) {
            error_log('ForgotPassword error: ' . $e->getMessage());
            // garder message générique pour l'utilisateur
            $_SESSION['flash'] = 'Une erreur est survenue. Réessayez plus tard.';
            header('Location: /forgotPassword.php');
            exit;
        }
    }

    protected function getPdo(): PDO
    {
        return new PDO($this->dsn, $this->dbUser, $this->dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
    }

    protected function buildResetUrl(string $token): string
    {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $path = rtrim(dirname($_SERVER['PHP_SELF'] ?? '/'), '/\\');
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        return sprintf('%s://%s%s/resetPassword.php?token=%s', $scheme, $host, $path, urlencode($token));
    }

    protected function sendResetEmail(string $to, string $resetUrl): void
    {
        if (!class_exists(PHPMailer::class)) {
            require_once __DIR__ . '/../../../vendor/autoload.php';
        }

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $this->smtpHost ?: getenv('SMTP_HOST');
            $mail->SMTPAuth = true;
            $mail->Username = $this->smtpUser ?: getenv('SMTP_USER');
            $mail->Password = $this->smtpPass ?: getenv('SMTP_PASS');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom($this->fromEmail, $this->fromName);
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = 'Réinitialisation de votre mot de passe';
            $mail->Body = "<p>Bonjour,</p>
                <p>Pour réinitialiser votre mot de passe, cliquez sur le lien suivant (valide 1 heure) :</p>
                <p><a href=\"{$resetUrl}\">Réinitialiser mon mot de passe</a></p>
                <p>Si vous n'avez pas demandé cette réinitialisation, ignorez ce message.</p>";

            // debug activé si APP_DEBUG=1
            $mail->SMTPDebug = (getenv('APP_DEBUG') === '1') ? 2 : 0;
            $mail->Debugoutput = function ($str, $level) {
                error_log('[PHPMailer][' . $level . '] ' . $str);
            };

            // options SSL pour environnements de test
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
            ];

            $mail->send();
            error_log("Reset email envoyé à {$to}");
        } catch (Exception $e) {
            error_log('Mail error: ' . $e->getMessage());
        }
    }

    function control()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $view = new ForgotPasswordView();
        echo $view->render();
    }

    static function support(string $chemin, string $method): bool
    {
        return $chemin === self::PATH && $method === "GET";
    }
}
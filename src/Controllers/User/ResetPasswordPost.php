<?php

namespace Controllers\User;

use Controllers\ControllerInterface;
use Models\User\PasswordResetToken;
use Models\User\User;
use Models\Database;
use Shared\Exceptions\DataBaseException;

class ResetPasswordPost implements ControllerInterface
{
    public const PATH = "/user/reset-password";

    public function control()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=forgot-password');
            exit;
        }

        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($token) || empty($password) || empty($confirmPassword)) {
            header('Location: ?page=reset-password&token=' . urlencode($token) . '&error=missing_fields');
            exit;
        }

        if ($password !== $confirmPassword) {
            header('Location: ?page=reset-password&token=' . urlencode($token) . '&error=passwords_dont_match');
            exit;
        }

        if (strlen($password) < 8) {
            header('Location: ?page=reset-password&token=' . urlencode($token) . '&error=password_too_short');
            exit;
        }

        try {
            $tokenModel = new PasswordResetToken();
            $email = $tokenModel->validateToken($token);

            if (!$email) {
                header('Location: ?page=forgot-password&error=invalid_token');
                exit;
            }

            // Mettre à jour le mot de passe
            $conn = Database::getConnection();
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("UPDATE users SET mdp = ? WHERE mail = ?");
            if (!$stmt) {
                throw new DataBaseException("SQL prepare failed in reset password.");
            }
            $stmt->bind_param("ss", $hashedPassword, $email);
            $stmt->execute();
            $stmt->close();

            // Supprimer le token utilisé
            $tokenModel->deleteToken($token);

            header('Location: ?page=connection&success=password_reset');
            exit;

        } catch (DataBaseException $e) {
            error_log("Erreur base de données dans ResetPasswordPost: " . $e->getMessage());
            header('Location: ?page=reset-password&token=' . urlencode($token) . '&error=database_error');
            exit;
        } catch (\Exception $e) {
            error_log("Erreur générale dans ResetPasswordPost: " . $e->getMessage());
            header('Location: ?page=reset-password&token=' . urlencode($token) . '&error=general_error');
            exit;
        }
    }

    static function support(string $chemin, string $method): bool
    {
        return ($chemin === self::PATH || 
                (isset($_GET['page']) && $_GET['page'] === 'reset-password'))
            && $method === "POST";
    }
}

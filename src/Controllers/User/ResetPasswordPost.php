<?php

namespace Controllers\User;

use Controllers\ControllerInterface;
use Models\Database;

class ResetPasswordPost implements ControllerInterface {

    public const PATH = "/user/reset-password-post";

    public function control() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
            $token = trim($_POST['token'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (empty($token) || empty($password) || empty($confirmPassword)) {
                $_SESSION['flash'] = [
                    'type' => 'error',
                    'message' => 'Veuillez remplir tous les champs.'
                ];
                header("Location: /user/reset-password?token=" . urlencode($token));
                exit();
            }

            if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
                $_SESSION['flash'] = [
                    'type' => 'error',
                    'message' => 'Token invalide.'
                ];
                header("Location: /user/login");
                exit();
            }

            if ($password !== $confirmPassword) {
                $_SESSION['flash'] = [
                    'type' => 'error',
                    'message' => 'Les mots de passe ne correspondent pas.'
                ];
                header("Location: /user/reset-password?token=" . urlencode($token));
                exit();
            }

            if (strlen($password) < 8 || strlen($password) > 20) {
                $_SESSION['flash'] = [
                    'type' => 'error',
                    'message' => 'Le mot de passe doit contenir entre 8 et 20 caractères.'
                ];
                header("Location: /user/reset-password?token=" . urlencode($token));
                exit();
            }

            if (!$this->isPasswordStrong($password)) {
                $_SESSION['flash'] = [
                    'type' => 'error',
                    'message' => 'Le mot de passe doit contenir au moins une lettre majuscule, une minuscule et un chiffre.'
                ];
                header("Location: /user/reset-password?token=" . urlencode($token));
                exit();
            }

            $conn = Database::getConnection();
            $stmt = $conn->prepare("SELECT id, user_id, expiry, used FROM password_reset_tokens WHERE token = ?");
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 0) {
                $_SESSION['flash'] = [
                    'type' => 'error',
                    'message' => 'Token invalide.'
                ];
                $stmt->close();
                header("Location: /user/login");
                exit();
            }

            $stmt->bind_result($tokenId, $userId, $expiry, $used);
            $stmt->fetch();
            $stmt->close();

            if ($used) {
                $_SESSION['flash'] = [
                    'type' => 'error',
                    'message' => 'Ce lien a déjà été utilisé.'
                ];
                header("Location: /user/login");
                exit();
            }

            if (strtotime($expiry) < time()) {
                $_SESSION['flash'] = [
                    'type' => 'error',
                    'message' => 'Ce lien a expiré.'
                ];
                header("Location: /user/forgot-password");
                exit();
            }

            // Hasher et mettre à jour
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $updateStmt = $conn->prepare("UPDATE users SET mdp = ? WHERE id = ?");
            $updateStmt->bind_param("si", $hashedPassword, $userId);
            if (!$updateStmt->execute()) {
                $_SESSION['flash'] = [
                    'type' => 'error',
                    'message' => 'Erreur lors de la mise à jour du mot de passe.'
                ];
                $updateStmt->close();
                header("Location: /user/reset-password?token=" . urlencode($token));
                exit();
            }
            $updateStmt->close();

            // Marquer token utilisé
            $markUsedStmt = $conn->prepare("UPDATE password_reset_tokens SET used = 1 WHERE id = ?");
            $markUsedStmt->bind_param("i", $tokenId);
            $markUsedStmt->execute();
            $markUsedStmt->close();

            $_SESSION['flash'] = [
                'type' => 'success',
                'message' => 'Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.'
            ];
            header("Location: /user/login");
            exit();
        } else {
            header("Location: /user/login");
            exit();
        }
    }

    private function isPasswordStrong(string $password): bool {
        return preg_match('/[A-Z]/', $password) && preg_match('/[a-z]/', $password) && preg_match('/[0-9]/', $password);
    }

    static function support(string $chemin, string $method): bool {
        return $chemin === self::PATH && $method === "POST";
    }
}
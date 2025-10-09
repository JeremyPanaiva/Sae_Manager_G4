<?php

namespace Controllers\User;

use Controllers\ControllerInterface;
use Models\Database;
use Views\User\ResetPasswordView;

class ResetPassword implements ControllerInterface {

    public const PATH = "/user/reset-password";

    public function control() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $token = $_GET['token'] ?? '';

        if (empty($token)) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'Token invalide ou manquant.'
            ];
            header("Location: /user/login");
            exit();
        }

        $conn = Database::getConnection();

        // Vérifier si le token est valide et non expiré
        $stmt = $conn->prepare("SELECT id, user_id, expiry, used FROM password_reset_tokens WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'Token invalide.'
            ];
            header("Location: /user/login");
            exit();
        }

        $stmt->bind_result($tokenId, $userId, $expiry, $used);
        $stmt->fetch();
        $stmt->close();

        // Vérifier si le token a déjà été utilisé
        if ($used) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'Ce lien a déjà été utilisé.'
            ];
            header("Location: /user/login");
            exit();
        }

        // Vérifier si le token a expiré
        if (strtotime($expiry) < time()) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'Ce lien a expiré. Veuillez faire une nouvelle demande.'
            ];
            header("Location: /user/forgot-password");
            exit();
        }

        // Afficher le formulaire de réinitialisation
        $view = new ResetPasswordView($token);
        echo $view->render();
    }

    static function support(string $chemin, string $method): bool {
        return $chemin === self::PATH && $method === "GET";
    }
}
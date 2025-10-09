<?php
namespace Controllers\User;

use Controllers\ControllerInterface;
use Models\User\User;
use Shared\Exceptions\ArrayException;
use Shared\Exceptions\ValidationException;
use Shared\Exceptions\EmailNotFoundException;
use Shared\Exceptions\InvalidPasswordException;
use Shared\Exceptions\DataBaseException;
use Views\User\ConnectionView;

class LoginPost implements ControllerInterface
{
    function control()
    {
        if (!isset($_POST['ok'])) return;

        $email = $_POST['uname'] ?? '';
        $mdp   = $_POST['psw'] ?? '';

        $User = new User();
        $validationExceptions = [];

        // 1️⃣ Vérifie email vide ou invalide
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $validationExceptions[] = new ValidationException("mail", "string", "Email invalide.");
        }

        // 2️⃣ Vérifie mot de passe vide
        if (empty($mdp)) {
            $validationExceptions[] = new ValidationException("mdp", "string", "Le mot de passe ne peut pas être vide.");
        }

        try {
            // 3️⃣ Si des erreurs de validation locales
            if (count($validationExceptions) > 0) {
                throw new ArrayException($validationExceptions);
            }

            // 4️⃣ Vérifie la BDD en priorité
            try {
                $userData = $User->findByEmail($email);
            } catch (DataBaseException $dbEx) {
                throw new ArrayException([$dbEx]);
            }

            // 5️⃣ Email non trouvé
            if (!$userData) {
                throw new ArrayException([new EmailNotFoundException($email)]);
            }

            // 6️⃣ Vérifie mot de passe
            if (!password_verify($mdp, $userData['mdp'])) {
                throw new ArrayException([new InvalidPasswordException()]);
            }

            // 7️⃣ Connexion réussie
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $_SESSION['user'] = [
                'id'     => $userData['id'],
                'nom'    => $userData['nom'],
                'prenom' => $userData['prenom']
            ];

            header("Location: /");
            exit();

        } catch (ArrayException $exceptions) {
            // Affiche les erreurs sur la vue
            $view = new ConnectionView($exceptions->getExceptions());
            echo $view->render();
            return;
        }
    }

    static function support(string $chemin, string $method): bool
    {
        return $chemin === "/user/login" && $method === "POST";
    }
}

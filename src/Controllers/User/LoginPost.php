<?php
namespace Controllers\User;

use Controllers\ControllerInterface;
use Models\User\User;
use Shared\Exceptions\ArrayException;
use Shared\Exceptions\ValidationException;
use Shared\Exceptions\EmailNotFoundException;
use Shared\Exceptions\InvalidPasswordException;
use Views\User\ConnectionView;

class LoginPost implements ControllerInterface
{
    function control()
    {
        if (isset($_POST['ok'])) {
            $email = $_POST['uname'] ?? '';
            $mdp   = $_POST['psw'] ?? '';

            $User = new User();
            $validationExceptions = [];

            // Vérifie si l'email est vide ou invalide
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $validationExceptions[] = new ValidationException(
                    "mail",
                    "string",
                    "Email invalide."
                );
            }

            // Vérifie si le mot de passe est vide
            if (empty($mdp)) {
                $validationExceptions[] = new ValidationException(
                    "mdp",
                    "string",
                    "Le mot de passe ne peut pas être vide."
                );
            }

            try {
                //  Si erreurs de validation
                if (count($validationExceptions) > 0) {
                    throw new ArrayException($validationExceptions);
                }

                //  Vérifie si l'utilisateur existe en base
                $userData = $User->findByEmail($email);

                if (!$userData) {
                    $validationExceptions[] = new EmailNotFoundException($email);
                    throw new ArrayException($validationExceptions);
                }

                // Vérifie le mot de passe
                if (!password_verify($mdp, $userData['mdp'])) {
                    $validationExceptions[] = new InvalidPasswordException();
                    throw new ArrayException($validationExceptions);
                }

                // Connexion réussie
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }

                $_SESSION['user'] = [
                    'id'      => $userData['id'],
                    'nom'     => $userData['nom'],
                    'prenom'  => $userData['prenom']
                ];

                header("Location: /");
                exit();

            } catch (ArrayException $exceptions) {
                // Erreurs → reste sur la page de connexion
                $view = new ConnectionView($exceptions->getExceptions());
                echo $view->render();
                return;
            }
        }
    }

    static function support(string $chemin, string $method): bool
    {
        return $chemin === "/user/login" && $method === "POST";
    }
}

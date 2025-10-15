<?php
namespace Controllers\User;

use Controllers\ControllerInterface;
use Models\User\User;
use Shared\Exceptions\ArrayException;
use Shared\Exceptions\ValidationException;
use Shared\Exceptions\EmailAlreadyExistsException;
use Shared\Exceptions\DataBaseException;
use Views\User\InscriptionView;

class RegisterPost implements ControllerInterface
{
    function control()
    {
        if (!isset($_POST['ok'])) return;

        $lastName  = $_POST['nom'] ?? '';
        $firstName = $_POST['prenom'] ?? '';
        $email     = $_POST['mail'] ?? '';
        $mdp       = $_POST['mdp'] ?? '';
        $role = $_POST['role'] ?? 'etudiant';


        $User = new User();
        $validationExceptions = [];

        // 1️⃣ Vérifie longueur mot de passe
        if (strlen($mdp) < 8 || strlen($mdp) > 20) {
            $validationExceptions[] = new ValidationException(
                "mdp", "string", "Le mot de passe doit contenir entre 8 et 20 caractères."
            );
        }

        try {
            // 2️⃣ Vérifie la BDD en priorité
            try {
                $User->emailExists($email);
            } catch (DataBaseException $dbEx) {
                throw new ArrayException([$dbEx]);
            } catch (EmailAlreadyExistsException $e) {
                $validationExceptions[] = new ValidationException("mail", "string", $e->getMessage());
            }


            if (count($validationExceptions) > 0) {
                throw new ArrayException($validationExceptions);
            }

            // 4️⃣ Inscription
            $User->register($firstName, $lastName, $email, $mdp, $role);


            // 5️⃣ Redirection ou message de succès
            header("Location: /user/login");
            exit();

        } catch (ArrayException $exceptions) {
            $view = new InscriptionView($exceptions->getExceptions());
            echo $view->render();
            return;
        }
    }

    static function support(string $chemin, string $method): bool
    {
        return $chemin === "/user/register" && $method === "POST";
    }
}

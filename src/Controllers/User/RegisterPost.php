<?php
namespace Controllers\User;

use Controllers\ControllerInterface ;
use Models\Database;
use Models\User\User;
use Shared\Exceptions\EmailAlreadyExistsException;
use Shared\Exceptions\ValidationException;
use Shared\Exceptions\ArrayException;

class RegisterPost implements ControllerInterface
{
    function control(){
        if (isset($_POST['ok'])) {
            $lastName    = $_POST['nom'] ?? '';
            $firstName = $_POST['prenom'] ?? '';
            $email   = $_POST['mail'] ?? '';
            $mdp    = $_POST['mdp'] ?? '';

        $User = new User() ;

        $validationsException = array();
        if (strlen($mdp) < 8 || strlen($mdp) > 20 ) {
            $exceptions = new ValidationException(
                "mdp" ,
                "string" ,
                "Invalid password length, it must be between 8 and 20 characters",

            );
            $validationsException[] = $exceptions;


        }

        try {
            try {
                $User->emailExists($email);

            }
            catch (EmailAlreadyExistsException $exception) {
//                $exceptions = new ValidationException(
//                    "mail" ,
//                    "string" ,
//                    $exception->getMessage(),
//
//                );
                $validationsException[] = $exception;
            }
            if(count($validationsException) > 0){
                throw new ArrayException($validationsException);
            }

        } catch (ArrayException $exceptions) {

            $view = new \Views\User\InscriptionView($exceptions->getExceptions());
            echo $view->render();
            return;
        }
            $User->register($lastName, $firstName, $email, $mdp);



        }
    }

    static function support(string $chemin, string $method) : bool{
        return $chemin === "/user/register" && $method === "POST";
    }

}
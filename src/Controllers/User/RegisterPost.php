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
               "Taille mdp invalide il doit etre compris entre 8 et 20",

            );
            $validationsException[] = $exceptions;


        }

        try {
            try {
                $User->register($lastName, $firstName, $email, $mdp);

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
        }



        }
    }

    static function support(string $chemin, string $method) : bool{
        return $chemin === "/user/register" && $method === "POST";
    }

}
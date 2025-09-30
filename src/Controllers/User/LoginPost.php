<?php
namespace Controllers\User;
use Controllers\ControllerInterface ;
use Models\User\User;
use Models\User\UserDTO;
use Views\User\ConnectionView;
use Views\User\UserView;


class LoginPost implements ControllerInterface
{
    function control(){
        $user = new User();
        $userDTO = $user->login($_POST[ConnectionView::USERNAME], $_POST[ConnectionView::PASSWORD]);
        if($userDTO !== null){
            // Repartir vers tableau de bord
            return ;
        }
        // repartir vers le formulaire
        $view = new UserView($user);
        $view->render();

    }

    static function support(string $chemin, string $method) : bool{
        return $chemin === "/user/login" && $method === "POST";
    }


}
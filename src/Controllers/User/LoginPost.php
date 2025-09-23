<?php
namespace Controllers\User;
use Controllers\ControllerInterface ; 
use Models\User\User;
use Views\User\LoginView;
use Views\User\UserView;


class LoginPost implements ControllerInterface
{
    function control(){
        $user = new User($_POST[LoginView::USERNAME], $_POST[LoginView::PASSWORD]);
        $view = new UserView($user);
        $view->render();

    }

    static function support(string $chemin, string $method) : bool{
        return $chemin === "/user/login" && $method === "POST";
    }


}
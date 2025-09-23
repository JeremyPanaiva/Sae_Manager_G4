<?php
namespace Controllers\User;
use Controllers\ControllerInterface ; 
use Models\User\UserDTO;
use Views\User\LoginView;

class Login implements ControllerInterface
{
    function control(){
        $view = new LoginView();
        $view->render();

    }

    static function support(string $chemin, string $method) : bool{
        return $chemin === "/user/login" && $method === "GET";
    }
}
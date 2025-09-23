<?php
namespace Controllers\User;
use Controllers\ControllerInterface ; 
use Models\User\User;
use Views\User\LoginView;

class Login implements ControllerInterface
{
    public const PATH = "/user/login";
    function control(){
        $view = new LoginView();
        $view->render();

    }

    static function support(string $chemin, string $method) : bool{
        return $chemin === self::PATH && $method === "GET";
    }
}
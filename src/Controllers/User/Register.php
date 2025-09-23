<?php
namespace Controllers\User;
use Controllers\ControllerInterface ; 

class Register implements ControllerInterface
{
    function control(){
        echo 'Sofien' . '<br>';
    }

    static function support(string $chemin, string $method) : bool{
        return $chemin === "/user/register" && $method === "GET";
    }

}
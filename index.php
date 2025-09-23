<?php
include "Autoloader.php";

use Controllers\User\Login;
use Controllers\User\Register;
use Controllers\User\LoginPost;

$controller = [new Login(), new Register(), new LoginPost()];



//$controller[$_SERVER['REQUEST_URI']]->control();

foreach ($controller as $key => $value) {
    if($value::support($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD'])){
        $value->control();
        exit();
    }


}

echo "Not Found";
    exit();

/*
if( === $_SERVER['REQUEST_URI']){
    $login = new Login();
    $login->control();
}
if(=== $_SERVER['REQUEST_URI']){
    $login = new Register();
    $login->control();
}*/
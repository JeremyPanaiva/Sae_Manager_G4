<?php
include "Autoloader.php";

use Controllers\Home\HomeController;
use Controllers\User\Login;
use Controllers\User\Register;
use Controllers\User\LoginPost;

$controllers = [new Login(), new Register(), new LoginPost(), new HomeController()];

// Gestion des routes via les controllers
foreach ($controllers as $controller) {
    if($controller::support($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD'])){
        $controller->control();
        exit();
    }
}

// Gestion de l’action inscription
if (isset($_GET['action']) && $_GET['action'] === 'inscription') {
    $view = new \Views\User\InscriptionView();
    $view->render();
    exit();
}

// Si aucune route trouvée
echo "Not Found";
exit();

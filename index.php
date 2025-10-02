<?php
include "Autoloader.php";

use Controllers\Home\HomeController;
use Controllers\User\Login;
use Controllers\User\Register;

// Démarrer la session dès le départ
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Liste des contrôleurs basés sur classes
$controllers = [new Login(), new Register(), new HomeController(), new \Controllers\User\RegisterPost(), new \Controllers\User\Logout()];

// Gestion des routes via les controllers
foreach ($controllers as $controller) {
    if ($controller::support($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD'])) {
        error_log(sprintf("controller utilisé: %s", $controller::class));
        $controller->control();
        exit();
    }
}


// Page d’accueil par défaut
$home = new HomeController();
$home->control();
exit();

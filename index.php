<?php
include "Autoloader.php";

use Controllers\Home\HomeController;
use Controllers\User\Login;
use Controllers\User\Register;
use Controllers\User\ForgotPassword;

// Démarrer la session dès le départ
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Liste des contrôleurs
$controllers = [
    new Login(),
    new Register(),
    new HomeController(),
    new \Controllers\User\RegisterPost(),
    new \Controllers\User\Logout(),
    new ForgotPassword(),
    new \Controllers\User\ForgotPasswordPost(),
    new \Controllers\User\ForgotPassword(),
    new \Controllers\User\ForgotPassword()
];

// Gestion des routes via les controllers
foreach ($controllers as $controller) {
    if ($controller::support($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD'])) {
        error_log(sprintf("controller utilisé: %s", $controller::class));
        $controller->control();
        exit();
    }
}

require_once __DIR__ . '/src/Controllers/User/ForgotPasswordPost.php';
require_once __DIR__ . '/src/Controllers/User/ResetPassword.php';
require_once __DIR__ . '/src/Controllers/User/ResetPasswordPost.php';

// Page d'accueil par défaut
$home = new HomeController();
$home->control();
exit();
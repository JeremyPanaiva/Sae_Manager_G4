<?php
include "Autoloader.php";

use Controllers\Home\HomeController;
use Controllers\User\Login;
use Controllers\User\Register;
use Controllers\User\ForgotPassword;
use Controllers\User\ListUsers; // 👈 ajoute ceci

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
    new ListUsers(), // 👈 ajoute ton contrôleur ici
];

// Récupérer uniquement le chemin (sans query string)
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Gestion des routes via les controllers
foreach ($controllers as $controller) {
    if ($controller::support($path, $_SERVER['REQUEST_METHOD'])) {
        error_log(sprintf("controller utilisé: %s", $controller::class));
        $controller->control();
        exit();
    }
}

// Page d'accueil par défaut
$home = new HomeController();
$home->control();
exit();

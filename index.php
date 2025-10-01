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
$controllers = [new Login(), new Register(), new HomeController()];

// Gestion des routes via les controllers
foreach ($controllers as $controller) {
    if ($controller::support($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD'])) {
        $controller->control();
        exit();
    }
}

// Gestion des routes procédurales (LoginPost)
if ($_SERVER['REQUEST_URI'] === '/user/login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/src/Controllers/User/LoginPost.php';
    exit();
}

// Gestion de la déconnexion
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    require_once __DIR__ . '/src/Controllers/User/Logout.php';
    exit();
}

// Gestion de l’inscription (Traitement.php)
if (isset($_GET['action']) && $_GET['action'] === 'inscription') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        require_once __DIR__ . '/src/Controllers/User/Traitement.php';
    } else {
        // Affichage du formulaire d'inscription
        $view = new \Views\User\InscriptionView();
        $view->render();
    }
    exit();
}

// Page d’accueil par défaut
$home = new HomeController();
$home->control();
exit();

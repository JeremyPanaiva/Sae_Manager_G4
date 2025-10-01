<?php
include "Autoloader.php";

use Controllers\Home\HomeController;
use Controllers\User\Login;
use Controllers\User\Register;

// Liste des contrôleurs basés sur des classes
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

// Gestion de l’action inscription
if (isset($_GET['action']) && $_GET['action'] === 'inscription') {
    $view = new \Views\User\InscriptionView();
    $view->render();
    exit();
}

// Si aucune route trouvée
echo "Not Found";
exit();

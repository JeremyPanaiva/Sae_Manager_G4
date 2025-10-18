<?php
// Inclure l'autoloader
require_once "Autoloader.php";
\Autoloader::register();

// Importer les controllers
use Controllers\Dashboard\TodoController;
use Controllers\Home\HomeController;
use Controllers\Legal\PlanDuSiteController;
use Controllers\Sae\AttribuerSaeController;
use Controllers\Sae\DeleteSaeController;
use Controllers\Sae\SaeController;
use Controllers\Sae\UpdateSaeDateController;
use Controllers\User\Login;
use Controllers\User\LoginPost;
use Controllers\User\Register;
use Controllers\User\RegisterPost;
use Controllers\User\Logout;
use Controllers\User\ForgotPassword;
use Controllers\User\ForgotPasswordPost;
use Controllers\User\ResetPassword;
use Controllers\User\ResetPasswordPost;
use Controllers\User\ListUsers;
use Controllers\Legal\MentionsLegalesController;
use Controllers\Dashboard\DashboardController;
use Controllers\Sae\CreateSaeController;


// Démarrer la session dès le départ
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Liste des contrôleurs
$controllers = [
    new HomeController(),
    new Login(),
    new LoginPost(),
    new Register(),
    new RegisterPost(),
    new Logout(),
    new ForgotPassword(),
    new ForgotPasswordPost(),
    new ResetPassword(),
    new ResetPasswordPost(),
    new ListUsers(),
    new MentionsLegalesController(),
    new PlanDuSiteController(),
    new DashboardController(),
    new SaeController(),
    new CreateSaeController() ,
    new AttribuerSaeController(),
    new DeleteSaeController(),
    new TodoController(),
    new UpdateSaeDateController()
];


// Récupérer uniquement le chemin de l'URL (sans query string)
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Parcourir les contrôleurs et exécuter celui qui supporte la route
foreach ($controllers as $controller) {
    if ($controller::support($path, $method)) {
        error_log(sprintf("Controller utilisé: %s", $controller::class));
        $controller->control();
        exit();
    }
}

// Page d'accueil par défaut si aucun contrôleur ne correspond
$home = new HomeController();
$home->control();
exit();

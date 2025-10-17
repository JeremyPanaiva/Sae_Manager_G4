<?php
// Affichage d'erreurs temporaire (à retirer en prod si besoin)
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Démarrer la session au plus tôt
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Autoloader du projet
$projectAutoloader = __DIR__ . '/Autoloader.php';
if (file_exists($projectAutoloader)) {
    require_once $projectAutoloader;
} else {
    error_log('Autoloader.php introuvable à ' . $projectAutoloader);
}

// Autoloader Composer (PHPMailer, etc.)
$composerAutoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
} else {
    error_log('vendor/autoload.php introuvable. Exécutez: composer install ou composer require phpmailer/phpmailer');
}

// Imports des contrôleurs
use Controllers\Home\HomeController;
use Controllers\User\Login;
use Controllers\User\Register;
use Controllers\User\RegisterPost;
use Controllers\User\Logout;
use Controllers\User\ForgotPassword;       // GET: /user/forgot-password
use Controllers\User\ForgotPasswordPost;   // POST: /user/forgot-password-post
use Controllers\User\ResetPassword;        // GET: /user/reset-password?token=...
use Controllers\User\ResetPasswordPost;    // POST: /user/reset-password

// Normaliser le chemin sans la query string
$path   = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Liste des contrôleurs (sans doublons et sans LoginPost, qui n'est pas une classe dans cette branche)
$controllers = [
    new Login(),               // GET /user/login
    new Register(),            // GET /user/register
    new RegisterPost(),        // POST /user/register
    new Logout(),              // GET /user/logout
    new ForgotPassword(),      // GET /user/forgot-password
    new ForgotPasswordPost(),  // POST /user/forgot-password-post
    new ResetPassword(),       // GET /user/reset-password?token=...
    new ResetPasswordPost(),   // POST /user/reset-password
    new HomeController(),      // GET /
];

try {
    foreach ($controllers as $controller) {
        if ($controller::support($path, $method)) {
            error_log(sprintf("Controller utilisé: %s pour %s %s", $controller::class, $method, $path));
            $controller->control();
            exit();
        }
    }

    // Fallback
    $home = new HomeController();
    $home->control();
} catch (Throwable $e) {
    error_log('Fatal error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    echo 'Une erreur est survenue. Consultez les logs pour plus de détails.';
}
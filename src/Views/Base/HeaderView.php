<?php

namespace Views\Base;

use Controllers\User\Login;
use Controllers\User\Logout;
use Views\AbstractView;

class HeaderView extends AbstractView
{
    public const USERNAME_KEY = 'USERNAME_KEY';
    public const LINK_KEY = 'LINK_KEY';
    public const INSCRIPTION_LINK_KEY = 'INSCRIPTION_LINK_KEY';
    public const CONNECTION_LINK_KEY = 'CONNECTION_LINK_KEY';

    public function __construct()
    {
        // Démarrer la session si nécessaire
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Chemin vers le template
    function templatePath(): string
    {
        return __DIR__ . '/header.html';
    }

    // Clés et valeurs à passer au template
    function templateKeys(): array
    {
        // Valeurs par défaut pour un utilisateur non connecté
        $username = 'Nom Prénom';
        $link = Login::PATH;
        $connectionText = 'Se connecter';

        // Si l'utilisateur est connecté
        if (isset($_SESSION['user']['nom'], $_SESSION['user']['prenom'])) {
            $username = $_SESSION['user']['nom'] . ' ' . $_SESSION['user']['prenom'];
            $link = Logout::PATH;
            $connectionText = 'Se déconnecter';
        }

        return [
            self::USERNAME_KEY => $username,
            self::LINK_KEY => $link,
            self::INSCRIPTION_LINK_KEY => '/user/register',
            self::CONNECTION_LINK_KEY => $connectionText,
        ];
    }
}

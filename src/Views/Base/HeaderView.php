<?php

namespace Views\Base;

use Controllers\User\Login;
use Views\AbstractView;

class HeaderView extends AbstractView
{
    public const USERNAME_KEY = 'USERNAME_KEY';
    public const LINK_KEY = 'LINK_KEY';
    public const INSCRIPTION_LINK_KEY = 'INSCRIPTION_LINK_KEY';

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
        $username = 'Nom Prénom';
        $link = Login::PATH; // lien vers la page login par défaut

        // Si l'utilisateur est connecté, afficher son nom et mettre le lien vers logout
        if (isset($_SESSION['user']['nom'], $_SESSION['user']['prenom'])) {
            $username = $_SESSION['user']['nom'] . ' ' . $_SESSION['user']['prenom'];
            $link = '/index.php?action=logout'; // <-- modification ici
        }

        return [
            self::USERNAME_KEY => $username,
            self::LINK_KEY => $link,
            self::INSCRIPTION_LINK_KEY => '/user/register',
        ];
    }
}

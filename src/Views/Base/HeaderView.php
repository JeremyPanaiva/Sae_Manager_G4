<?php

namespace Views\Base;

use Controllers\User\Login;
use Controllers\User\Logout;
use Controllers\User\ListUsers;
use Views\AbstractView;

class HeaderView extends AbstractView
{
    public const USERNAME_KEY = 'USERNAME_KEY';
    public const LINK_KEY = 'LINK_KEY';
    public const INSCRIPTION_LINK_KEY = 'INSCRIPTION_LINK_KEY';
    public const CONNECTION_LINK_KEY = 'CONNECTION_LINK_KEY';
    public const USERS_LINK_KEY = 'USERS_LINK_KEY'; // 👈 nouveau lien

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    function templatePath(): string
    {
        return __DIR__ . '/header.html';
    }

    function templateKeys(): array
    {
        $username = 'Nom Prénom';
        $link = Login::PATH;
        $connectionText = 'Se connecter';

        // Si l’utilisateur est connecté
        if (isset($_SESSION['user']['nom'], $_SESSION['user']['prenom'])) {
            $username = $_SESSION['user']['nom'] . ' ' . $_SESSION['user']['prenom'];
            $link = Logout::PATH;
            $connectionText = 'Se déconnecter';
            $usersLink = ListUsers::PATH; // Utilisateurs si connecté
        } else {
            $usersLink = Login::PATH; // redirige vers login si pas connecté
        }

        return [
            self::USERNAME_KEY => $username,
            self::LINK_KEY => $link,
            self::INSCRIPTION_LINK_KEY => '/user/register',
            self::CONNECTION_LINK_KEY => $connectionText,
            self::USERS_LINK_KEY => $usersLink,
        ];
    }
}

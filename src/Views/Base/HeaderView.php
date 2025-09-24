<?php

namespace Views\Base;

use Controllers\User\Login;
use Models\User\User;
use Views\AbstractView;

class HeaderView extends AbstractView
{


    public const USERNAME_KEY = 'USERNAME_KEY';
    public const LINK_KEY = 'LINK_KEY';
    private $user;

    public function __construct(?User $user = null)
    {
    }

    function templatePath(): string
    {
        return __DIR__ . '/header.html';
    }

    function templateKeys(): array
    {

        $isLogged = $this->user !== null;
        return [
            self::USERNAME_KEY => $this->user?->getUsername() ?? 'Nom Prenom',
            self::LINK_KEY => $isLogged  ? '/user/logout' : Login::PATH,
        ] ;
    }
}
<?php

namespace Views\User;
use Models\User\User;
use Views\AbstractView;

class LoginView extends AbstractView {

    public const USERNAME = "uname";
    public const PASSWORD = "psw";
    public const USERNAME_KEY = 'USERNAME_KEY';
    public const PASSWORD_KEY = 'PASSWORD_KEY';
    private const KEYS = [self::USERNAME_KEY => self::USERNAME, self::PASSWORD_KEY => self::PASSWORD];
    private const TEMPLATE_HTML = __DIR__ . '/form.html';

    public function templatePath() : string {
        return self::TEMPLATE_HTML; 
    }

    public function templateKeys() : array {
        return self::KEYS ; 
    }
}
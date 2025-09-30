<?php

namespace Views\User;
use Models\User\UserDTO;
use Views\AbstractView;
use Views\Base\BaseView;

class UserView extends BaseView {

    public const USERNAME = "uname";
    public const USERNAME_KEY = 'USERNAME_KEY';
    private const TEMPLATE_HTML = __DIR__ . '/user.html';

    public function __construct(private UserDTO $user){
    }

    public function templatePath() : string {
        return self::TEMPLATE_HTML; 
    }

    public function templateKeys() : array {
        return [self::USERNAME_KEY => $this->user->getUsername()] ;
    }
}
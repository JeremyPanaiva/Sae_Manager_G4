<?php

namespace Views\User;
use Views\Base\BaseView;

class ForgotPasswordView extends BaseView {

    public const EMAIL = "email";
    public const EMAIL_KEY = 'EMAIL_KEY';
    private const KEYS = [self::EMAIL_KEY => self::EMAIL];
    private const TEMPLATE_HTML = __DIR__ . '/forgot-password.html';

    public function templatePath() : string {
        return self::TEMPLATE_HTML;
    }

    public function templateKeys() : array {
        return self::KEYS;
    }
}
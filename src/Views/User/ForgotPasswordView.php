<?php

namespace Views\User;

use Views\Base\BaseView;

class ForgotPasswordView extends BaseView {

    private const TEMPLATE_HTML = __DIR__ . '/forgot-password.html';

    public function templatePath(): string {
        return self::TEMPLATE_HTML;
    }

    public function templateKeys(): array {
        return [];
    }
}
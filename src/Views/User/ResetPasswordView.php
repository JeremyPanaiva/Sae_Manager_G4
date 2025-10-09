<?php

namespace Views\User;

use Views\Base\BaseView;

class ResetPasswordView extends BaseView {

    private const TEMPLATE_HTML = __DIR__ . '/reset-password.html';
    private string $token;

    public function __construct(string $token) {
        parent::__construct();
        $this->token = $token;
    }

    public function templatePath(): string {
        return self::TEMPLATE_HTML;
    }

    public function templateKeys(): array {
        return [
            'TOKEN_KEY' => $this->token
        ];
    }
}
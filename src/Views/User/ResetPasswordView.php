<?php

namespace Views\User;

use Views\Base\BaseView;

class ResetPasswordView extends BaseView {

    private const TEMPLATE_HTML = __DIR__ . '/reset-password.html';
    private string $token;

    public function __construct(string $token) {
        parent::__construct();
        $this->token = htmlspecialchars($token, ENT_QUOTES, 'UTF-8');
    }

    public function templatePath(): string {
        return self::TEMPLATE_HTML;
    }

    public function templateKeys(): array {
        $flashMessage = '';

        if (isset($_SESSION['flash'])) {
            $type = $_SESSION['flash']['type'];
            $message = htmlspecialchars($_SESSION['flash']['message']);
            $flashMessage = "<div class='alert alert-" . ($type === 'success' ? 'success' : 'error') . "'>$message</div>";
            unset($_SESSION['flash']);
        }

        return [
            'TOKEN_KEY' => $this->token,
            'FLASH_MESSAGE' => $flashMessage
        ];
    }
}
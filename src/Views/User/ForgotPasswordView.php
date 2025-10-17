<?php

namespace Views\User;

use Views\Base\BaseView;

class ForgotPasswordView extends BaseView {

    private const TEMPLATE_HTML = __DIR__ . '/forgot-password.html';

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
            'FLASH_MESSAGE' => $flashMessage
        ];
    }
}
<?php

namespace Views\Dashboard;

use Views\Base\BaseView;

class DashboardView extends BaseView
{
    public const TITLE_KEY = 'TITLE_KEY';
    public const CONTENT_KEY = 'CONTENT_KEY';

    // Clés pour le header
    public const USERNAME_KEY = 'USERNAME_KEY';
    public const ROLE_KEY = 'ROLE_KEY';
    public const ROLE_CLASS = 'ROLE_CLASS';
    public const LINK_KEY = 'LINK_KEY';
    public const INSCRIPTION_LINK_KEY = 'INSCRIPTION_LINK_KEY';
    public const CONNECTION_LINK_KEY = 'CONNECTION_LINK_KEY';
    public const USERS_LINK_KEY = 'USERS_LINK_KEY';

    private string $title;
    private string $content;

    public function __construct(string $title, string $content)
    {
        $this->title = $title;
        $this->content = $content;
    }

    public function templatePath(): string
    {
        return __DIR__ . '/dashboard.html';
    }

    public function templateKeys(): array
    {
        // On récupère le header
        $headerView = new \Views\Base\HeaderView();
        $headerKeys = $headerView->templateKeys();

        return array_merge($headerKeys, [
            self::TITLE_KEY => $this->title,
            self::CONTENT_KEY => $this->content,
        ]);
    }
}

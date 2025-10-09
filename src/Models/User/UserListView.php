<?php

namespace Views\User;

use Views\Base\BaseView;

class UserListView extends BaseView
{
    private const TEMPLATE_HTML = __DIR__ . '/user.html';

    public const USERS_ROWS_KEY = 'USERS_ROWS';
    public const PAGINATION_KEY = 'PAGINATION';
    public const USERNAME_KEY = 'USERNAME_KEY';
    public const LINK_KEY = 'LINK_KEY';
    public const INSCRIPTION_LINK_KEY = 'INSCRIPTION_LINK_KEY';
    public const CONNECTION_LINK_KEY = 'CONNECTION_LINK_KEY';

    private array $users;
    private string $paginationHtml;
    private array $headerData;

    public function __construct(array $users, string $paginationHtml = '', array $headerData = [])
    {
        $this->users = $users;
        $this->paginationHtml = $paginationHtml;
        $this->headerData = $headerData;
    }

    public function templatePath(): string
    {
        return self::TEMPLATE_HTML;
    }

    public function templateKeys(): array
    {
        $rowsHtml = '';
        foreach ($this->users as $user) {
            $rowsHtml .= "<tr><td>{$user['nom']}</td><td>{$user['prenom']}</td></tr>";
        }

        return [
            self::USERS_ROWS_KEY => $rowsHtml,
            self::PAGINATION_KEY => $this->paginationHtml,
            self::USERNAME_KEY => $this->headerData['USERNAME_KEY'] ?? 'Nom Prénom',
            self::LINK_KEY => $this->headerData['LINK_KEY'] ?? '/user/login',
            self::INSCRIPTION_LINK_KEY => $this->headerData['INSCRIPTION_LINK_KEY'] ?? '/user/register',
            self::CONNECTION_LINK_KEY => $this->headerData['CONNECTION_LINK_KEY'] ?? 'Se connecter',
        ];
    }
}

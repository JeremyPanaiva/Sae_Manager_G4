<?php

namespace Controllers\Dashboard;

use Controllers\ControllerInterface;
use Views\Dashboard\DashboardView;

class DashboardController implements ControllerInterface
{
    public const PATH = '/dashboard'; // 👈 ajouter cette ligne

    public function control()
    {
        $view = new \Views\Dashboard\DashboardView(
            title: 'Bienvenue sur votre tableau de bord',
            content: '<p>Ici tu peux voir tes SAEs, notifications, etc.</p>'
        );

        echo $view->render();

    }

    public static function support(string $path, string $method): bool
    {
        return $path === self::PATH && $method === 'GET';
    }
}

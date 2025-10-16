<?php
namespace Controllers\Dashboard;

use Controllers\ControllerInterface;
use Models\Sae\SaeAttribution;
use Models\Sae\TodoList;
use Views\Dashboard\DashboardView;

class DashboardController implements ControllerInterface
{
    public const PATH = '/dashboard';

    public function control()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit();
        }

        $user = $_SESSION['user'];
        $role = strtolower($user['role']);
        $username = $user['nom'] . ' ' . $user['prenom'];
        $userId = $user['id'];

        $data = $this->prepareDashboardData($userId, $role);

        $view = new DashboardView(
            title: 'Tableau de bord',
            username: $username,
            role: ucfirst($role),
            data: $data
        );

        echo $view->render();
    }

    private function prepareDashboardData(int $userId, string $role): array
    {
        if ($role === 'etudiant') {
            // Récupération des SAE pour l'étudiant
            $saes = SaeAttribution::getSaeForStudent($userId);

            foreach ($saes as &$sae) {
                $saeAttributionId = $sae['sae_attribution_id'] ?? null;
                $sae['todos'] = $sae['sae_id'] ? TodoList::getBySae($sae['sae_id']) : [];
                $sae['etudiants'] = $sae['sae_id'] ? SaeAttribution::getStudentsBySae($sae['sae_id']) : [];
            }
        } elseif ($role === 'responsable') {
            // Récupération des SAE attribuées par le responsable
            $saes = SaeAttribution::getSaeForResponsable($userId);

            foreach ($saes as &$sae) {
                $saeId = $sae['sae_id'] ?? null;

                // Récupérer **toutes les tâches de la SAE** (lecture seule)
                $sae['todos'] = $saeId ? TodoList::getBySae($saeId) : [];

                // Tous les étudiants associés à cette SAE
                $sae['etudiants'] = $saeId ? SaeAttribution::getStudentsBySae($saeId) : [];
            }
        }
        else {
            $saes = [];
        }

        return ['saes' => $saes];
    }




    public static function support(string $path, string $method): bool
    {
        return $path === self::PATH && $method === 'GET';
    }
}

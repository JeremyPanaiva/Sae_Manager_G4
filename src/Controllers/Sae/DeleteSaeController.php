<?php


namespace Controllers\Sae;

use Controllers\ControllerInterface;
use Models\Sae\Sae;

class DeleteSaeController implements ControllerInterface
{
    public const PATH = '/delete_sae';

    public function control()
    {
        // Vérifie que la requête est bien POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /sae');
            exit();
        }

        // Vérifie que l'utilisateur est un client connecté
        if (!isset($_SESSION['user']) || strtolower($_SESSION['user']['role']) !== 'client') {
            header('HTTP/1.1 403 Forbidden');
            echo "Accès refusé";
            exit();
        }

        $clientId = intval($_SESSION['user']['id']);
        $saeId = intval($_POST['sae_id'] ?? 0);

        if ($saeId <= 0) {
            header('Location: /sae?error=invalid_id');
            exit();
        }

        // Supprimer la SAE (uniquement si elle appartient au client)
        Sae::delete($clientId, $saeId);

        // Redirection avec message de succès
        header('Location: /sae?success=sae_deleted');
        exit();
    }

    public static function support(string $path, string $method): bool
    {
        return $path === self::PATH && $method === 'POST';
    }
}

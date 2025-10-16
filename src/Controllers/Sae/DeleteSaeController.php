<?php

namespace Controllers\Sae;

use Controllers\ControllerInterface;
use Models\Sae\Sae;
use Shared\Exceptions\SaeAttribueException;
use Views\Sae\SaeView;

class DeleteSaeController implements ControllerInterface
{
    public const PATH = '/delete_sae';

    public function control()
    {
        // Vérifie que la requête est POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /sae');
            exit();
        }

        // Vérifie que l'utilisateur est un client
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

        try {
            // Récupère la SAE
            $sae = Sae::getById($saeId);
            if (!$sae) {
                header('Location: /sae?error=sae_not_found');
                exit();
            }

            // Vérifie si la SAE est déjà attribuée
            if (Sae::isAttribuee($saeId)) {
                throw new SaeAttribueException($sae['titre']);
            }

            // Supprimer la SAE
            Sae::delete($clientId, $saeId);

            // Redirection avec succès
            header('Location: /sae?success=sae_deleted');
            exit();

        } catch (\Throwable $e) {
            // Récupère toutes les SAE du client
            $saes = Sae::getByClient($clientId);
            $username = $_SESSION['user']['nom'] . ' ' . $_SESSION['user']['prenom'];
            $role = $_SESSION['user']['role'];

            // Prépare les données pour la vue
            $data = [
                'saes' => $saes,
                'error_message' => $e->getMessage(),
            ];

            // Affiche la page SAE avec le message d'erreur
            $view = new SaeView("Gestion des SAE", $data, $username, $role);
            echo $view->render();
            exit();
        }
    }

    public static function support(string $path, string $method): bool
    {
        return $path === self::PATH && $method === 'POST';
    }
}

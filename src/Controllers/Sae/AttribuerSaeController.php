<?php

namespace Controllers\Sae;

use Controllers\ControllerInterface;
use Models\Sae\SaeAttribution;

class AttribuerSaeController implements ControllerInterface
{
    public const PATH = '/attribuer_sae';

    public function control()
    {
        // Vérifier que la requête est POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /sae');
            exit();
        }

        // Vérifier que l'utilisateur est connecté et est responsable
        if (!isset($_SESSION['user']) || strtolower($_SESSION['user']['role']) !== 'responsable') {
            header('HTTP/1.1 403 Forbidden');
            echo "Accès refusé";
            exit();
        }

        $saeId = intval($_POST['sae_id'] ?? 0);
        $etudiants = $_POST['etudiants'] ?? [];
        $responsableId = $_SESSION['user']['id'];

        // Vérifier que tous les champs sont remplis
        if ($saeId <= 0 || empty($etudiants)) {
            header('Location: /sae?error=missing_fields');
            exit();
        }

        // Attribution unique pour tous les étudiants sélectionnés
        SaeAttribution::assignStudentsToSae(
            $saeId,
            array_map('intval', $etudiants),
            $responsableId
        );

        // Redirection avec succès
        header('Location: /sae?success=sae_assigned');
        exit();
    }

    public static function support(string $path, string $method): bool
    {
        return $path === self::PATH && $method === 'POST';
    }
}

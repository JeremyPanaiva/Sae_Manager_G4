<?php

namespace Models\Sae;

use Models\Database;

class Sae
{
    public static function create(int $clientId, string $titre, string $description): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO sae (titre, description, client_id, date_creation) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("ssi", $titre, $description, $clientId);
        $stmt->execute();
        $stmt->close();
    }
    public static function getAllProposed(): array
    {
        $db = Database::getConnection();
        $result = $db->query("SELECT * FROM sae");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public static function getByClient(int $clientId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM sae WHERE client_id = ?");
        $stmt->bind_param('i', $clientId);
        $stmt->execute();

        $result = $stmt->get_result();
        $saes = $result->fetch_all(MYSQLI_ASSOC);

        $stmt->close();
        return $saes;
    }
    public static function delete(int $clientId, int $saeId): bool
    {
        $mysqli = \Models\Database::getConnection(); // mysqli

        // Supprime uniquement de la table SAE
        $stmt = $mysqli->prepare("DELETE FROM sae WHERE id = ? AND client_id = ?");
        if (!$stmt) {
            throw new \Exception("Erreur prepare: " . $mysqli->error);
        }

        $stmt->bind_param("ii", $saeId, $clientId);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    public static function isAttribuee(int $saeId): bool
    {
        $db = \Models\Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM sae_attributions WHERE sae_id = ?");
        $stmt->bind_param("i", $saeId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result['count'] > 0;
    }

    public static function getById(int $saeId): ?array
    {
        $db = \Models\Database::getConnection();

        $stmt = $db->prepare("SELECT * FROM sae WHERE id = ?");
        $stmt->bind_param("i", $saeId);
        $stmt->execute();
        $result = $stmt->get_result();
        $sae = $result->fetch_assoc();
        $stmt->close();

        return $sae ?: null; // Retourne null si pas trouvé
    }


}

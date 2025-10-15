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
}

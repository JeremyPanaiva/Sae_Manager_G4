<?php
namespace Models\Sae;

use Models\Database;

class SaeAttribution
{
    public static function assignToStudent(int $saeId, int $studentId, int $responsableId, string $dateRendu): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
        INSERT INTO sae_attributions (sae_id, student_id, responsable_id, date_rendu)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE date_rendu = VALUES(date_rendu)
    ");
        $stmt->bind_param("iiis", $saeId, $studentId, $responsableId, $dateRendu);
        $stmt->execute();
        $stmt->close();
    }

    public static function getSaeForStudent(int $studentId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
        SELECT 
            s.id AS sae_id,
            s.titre AS sae_titre,
            s.description AS sae_description,
            u_resp.nom AS responsable_nom,
            u_resp.prenom AS responsable_prenom,
            u_resp.mail AS responsable_mail,
            u_client.nom AS client_nom,
            u_client.prenom AS client_prenom,
            u_client.mail AS client_mail,
            sa.date_rendu
        FROM sae s
        JOIN sae_attributions sa ON s.id = sa.sae_id
        LEFT JOIN users u_resp ON sa.responsable_id = u_resp.id
        LEFT JOIN users u_client ON s.client_id = u_client.id
        WHERE sa.student_id = ?
    ");
        $stmt->bind_param("i", $studentId);
        $stmt->execute();
        $result = $stmt->get_result();
        $saes = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $saes;
    }


}

<?php
namespace Models\Sae;

use Models\Database;

class SaeAttribution
{
    public static function assignStudentsToSae(int $saeId, array $studentIds, int $responsableId): void
    {
        $db = Database::getConnection();

        // Vérifier si une attribution existe déjà pour cette SAE et ce responsable
        $stmt = $db->prepare("SELECT date_rendu FROM sae_attributions WHERE sae_id = ? AND responsable_id = ? LIMIT 1");
        $stmt->bind_param("ii", $saeId, $responsableId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $dateRendu = $row['date_rendu']; // réutiliser la date existante
        } else {
            $dateRendu = date('Y-m-d'); // sinon on met aujourd'hui
        }
        $stmt->close();

        foreach ($studentIds as $studentId) {
            // Vérifie si cet étudiant a déjà cette SAE pour ce responsable
            $stmtCheck = $db->prepare("
            SELECT id FROM sae_attributions 
            WHERE sae_id = ? AND student_id = ? AND responsable_id = ?
        ");
            $stmtCheck->bind_param("iii", $saeId, $studentId, $responsableId);
            $stmtCheck->execute();
            $resultCheck = $stmtCheck->get_result();

            if (!$resultCheck->fetch_assoc()) {
                // Ajouter l'étudiant avec la date de rendu existante
                $stmtInsert = $db->prepare("
                INSERT INTO sae_attributions (sae_id, student_id, responsable_id, date_rendu)
                VALUES (?, ?, ?, ?)
            ");
                $stmtInsert->bind_param("iiis", $saeId, $studentId, $responsableId, $dateRendu);
                $stmtInsert->execute();
                $stmtInsert->close();
            }

            $stmtCheck->close();
        }
    }


    public static function updateDateRendu(int $saeId, int $responsableId, string $newDate): void
    {
        $db = \Models\Database::getConnection();

        // Mettre à jour la date de rendu pour **tous les étudiants** de cette SAE et ce responsable
        $stmt = $db->prepare("
        UPDATE sae_attributions 
        SET date_rendu = ? 
        WHERE sae_id = ? AND responsable_id = ?
    ");
        $stmt->bind_param("sii", $newDate, $saeId, $responsableId);
        $stmt->execute();
        $stmt->close();
    }


    public static function getSaeForStudent(int $studentId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT 
                sa.id AS sae_attribution_id,
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

    public static function getStudentsBySae(int $saeId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT u.nom, u.prenom
            FROM users u
            JOIN sae_attributions sa ON sa.student_id = u.id
            WHERE sa.sae_id = ?
        ");
        $stmt->bind_param("i", $saeId);
        $stmt->execute();
        $result = $stmt->get_result();
        $students = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $students;
    }

    public static function getSaeForResponsable(int $responsableId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT 
                MIN(sa.id) AS sae_attribution_id,
                s.id AS sae_id,
                s.titre AS sae_titre,
                s.description AS sae_description,
                sa.date_rendu,
                GROUP_CONCAT(CONCAT(u.nom,' ',u.prenom) SEPARATOR ', ') AS etudiants
            FROM sae_attributions sa
            JOIN sae s ON s.id = sa.sae_id
            JOIN users u ON u.id = sa.student_id
            WHERE sa.responsable_id = ?
            GROUP BY sa.sae_id, s.titre, s.description, sa.date_rendu
        ");
        $stmt->bind_param("i", $responsableId);
        $stmt->execute();
        $result = $stmt->get_result();
        $saes = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $saes;
    }
}

<?php

namespace Models\User;


use Models\Database;
use Shared\Exceptions\EmailAlreadyExistsException;

class User {

    function emailExists(string $email)
    {
        $conn = Database::getConnection();

        $checkStmt = $conn->prepare("SELECT id FROM users WHERE mail = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            throw new EmailAlreadyExistsException($email);
        }

        $checkStmt->close();
    }

    function register($firstName, $lastName, $email, $password): void
    {
        $conn = Database::getConnection();

        // Hasher le mot de passe
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $insertStmt = $conn->prepare("INSERT INTO users (nom, prenom, mail, mdp) VALUES (?, ?, ?, ?)");
        $insertStmt->bind_param("ssss", $lastName, $firstName, $email, $hashedPassword);

        if ($insertStmt->execute()) {
            echo "<p style='color:green;'>Inscription réussie ! Vous pouvez maintenant vous connecter.</p>";
            header("refresh:3;url=/index.php?action=connexion");
            exit();
        } else {
            echo "<p style='color:red;'>Erreur lors de l'inscription.</p>";
            exit();
        }

        $insertStmt->close();
        $conn->close();
    }

    public function getUsersPaginated(int $limit, int $offset): array
    {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("SELECT nom, prenom FROM users ORDER BY date_creation asc LIMIT ? OFFSET ?");
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();

        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }

        $stmt->close();

        return $users;
    }

    public function countUsers(): int
    {
        $conn = Database::getConnection();
        $result = $conn->query("SELECT COUNT(*) AS total FROM users");
        $count = $result->fetch_assoc()['total'];
        // ❌ Ne pas fermer la connexion ici non plus
        return $count;
    }

}

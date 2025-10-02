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
}

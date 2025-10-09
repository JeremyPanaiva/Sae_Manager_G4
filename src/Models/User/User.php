<?php

namespace Models\User;

use Models\Database;
use Shared\Exceptions\EmailAlreadyExistsException;
use Shared\Exceptions\DataBaseException;

class User
{
    /**
     * Vérifie si un email existe déjà
     *
     * @throws EmailAlreadyExistsException
     * @throws DataBaseException
     */
    public function emailExists(string $email): void
    {
        try {
            $conn = Database::getConnection();
        } catch (\Throwable $e) {
            throw new DataBaseException("Impossible de se connecter à la base de données.");
        }

        $stmt = $conn->prepare("SELECT id FROM users WHERE mail = ?");
        if (!$stmt) {
            throw new DataBaseException("Erreur lors de la préparation de la requête SQL (emailExists).");
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->close();
            throw new EmailAlreadyExistsException($email);
        }

        $stmt->close();
        $conn->close();
    }

    /**
     * Enregistre un nouvel utilisateur
     *
     * @throws DataBaseException
     */
    public function register(string $lastName, string $firstName, string $email, string $password): void
    {
        try {
            $conn = Database::getConnection();
        } catch (\Throwable $e) {
            throw new DataBaseException("Impossible de se connecter à la base de données.");
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (nom, prenom, mail, mdp) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            throw new DataBaseException("Erreur lors de la préparation de la requête SQL (register).");
        }

        if (!$stmt->bind_param("ssss", $lastName, $firstName, $email, $hashedPassword) || !$stmt->execute()) {
            throw new DataBaseException("Erreur lors de l’exécution de la requête SQL (register).");
        }

        $stmt->close();
        $conn->close();
    }

    /**
     * Récupère un utilisateur par email
     *
     * @throws DataBaseException
     */
    public function findByEmail(string $email): ?array
    {
        try {
            $conn = Database::getConnection();
        } catch (\Throwable $e) {
            throw new DataBaseException("Impossible de se connecter à la base de données.");
        }

        $stmt = $conn->prepare("SELECT id, mdp, nom, prenom, mail FROM users WHERE mail = ?");
        if (!$stmt) {
            throw new DataBaseException("Erreur lors de la préparation de la requête SQL (findByEmail).");
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        $user = $result->fetch_assoc() ?: null;

        $stmt->close();
        $conn->close();

        return $user;
    }
}

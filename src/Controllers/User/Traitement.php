<?php
require_once __DIR__ . '/../../Models/Database.php';
use Models\Database;

if (isset($_POST['ok'])) {
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $mail = $_POST['mail'] ?? '';
    $mdp = $_POST['mdp'] ?? ''; // mot de passe en clair

    $conn = Database::getConnection();

    // Vérifier si l'email existe déjà
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE mail = ?");
    $checkStmt->bind_param("s", $mail);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        // Email déjà utilisé : redirection vers le formulaire avec message
        echo '<p style="
    color: red; 
    font-size: 24px; 
    font-weight: bold; 
    text-align: center; 
    margin-top: 20%;
">Email déjà existant</p>';

        header("refresh:3;url=/index.php?action=inscription");
        exit();

    } else {
        // Insérer l'utilisateur
        $stmt = $conn->prepare("INSERT INTO users (nom, prenom, mail, mdp) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nom, $prenom, $mail, $mdp);

        if ($stmt->execute()) {
            echo "<p>Inscription réussie ! Redirection vers l'accueil...</p>";
            header("refresh:3;url=/"); // redirection vers home
            exit();
        } else {
            echo "<p>Erreur : " . $stmt->error . "</p>";
        }

        $stmt->close();
    }

    $checkStmt->close();
    $conn->close();
}

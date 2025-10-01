<?php
require_once __DIR__ . '/../../Models/Database.php';
use Models\Database;

if (isset($_POST['ok'])) {
    $nom    = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $mail   = $_POST['mail'] ?? '';
    $mdp    = $_POST['mdp'] ?? '';

    $conn = Database::getConnection();

    // Vérifier si l'email existe déjà
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE mail = ?");
    $checkStmt->bind_param("s", $mail);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        echo "<p style='color:red;'>Cet email existe déjà !</p>";
        exit();
    }

    // Insérer le nouvel utilisateur (mot de passe en clair)
    $insertStmt = $conn->prepare("INSERT INTO users (nom, prenom, mail, mdp) VALUES (?, ?, ?, ?)");
    $insertStmt->bind_param("ssss", $nom, $prenom, $mail, $mdp);
    if ($insertStmt->execute()) {
        echo "<p style='color:green;'>Inscription réussie ! Vous pouvez maintenant vous connecter.</p>";
        header("refresh:3;url=/index.php?action=connexion"); // redirection optionnelle
        exit();
    } else {
        echo "<p style='color:red;'>Erreur lors de l'inscription.</p>";
        exit();
    }

    $checkStmt->close();
    $insertStmt->close();
    $conn->close();
}

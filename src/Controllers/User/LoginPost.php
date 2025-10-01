<?php
require_once __DIR__ . '/../../Models/Database.php';
use Models\Database;

// Démarrage de session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_POST['ok'])) {
    $mail = $_POST['uname'] ?? '';
    $mdp  = $_POST['psw'] ?? '';

    $conn = Database::getConnection();

    // Vérifier si l'email existe
    $stmt = $conn->prepare("SELECT id, mdp, nom, prenom FROM users WHERE mail = ?");
    $stmt->bind_param("s", $mail);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $storedMdp, $nom, $prenom);
        $stmt->fetch();

        // ⚠ Ici comparaison directe, à remplacer par password_verify si tu hashes les mots de passe
        if ($mdp === $storedMdp) {

            // Stocker les infos utilisateur dans la session
            $_SESSION['user'] = [
                'id' => $id,
                'nom' => $nom,
                'prenom' => $prenom
            ];

            // Message succès
            echo '<p style="
                color: green; 
                font-size: 24px; 
                font-weight: bold; 
                text-align: center; 
                margin-top: 20%;
            ">Connexion réussie !</p>';

            header("refresh:2;url=/"); // redirection vers l'accueil
            exit();

        } else {
            // Mot de passe incorrect
            echo '<p style="
                color: red; 
                font-size: 24px; 
                font-weight: bold; 
                text-align: center; 
                margin-top: 20%;
            ">Mot de passe incorrect</p>';

            header("refresh:3;url=/user/login");
            exit();
        }
    } else {
        // Email non trouvé
        echo '<p style="
            color: red; 
            font-size: 24px; 
            font-weight: bold; 
            text-align: center; 
            margin-top: 20%;
        ">Email non trouvé</p>';

        header("refresh:3;url=/user/login");
        exit();
    }

    $stmt->close();
    $conn->close();
}

<?php
require_once __DIR__ . '/../../Models/Database.php';
use Models\Database;

if (isset($_POST['ok'])) {
    $mail = $_POST['uname'] ?? '';
    $mdp  = $_POST['psw'] ?? '';

    $conn = Database::getConnection();

    // Vérifier si l'email existe
    $checkStmt = $conn->prepare("SELECT id, mdp FROM users WHERE mail = ?");
    $checkStmt->bind_param("s", $mail);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        $checkStmt->bind_result($id, $storedMdp);
        $checkStmt->fetch();

        // ⚠ Tu n’as pas hashé le mot de passe à l'inscription → comparaison directe
        if ($mdp === $storedMdp) {
            echo '<p style="
                color: green; 
                font-size: 24px; 
                font-weight: bold; 
                text-align: center; 
                margin-top: 20%;
            ">Connexion réussie !</p>';

            header("refresh:2;url=/"); // redirige vers l'accueil
            exit();
        } else {
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

    $checkStmt->close();
    $conn->close();
}

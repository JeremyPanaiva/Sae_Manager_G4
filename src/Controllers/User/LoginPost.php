<?php
namespace Controllers\User;

require_once __DIR__ . '/../../Models/Database.php';
use Models\Database;

// Démarrage de session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ok'])) {
    $mail = trim($_POST['uname'] ?? '');
    $mdp  = $_POST['psw'] ?? '';

    // Vérification des champs
    if (empty($mail) || empty($mdp)) {
        $_SESSION['flash'] = [
            'type' => 'error',
            'message' => 'Veuillez remplir tous les champs.'
        ];
        header("Location: /user/login");
        exit();
    }

    $conn = Database::getConnection();

    if (!$conn) {
        $_SESSION['flash'] = [
            'type' => 'error',
            'message' => 'Erreur de connexion à la base de données.'
        ];
        header("Location: /user/login");
        exit();
    }

    // Préparation de la requête
    $stmt = $conn->prepare("SELECT id, mdp, nom, prenom FROM users WHERE mail = ?");
    if (!$stmt) {
        $_SESSION['flash'] = [
            'type' => 'error',
            'message' => 'Erreur lors de la préparation de la requête.'
        ];
        header("Location: /user/login");
        exit();
    }

    $stmt->bind_param("s", $mail);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        // Email non trouvé
        $_SESSION['flash'] = [
            'type' => 'error',
            'message' => 'Email ou mot de passe incorrect.'
        ];
        header("Location: /user/login");
        exit();
    }

    $stmt->bind_result($id, $storedMdp, $nom, $prenom);
    $stmt->fetch();

    // Vérification du mot de passe
    if (password_verify($mdp, $storedMdp)) {
        $_SESSION['user'] = [
            'id' => $id,
            'nom' => $nom,
            'prenom' => $prenom
        ];

        $_SESSION['flash'] = [
            'type' => 'success',
            'message' => 'Connexion réussie !'
        ];

        header("Location: /"); // redirection vers l'accueil
        exit();
    } else {
        $_SESSION['flash'] = [
            'type' => 'error',
            'message' => 'Email ou mot de passe incorrect.'
        ];
        header("Location: /user/login");
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    // Accès direct au script
    header("Location: /user/login");
    exit();
}

<?php
// Connexion à la base Alwaysdata
$servername = "mysql-sae-manager-g4.alwaysdata.net";  // host Alwaysdata
$username = "432905_jeremy";
$password = "saemanager-g4!";            // ton mot de passe MySQL
$dbname = "sae-manager-g4_db";

// Créer la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connexion échouée: " . $conn->connect_error);
}
echo "Connexion réussie !<br>";

// Tester : récupérer tous les utilisateurs
$result = $conn->query("SELECT id, nom, prenom, mail FROM users");

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo $row['id'] . " - " . $row['prenom'] . " " . $row['nom'] . " <" . $row['mail'] . "><br>";
    }
} else {
    echo "Aucun utilisateur trouvé.";
}
?>

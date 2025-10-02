<?php

namespace Models\User;

use Models\Database;
use Shared\Exceptions\EmailAlreadyExistsException;

class User {
   function register($firstName, $lastName, $email, $password): void
   {
       $conn = Database::getConnection();

       // Vérifier si l'email existe déjà
       $checkStmt = $conn->prepare("SELECT id FROM users WHERE mail = ?");
       $checkStmt->bind_param("s", $email);
       $checkStmt->execute();
       $checkStmt->store_result();

       if ($checkStmt->num_rows > 0) {
           error_log(sprintf("E-mail deja utilisé %s", $email));
           throw new EmailAlreadyExistsException($email);

       }

       error_log(sprintf("Nouveau utilisateur %s %s %s %s", $firstName, $lastName, $email,  $password));
       // Insérer le nouvel utilisateur (mot de passe en clair)
       $insertStmt = $conn->prepare("INSERT INTO users (nom, prenom, mail, mdp) VALUES (?, ?, ?, ?)");
       $insertStmt->bind_param("ssss", $lastName, $firstName, $email, $password);
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
}

<?php

$dsn = 'pgsql:dbname=projet_doct;host=localhost;port=5432';
$user = 'postgres';
$password = 'Isen44';

try { //connexion à la base de données
    $conn = new PDO($dsn, $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    

    //Vérifier si le formulaire a été envoyé

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // Récupérer les données du formulaire
        $nom = $_POST['nom'];
        $prenom = $_POST['prenom'];
        
        // Vérification des emails
        if ($_POST['mail1'] != $_POST['mail2']) {
            die("Les adresses emails ne correspondent pas.");
        }
        $email = $_POST['mail1'];

        // Vérification des mots de passe
        if ($_POST['motdepasse1'] != $_POST['motdepasse2']) {
            die("Les mots de passe ne correspondent pas.");
        }
        $motDePasse = password_hash($_POST['motdepasse1'], PASSWORD_DEFAULT);
        
        $telephone = $_POST['telephone'];
        $medecin = false; // 0 pour patient, 1 pour médecin - par défaut c'est false

        //Liaisons des paramètres
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':prenom', $prenom);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':motDePasse', $motDePasse);
        $stmt->bindParam(':medecin', $medecin, PDO::PARAM_BOOL);
        $stmt->bindParam(':telephone', $telephone);

        //Exécution de la requête d'insertion
        $stmt->execute();
        header("Location: ../html/connexion_patient.html");
    }
    else {
        echo "Veuillez soumettre le formulaire.";
    }
} catch (PDOException $e) {
    die('Connexion échouée : ' . $e->getMessage());
}

//Fermeture de la connexion à la base de données
$conn = null;

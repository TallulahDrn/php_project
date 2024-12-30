<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Récupérer les données du formulaire
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['mail1'];
    $motDePasse = password_hash($_POST['motdepasse1'], PASSWORD_DEFAULT);
    $telephone = $_POST['telephone'];
    $medecin = 'false'; // 0 pour patient, 1 pour médecin



    // Vérification des emails
    if ($_POST['mail1'] != $_POST['mail2']) {
        die("Les adresses emails ne correspondent pas.");
    }

    // Connexion à la base de données
    $conn = pg_connect('host=localhost dbname=projet_doct user=postgres password=Isen44');
    if (!$conn) {
        die("Erreur de connexion : " . pg_last_error($conn));
    }
    else {
        echo "Connexion réussie.";
    }

    // Insérer dans la base de données
    //$sql = "INSERT INTO personne (nom, prenom, email, mot_de_passe, medecin, telephone) 
            //VALUES ('$nom', '$prenom', '$email', '$motDePasse', $medecin, '$telephone')";
    $sql = "INSERT INTO personne (nom, prenom, email, mot_de_passe, medecin, telephone) 
        VALUES ($1, $2, $3, $4, $5, $6)";
    //$result = pg_query($conn, $sql);


    $result = $conn, $sql, [
        $nom, //$1
        $prenom, //$2
        $email, //$3
        $motDePasse, //$4
        $medecin, //$5
        $telephone];//$6


    if (!$result) {
        die("Erreur d'insertion : " . pg_last_error($conn));
    } 
    else {
        echo "Insertion réussie.";
    }
    
    if (strlen($nom) > 50 || strlen($prenom) > 50 || strlen($email) > 255) {
        die("Les champs 'nom', 'prenom' ou 'email' dépassent la longueur autorisée.");
    }


    if ($result) {
        // Rediriger après une inscription réussie
        header("Location: ../html/connexion_patient.html");
        exit();
    } 
    else {
        echo "Erreur : " . pg_last_error($conn);
    }

    pg_close($conn); // Fermer la connexion
?>

<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Récupérer les données du formulaire
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['mail1'];
    $motDePasse = password_hash($_POST['motdepasse1'], PASSWORD_DEFAULT);
    $telephone = $_POST['telephone'];
    $medecin = 'true'; // 0 pour patient, 1 pour médecin
    $specialite = ['specialite'];



    // Vérification des emails
    if ($_POST['mail1'] != $_POST['mail2']) {
        die("Les adresses emails ne correspondent pas.");
    }

    //verifier que les mdp soient les memes 
    if ($_POST['motdepasse1'] != $_POST['motdepasse2']) {
        die("Les mots de passe ne correspondent pas.");
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


    $result = pg_query_params($conn, $sql, [
        $nom, //$1
        $prenom, //$2
        $email, //$3
        $motDePasse, //$4
        $medecin, //$5
        $telephone]);//$6


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
        header("Location: ../html/connexion_medecin.html");
        exit();
    } 
    else {
        echo "Erreur : " . pg_last_error($conn);
    }


    // Récupérer l'ID de la spécialité
    $specialite = $_POST['specialite'];
    $query_specialite = "SELECT id FROM specialite WHERE nom_specialite = $1";
    $result_specialite = pg_query_params($conn, $query_specialite, [$specialite]);

    if (!$result_specialite) {
        die("Erreur lors de la récupération de la spécialité : " . pg_last_error($conn));
    }

    if (pg_num_rows($result_specialite) == 0) {
        // Si la spécialité n'existe pas, on l'ajoute
        $query_insert_specialite = "INSERT INTO specialite (nom_specialite) VALUES ($1) RETURNING id";
        $result_insert_specialite = pg_query_params($conn, $query_insert_specialite, [$specialite]);

        if (!$result_insert_specialite) {
            die("Erreur lors de l'ajout de la spécialité : " . pg_last_error($conn));
        }

        $specialite_id = pg_fetch_result($result_insert_specialite, 0, 'id');
    } else {
        // Récupérer l'ID de la spécialité existante
        $specialite_id = pg_fetch_result($result_specialite, 0, 'id');
    }

    // Ajouter la relation entre le médecin et la spécialité
    $query_possede = "INSERT INTO possede (id_medecin, id_specialite) VALUES ($1, $2)";
    $result_possede = pg_query_params($conn, $query_possede, [$medecin_id, $specialite_id]);

    if (!$result_possede) {
        die("Erreur lors de l'ajout de la relation médecin-spécialité : " . pg_last_error($conn));
    }

    //verifier que l'email n'est pas en double 
    $query_email = "SELECT * FROM personne WHERE email = $1";
    $result_email = pg_query_params($conn, $query_email, [$email]);
    
    if (pg_num_rows($result_email) > 0) {
        die("Cet email est déjà utilisé.");
    }
    
    

    pg_close($conn); // Fermer la connexion
?>

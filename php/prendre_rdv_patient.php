<?php
    // Démarrer la session
    session_start();

    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../php/connexion.php");
        exit();
    }

    // Connexion à la base de données
    $conn = pg_connect("host=localhost dbname=projet_doct user=postgres password=Isen44");
    if (!$conn) {
        die("Erreur de connexion à la base de données : ". pg_last_error());
    }

    // Vérifier si le formulaire est soumis
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Récupérer les données du formulaire
        $date = $_POST['date'];
        $heure = $_POST['heure'];
        $duree = $_POST['duree'];
        $etablissement = $_POST['etablissement'];
        $medecin_id = $_POST['medecin'];  // Récupérer l'ID du médecin choisi

        // Si l'heure est saisie sans minutes ou secondes (par exemple "15" au lieu de "15:00")
        if (strlen($heure) == 2) {
            $heure = $heure . ":00:00";  // Ajoute les minutes et secondes à l'heure
        } elseif (strlen($heure) == 5) {
            $heure = $heure . ":00";  // Ajoute les secondes si seulement les minutes sont présentes (par exemple "15:30" -> "15:30:00")
        }

        // Si la durée est saisie sans minutes ou secondes (par exemple "2" au lieu de "02:00:00")
        if (strlen($duree) == 1) {
            $duree = "00:$duree:00";  // Ajoute des heures et minutes (par exemple "2" -> "00:02:00")
        } elseif (strlen($duree) == 2) {
            $duree = "00:$duree:00";  // Ajoute des heures et secondes (par exemple "02" -> "00:02:00")
        } elseif (strlen($duree) == 5) {
            $duree = $duree . ":00";  // Complète la durée (par exemple "02:00" -> "02:00:00")
        }

        // Récupérer l'ID de l'utilisateur connecté
        $user_id = $_SESSION['user_id'];

        // Vérification du médecin : Assurer que le médecin existe dans la base de données
        $query_medecin_check = "SELECT id FROM medecin WHERE id = $1";
        $result_medecin_check = pg_query_params($conn, $query_medecin_check, [$medecin_id]);

        if (pg_num_rows($result_medecin_check) == 0) {
            die("Erreur : Le médecin sélectionné n'existe pas.");
        }

        // Récupérer l'ID de l'établissement à partir de son adresse
        $query_etablissement = "SELECT id FROM etablissement WHERE adresse = $1";
        $result_etablissement = pg_query_params($conn, $query_etablissement, [$etablissement]);

        if (!$result_etablissement) {
            die("Erreur lors de la récupération de l'établissement : " . pg_last_error($conn));
        }

        // Si l'établissement n'existe pas, on l'ajoute
        if (pg_num_rows($result_etablissement) == 0) {
            // Ajouter l'établissement dans la base de données
            $query_insert_etablissement = "INSERT INTO etablissement (adresse) VALUES ($1) RETURNING id";
            $result_insert_etablissement = pg_query_params($conn, $query_insert_etablissement, [$etablissement]);

            if (!$result_insert_etablissement) {
                die("Erreur lors de l'ajout de l'établissement : " . pg_last_error($conn));
            }

            // Récupérer l'ID de l'établissement inséré
            $etablissement_id = pg_fetch_result($result_insert_etablissement, 0, 'id');
        } else {
            // Si l'établissement existe déjà, récupérer son ID
            $etablissement_id = pg_fetch_result($result_etablissement, 0, 'id');
        }

        // Insérer le rendez-vous dans la table rdv
        $query_rdv = "INSERT INTO rdv (date, heure, duree, id_personne, id_medecin, id_etablissement) 
                      VALUES ($1, $2, $3, $4, $5, $6) RETURNING id";
        $result_rdv = pg_query_params($conn, $query_rdv, [$date, $heure, $duree, $user_id, $medecin_id, $etablissement_id]);

        if (!$result_rdv) {
            die("Erreur lors de l'ajout du rendez-vous : " . pg_last_error($conn));
        }

        // Récupérer l'ID du rendez-vous inséré
        $rdv_id = pg_fetch_result($result_rdv, 0, 'id');

        // Rediriger après avoir ajouté le rendez-vous
        header("Location: ../php/mon_espace_patient.php"); // Page où vous voulez rediriger après l'ajout
        exit();
    }

    // Fermer la connexion à la base de données
    pg_close($conn);
?>

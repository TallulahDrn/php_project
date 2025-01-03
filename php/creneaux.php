<?php
    session_start();


    // Connexion à la base de données avec PDO
    try {
        $conn = new PDO('pgsql:host=localhost;dbname=projet_doct', 'postgres', 'Isen44');
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Erreur de connexion : ".$e->getMessage());
    }


    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // Récupére les données du formulaire
        $date = $_POST['date'];
        $heure_debut = $_POST['heure_debut']; // HH:mm
        $heure_fin = $_POST['heure_fin'];
        $duree = $_POST['duree_rdv'];
        $etablissement = $_POST['etablissement'];
        $custom_etablissement = isset($_POST['custom_etablissement']) ? trim($_POST['custom_etablissement']) : '';

        // Si "Autre" est sélectionné, utiliser le champ personnalisé
        if ($etablissement === 'autre' && !empty($custom_etablissement)) {
            $etablissement = $custom_etablissement;
        }

        
        // Convertir les heures en timestamps
        $heure_debut = strtotime($heure_debut);
        $heure_fin = strtotime($heure_fin);
        $duree_seconds = $duree * 60;// Durée du rendez-vous en secondes

        // Vérification que l'heure de fin est après l'heure de début
        if ($heure_debut >= $heure_fin) {
            die("L'heure de début doit être avant l'heure de fin.");
        }

        // Récupérer l'ID du médecin
        $id_personne = $_SESSION['user_id'];
        $sql_medecin = "SELECT id FROM Medecin WHERE id_personne = :id_personne";
        $stmt_medecin = $conn->prepare($sql_medecin);
        $stmt_medecin->bindParam(':id_personne', $id_personne);
        $stmt_medecin->execute();
        $result_medecin = $stmt_medecin->fetch(PDO::FETCH_ASSOC);

        if ($result_medecin) {
            $id_medecin = $result_medecin['id'];
        }





        //-------------------INSERER DANS LA TABLE ETABLISSEMENT------------
    
        $sql_check_etablissement = "SELECT id FROM Etablissement WHERE adresse = :adresse";
        $stmt_check_etablissement = $conn->prepare($sql_check_etablissement);
        $stmt_check_etablissement->bindParam(':adresse', $etablissement);
        $stmt_check_etablissement->execute();
        $result_check = $stmt_check_etablissement->fetch(PDO::FETCH_ASSOC);

        if ($result_check) {
            // Si l'établissement existe déjà, on récupère son ID
            $id_etablissement = $result_check['id'];
        } 
        else {
            // Sinon, on insère l'établissement dans la base de données
            $sql_etablissement = "INSERT INTO Etablissement (adresse) VALUES (:adresse) RETURNING id";
            $stmt_etablissement = $conn->prepare($sql_etablissement);
            $stmt_etablissement->bindParam(':adresse', $etablissement);
            $stmt_etablissement->execute();
            $result = $stmt_etablissement->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                $id_etablissement = $result['id'];
            }
        }




        $hours = floor($duree_seconds/ 3600); // Heures
        $minutes = floor(($duree_seconds / 60) % 60); // Minutes
        $seconds = $duree_seconds % 60; // Secondes
        $duree_rdv = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        //echo (gettype($duree_rdv));



        //-------------------INSERER DANS LA TABLE RDV------------
        // Boucle pour insérer chaque créneau horaire de x minutes
        while ($heure_debut < $heure_fin) {
            // Calculer l'heure de début du créneau
            $creneau_heure = date('H:i', $heure_debut);

            // Préparer la requête pour insérer le créneau dans la table Rdv
            $sql = "INSERT INTO Rdv (heure, duree, date, id_medecin, id_etablissement)
                    VALUES (:heure, :duree, :date, :id_medecin, :id_etablissement)";
            
            // Préparer la requête
            $stmt = $conn->prepare($sql);
            
            // Définir les paramètres pour la requête
            $stmt->bindParam(':heure', $creneau_heure);
            $stmt->bindParam(':date', $date);
            $stmt->bindParam(':duree', $duree_rdv);
            $stmt->bindParam(':id_medecin', $id_medecin);
            $stmt->bindParam(':id_etablissement', $id_etablissement);

            
            // Exécuter la requête
            try {
                $stmt->execute();
            } catch (PDOException $e) {
                die("Erreur lors de l'insertion : " . $e->getMessage());
            }
            //$stmt->execute();

            // Passer au créneau suivant (ajouter 30 minutes)
            $heure_debut += $duree_seconds;
        }
    }

    header("Location: espace_medecin.php");
    exit();

    //Fermeture de la connexion à la base de données
    $conn = null;
?>

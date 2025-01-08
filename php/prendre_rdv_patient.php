<?php

    // Démarrer la session pour accéder aux variables de session
    session_start();


    // Connexion à la base de données
    $conn = pg_connect("host=localhost dbname=projet_doct user=postgres password=Isen44");
    if (!$conn) {
        die("Erreur de connexion à la base de données : " . pg_last_error());
    }

    //si le formulaire est envoyé en POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['recherche'])) {
        $dsn = 'pgsql:dbname=projet_doct;host=localhost;port=5432';
        $user = 'postgres';
        $password = 'Isen44';

        //connexion à la bdd
        try {
            $pdo = new PDO($dsn, $user, $password);
            $recherche = trim($_POST['recherche']);//récupère ce que l'utilisateur tape pour la recherche
            //trim enlève les espaces en trop

            //On recherche pour n'importe quelle ligne s'il y a $recherche dedans par exemple un 'a' (peu importe sa place)
            $recherche = "%".$recherche."%";

            // Requête SQL pour récupérer les médecins et les établissements dans lesquels ils consultent
            $sql = "
            SELECT 
                Medecin.id AS medecin_id,
                Personne.nom,
                Personne.prenom,
                Specialite.nom_specialite AS specialite,
                Etablissement.adresse AS etablissement,
                Etablissement.id AS etablissement_id,  -- ID de l'établissement
                Specialite.id AS specialite_id        -- ID de la spécialité
            FROM 
                Medecin
            JOIN Personne ON Medecin.id_personne = Personne.id
            JOIN possede ON Medecin.id = possede.id_medecin
            JOIN Specialite ON possede.id_specialite = Specialite.id
            JOIN Rdv ON Medecin.id = Rdv.id_medecin
            JOIN Etablissement ON Rdv.id_etablissement = Etablissement.id
            WHERE 
                (Personne.nom ILIKE :recherche 
                OR Specialite.nom_specialite ILIKE :recherche 
                OR Etablissement.adresse ILIKE :recherche) --si il y a un endroit qui correspond à la recherche
            GROUP BY 
                Medecin.id, Personne.nom, Personne.prenom, Specialite.nom_specialite, Etablissement.adresse, Etablissement.id, Specialite.id
            ORDER BY 
                Personne.nom, Personne.prenom, Etablissement.adresse;
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':recherche', $recherche, PDO::PARAM_STR);//remplace le paramètre dans la recherche sql par $recherche
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Afficher les résultats si la requête a retourné des résultats
            $show_results = !empty($results); //Vérifie s'il y a des résultats à afficher
        } 
        catch (PDOException $e) {//si on n'a pas réussi à se connecter à la bdd
            echo '<p>Erreur de connexion : ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['medecin_id'], $_GET['etablissement'])) {
        $medecin_id = intval($_GET['medecin_id']);
        $etablissement = $_GET['etablissement'];
    
        // Requête pour récupérer les créneaux disponibles pour ce médecin
        $query_dispo = "
    SELECT 
        medecin.id AS medecin_id,
        personne.nom,
        personne.prenom,
        specialite.nom_specialite AS specialite_medecin,
        etablissement.adresse AS adresse_etablissement,
        etablissement.id AS etablissement_id,  -- ID de l'établissement
        specialite.id AS specialite_id        -- ID de la spécialité
        FROM 
            medecin
        JOIN personne ON medecin.id_personne = personne.id
        JOIN possede ON medecin.id = possede.id_medecin
        JOIN specialite ON possede.id_specialite = specialite.id
        JOIN rdv ON medecin.id = rdv.id_medecin
        JOIN etablissement ON rdv.id_etablissement = etablissement.id
        WHERE 
            medecin.id = $1 
            AND etablissement.adresse = $2
            AND rdv.date >= CURRENT_DATE
        GROUP BY 
            medecin.id, personne.nom, personne.prenom, specialite.nom_specialite, etablissement.adresse, etablissement.id, specialite.id
        ORDER BY 
        personne.nom, personne.prenom, etablissement.adresse;
        ";

        $result_dispo = pg_query_params($conn, $query_dispo, [$medecin_id, $etablissement]);
    
        if (!$result_dispo) {
            die("Erreur lors de l'exécution de la requête : " . pg_last_error($conn));
        }
    
        $creneaux_disponibles = pg_fetch_all($result_dispo) ?: [];
    } 
    else {
        $creneaux_disponibles = [];
    }
    

    // Fermer la connexion à la base de données
    pg_close($conn);
?>



    


<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">

        <link href="../css/rdv_patient.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <title>Prendre un rendez-vous</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>
    <body>
        <nav class="navbar">
            <div class="container-fluid d-flex justify-content-between">
                <a class="navbar-brand">Tibobo</a>
                <ul class="navbar-nav d-flex flex-row align-items-center"> <!-- Liens de navigation à gauche -->
                    <li class="nav-item"><a class="nav-link " href="espace_utilisateur.php">Accueil</a></li>
                    <li class="nav-item"><a class="nav-link" href="mon_espace_patient.php">Mon espace</a></li>
                    <li class="nav-item"><a class="nav-link active" href="prendre_rdv_patient.php">Prendre rendez-vous</a></li>
                </ul>

                <!-- Bouton "Aide" et image alignés à droite -->
                <ul class="navbar-nav d-flex flex-row align-items-center ms-auto"> 
    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="espace_utilisateur.php" id="navbarDropdown" role="button" aria-expanded="false">
                            <img src="../images/malade.png" style="height:50px;width:50px">
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="deconnexion.php">Se déconnecter</a></li>
                            <li><a class="dropdown-item" href="../html/aide_patient.html">Aide</a></li>
                            <li><a class="dropdown-item" href="mon_compte_patient.php">Mon compte</a></li>
    
                        </ul>
                        
                    </li>
    
    
                </ul>
            </div>
        </nav>

        <div class="titre1">
                <h1>Prendre un rendez-vous</h1>
        </div>  
        <div class="container my-5 light_green">
              
        
            <div class="formulaire">
                <!-- Formulaire de recherche -->
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="recherche" class="form-label">Rechercher un médecin, une spécialité ou un établissement :</label>
                        <input type="text" id="recherche" name="recherche" class="form-control" placeholder="Tapez ici..." required>
                    </div>
                    <button type="submit" class="btn dark_green">Rechercher</button>
                </form>

                <!-- Tableau des résultats -->
                <?php if (isset($show_results) && $show_results): ?>
                    <div class="table-responsive mt-4">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Nom du Médecin</th>
                                    <th>Spécialité</th>
                                    <th>Établissement</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($results as $row): ?>
                                <tr>
                                    <td>Dr. <?= htmlspecialchars($row['nom']) ?> <?= htmlspecialchars($row['prenom']) ?></td>
                                    <td><?= htmlspecialchars($row['specialite']) ?></td>
                                    <td><?= htmlspecialchars($row['etablissement']) ?></td>
                                    <td>
                                        <!-- Formulaire pour envoyer les paramètres via POST -->
                                        <form method="POST" action="reserver_creneaux.php">
                                            <input type="hidden" name="medecin_id" value="<?= htmlspecialchars($row['medecin_id']) ?>">
                                            <input type="hidden" name="etablissement_id" value="<?= htmlspecialchars($row['etablissement_id']) ?>">
                                            <input type="hidden" name="specialite_id" value="<?= htmlspecialchars($row['specialite_id']) ?>">
                                            <button type="submit" class="btn dark_green">Voir les créneaux</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>

                        </table>
                    </div>
                <?php elseif (isset($show_results) && !$show_results): ?>
                    <p>Aucun résultat trouvé.</p>
                <?php endif; ?>
            </div>
            
            <?php if (count($creneaux_disponibles) > 0): ?>
                <div class="table-responsive mt-4">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nom du Médecin</th>
                                <th>Spécialité</th>
                                <th>Établissement</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($creneaux_disponibles as $creneau): ?>
                            <tr>
                                <td>Dr. <?= htmlspecialchars($creneau['prenom']) ?> <?= htmlspecialchars($creneau['nom']) ?></td>
                                <td><?= htmlspecialchars($creneau['specialite_medecin']) ?></td>
                                <td><?= htmlspecialchars($creneau['adresse_etablissement']) ?></td>
                                <td>
                                    <!-- Formulaire pour envoyer les paramètres via POST -->
                                    <form method="POST" action="reserver_creneaux.php">
                                        <input type="hidden" name="medecin_id" value="<?= htmlspecialchars($creneau['medecin_id']) ?>">
                                        <input type="hidden" name="etablissement_id" value="<?= htmlspecialchars($creneau['etablissement_id']) ?>">
                                        <input type="hidden" name="specialite_id" value="<?= htmlspecialchars($creneau['specialite_id']) ?>">
                                        <input type="hidden" name="rdv_id" value="<?= htmlspecialchars($creneau['rdv_id']) ?>"> <!-- Ajouter l'ID du créneau -->
                                        <button type="submit" class="btn dark_green">Voir les créneaux</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>

                    </table>
                </div>
            
            <?php endif; ?>
        </div>
    </body>
</html>

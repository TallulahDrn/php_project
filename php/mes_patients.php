<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Démarrer la session pour accéder aux variables de session
    session_start();

    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['user_id'])) {
        header("Location: connexion.php"); // Rediriger vers la page de connexion si non connecté
        exit();
    }

    // Connexion à la base de données
    $conn = pg_connect("host=localhost dbname=projet_doct user=postgres password=Isen44");
    if (!$conn) {
        die("Erreur de connexion à la base de données : " . pg_last_error());
    }

    // Récupérer l'ID du médecin (l'utilisateur connecté)
    $user_id = $_SESSION['user_id'];

    // Initialiser les variables de recherche
    $search_term = '';

    // Vérifier si un terme de recherche est fourni
    if (isset($_GET['search'])) {
        $search_term = trim(pg_escape_string($conn, $_GET['search'])); // Supprimer les espaces inutiles
    }

    // Initialiser les paramètres pour la recherche
    $params = [$user_id];

    // Requête de base
    $query_patients = "
        SELECT DISTINCT personne.id, personne.prenom, personne.nom, personne.telephone, personne.email
        FROM rdv
        INNER JOIN personne ON rdv.id_personne = personne.id
        WHERE rdv.id_medecin = $1
    ";

    // Ajouter des conditions en fonction du terme de recherche
    if (!empty($search_term)) {
        $search_parts = explode(' ', $search_term);

        if (count($search_parts) == 2) {
            // Si deux mots sont donnés, rechercher "prenom nom" et "nom prenom"
            $query_patients .= " AND (
                (personne.prenom ILIKE $2 AND personne.nom ILIKE $3) OR
                (personne.prenom ILIKE $3 AND personne.nom ILIKE $2)
            )";
            $params[] = "%" . $search_parts[0] . "%"; // Premier mot
            $params[] = "%" . $search_parts[1] . "%"; // Deuxième mot
        } else {
            // Si un seul mot est donné, chercher dans les deux champs (prénom et nom)
            $query_patients .= " AND (
                personne.prenom ILIKE $2 OR personne.nom ILIKE $2
            )";
            $params[] = "%" . $search_term . "%"; // Le seul mot donné
        }
    }

    // Exécuter la requête
    $result_patients = pg_query_params($conn, $query_patients, $params);

    // Vérifier si des patients ont été trouvés
    $patients_found = pg_num_rows($result_patients) > 0;

    // Initialiser les détails du patient
    $patient_details = null;
    if (isset($_GET['patient_id'])) {
        $patient_id = intval($_GET['patient_id']); // Sécuriser l'entrée

        // Requête pour récupérer les détails du patient
        
        //$query_details = "SELECT prenom, nom, telephone, email, adresse FROM personne WHERE id = $1";
        $query_details = "
            SELECT personne.prenom, personne.nom, personne.telephone, personne.email 
            FROM personne
            INNER JOIN rdv ON rdv.id_personne = personne.id
            WHERE personne.id = $1
        ";

        
        $result_details = pg_query_params($conn, $query_details, [$patient_id]);

        if ($result_details && pg_num_rows($result_details) > 0) {
            $patient_details = pg_fetch_assoc($result_details);
        }


        // Requête pour les rendez-vous passés
        $query_past_rdv = "
        SELECT rdv.date, rdv.heure, rdv.duree, etablissement.adresse 
        FROM rdv
        INNER JOIN etablissement ON rdv.id_etablissement = etablissement.id
        WHERE rdv.id_personne = $1 AND rdv.date < CURRENT_DATE
        ORDER BY rdv.date DESC, rdv.heure DESC
        ";
        $result_past_rdv = pg_query_params($conn, $query_past_rdv, [$patient_id]);

        // Requête pour les rendez-vous à venir
        $query_upcoming_rdv = "
        SELECT rdv.date, rdv.heure, rdv.duree, etablissement.adresse 
        FROM rdv
        INNER JOIN etablissement ON rdv.id_etablissement = etablissement.id
        WHERE rdv.id_personne = $1 AND rdv.date >= CURRENT_DATE
        ORDER BY rdv.date ASC, rdv.heure ASC
        ";
        $result_upcoming_rdv = pg_query_params($conn, $query_upcoming_rdv, [$patient_id]);
            
    }


    // Vérifier si un rdv_id est fourni
    if (isset($_GET['rdv_id'])) {
        $rdv_id = intval($_GET['rdv_id']); // Sécuriser l'entrée

        // Requête pour récupérer les détails du RDV
        $query_rdv_details = "
            SELECT rdv.date, rdv.heure, rdv.duree, personne.prenom, personne.nom, personne.telephone, personne.email, etablissement.adresse
            FROM rdv
            INNER JOIN personne ON rdv.id_personne = personne.id
            INNER JOIN etablissement ON rdv.id_etablissement = etablissement.id
            WHERE rdv.id = $1
        ";

        $result_rdv_details = pg_query_params($conn, $query_rdv_details, [$rdv_id]);

        if ($result_rdv_details && pg_num_rows($result_rdv_details) > 0) {
            $rdv_details = pg_fetch_assoc($result_rdv_details);
        }
    }




    // Fermer la connexion
    pg_close($conn);
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <link href="../css/mes_patients.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <title>Mes Patients</title>
    </head>
    <body>
    <nav class="navbar">
                <div class="container-fluid d-flex justify-content-between">
                    <a class="navbar-brand">Tibobo</a>
                    <ul class="navbar-nav d-flex flex-row align-items-center"> <!-- Liens de navigation à gauche -->
                        <li class="nav-item">
                            <a class="nav-link" href="espace_medecin.php">Accueil</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="mon_espace_medecin.php">Mon espace</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../html/creneaux.html">Mes disponibilités</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link " href="planning.php">Planning</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="mes_patients.php">Mes patients</a>
                        </li>
                    </ul>
                    <ul class="navbar-nav d-flex flex-row align-items-center ms-auto"> <!-- Bouton "Aide" et image alignés à droite -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="espace_medecin.php" id="navbarDropdown" role="button"  aria-expanded="false">
                                <img src="../images/medecin.png" style="height:50px;width:50px">
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="deconnexion.php">Se déconnecter</a></li>
                                <li><a class="dropdown-item" href="../html/aide_medecin.html">Aide</a></li>
                                <li><a class="dropdown-item" href="mon_compte_medecin.php">Mon compte</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>

        <!-- Barre de recherche sous la navbar -->
        <div class="container mt-4">
            <form class="d-flex" method="GET" action="mes_patients.php">
                <input type="text" name="search" class="form-control" placeholder="Rechercher un patient..." value="<?php echo htmlspecialchars($search_term); ?>">
                <button type="submit" class="btn btn-primary ms-2">Rechercher</button>
            </form>
        </div>

        <div class="container mt-4">
            <?php if (isset($rdv_details)): ?>
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5>Détails du Rendez-vous</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Date :</strong> <?php echo htmlspecialchars($rdv_details['date']); ?></p>
                        <p><strong>Heure :</strong> <?php echo htmlspecialchars($rdv_details['heure']); ?></p>
                        <p><strong>Durée :</strong> <?php echo htmlspecialchars($rdv_details['duree']); ?> minutes</p>
                        <p><strong>Patient :</strong> <?php echo htmlspecialchars($rdv_details['prenom'] . ' ' . $rdv_details['nom']); ?></p>
                        <p><strong>Téléphone :</strong> <?php echo htmlspecialchars($rdv_details['telephone']); ?></p>
                        <p><strong>Email :</strong> <?php echo htmlspecialchars($rdv_details['email']); ?></p>
                        <p><strong>Adresse :</strong> <?php echo htmlspecialchars($rdv_details['adresse']); ?></p>
                    </div>
                    <div class="card-footer text-end">
                        <a href="mon_espace_medecin.php" class="btn btn-secondary">Retour à mon espace</a>

                        <a href="planning.php" class="btn btn-secondary">Retour au planning</a>
                    </div>
                </div>
            <?php endif; ?>


            
            <?php if ($patient_details): ?>
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5>Détails du patient</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Nom :</strong> <?php echo htmlspecialchars($patient_details['prenom'] . ' ' . $patient_details['nom']); ?></p>
                        <p><strong>Téléphone :</strong> <?php echo htmlspecialchars($patient_details['telephone']); ?></p>
                        <p><strong>Email :</strong> <?php echo htmlspecialchars($patient_details['email']); ?></p>

                        <h6 class="mt-4">Rendez-vous à venir</h6>
                        <?php if (pg_num_rows($result_upcoming_rdv) > 0): ?>
                            <ul class="list-group">
                                <?php while ($row = pg_fetch_assoc($result_upcoming_rdv)): ?>
                                    <li class="list-group-item">
                                        <strong>Date :</strong> <?php echo htmlspecialchars($row['date']); ?><br>
                                        <strong>Heure :</strong> <?php echo htmlspecialchars($row['heure']); ?><br>
                                        <strong>Durée :</strong> <?php echo htmlspecialchars($row['duree']); ?><br>
                                        <strong>Adresse :</strong> <?php echo htmlspecialchars($row['adresse']); ?>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        <?php else: ?>
                            <p>Aucun rendez-vous à venir.</p>
                        <?php endif; ?>

                        <h6 class="mt-4">Rendez-vous passés</h6>
                        <?php if (pg_num_rows($result_past_rdv) > 0): ?>
                            <ul class="list-group">
                                <?php while ($row = pg_fetch_assoc($result_past_rdv)): ?>
                                    <li class="list-group-item">
                                        <strong>Date :</strong> <?php echo htmlspecialchars($row['date']); ?><br>
                                        <strong>Heure :</strong> <?php echo htmlspecialchars($row['heure']); ?><br>
                                        <strong>Durée :</strong> <?php echo htmlspecialchars($row['duree']); ?><br>
                                        <strong>Adresse :</strong> <?php echo htmlspecialchars($row['adresse']); ?>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        <?php else: ?>
                            <p>Aucun rendez-vous passé.</p>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer text-end">
                        <a href="mes_patients.php" class="btn btn-secondary">Fermer</a>
                    </div>
                </div>

            <?php endif; ?>








            <div class="row">
                <div class="col-md-12">
                    <h2>Liste des Patients</h2>
                    <?php if (!empty($search_term) && !$patients_found): ?>
                        <p>Aucun patient trouvé pour la recherche "<?php echo htmlspecialchars($search_term); ?>"</p>
                    <?php endif; ?>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Téléphone</th>
                                <th>Email</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = pg_fetch_assoc($result_patients)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['prenom'] . ' ' . $row['nom']); ?></td>
                                    <td><?php echo htmlspecialchars($row['telephone']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td>
                                        <a href="mes_patients.php?patient_id=<?php echo $row['id']; ?>" class="btn btn-info">Voir détails</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </body>
</html>

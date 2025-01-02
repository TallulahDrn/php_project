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

    // Récupérer l'ID de l'utilisateur connecté
    $user_id = $_SESSION['user_id'];

    // Récupérer la date d'aujourd'hui
    $current_date = date('Y-m-d');

    // Requête pour récupérer les rendez-vous de la journée
    $query_rdv = "
        SELECT rdv.id, rdv.date, rdv.heure, rdv.duree, personne.prenom, personne.nom, etablissement.adresse
        FROM rdv
        INNER JOIN personne ON rdv.id_personne = personne.id
        INNER JOIN etablissement ON rdv.id_etablissement = etablissement.id
        WHERE rdv.id_medecin = $1
        AND rdv.date = $2
        ORDER BY rdv.heure
    ";

    $result_rdv = pg_query_params($conn, $query_rdv, [$user_id, $current_date]);

    if (!$result_rdv) {
        die("Erreur lors de l'exécution de la requête : " . pg_last_error($conn));
    }

    // Récupérer les rendez-vous
    $rdvs = [];
    while ($rdv = pg_fetch_assoc($result_rdv)) {
        $rdvs[] = $rdv;
    }

    // Formulaire pour ajouter une nouvelle disponibilité
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Récupérer les données du formulaire
        $dispo_date = $_POST['dispo_date'];
        $dispo_heure = $_POST['dispo_heure'];

        // Requête pour insérer la disponibilité dans la base de données
        $query_dispo = "
            INSERT INTO disponibilites (id_medecin, date, heure)
            VALUES ($1, $2, $3)
        ";

        $result_dispo = pg_query_params($conn, $query_dispo, [$user_id, $dispo_date, $dispo_heure]);

        if (!$result_dispo) {
            die("Erreur lors de l'insertion de la disponibilité : " . pg_last_error($conn));
        }
    }

    // Fermer la connexion à la base de données
    pg_close($conn);
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <link href="../css/mon_espace_medecin.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <title>Mon Espace Médecin</title>
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
                        <a class="nav-link active" href="mon_espace_medecin.php">Mon espace</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../html/creneaux.html">Mes disponibilités</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link " href="planning.php">Planning</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="mes_patients.php">Mes patients</a>
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

        <div class="container mt-5">
            <div class="row">
                <!-- Colonne de gauche: Planning de la journée -->
                <div class="col-md-6">
                    <h3>Planning de la journée - <?php echo $current_date; ?></h3>
                    <div class="list-group">
                    <?php if (count($rdvs) > 0): ?>
                        <?php foreach ($rdvs as $rdv): ?>
                            <a href="mes_patients.php?rdv_id=<?php echo $rdv['id']; ?>" class="list-group-item list-group-item-action">
                                <strong><?php echo $rdv['heure']; ?></strong> - <?php echo $rdv['duree']; ?> min<br>
                                <?php echo htmlspecialchars($rdv['prenom'] . ' ' . $rdv['nom']); ?><br>
                                <em><?php echo htmlspecialchars($rdv['adresse']); ?></em>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Aucun rendez-vous pour aujourd'hui.</p>
                    <?php endif; ?>
                    </div>
                </div>

                <!-- Colonne de droite: Détails des rendez-vous et formulaire de disponibilité -->
                <div class="col-md-6">
                    <h3>Détails des rendez-vous à venir</h3>
                    <div class="mb-4">
                        <!-- Bouton pour rediriger vers la page de gestion des disponibilités -->
                        <a href="../html/creneaux.html" class="btn btn-primary">Gérer mes disponibilités</a>
                    </div>

                    

                    <div class="list-group">
                        <h4>Rendez-vous à venir</h4>
                        <?php
                            // Requête pour récupérer les rendez-vous à venir
                            $conn = pg_connect("host=localhost dbname=projet_doct user=postgres password=Isen44");
                            $query_future_rdv = "
                                SELECT rdv.id, rdv.date, rdv.heure, rdv.duree, personne.prenom, personne.nom, etablissement.adresse
                                FROM rdv
                                INNER JOIN personne ON rdv.id_personne = personne.id
                                INNER JOIN etablissement ON rdv.id_etablissement = etablissement.id
                                WHERE rdv.id_medecin = $1
                                AND rdv.date > $2
                                ORDER BY rdv.date, rdv.heure
                            ";
                            $current_date = date('Y-m-d');
                            $result_future_rdv = pg_query_params($conn, $query_future_rdv, [$user_id, $current_date]);

                            if (!$result_future_rdv) {
                                die("Erreur lors de l'exécution de la requête : " . pg_last_error($conn));
                            }

                            // Vérification si des rendez-vous sont trouvés
                            if (pg_num_rows($result_future_rdv) > 0) {
                                // Affichage des rendez-vous à venir sous forme de liste
                                while ($future_rdv = pg_fetch_assoc($result_future_rdv)) {
                                    echo '<a href="mes_patients.php?rdv_id=' . $future_rdv['id'] . '" class="list-group-item list-group-item-action">';
                                    echo '<strong>' . $future_rdv['date'] . ' - ' . $future_rdv['heure'] . '</strong><br>';
                                    echo htmlspecialchars($future_rdv['prenom'] . ' ' . $future_rdv['nom']) . '<br>';
                                    echo '<em>' . htmlspecialchars($future_rdv['adresse']) . '</em>';
                                    echo '</a>';
                                }
                            } else {
                                // Aucun rendez-vous à venir
                                echo '<p>Aucun rendez-vous à venir.</p>';
                            }

                            pg_close($conn);
                        ?>
                    </div>



                </div>
            </div>
        </div>
    </body>
</html>

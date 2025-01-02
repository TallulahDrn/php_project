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

    // Déterminer la semaine actuelle ou sélectionnée
    $current_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
    $start_of_week = date('Y-m-d', strtotime('monday this week', strtotime($current_date)));
    $end_of_week = date('Y-m-d', strtotime('sunday this week', strtotime($current_date)));

    // Requête pour récupérer les rendez-vous de la semaine pour le médecin
    $query_rdv = "
        SELECT rdv.id, rdv.date, rdv.heure, rdv.duree, personne.prenom, personne.nom, etablissement.adresse
        FROM rdv
        INNER JOIN personne ON rdv.id_personne = personne.id
        INNER JOIN etablissement ON rdv.id_etablissement = etablissement.id
        WHERE rdv.id_medecin = $1
        AND rdv.date BETWEEN $2 AND $3
        ORDER BY rdv.date, rdv.heure
    ";

    $result_rdv = pg_query_params($conn, $query_rdv, [$user_id, $start_of_week, $end_of_week]);

    if (!$result_rdv) {
        die("Erreur lors de l'exécution de la requête : " . pg_last_error($conn));
    }

    // Organiser les rendez-vous par jour de la semaine
    $planning = [];
    while ($rdv = pg_fetch_assoc($result_rdv)) {
        $day = date('l', strtotime($rdv['date'])); // Récupérer le jour (Monday, Tuesday, ...)
        if (!isset($planning[$day])) {
            $planning[$day] = [];
        }
        $planning[$day][] = $rdv;
    }


    // Organiser les rendez-vous par jour de la semaine en français
    $jours_francais = [
        'Monday' => 'Lundi',
        'Tuesday' => 'Mardi',
        'Wednesday' => 'Mercredi',
        'Thursday' => 'Jeudi',
        'Friday' => 'Vendredi',
        'Saturday' => 'Samedi',
        'Sunday' => 'Dimanche'
    ];

    // Organiser les rendez-vous en utilisant les jours en français
    $planning_francais = [];
    foreach ($planning as $day => $rdvs) {
        $day_fr = $jours_francais[$day]; // Convertir l'anglais en français
        $planning_francais[$day_fr] = $rdvs;
    }

    // Fermer la connexion
    pg_close($conn);
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <link href="../css/planning.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <title>Planning</title>
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
                        <a class="nav-link" href="#">Mon espace</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../html/creneaux.html">Mes disponibilités</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="planning.php">Planning</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="mes_patients.php">Mes patients</a>
                    </li>
                </ul>
                <ul class="navbar-nav d-flex flex-row align-items-center ms-auto"> <!-- Bouton "Aide" et image alignés à droite -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="espace_medecin.php" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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

        <!-- Planning -->
        <div class="container">
            <h1 class="my-4">Planning de la semaine du <?php echo $start_of_week; ?> au <?php echo $end_of_week; ?></h1>

            <!-- Navigation entre les semaines -->
            <div class="d-flex justify-content-between mb-4">
                <a href="planning.php?date=<?php echo date('Y-m-d', strtotime('-1 week', strtotime($start_of_week))); ?>" class="btn btn-primary">Semaine précédente</a>
                <a href="planning.php?date=<?php echo date('Y-m-d', strtotime('+1 week', strtotime($start_of_week))); ?>" class="btn btn-primary">Semaine suivante</a>
            </div>

            <div class="row">
                <?php foreach (['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'] as $day): ?>

                    <div class="col-md-1 text-center border p-3">
                        <h4><?php echo $day; ?></h4>
                        <?php if (isset($planning_francais[$day]) && count($planning_francais[$day]) > 0): ?>
                            <ul class="list-unstyled">
                                <?php foreach ($planning_francais[$day] as $rdv): ?>
                                    <li>
                                        <a href="mes_patients.php?rdv_id=<?php echo $rdv['id']; ?>">
                                            <strong><?php echo $rdv['heure']; ?></strong> - <?php echo $rdv['duree']; ?> min<br>
                                            <?php echo htmlspecialchars($rdv['prenom'] . ' ' . $rdv['nom']); ?><br>
                                            <em><?php echo htmlspecialchars($rdv['adresse']); ?></em>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p>Aucun RDV</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </body>
</html>

<?php
    // Démarrer la session pour pouvoir accéder aux variables de session
    session_start();

    

    // Connexion à la base de données
    $conn = pg_connect("host=localhost dbname=projet_doct user=postgres password=Isen44");
    if (!$conn) {
        die("Erreur de connexion à la base de données : ". pg_last_error());
    }

    // Récupérer l'ID de l'utilisateur connecté
    $user_id = $_SESSION['user_id'];

    // Requête pour récupérer les informations de l'utilisateur
    $query_user_info = "SELECT prenom, nom, email, telephone FROM personne WHERE id = $1";
    $result_user_info = pg_query_params($conn, $query_user_info, [$user_id]);

    if (!$result_user_info) {
        die("Erreur lors de l'exécution de la requête : " . pg_last_error($conn));
    }

    // Récupérer les informations utilisateur
    $user_info = pg_fetch_assoc($result_user_info);

    // Requête pour récupérer les rendez-vous de l'utilisateur
    $query_rdv = "
        SELECT rdv.date, rdv.heure, rdv.duree, etablissement.adresse, personne.nom AS nom_medecin, personne.prenom AS prenom_medecin, medecin.id AS medecin_id
        FROM rdv
        INNER JOIN etablissement ON rdv.id_etablissement = etablissement.id
        INNER JOIN medecin ON rdv.id_medecin = medecin.id
        INNER JOIN personne ON medecin.id_personne = personne.id
        WHERE rdv.id_personne = $1
        ORDER BY rdv.date, rdv.heure
    ";
    $result_rdv = pg_query_params($conn, $query_rdv, [$user_id]);

    if (!$result_rdv) {
        die("Erreur lors de l'exécution de la requête : " . pg_last_error($conn));
    }

    // Séparer les rendez-vous passés et à venir
    $rdv_avenir = [];
    $rdv_passes = [];
    $current_date = date('Y-m-d');  // Récupérer la date actuelle

    

   

    while ($rdv = pg_fetch_assoc($result_rdv)) {
        // Comparer la date du rendez-vous avec la date actuelle
        if ($rdv['date'] >= $current_date) {
            $rdv_avenir[] = $rdv;  // Rendez-vous à venir
        } 
        else {
            $rdv_passes[] = $rdv;  // Rendez-vous passés
        }
    }

    // Fermer la connexion à la base de données
    pg_close($conn);
    

?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <link href="../css/mon_espace_patient.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

        <title>Espace Utilisateur</title>
    </head>
    <body>
        <nav class="navbar">
            <div class="container-fluid d-flex justify-content-between">
                <a class="navbar-brand">Tibobo</a>

                <ul class="navbar-nav d-flex flex-row align-items-center"> <!-- Liens de navigation à gauche -->
                    <li class="nav-item">
                        <a class="nav-link" href="espace_utilisateur.php">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="mon_espace_patient.php">Mon espace</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="prendre_rdv_patient.php">Prendre rendez-vous</a>
                    </li>
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
        

            <div class="container">
                <div class="titre1">
                    <h1>Vos Rendez-vous</h1>
                </div>
                <div class="rdv">

                    <div class="rdv1">
                        <h2> Vos rendez-vous à venir : </h2>
                            <?php if (count($rdv_avenir) > 0): ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Heure</th>
                                        <th>Durée</th>
                                        <th>Établissement</th>
                                        <th>Médecin</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rdv_avenir as $rdv): ?>
                                        <tr>
                                            <td><?php echo $rdv['date']; ?></td>
                                            <td><?php echo $rdv['heure']; ?></td>
                                            <td><?php echo $rdv['duree']; ?></td>
                                            <td><?php echo $rdv['adresse']; ?></td>
                                            <td> Dr. <?php echo $rdv['prenom_medecin']; ?> <?php echo $rdv['nom_medecin']; ?></td>

                                            
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php else: ?>
                                <p>Vous n'avez pas de rendez-vous à venir.</p>
                            <?php endif; ?>
                    </div>
                    <div class="rdv2">
                        <h2> Vos rendez-vous passés : </h2>
                            <?php if (count($rdv_passes) > 0): ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Heure</th>
                                        <th>Durée</th>
                                        <th>Établissement</th>
                                        <th>Médecin</th>
                                        <th>Action</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rdv_passes as $rdv): ?>
                                        <tr>
                                            <td><?php echo $rdv['date']; ?></td>
                                            <td><?php echo $rdv['heure']; ?></td>
                                            <td><?php echo $rdv['duree']; ?></td>
                                            <td><?php echo $rdv['adresse']; ?></td>
                                            <td> Dr. <?php echo $rdv['prenom_medecin']; ?> <?php echo $rdv['nom_medecin']; ?></td>
                                            <td>
                                                <form method="GET" action="prendre_rdv_patient.php">
                                                    <input type="hidden" name="medecin_id" value="<?= htmlspecialchars($rdv['medecin_id']) ?>">
                                                    <input type="hidden" name="etablissement" value="<?= htmlspecialchars($rdv['adresse']) ?>">
                                                    <button type="submit" class="btn btn-primary">Reprendre RDV</button>
                                                </form>
                                            </td>


                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php else: ?>
                                <p>Vous n'avez pas de rendez-vous passés.</p>
                            <?php endif; ?>
                    </div>
                    <div class="rdv3">
                        <h2> Vous souhaitez prendre rendez-vous : </h2>
                        <!--<a href="../html/prendre_rdv_patient.html" class="btn-prendre-rdv">Prendre RDV</a>-->
                        <a href="prendre_rdv_patient.php" class="btn-prendre-rdv">Prendre RDV</a>
                    </div>
                </div>
            </div>


        
    </body>
</html>

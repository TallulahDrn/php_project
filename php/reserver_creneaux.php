<?php
session_start();

// Paramètres de connexion
$dsn = 'pgsql:dbname=projet_doct;host=localhost;port=5432';
$user = 'postgres';
$password = 'Isen44';

// Gestion des dates pour la navigation hebdomadaire
if (isset($_POST['start_date'])) {
    $start_date = new DateTime($_POST['start_date']);
} else {
    $start_date = new DateTime();
    $start_date->modify('monday this week');
}
$end_date = clone $start_date;
$end_date->modify('+6 days');

// Calcul des dates de navigation
$previous_start_date = clone $start_date;
$previous_start_date->modify('-7 days');
$next_start_date = clone $start_date;
$next_start_date->modify('+7 days');

// Jours de la semaine en français
$jours_fr = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];

// Vérification des paramètres requis
if (isset($_POST['medecin_id'], $_POST['etablissement_id'], $_POST['specialite_id'])) {
    $medecin_id = (int) $_POST['medecin_id'];
    $etablissement_id = (int) $_POST['etablissement_id'];
    $specialite_id = (int) $_POST['specialite_id'];


    try {
        $pdo = new PDO($dsn, $user, $password);

        // Si une réservation est faite
        if (isset($_POST['reserver_id']) && isset($_SESSION['user_id'])) {
            $rdv_id = (int) $_POST['reserver_id'];
            $user_id = (int) $_SESSION['user_id'];

            $update_sql = "UPDATE Rdv SET id_personne = :user_id WHERE id = :rdv_id AND id_personne IS NULL";
            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $update_stmt->bindParam(':rdv_id', $rdv_id, PDO::PARAM_INT);
            $update_stmt->execute();

            if ($update_stmt->rowCount() > 0) {
                echo "<script>window.onload = function() { alert('Rendez-vous réservé avec succès.'); };</script>";
            } else {
                echo "<script>window.onload = function() { alert('Impossible de réserver ce créneau.'); };</script>";
            }
        }

        // Requête pour récupérer les créneaux disponibles pour la semaine
        $sql = "
            SELECT 
                Rdv.id AS rdv_id,
                Rdv.date,
                Rdv.heure,
                Rdv.id_personne,
                Etablissement.adresse AS etablissement,
                Specialite.nom_specialite AS specialite
            FROM 
                Rdv
            JOIN Etablissement ON Rdv.id_etablissement = Etablissement.id
            JOIN Medecin ON Rdv.id_medecin = Medecin.id
            JOIN possede ON Medecin.id = possede.id_medecin
            JOIN Specialite ON possede.id_specialite = Specialite.id
            WHERE 
                Rdv.id_medecin = :medecin_id
            AND 
                Rdv.id_etablissement = :etablissement_id
            AND 
                possede.id_specialite = :specialite_id
            AND 
                Rdv.date BETWEEN :start_date AND :end_date
            ORDER BY 
                Rdv.date, Rdv.heure ASC;
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':medecin_id', $medecin_id, PDO::PARAM_INT);
        $stmt->bindParam(':etablissement_id', $etablissement_id, PDO::PARAM_INT);
        $stmt->bindParam(':specialite_id', $specialite_id, PDO::PARAM_INT);
        $stmt->bindParam(':start_date', $start_date->format('Y-m-d'));
        $stmt->bindParam(':end_date', $end_date->format('Y-m-d'));
        $stmt->execute();
        $creneaux = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Regrouper les créneaux par jour
        $planning = [];
        foreach ($creneaux as $creneau) {
            $date = $creneau['date'];
            if (!isset($planning[$date])) {
                $planning[$date] = [];
            }
            $planning[$date][] = $creneau;
        }
    } catch (PDOException $e) {
        echo '<p>Erreur de connexion : ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
} else {
    echo '<p>Paramètres manquants.</p>';
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <link href="../css/inscription.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <title>Prendre un rendez-vous</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
        .card-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        /* 3 cartes par ligne, sauf dimanche */
        .card {
            width: 30%; /* 3 cartes par ligne */
        }
        /* Ajuste la taille des cartes pour que dimanche soit égal aux autres */
        .card-container .card:nth-child(7) {
            width: 30%; /* Forcer la largeur de dimanche */
        }
        .card-container .card:nth-child(8) {
            width: 30%; /* Pour le dernier jour (dimanche) */
        }
    </style>
    </head>
    <body>
        <nav class="navbar">
            <div class="container-fluid d-flex justify-content-between">
                <a class="navbar-brand">Tibobo</a>
                <ul class="navbar-nav d-flex flex-row align-items-center"> <!-- Liens de navigation à gauche -->
                    <li class="nav-item"><a class="nav-link active" href="espace_utilisateur.php">Accueil</a></li>
                    <li class="nav-item"><a class="nav-link" href="mon_espace_patient.php">Mon espace</a></li>
                    <li class="nav-item"><a class="nav-link" href="prendre_rdv_patient.php">Prendre rendez-vous</a></li>
                </ul>
                <ul class="navbar-nav d-flex flex-row align-items-center ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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
    


<div class="container mt-5">
    <h1>Planning de la semaine du <?= $start_date->format('d/m/Y') ?> au <?= $end_date->format('d/m/Y') ?></h1>
    <div class="d-flex justify-content-between mb-4">
        <!-- Formulaire pour la semaine précédente -->
        <form action="reserver_creneaux.php" method="POST" style="display:inline;">
            <input type="hidden" name="start_date" value="<?= $previous_start_date->format('Y-m-d') ?>">
            <input type="hidden" name="medecin_id" value="<?= $medecin_id ?>">
            <input type="hidden" name="etablissement_id" value="<?= $etablissement_id ?>">
            <input type="hidden" name="specialite_id" value="<?= $specialite_id ?>">
            <button type="submit" class="btn btn-primary">Semaine précédente</button>
        </form>

        <!-- Formulaire pour la semaine suivante -->
        <form action="reserver_creneaux.php" method="POST" style="display:inline;">
            <input type="hidden" name="start_date" value="<?= $next_start_date->format('Y-m-d') ?>">
            <input type="hidden" name="medecin_id" value="<?= $medecin_id ?>">
            <input type="hidden" name="etablissement_id" value="<?= $etablissement_id ?>">
            <input type="hidden" name="specialite_id" value="<?= $specialite_id ?>">
            <button type="submit" class="btn btn-primary">Semaine suivante</button>
        </form>
    </div>

    <div class="card-container">
        <?php foreach ($jours_fr as $i => $jour): ?>
            <?php 
            $date = (clone $start_date)->modify("+$i days")->format('Y-m-d');
            $creneaux_du_jour = $planning[$date] ?? [];
            ?>
            <div class="card">
                <div class="card-header">
                    <?= ucfirst($jour) ?> <?= (new DateTime($date))->format('d/m/Y') ?>
                </div>
                <div class="card-body">
                    <?php if (!empty($creneaux_du_jour)): ?>
                        <?php foreach ($creneaux_du_jour as $creneau): ?>
                            <?php if ($creneau['id_personne'] === null): ?>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <!-- Enlever les secondes de l'heure -->
                                    <span><?= (new DateTime($creneau['heure']))->format('H:i') ?></span>
                                    <form method="POST" action="reserver_creneaux.php">
                                        <input type="hidden" name="medecin_id" value="<?= $medecin_id ?>">
                                        <input type="hidden" name="etablissement_id" value="<?= $etablissement_id ?>">
                                        <input type="hidden" name="specialite_id" value="<?= $specialite_id ?>">
                                        <input type="hidden" name="reserver_id" value="<?= $creneau['rdv_id'] ?>">
                                        <button type="submit" class="btn btn-success btn-sm">Réserver</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Aucun créneau disponible ce jour.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</body>
</html>

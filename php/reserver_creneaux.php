<?php
session_start();

// Vérification de l'existence de l'ID du médecin, établissement et spécialité
if (isset($_GET['medecin_id']) && isset($_GET['etablissement_id']) && isset($_GET['specialite_id'])) {
    $medecin_id = $_GET['medecin_id'];
    $etablissement_id = $_GET['etablissement_id'];  // ID de l'établissement
    $specialite_id = $_GET['specialite_id'];  // ID de la spécialité

    // Paramètres de connexion à la base de données
    $dsn = 'pgsql:dbname=projet_doct;host=localhost;port=5432';
    $user = 'postgres';
    $password = 'Isen44';

    try {
        $pdo = new PDO($dsn, $user, $password);

        // Requête SQL pour récupérer les créneaux disponibles pour ce médecin, établissement et spécialité
        $sql = "
            SELECT 
                Rdv.id AS rdv_id,
                CONCAT(Rdv.date, ' ', Rdv.heure) AS date_heure,
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
                Rdv.date > CURRENT_DATE  -- Filtrer pour les créneaux à venir
            AND
                Rdv.id_personne IS NULL  -- S'assurer que le créneau n'est pas déjà réservé
            ORDER BY 
                Rdv.date, Rdv.heure ASC;
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':medecin_id', $medecin_id, PDO::PARAM_INT);
        $stmt->bindParam(':etablissement_id', $etablissement_id, PDO::PARAM_INT);
        $stmt->bindParam(':specialite_id', $specialite_id, PDO::PARAM_INT);
        $stmt->execute();
        $creneaux = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        echo '<p>Erreur de connexion : ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
} else {
    echo '<p>Aucun médecin, établissement ou spécialité sélectionné.</p>';
    exit;
}

// Réservation du créneau
if (isset($_GET['reserver_id'])) {
    if (!isset($_SESSION['id_personne'])) {
        echo "Vous devez être connecté pour réserver un créneau.";
        exit;
    }

    // Récupérer l'ID de la personne connectée
    $id_personne = $_SESSION['id_personne']; 
    $rdv_id = $_GET['reserver_id'];

    try {
        $pdo = new PDO($dsn, $user, $password);

        // Requête SQL pour mettre à jour le créneau avec l'ID de la personne
        $sql_update = "
            UPDATE Rdv 
            SET id_personne = :id_personne 
            WHERE id = :rdv_id AND id_personne IS NULL
        ";
        $stmt = $pdo->prepare($sql_update);
        $stmt->bindParam(':id_personne', $id_personne, PDO::PARAM_INT);
        $stmt->bindParam(':rdv_id', $rdv_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo "Réservation effectuée avec succès!";
            // Rediriger pour éviter la double soumission
            header("Location: prendre_rdv_patient.php?medecin_id=$medecin_id&etablissement_id=$etablissement_id&specialite_id=$specialite_id");
            exit;
        } else {
            echo "Erreur lors de la réservation. Veuillez réessayer.";
        }

    } catch (PDOException $e) {
        echo 'Erreur de connexion : ' . htmlspecialchars($e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <title>Créneaux disponibles</title>
        <style>
            body {
                padding-top: 70px; /* Ajustez cette valeur à la hauteur de votre navbar */
            }
        </style>
    </head>
    <body>
        <!-- Navbar Bootstrap -->
        <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">Tibobo</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link active" href="espace_utilisateur.php">Accueil</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="mon_espace_patient.php">Mon espace</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="prendre_rdv_patient.php">Prendre rendez-vous</a>
                        </li>
                    </ul>
                    <ul class="navbar-nav ms-auto">
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
            </div>
        </nav>

        <!-- Conteneur principal avec un peu d'espacement après la navbar -->
        <div class="container mt-5">
            <h1>Créneaux disponibles pour le médecin</h1>

            <?php if (!empty($creneaux)): ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Créneau</th>
                            <th>Établissement</th>
                            <th>Spécialité</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($creneaux as $creneau): ?>
                            <tr>
                                <td><?= htmlspecialchars($creneau['date_heure']) ?></td>
                                <td><?= htmlspecialchars($creneau['etablissement']) ?></td>
                                <td><?= htmlspecialchars($creneau['specialite']) ?></td>
                                <td>
                                    <!-- Lien pour réserver un créneau -->
                                    <a href="prendre_rdv_patient.php?medecin_id=<?= $medecin_id ?>&etablissement_id=<?= $etablissement_id ?>&specialite_id=<?= $specialite_id ?>&reserver_id=<?= $creneau['rdv_id'] ?>" class="btn btn-success">Réserver</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Aucun créneau disponible pour ce médecin, établissement et spécialité.</p>
            <?php endif; ?>
        </div>

        <!-- Script Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
    </body>
</html>

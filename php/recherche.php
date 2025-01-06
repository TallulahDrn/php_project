<?php
session_start();

// Vérification si l'utilisateur est connecté
$estConnecte = isset($_SESSION['utilisateur_id']);

// Connexion à la base de données
$dsn = 'pgsql:dbname=projet_doct;host=localhost;port=5432';
$user = 'postgres';
$password = 'Isen44';

try {
    $pdo = new PDO($dsn, $user, $password);

    // Initialiser la variable de résultats
    $results = [];
    $show_results = false;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['recherche'])) {
        // Recherche par mot-clé
        $recherche = trim($_POST['recherche']);
        $recherche = "%" . $recherche . "%";

        $sql = "
        SELECT 
            Medecin.id AS medecin_id,
            Personne.nom,
            Personne.prenom,
            Specialite.nom_specialite AS specialite,
            Etablissement.adresse AS etablissement,
            Etablissement.id AS etablissement_id,
            Specialite.id AS specialite_id
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
            OR Etablissement.adresse ILIKE :recherche)
        GROUP BY 
            Medecin.id, Personne.nom, Personne.prenom, Specialite.nom_specialite, Etablissement.adresse, Etablissement.id, Specialite.id
        ORDER BY 
            Personne.nom, Personne.prenom, Etablissement.adresse;
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':recherche', $recherche, PDO::PARAM_STR);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $show_results = !empty($results);
    } else {
        // Requête par défaut pour afficher tous les médecins
        $sql = "
        SELECT 
            Medecin.id AS medecin_id,
            Personne.nom,
            Personne.prenom,
            Specialite.nom_specialite AS specialite,
            Etablissement.adresse AS etablissement,
            Etablissement.id AS etablissement_id,
            Specialite.id AS specialite_id
        FROM 
            Medecin
        JOIN Personne ON Medecin.id_personne = Personne.id
        JOIN possede ON Medecin.id = possede.id_medecin
        JOIN Specialite ON possede.id_specialite = Specialite.id
        JOIN Rdv ON Medecin.id = Rdv.id_medecin
        JOIN Etablissement ON Rdv.id_etablissement = Etablissement.id
        GROUP BY 
            Medecin.id, Personne.nom, Personne.prenom, Specialite.nom_specialite, Etablissement.adresse, Etablissement.id, Specialite.id
        ORDER BY 
            Personne.nom, Personne.prenom, Etablissement.adresse;
        ";

        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $show_results = !empty($results);
    }
} catch (PDOException $e) {
    echo '<p>Erreur de connexion : ' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>


<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <link href="../css/recherche.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <title>Recherche</title>
    </head>
    <body>
        <nav class="navbar">
            <div class="container-fluid d-flex justify-content-between">
                <a class="navbar-brand">Tibobo</a>

                <ul class="navbar-nav d-flex flex-row align-items-center"> <!-- Liens de navigation à gauche -->
                    <li class="nav-item">
                        <a class="nav-link " href="../html/accueil.html">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="recherche.php">Recherche</a>
                    </li>
                </ul>

                <!-- Bouton "Aide" et image alignés à droite -->
                <ul class="navbar-nav d-flex flex-row align-items-center ms-auto"> 
                    <li class="nav-item">
                        <a class="nav-link" href="../html/aide.html">Aide</a>
                    </li>
                    <li class="nav-item">
                        <a href="../html/accueil.html"> <img src="../images/anonyme.png" style="height:50px;width:50px"></a>
                    </li>
                </ul>
            </div>
        </nav>

        <div class="container my-5 light_green">
            <h1>Recherche</h1>

            <form method="POST" action="">
                <div class="mb-3">
                    <label for="recherche" class="form-label">Rechercher un médecin, une spécialité ou un établissement :</label>
                    <input type="text" id="recherche" name="recherche" class="form-control" placeholder="Tapez ici..." required>
                </div>
                <button type="submit" class="btn dark_green">Rechercher</button>
            </form>

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
                                    <?php if (!$estConnecte): ?>
                                        <p class="text-danger">Veuillez vous <a href="connexion_patient.php">connecter</a> ou <a href="../html/inscription_patient.html">inscrire</a> pour réserver un rendez-vous.</p>
                                    <?php else: ?>
                                        <form method="POST" action="reserver_creneaux.php">
                                            <input type="hidden" name="medecin_id" value="<?= htmlspecialchars($row['medecin_id']) ?>">
                                            <input type="hidden" name="etablissement_id" value="<?= htmlspecialchars($row['etablissement_id']) ?>">
                                            <input type="hidden" name="specialite_id" value="<?= htmlspecialchars($row['specialite_id']) ?>">
                                            <button type="submit" class="btn dark_green">Voir les créneaux</button>
                                        </form>
                                    <?php endif; ?>
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
    </body>
</html>

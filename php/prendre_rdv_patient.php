<?php
    // Si la méthode de la requête est POST et qu'il y a une requête
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['query'])) {
        $dsn = 'pgsql:dbname=projet_doct;host=localhost;port=5432';
        $user = 'postgres';
        $password = 'Isen44';

        try {
            $pdo = new PDO($dsn, $user, $password);
            $query = trim($_POST['query']);

            // Échapper les caractères spéciaux pour éviter des erreurs de syntaxe
            $query = "%" . $query . "%";  // Ajouter les jokers % pour la recherche LIKE/ILIKE

            // Requête SQL pour récupérer les médecins et leurs établissements sans horaires de RDV
            $sql = "
            SELECT 
                Medecin.id AS medecin_id,
                Personne.nom,
                Personne.prenom,
                Specialite.nom_specialite AS specialite,
                Etablissement.adresse AS etablissement
            FROM 
                Medecin
            JOIN Personne ON Medecin.id_personne = Personne.id
            JOIN possede ON Medecin.id = possede.id_medecin
            JOIN Specialite ON possede.id_specialite = Specialite.id
            JOIN Rdv ON Medecin.id = Rdv.id_medecin
            JOIN Etablissement ON Rdv.id_etablissement = Etablissement.id
            WHERE 
                (Personne.nom ILIKE :query 
                OR Specialite.nom_specialite ILIKE :query 
                OR Etablissement.adresse ILIKE :query)
            GROUP BY 
                Medecin.id, Personne.nom, Personne.prenom, Specialite.nom_specialite, Etablissement.adresse
            ORDER BY 
                Personne.nom, Personne.prenom, Etablissement.adresse;
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':query', $query, PDO::PARAM_STR); // Liens sécurisés avec les paramètres
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Afficher les résultats si la requête a retourné des résultats
            $show_results = !empty($results); // Vérifie s'il y a des résultats à afficher
        } catch (PDOException $e) {
            echo '<p>Erreur de connexion : ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
    }
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <link href="../css/prendre_rdv_patient.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <link href="https://fonts.googleapis.com/css2?family=Lalezar&family=Marko+One&family=Roboto&family=Stint+Ultra+Expanded&display=swap" rel="stylesheet">

        <title> Prendre rendez-vous </title>
    </head>
    <body>
        
        <nav class="navbar">
            <div class="container-fluid d-flex justify-content-between">
                <a class="navbar-brand">Tibobo</a>
                <ul class="navbar-nav d-flex flex-row align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="../php/espace_utilisateur.php">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../php/mon_espace_patient.php">Mon espace</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="prendre_rdv_patient.php">Prendre rendez-vous</a>
                    </li>
                </ul>
                <ul class="navbar-nav d-flex flex-row align-items-center ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="../php/espace_utilisateur.php" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="../images/malade.png" style="height:50px;width:50px">
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="../php/deconnexion.php">Se déconnecter</a></li>
                            <li><a class="dropdown-item" href="aide_patient.html">Aide</a></li>
                            <li><a class="dropdown-item" href="../php/mon_compte_patient.php">Mon compte</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>

        <div class="container mt-5">
            <h1>Prendre un rendez-vous</h1>

            <!-- Formulaire de recherche -->
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="search-input" class="form-label">Rechercher un médecin, une spécialité ou un établissement :</label>
                    <input type="text" id="search-input" name="query" class="form-control" placeholder="Tapez ici..." required>
                </div>
                <button type="submit" class="btn btn-primary">Rechercher</button>
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
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $row): ?>
                                <tr>
                                    <td>DR. <?= htmlspecialchars($row['nom']) ?> <?= htmlspecialchars($row['prenom']) ?></td>
                                    <td><?= htmlspecialchars($row['specialite']) ?></td>
                                    <td><?= htmlspecialchars($row['etablissement']) ?></td>
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

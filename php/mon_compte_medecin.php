<?php
    // Démarrer la session pour pouvoir accéder aux variables de session
    session_start();

    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['user_id'])) {
        header("Location: connexion.php"); // Rediriger vers la page de connexion si non connecté
        exit();
    }

    // Connexion à la base de données PostgreSQL
    $conn = pg_connect("host=localhost dbname=projet_doct user=postgres password=Isen44");

    if (!$conn) {
        die("Erreur de connexion à la base de données : ". pg_last_error());
    }

    // Récupérer l'ID de l'utilisateur depuis la session
    $user_id = $_SESSION['user_id'];

    // Requête pour récupérer les informations de l'utilisateur
    //$query = "SELECT prenom, nom, email, telephone FROM personne WHERE id = $1";
    $query = "
    SELECT 
        personne.prenom, 
        personne.nom, 
        personne.email, 
        personne.telephone, 
        specialite.nom_specialite AS specialite, 
        medecin.code_postal
        FROM personne
        LEFT JOIN medecin ON personne.id = medecin.id_personne
        LEFT JOIN possede ON medecin.id = possede.id_medecin
        LEFT JOIN specialite ON possede.id_specialite = specialite.id
        WHERE personne.id = $1";
    
    $result = pg_query_params($conn, $query, [$user_id]);

    if (!$result) {
        die("Erreur lors de l'exécution de la requête : " . pg_last_error($conn));
    }

    $user_info = pg_fetch_assoc($result);

    // Fermer la connexion et libérer les ressources
    pg_free_result($result);
    pg_close($conn);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <link href="../css/mon_compte_patient.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>

    <title>Mon compte</title>
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
                    <a class="nav-link" href="planning.php">Planning</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="mes_patients.php">Mes patients</a>
                </li>
            </ul>

            <!-- Bouton "Aide" et image alignés à droite -->
            <ul class="navbar-nav d-flex flex-row align-items-center ms-auto"> 

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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
    
    <div class="container">
        <div class="info-box">
            <div class="titre">
                <h1>Mon compte</h1>
            </div>
            
            <div class="blabla">
            <!-- Afficher les informations de l'utilisateur -->
                <p><strong>Mes informations: </strong></p>
                <p><strong>Prénom :</strong> <?php echo htmlspecialchars($user_info['prenom']); ?></p>
                <p><strong>Nom :</strong> <?php echo htmlspecialchars($user_info['nom']); ?></p>
                <p><strong>Email :</strong> <?php echo htmlspecialchars($user_info['email']); ?></p>
                <p><strong>Téléphone :</strong> <?php echo htmlspecialchars($user_info['telephone']); ?></p>
                <p><strong>Spécialité :</strong> <?php echo htmlspecialchars($user_info['specialite']); ?></p>
                <p><strong>Code postal :</strong> <?php echo htmlspecialchars($user_info['code_postal']); ?></p>

            </div>
            <!-- Bouton pour modifier les informations (optionnel) -->
            <!--<a href="modifier_compte.php" class="btn btn-primary">Modifier mes informations</a>-->
        </div>

    </div>


    
</body>
</html>

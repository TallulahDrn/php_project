<?php

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    session_start(); // Démarrer la session

    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_email'])) {
        die("Erreur : L'utilisateur n'est pas connecté ou les variables de session ne sont pas définies.");
    }
    //echo "User ID : " . $_SESSION['user_id'] . "<br>";
    //echo "User Email : " . $_SESSION['user_email'] . "<br>";
    //exit();



    // Connexion PostgreSQL
    $conn = pg_connect("host=localhost dbname=projet_doct user=postgres password=Isen44");

    if (!$conn) {
        die("Erreur de connexion à la base de données : ". pg_last_error());
    }
    //echo "Connexion réussie !";
    //exit();

    // Récupérer l'ID de l'utilisateur depuis la session
    $user_id = $_SESSION['user_id'];

    // Initialiser les variables pour le prénom et le nom
    $user_firstname = '';
    $user_lastname = '';

    // Requête pour récupérer le prénom et le nom
    $query = "SELECT prenom, nom FROM personne WHERE id = $1";
    $result = pg_query_params($conn, $query, [$user_id]);  //méthode pour executer des requêtes avec des paramètres évitant les injections SQL


    if (!$result) {
        die("Erreur lors de l'exécution de la requête : " . pg_last_error($conn));
    }

    if (pg_num_rows($result) > 0) {
    $row = pg_fetch_assoc($result);
    $user_firstname = htmlspecialchars($row['prenom']);
    $user_lastname = htmlspecialchars($row['nom']);
    } 
    else {
        die("Erreur : Aucun utilisateur trouvé avec cet ID.");
    }

    // Fermer la connexion si elle n'est plus utilisée
    pg_free_result($result); //permet de libérer les résultats pour optimiser la mémoire
    pg_close($conn);


// Vous pouvez aussi récupérer d'autres informations depuis la base de données si nécessaire
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <link href="../css/espace_utilisateur.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>

    <title>Espace Utilisateur</title>
</head>
<body>
    <nav class="navbar">
        <div class="container-fluid d-flex justify-content-between">
            <a class="navbar-brand">Tibobo</a>

            <ul class="navbar-nav d-flex flex-row align-items-center"> <!-- Liens de navigation à gauche -->
                <li class="nav-item">
                    <a class="nav-link active" href="espace_medecin.php">Accueil</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Mon espace</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Planning</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Mes patients</a>
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
                        <li><a class="dropdown-item" href="#">Mon compte</a></li>

                    </ul>
                    
                </li>


            </ul>
        </div>
    </nav>
    
    <div class="container">
        <div class="bienvenue-box">
            <h1>Bienvenue, <?php echo $user_firstname . ' ' . $user_lastname; ?> !</h1>
            <img id="icone" src="../images/medecin.png" style="height:250px;width:250px">
        
            
        </div>

    </div>


    
</body>
</html>

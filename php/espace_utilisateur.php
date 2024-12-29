<?php
    session_start(); // Démarrer la session

    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['user_id'])) {
        // Si l'utilisateur n'est pas connecté, rediriger vers la page de connexion
        header("Location: connexion_patient.html");
        exit();
    }

    // Récupérer les informations de l'utilisateur depuis la session
    $user_email = $_SESSION['user_email'];

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
						<a class="nav-link active" href="#">Mon espace</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="#">Mes rendez-vous</a>
					</li>
				</ul>

				<!-- Bouton "Aide" et image alignés à droite -->
				<ul class="navbar-nav d-flex flex-row align-items-center ms-auto"> 

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="../images/femelle.png" style="height:50px;width:50px">
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="deconnexion.php">Se déconnecter</a></li>
                            <li><a class="dropdown-item" href="../html/aide.html">Aide</a></li>
                        </ul>
                        
                    </li>


				</ul>
			</div>
		</nav>
    
    <div class="container">
        <h1>Bienvenue, <?php echo htmlspecialchars($user_email); ?></h1>
        <!-- Afficher d'autres informations de l'utilisateur si nécessaire -->
    </div>



    
</body>
</html>

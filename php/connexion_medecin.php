<?php 

    
    session_start(); // Démarrer une session


    // Initialiser un message d'erreur vide
    $messageErreur = '';

    // Vérifier si les informations du formulaire ont été envoyées
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        // Récupérer l'email et le mot de passe du formulaire
        $email = $_POST['mail'];
        $motDePasse = $_POST['motdepasse'];

        // Connexion à la base de données
        $conn = pg_connect('host=localhost dbname=projet_doct user=postgres password=Isen44');
        if (!$conn) {
            die("Erreur de connexion à la base de données : " . pg_last_error($conn));
        }

        // Préparer la requête pour récupérer les informations de l'utilisateur par email
        $sql = "SELECT * FROM personne WHERE email = $1";
        $result = pg_query_params($conn, $sql, array($email));

        if ($result) {
            $user = pg_fetch_assoc($result); // Récupérer les informations de l'utilisateur

            // Vérifier si l'utilisateur existe
            if ($user) {

                // Vérifier que l'utilisateur est un médecin (medecin = 't' pour postgresql)
                if ($user['medecin'] == 't') {
                    // Comparer le mot de passe fourni avec le mot de passe stocké dans la base de données
                    if (password_verify($motDePasse, $user['mot_de_passe'])) {
                        // Si les informations sont correctes, connecter l'utilisateur

                        // Démarrer la session
                        $_SESSION['user_id'] = $user['id']; // Vous pouvez stocker d'autres informations, comme le nom, etc.
                        $_SESSION['user_email'] = $user['email'];
                        
                        // Rediriger vers l'espace utilisateur
                        header("Location: espace_medecin.php");
                        exit();
                    } 
                    else {
                        // Si le mot de passe est incorrect
                        $messageErreur = "Mot de passe incorrect.";
                    }
                }
                else{
                    //echo "Vous devez vous connecter via la page Médecin.";
                                        
                    // Si l'utilisateur est un médecin
                    $messageErreur = "Vous devez vous connecter via la page Patient, vous allez être redirigé dans quelques instants. ";
                    //echo "<script>window.location.href = 'connexion_patient.php';</script>";
                    //exit();
                    $redirectionNecessaire = true;

                }
            } 
            else {
                // Si l'email n'existe pas dans la base de données
                //echo "Aucun medecin trouvé avec cet email.";
                $messageErreur = "Aucun médecin trouvé avec cet email.";

            }
        } 
        else {
            // Si la requête échoue
            echo "Erreur de requête : " . pg_last_error($conn);
        }

        // Fermer la connexion à la base de données
        pg_close($conn);
    }
?>


<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <link href="../css/inscription.css" rel="stylesheet">
        <!--<script src="script.js"></script> -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <title>Connexion Médecin</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">

    </head>
    <body>
        <nav class="navbar">
			<div class="container-fluid d-flex justify-content-between">
				<a class="navbar-brand">Tibobo</a>

				<ul class="navbar-nav d-flex flex-row align-items-center"> <!-- Liens de navigation à gauche -->
					<li class="nav-item">
						<a class="nav-link active" href="../html/accueil.html">Accueil</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="recherche.php">Recherche</a>
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

        <br>
        <br>
        <div class="light_green container">
            <h1 class="text-center ">Connectez-vous</h1>
            <img id="icone" src="../images/medecin.png" class="d-block mx-auto">
            <br>

            <?php if (!empty($messageErreur)): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($messageErreur) ?>
            </div>

            <?php if (!empty($redirectionNecessaire)): ?>
            <script>
                // Redirige après un délai de 3 secondes
                setTimeout(function() {
                    window.location.href = 'connexion_patient.php';
                }, 3000); // 3000 millisecondes = 3 secondes
            </script>
            <?php endif; ?>

            <?php endif; ?>


            <form class="formulaire d-flex flex-column align-items-center" action="../php/connexion_medecin.php" method="POST">
                    
                    <div class="mb-3 w-100">
                        <label for="mail1" class="form-label">Saisissez votre adresse mail : *</label>
                        <div class="form-floating">
                            <input type="email" class="form-control" id="mail" name="mail" placeholder="nom@mail.com" required>
                            <label for="mail">nom@mail.com</label>
                        </div>
                    </div>
                    
                    <div class="mb-3 w-100">
                        <label for="motdepasse" class="form-label">Saisissez votre mot de passe : *</label>
                        <div class="form-floating">
                            <input type="password" class="form-control" id="motdepasse" name="motdepasse" placeholder="**********"  pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{10,}$"  required>
                            <label for="motdepasse">**********</label>
                        </div>
                        
                        <div class="mdp_oublie">
                            <a href="../html/mot_passe.html">Mot de passe oublié ?</a>
                        </div>
                    </div>
                
                
                <div class="d-flex justify-content-center">
                    <input class="btn dark_green" type="submit" value="Se connecter">
                </div>
            </form>
        </div>
        <br>
        <br>
        <br>
    </body>
</html>
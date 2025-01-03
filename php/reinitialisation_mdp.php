<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Connexion à la base de données
    $conn = pg_connect('host=localhost dbname=projet_doct user=postgres password=Isen44');
    if (!$conn) {
        echo json_encode(['status' => 'error', 'message' => 'Erreur de connexion à la base de données.']);
        exit();
    }



    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        
        // Vérification si l'email est bien passé en POST (car c'est la méthode utilisée pour soumettre le formulaire)
        if (!isset($_POST['email'])) {
            echo json_encode(['status' => 'error', 'message' => 'L\'email est manquant.']);
            exit();
        }
        
        $email = $_POST['email']; // Récupérer l'email passé en POST
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
            
        
        // Vérifier si l'email existe dans la base de données
        $email_check_sql = "SELECT * FROM personne WHERE email = $1";
        $email_check_result = pg_query_params($conn, $email_check_sql, array($email));


        if (!$email_check_result) {
            echo json_encode(['status' => 'error', 'message' => 'Erreur dans la requête SQL: ' . pg_last_error($conn)]);
            exit();
        }
        

        if (pg_num_rows($email_check_result) == 0) {
            echo json_encode(['status' => 'error', 'message' => 'Aucun utilisateur trouvé avec cet email.']);
            exit();
        }

        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

            // Mise à jour du mot de passe dans la base de données
            $sql = "UPDATE personne SET mot_de_passe = $1 WHERE email = $2";
            $result = pg_query_params($conn, $sql, array($hashed_password, $email));

            if ($result) {
                echo json_encode(['status' => 'success', 'message' => 'Mot de passe réinitialisé avec succès. Vous allez être redirigés vers la page de connexion']);
            } else {
                $error_message = pg_last_error($conn);
                echo json_encode(['status' => 'error', 'message' => 'Erreur lors de la réinitialisation du mot de passe: ' . $error_message]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Les mots de passe ne correspondent pas.']);
        }

        pg_close($conn);
        exit();  // Assurez-vous de quitter après avoir envoyé la réponse JSON
    }
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <link href="../css/inscription.css" rel="stylesheet">
        <script src="../js/reinitialiser_mdp.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <title>Réinitialisation du Mot de Passe</title>
    </head>
    <body>
        <div class="container mt-5">
            <h1 class="text-center">Réinitialisation du Mot de Passe</h1>

            <?php
            // Vérification que l'email est bien passé dans l'URL
            if (isset($_GET['email'])) {
                $email = $_GET['email']; // Récupérer l'email de l'URL
            ?>
            
            <form action="reinitialisation_mdp.php" method="POST" class="mt-4" id="reinitialiserForm">
                <!-- Champ caché pour envoyer l'email -->
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>"> 

                <div class="mb-3">
                    <label for="new_password" class="form-label">Nouveau Mot de Passe :</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirmer le Mot de Passe :</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Réinitialiser le Mot de Passe</button>
            </form>

            <?php
            } else {
                echo '<p class="text-danger">L\'email est manquant. Veuillez vérifier l\'URL.</p>';
            }
            ?>

        </div>


        <!-- Modal pour afficher les résultats -->
        <div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="resultModalLabel">Résultat de la Réinitialisation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="modalMessage"></div> <!-- Element où vous insérerez les messages -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
                </div>
            </div>
        </div>



    </body>
</html>

<?php 
    session_start(); // Démarrer une session

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
                // Comparer le mot de passe fourni avec le mot de passe stocké dans la base de données
                if (password_verify($motDePasse, $user['mot_de_passe'])) {
                    // Si les informations sont correctes, connecter l'utilisateur

                    // Démarrer la session
                    $_SESSION['user_id'] = $user['id']; // Vous pouvez stocker d'autres informations, comme le nom, etc.
                    $_SESSION['user_email'] = $user['email'];
                    
                    // Rediriger vers l'espace utilisateur
                    header("Location: espace_utilisateur.php");
                    exit();
                } 
                else {
                    // Si le mot de passe est incorrect
                    echo "Mot de passe incorrect.";
                }
            } 
            else {
                // Si l'email n'existe pas dans la base de données
                echo "Aucun utilisateur trouvé avec cet email.";
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

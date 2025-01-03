<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json'); // Réponse en JSON

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $telephone = $_POST['telephone'];

    // Connexion à la base de données
    $conn = pg_connect('host=localhost dbname=projet_doct user=postgres password=Isen44');
    if (!$conn) {
        echo json_encode(['status' => 'error', 'message' => 'Erreur de connexion à la base de données.']);
        exit();
    }

    // Requête pour vérifier les informations de l'utilisateur
    $sql = "SELECT * FROM personne WHERE email = $1 AND nom = $2 AND prenom = $3 AND telephone = $4";
    $result = pg_query_params($conn, $sql, array($email, $nom, $prenom, $telephone));

    if ($result) {
        $user = pg_fetch_assoc($result);
        if ($user) {
            echo json_encode(['status' => 'success', 'message' => 'Les informations sont correctes, vous allez être redirigé vers la page de réinitialisation dans quelques instants.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => "Aucun utilisateur trouvé avec ces informations."]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erreur de requête : ' . pg_last_error($conn)]);
    }

    pg_close($conn);
}
?>

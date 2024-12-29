<?php
// Initialisation de l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start(); // Démarrer la session

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_email'])) {
    die("Erreur : L'utilisateur n'est pas connecté ou les variables de session ne sont pas définies.");
}

// Connexion PostgreSQL
$conn = pg_connect("host=localhost dbname=projet_doct user=postgres password=Isen44");

if (!$conn) {
    die("Erreur de connexion à la base de données : ". pg_last_error());
}

// Récupérer l'ID de l'utilisateur depuis la session
$user_id = $_SESSION['user_id'];

// Initialiser les variables pour le prénom et le nom
$user_firstname = '';
$user_lastname = '';

// Requête pour récupérer le prénom et le nom
$query = "SELECT prenom, nom FROM personne WHERE id = $1";
$result = pg_query_params($conn, $query, [$user_id]);

if (!$result) {
    die("Erreur lors de l'exécution de la requête : " . pg_last_error($conn));
}

if (pg_num_rows($result) > 0) {
    $row = pg_fetch_assoc($result);
    $user_firstname = htmlspecialchars($row['prenom']);
    $user_lastname = htmlspecialchars($row['nom']);
} else {
    die("Erreur : Aucun utilisateur trouvé avec cet ID.");
}

// Fermer la connexion à la base de données
pg_free_result($result);
pg_close($conn);

// Inclure le fichier HTML pour l'affichage
include('../html/espace_utilisateur.html');
?>

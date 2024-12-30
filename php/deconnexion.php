<?php
    session_start();
    session_unset(); // Libérer toutes les variables de session
    session_destroy(); // Détruire la session
    header("Location: ../html/accueil.html"); // Rediriger vers la page de connexion
    exit();
?>

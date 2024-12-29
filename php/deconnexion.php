<?php
session_start();
session_destroy();
header("Location: ../html/connexion.html");
exit();
?>

<?php
// Démarre la session
session_start();

// Détruit toutes les variables de session
session_unset();
session_destroy();

// Redirige vers la page de connexion
header('Location: index.php');
exit();
?>
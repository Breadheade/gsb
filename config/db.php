<?php
// Connexion à la BDD 'appli_frais' avec PDO

try {
    $bdd = new PDO('mysql:host=localhost;dbname=appli_frais;charset=utf8', 'appli_user', 'Azerty31');
    // Si tu as un mot de passe MySQL, remplace '' par ton mot de passe
} catch (Exception $e) {
    die('Erreur : ' . $e->getMessage());
}
?>
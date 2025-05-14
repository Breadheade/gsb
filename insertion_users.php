<?php
require_once('config/db.php');

$utilisateurs = [
    ['Martin', 'Nathan', 'nathan', 'nathan123', 'visiteur'],
    ['Guibert', 'Eddy', 'eddy', 'eddy123', 'visiteur'],
    ['Gerbault', 'Nils', 'nils', 'nils123', 'comptable']
];

foreach ($utilisateurs as $user) {
    $hash = password_hash($user[3], PASSWORD_DEFAULT);
    $sql = $bdd->prepare("INSERT INTO visiteur (nom, prenom, login, mdp, role) VALUES (?, ?, ?, ?, ?)");
    $sql->execute([$user[0], $user[1], $user[2], $hash, $user[4]]);
}

echo "Utilisateurs insérés avec succès.";
?>
<?php
require_once('config/db.php');

// Récupère la date du jour
$aujourdhui = date('Y-m-d');
$moisActuel = date('m');
$anneeActuelle = date('Y');

// Si on est après le 10 du mois, on doit clôturer les fiches du mois précédent
if (date('d') > 10) {
    $moisPrecedent = $moisActuel - 1;
    if ($moisPrecedent == 0) { // Si janvier → on passe à décembre de l'année précédente
        $moisPrecedent = 12;
        $anneeActuelle -= 1;
    }

    $moisPrecedentFormat = str_pad($moisPrecedent, 2, '0', STR_PAD_LEFT); // Ex : 03

    // Mets à jour toutes les fiches qui sont "CR" (Créées) sur "CL" (Clôturées)
    $requete = $bdd->prepare('
        UPDATE fichefrais 
        SET idEtat = "CL", dateModif = CURDATE() 
        WHERE mois = ? AND idEtat = "CR"
    ');
    $requete->execute([$moisPrecedentFormat]);

    echo "✅ Clôture automatique effectuée pour les fiches du mois $moisPrecedentFormat.";
} else {
    echo "ℹ️ Pas de clôture automatique (on est avant le 10 du mois).";
}
?>
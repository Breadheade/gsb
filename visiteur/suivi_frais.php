<?php
session_start();
require_once('../config/db.php');

// Sécurité : seul le visiteur peut accéder à cette page
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'visiteur') {
    header('Location: ../index.php');
    exit();
}

$idVisiteur = $_SESSION['id'];

// Récupération des fiches de frais du visiteur
$requete = $bdd->prepare('
    SELECT mois, dateModif, idEtat
    FROM fichefrais
    WHERE idVisiteur = ?
    ORDER BY mois DESC
');
$requete->execute([$idVisiteur]);
$fiches = $requete->fetchAll();

// Récupération des états possibles (exemple : "CR" = Créée, "CL" = Clôturée, etc.)
$etats = [
    'CR' => 'Créée',
    'CL' => 'Clôturée',
    'VA' => 'Validée',
    'MP' => 'Mise en paiement',
    'RB' => 'Remboursée'
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Suivi des fiches de frais - Visiteur</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="logo-container">
        <img src="../img/logo2_gsb.png" alt="Logo GSB" class="logo-gsb">
    </div>

    <h1>Suivi de vos fiches de frais</h1>

    <?php if (empty($fiches)) : ?>
        <p>Vous n'avez encore saisi aucune fiche de frais.</p>
    <?php else : ?>
        <table style="width: 80%; margin: auto; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="padding: 10px; border-bottom: 1px solid #555;">Mois</th>
                    <th style="padding: 10px; border-bottom: 1px solid #555;">État</th>
                    <th style="padding: 10px; border-bottom: 1px solid #555;">Dernière modification</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($fiches as $fiche) : ?>
                    <tr>
                        <td style="text-align: center; padding: 10px;"><?php echo htmlspecialchars($fiche['mois']); ?></td>
                        <td style="text-align: center; padding: 10px;"><?php echo isset($etats[$fiche['idEtat']]) ? $etats[$fiche['idEtat']] : 'Inconnu'; ?></td>
                        <td style="text-align: center; padding: 10px;"><?php echo htmlspecialchars($fiche['dateModif']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <br><br>
<div style="text-align: center; margin-top: 20px;">
    <a href="saisie_frais.php" style="text-decoration: none;">
        <button class="bouton-retour">🔙 Retour à la saisie</button>
    </a>

    <a href="../logout.php" style="text-decoration: none;">
        <button class="bouton-deconnexion">🚪 Déconnexion</button>
    </a>
</div>

</body>
</html>
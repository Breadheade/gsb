<?php
session_start();
require_once('../config/db.php');

// Sécurité : seul le comptable peut accéder à cette page
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'comptable') {
    header('Location: ../index.php');
    exit();
}

// ----- Récupération des visiteurs -----
$visiteurs = $bdd->query('SELECT id, nom, prenom, login FROM visiteur WHERE role = "visiteur"');

// ----- Si un visiteur est sélectionné -----
if (isset($_POST['visiteur_id'])) {
    $idVisiteur = $_POST['visiteur_id'];

    // Récupération des mois pour ce visiteur
    $moisQuery = $bdd->prepare('SELECT DISTINCT mois FROM fichefrais WHERE idVisiteur = ?');
    $moisQuery->execute([$idVisiteur]);
    $moisDisponibles = $moisQuery->fetchAll();
}

// ----- Si visiteur + mois sélectionnés -----
if (isset($_POST['mois'])) {
    $idVisiteur = $_POST['visiteur_id'];
    $moisChoisi = $_POST['mois'];

    // ----- Récupération des frais forfaitisés -----
    $ficheQuery = $bdd->prepare('SELECT id FROM fichefrais WHERE idVisiteur = ? AND mois = ?');
    $ficheQuery->execute([$idVisiteur, $moisChoisi]);
    $fiche = $ficheQuery->fetch();
    $ficheId = $fiche['id'];

    $fraisForfait = $bdd->prepare('
        SELECT lignefraisforfait.idFraisForfait, fraisforfait.libelle, lignefraisforfait.quantite
        FROM lignefraisforfait
        INNER JOIN fraisforfait ON lignefraisforfait.idFraisForfait = fraisforfait.id
        WHERE lignefraisforfait.idFicheFrais = ?
    ');
    $fraisForfait->execute([$ficheId]);

    // ----- Récupération des frais hors forfait -----
    $fraisHorsForfait = $bdd->prepare('
        SELECT id, libelle, date, montant
        FROM lignefraishorsforfait
        WHERE idFicheFrais = ?
    ');
    $fraisHorsForfait->execute([$ficheId]);
}

// ----- Traitement de la validation -----
if (isset($_POST['valider_fiche'])) {
    $idFiche = $_POST['fiche_id'];
    $updateEtat = $bdd->prepare('UPDATE fichefrais SET idEtat = "VA" WHERE id = ?'); // "VA" = Validée
    $updateEtat->execute([$idFiche]);
    $message = "✅ Fiche de frais validée avec succès.";
}
if (isset($_POST['mettre_en_paiement'])) {
    $idFiche = $_POST['fiche_id'];
    $updateEtat = $bdd->prepare('UPDATE fichefrais SET idEtat = "MP" WHERE id = ?');
    $updateEtat->execute([$idFiche]);
    $message = "💸 Fiche de frais mise en paiement avec succès.";
}
if (isset($_POST['rembourser_fiche'])) {
    $idFiche = $_POST['fiche_id'];
    $updateEtat = $bdd->prepare('UPDATE fichefrais SET idEtat = "RB" WHERE id = ?');
    $updateEtat->execute([$idFiche]);
    $message = "💶 Fiche de frais remboursée avec succès.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Validation des frais - Comptable</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="logo-container">
        <img src="../img/logo2_gsb.png" alt="Logo GSB" class="logo-gsb">
    </div>

    <h1>Validation des frais - Comptable</h1>

    <?php if (isset($message)) echo '<p class="message-success">'.$message.'</p>'; ?>

    <!-- Choix du visiteur -->
    <form method="POST" action="">
        <label for="visiteur_id">Choisir un visiteur :</label>
        <select name="visiteur_id" required onchange="this.form.submit()">
            <option value="">-- Sélectionner --</option>
            <?php foreach ($visiteurs as $visiteur) {
                echo '<option value="'.$visiteur['id'].'"';
                if (isset($idVisiteur) && $idVisiteur == $visiteur['id']) echo ' selected';
                echo '>'.$visiteur['prenom'].' '.$visiteur['nom'].' ('.$visiteur['login'].')</option>';
            } ?>
        </select>
    </form>

    <?php if (isset($moisDisponibles)) : ?>
        <!-- Choix du mois -->
        <form method="POST" action="">
            <input type="hidden" name="visiteur_id" value="<?php echo $idVisiteur; ?>">
            <label for="mois">Choisir le mois :</label>
            <select name="mois" required onchange="this.form.submit()">
                <option value="">-- Sélectionner --</option>
                <?php foreach ($moisDisponibles as $mois) {
                    echo '<option value="'.$mois['mois'].'"';
                    if (isset($moisChoisi) && $moisChoisi == $mois['mois']) echo ' selected';
                    echo '>'.$mois['mois'].'</option>';
                } ?>
            </select>
        </form>
    <?php endif; ?>

    <?php if (isset($fraisForfait) && isset($fraisHorsForfait)) : ?>
        <h2>Frais forfaitisés :</h2>
        <ul>
            <?php foreach ($fraisForfait as $frais) {
                echo '<li>'.$frais['libelle'].' : '.$frais['quantite'].'</li>';
            } ?>
        </ul>

        <h2>Frais hors forfait :</h2>
        <ul>
            <?php foreach ($fraisHorsForfait as $hors) {
                echo '<li>'.$hors['libelle'].' | Date : '.$hors['date'].' | Montant : '.$hors['montant'].' €</li>';
            } ?>
        </ul>

        <!-- Bouton validation -->
        <form method="POST" action="">
    <input type="hidden" name="fiche_id" value="<?php echo $ficheId; ?>">
    <button type="submit" name="valider_fiche"> Valider la fiche</button>
    <button type="submit" name="mettre_en_paiement" style="background-color: #28a745; margin-top: 10px;"> Mettre en paiement</button>
    <button type="submit" name="rembourser_fiche" style="background-color: #6f42c1; margin-top: 10px;"> Marquer comme remboursée</button>
</form>
    <?php endif; ?>

    <br><a href="../logout.php">Déconnexion</a>
</body>
</html>
<?php
session_start();
require_once('../config/db.php');

// Si l'utilisateur n'est pas connecté OU si ce n'est pas un visiteur → redirection
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'visiteur') {
    header('Location: ../index.php');
    exit();
}

$idVisiteur = $_SESSION['id'];

// Gestion de l'envoi des frais forfaitisés
if (isset($_POST['submit_forfait'])) {
    $mois = htmlspecialchars($_POST['mois']);
    $quantites = $_POST['quantite'];

    // Vérifie si une fiche existe déjà pour ce mois
    $check = $bdd->prepare('SELECT id FROM fichefrais WHERE idVisiteur = ? AND mois = ?');
    $check->execute([$idVisiteur, $mois]);
    $fiche = $check->fetch();

    if (!$fiche) {
        $insertFiche = $bdd->prepare('INSERT INTO fichefrais (idVisiteur, mois, dateModif) VALUES (?, ?, CURDATE())');
        $insertFiche->execute([$idVisiteur, $mois]);
        $ficheId = $bdd->lastInsertId();
    } else {
        $ficheId = $fiche['id'];
    }

    foreach ($quantites as $idFraisForfait => $quantite) {
        $insertLigne = $bdd->prepare('INSERT INTO lignefraisforfait (idFicheFrais, idFraisForfait, quantite)
            VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantite = VALUES(quantite)');
        $insertLigne->execute([$ficheId, $idFraisForfait, $quantite]);
    }

    $message_forfait = "✅ Frais forfaitisés enregistrés avec succès.";
}

// Gestion de l'envoi des frais hors forfait
if (isset($_POST['submit_horsforfait'])) {
    $mois = htmlspecialchars($_POST['mois']);
    $libelle = htmlspecialchars($_POST['libelle']);
    $montant = htmlspecialchars($_POST['montant']);
    $date = htmlspecialchars($_POST['date']);

    $check = $bdd->prepare('SELECT id FROM fichefrais WHERE idVisiteur = ? AND mois = ?');
    $check->execute([$idVisiteur, $mois]);
    $fiche = $check->fetch();

    if (!$fiche) {
        $insertFiche = $bdd->prepare('INSERT INTO fichefrais (idVisiteur, mois, dateModif) VALUES (?, ?, CURDATE())');
        $insertFiche->execute([$idVisiteur, $mois]);
        $ficheId = $bdd->lastInsertId();
    } else {
        $ficheId = $fiche['id'];
    }

    $insertHorsForfait = $bdd->prepare('INSERT INTO lignefraishorsforfait (idFicheFrais, libelle, date, montant)
        VALUES (?, ?, ?, ?)');
    $insertHorsForfait->execute([$ficheId, $libelle, $date, $montant]);

    $message_horsforfait = "✅ Frais hors forfait enregistrés avec succès.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <div class="logo-container">
    <img src="../img/logo2_gsb.png" alt="Logo GSB" class="logo-gsb">
</div>
    <title>Saisie des frais - Visiteur</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
   
    </a>
</div>
<div style="text-align: center; margin-top: 20px;">
    <a href="suivi_frais.php" style="
        display: inline-block;
        background-color: #007bff;
        color: white;
        padding: 10px 20px;
        border-radius: 10px;
        text-decoration: none;
        transition: background-color 0.3s;">
          Suivre l'avancement de mes fiches de frais
    </a>
</div>
    <h1>Bonjour <?php echo $_SESSION['login']; ?>, saisissez vos frais</h1>

    <?php
    if (isset($message_forfait)) echo '<p style="color:green;">'.$message_forfait.'</p>';
    if (isset($message_horsforfait)) echo '<p style="color:green;">'.$message_horsforfait.'</p>';
    ?>

    <h2>Frais forfaitisés</h2>
    <form method="POST" action="">
    <label for="mois">Mois (ex : 04) :</label>
    <input type="text" name="mois" pattern="[0-9]{2}" maxlength="2" placeholder="MM" required><br><br>

        <?php
        $req = $bdd->query('SELECT * FROM fraisforfait');
        while ($frais = $req->fetch()) {
            echo '<label>'.$frais['libelle'].' :</label>';
            echo '<input type="number" name="quantite['.$frais['id'].']" value="0" min="0"><br>';
        }
        ?>

        <button type="submit" name="submit_forfait">Envoyer les frais forfaitisés</button>
    </form>

    <h2>Frais hors forfait</h2>
    <form method="POST" action="">
    <label for="mois">Mois (ex : 04) :</label>
    <input type="text" name="mois" pattern="[0-9]{2}" maxlength="2" placeholder="MM" required><br><br>

        <label for="libelle">Libellé :</label>
        <input type="text" name="libelle" required><br><br>

        <label for="date">Date :</label>
        <input type="date" name="date" required><br><br>

        <label for="montant">Montant :</label>
        <input type="number" name="montant" step="0.01" required><br><br>

        <button type="submit" name="submit_horsforfait">Envoyer les frais hors forfait</button>
    </form>

    <br><a href="../logout.php">Déconnexion</a>
</body>
</html>
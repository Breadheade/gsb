<?php
session_start();
require_once('config/db.php');

if (isset($_POST['login']) && isset($_POST['mdp'])) {
    $login = trim($_POST['login']);
    $mdp = trim($_POST['mdp']);

    $sql = $bdd->prepare('SELECT id, nom, prenom, login, mdp, role FROM visiteur WHERE login = ?');
    $sql->execute([$login]);
    $user = $sql->fetch();

    if (!$user) {
        $erreur = "⚠️ Aucun utilisateur trouvé avec ce login.";
    } else {
        if (password_verify($mdp, $user['mdp'])) {
            $_SESSION['id'] = $user['id'];
            $_SESSION['login'] = $user['login'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'visiteur') {
                header('Location: visiteur/saisie_frais.php');
            } elseif ($user['role'] === 'comptable') {
                header('Location: comptable/valider_frais.php');
            }
            exit();
        } else {
            $erreur = "❌ Mot de passe incorrect.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <div class="logo-container">
    
</div>
    <title>Connexion - Appli-Frais</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="logo-container">
<div class="logo-container">
    <img src="img/logo2_gsb.png" alt="Logo GSB" class="logo-gsb">
</div>
</div>
    <h1>Connexion à GSB</h1>

    <?php
    if (isset($erreur)) {
        echo '<p style="color:red;">'.$erreur.'</p>';
    }
    ?>

    <form method="POST" action="">
        <label for="login">Login :</label>
        <input type="text" name="login" required><br><br>

        <label for="mdp">Mot de passe :</label>
        <input type="password" name="mdp" required><br><br>

        <button type="submit">Se connecter</button>
    </form>
</body>
</html>
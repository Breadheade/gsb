<?php
require_once('config/db.php');

// Si le formulaire est soumis
if (isset($_POST['nom'], $_POST['prenom'], $_POST['login'], $_POST['mdp'], $_POST['role'])) {
    $nom = htmlspecialchars($_POST['nom']);
    $prenom = htmlspecialchars($_POST['prenom']);
    $login = htmlspecialchars($_POST['login']);
    $mdp = password_hash($_POST['mdp'], PASSWORD_DEFAULT); // On hash le mot de passe ici !
    $role = $_POST['role'];

    // Prépare la requête SQL
    $sql = $bdd->prepare('INSERT INTO visiteur (nom, prenom, login, mdp, role) VALUES (?, ?, ?, ?, ?)');
    $sql->execute([$nom, $prenom, $login, $mdp, $role]);

    $message = "✅ Utilisateur ajouté avec succès !";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un utilisateur - Appli-Frais</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h1>Ajouter un nouvel utilisateur</h1>

    <?php
    if (isset($message)) {
        echo '<p style="color:green;">'.$message.'</p>';
    }
    ?>

    <form method="POST" action="">
        <label for="nom">Nom :</label>
        <input type="text" name="nom" required><br><br>

        <label for="prenom">Prénom :</label>
        <input type="text" name="prenom" required><br><br>

        <label for="login">Login :</label>
        <input type="text" name="login" required><br><br>

        <label for="mdp">Mot de passe :</label>
        <input type="password" name="mdp" required><br><br>

        <label for="role">Rôle :</label>
        <select name="role" required>
            <option value="visiteur">Visiteur</option>
            <option value="comptable">Comptable</option>
        </select><br><br>

        <button type="submit">Ajouter l'utilisateur</button>
    </form>
</body>
</html>
<?php
session_start();

// Inclusion du fichier de connexion à la base de données
require_once('db.php');

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

// Traitement du formulaire de modification de mot de passe
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $ancien_mot_de_passe = $_POST['ancien_mot_de_passe'];
    $nouveau_mot_de_passe = $_POST['nouveau_mot_de_passe'];

    // Récupérer le mot de passe actuel de la base de données
    $sql = "SELECT mot_de_passe FROM Utilisateur WHERE id = ?";
    $stmt = $connexion->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $mot_de_passe_bdd = $row['mot_de_passe'];

    // Vérifier si l'ancien mot de passe saisi correspond au mot de passe stocké en base de données
    if (password_verify($ancien_mot_de_passe, $mot_de_passe_bdd)) {
        // Mettre à jour le mot de passe dans la base de données
        $nouveau_mot_de_passe_hash = password_hash($nouveau_mot_de_passe, PASSWORD_DEFAULT);
        $sql_update = "UPDATE Utilisateur SET mot_de_passe = ? WHERE id = ?";
        $stmt_update = $connexion->prepare($sql_update);
        $stmt_update->bind_param("si", $nouveau_mot_de_passe_hash, $user_id);
        $stmt_update->execute();

        // Rediriger vers une page de confirmation
        header("Location: confirmation.php");
        exit();
    } else {
        // Afficher un message d'erreur si l'ancien mot de passe est incorrect
        $error_message = "L'ancien mot de passe est incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier mot de passe</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <h1 class="mt-5">Modifier mot de passe</h1>
    <?php if (isset($error_message)) { ?>
        <div class="alert alert-danger" role="alert"><?php echo $error_message; ?></div>
    <?php } ?>
    <form method="post" class="mt-3">
        <div class="form-group">
            <label for="ancien_mot_de_passe">Ancien mot de passe</label>
            <input type="password" name="ancien_mot_de_passe" id="ancien_mot_de_passe" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="nouveau_mot_de_passe">Nouveau mot de passe</label>
            <input type="password" name="nouveau_mot_de_passe" id="nouveau_mot_de_passe" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Modifier mot de passe</button>
    </form>
</div>
</body>
</html>

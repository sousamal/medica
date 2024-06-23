<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation de Mot de Passe</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-lg-6 offset-lg-3">
            <div class="card mt-5">
                <div class="card-body">
                    <h2 class="text-center mb-4">Réinitialisation de Mot de Passe</h2>
                    <?php if (isset($message)) { echo $message; } ?>
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="mb-3">
                            <label for="motdepasse" class="form-label">Nouveau Mot de Passe :</label>
                            <input type="password" class="form-control" id="motdepasse" name="motdepasse" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-block">Réinitialiser</button>
                        </div>
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

<?php
// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$database = "systeme de resérvation de service de santés";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Échec de la connexion à la base de données: " . $conn->connect_error);
}

// Vérifier si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'];
    $nouveauMotDePasse = $_POST['motdepasse'];

    // Vérifier si le token est valide et non expiré
    $sqlToken = "SELECT email FROM password_resets WHERE token=? AND expire > NOW()";
    $stmt = $conn->prepare($sqlToken);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $resultatToken = $stmt->get_result();

    if ($resultatToken->num_rows > 0) {
        $row = $resultatToken->fetch_assoc();
        $email = $row['email'];

        // Mettre à jour le mot de passe dans la table 'utilisateurs'
        $sqlUpdateUtilisateur = "UPDATE utilisateurs SET mot_de_passe=? WHERE email=?";
        $stmt = $conn->prepare($sqlUpdateUtilisateur);
        $stmt->bind_param("ss", $nouveauMotDePasse, $email);
        $stmt->execute();

        // Mettre à jour le mot de passe dans la table 'professionnelssante'
        $sqlUpdateProfessionnel = "UPDATE professionnelssante SET mot_de_passe=? WHERE email=?";
        $stmt = $conn->prepare($sqlUpdateProfessionnel);
        $stmt->bind_param("ss", $nouveauMotDePasse, $email);
        $stmt->execute();

        // Supprimer le token de réinitialisation
        $sqlDeleteToken = "DELETE FROM password_resets WHERE token=?";
        $stmt = $conn->prepare($sqlDeleteToken);
        $stmt->bind_param("s", $token);
        $stmt->execute();

        $message = "<div class='alert alert-success' role='alert'>Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.</div>";
    } else {
        $message = "<div class='alert alert-danger' role='alert'>Token invalide ou expiré. Veuillez soumettre une nouvelle demande de réinitialisation de";
    }
}
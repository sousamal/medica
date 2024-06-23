<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Récupération de Mot de Passe</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-lg-6 offset-lg-3">
            <div class="card mt-5">
                <div class="card-body">
                    <h2 class="text-center mb-4">Récupération de Mot de Passe</h2>
                    <?php if (isset($message)) { echo $message; } ?>
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="mb-3">
                            <label for="email" class="form-label">E-mail :</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-block">Envoyer</button>
                        </div>
                    </form>
                    <div class="mt-3">
                        <a href="connexion.php">Retour à la connexion</a>
                    </div>
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
    // Récupérer les données du formulaire
    $email = $_POST["email"];

    // Vérifier si l'adresse e-mail existe dans les tables 'utilisateurs' et 'professionnelssante'
    $sqlUtilisateur = "SELECT * FROM utilisateurs WHERE email=?";
    $sqlProfessionnel = "SELECT * FROM professionnelssante WHERE email=?";
    
    $stmt = $conn->prepare($sqlUtilisateur);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultatUtilisateur = $stmt->get_result();
    
    $stmt = $conn->prepare($sqlProfessionnel);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultatProfessionnel = $stmt->get_result();
    
    if ($resultatUtilisateur->num_rows > 0 || $resultatProfessionnel->num_rows > 0) {
        // L'adresse e-mail existe
        // Générer un token de réinitialisation unique
        $token = bin2hex(random_bytes(50));
        $expire = date("Y-m-d H:i:s", strtotime('+1 hour'));

        // Enregistrer le token dans la base de données (table 'password_resets')
        $sqlReset = "INSERT INTO password_resets (email, token, expire) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sqlReset);
        $stmt->bind_param("sss", $email, $token, $expire);
        $stmt->execute();

        // Envoyer un e-mail de réinitialisation de mot de passe
        $resetLink = "http://votre_serveur/reinitialisation_motdepasse.php?token=" . $token;
        $sujet = "Réinitialisation de votre mot de passe";
        $message = "Cliquez sur le lien suivant pour réinitialiser votre mot de passe : " . $resetLink;
        $headers = "From: noreply@votre_serveur.com";

        if (mail($email, $sujet, $message, $headers)) {
            $message = "<div class='alert alert-success' role='alert'>Un e-mail de réinitialisation de mot de passe a été envoyé à votre adresse e-mail.</div>";
        } else {
            $message = "<div class='alert alert-danger' role='alert'>Erreur lors de l'envoi de l'e-mail. Veuillez réessayer plus tard.</div>";
        }
    } else {
        // L'adresse e-mail n'existe pas
        $message = "<div class='alert alert-danger' role='alert'>Adresse e-mail non trouvée. Veuillez entrer une adresse e-mail valide.</div>";
    }

    $stmt->close();
}

$conn->close();
?>

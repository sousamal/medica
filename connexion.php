<?php
session_start();
require 'db.php'; // Fichier de connexion à la base de données

// Vérifier si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire
    $email = $_POST["email"];
    $motdepasse = $_POST["motdepasse"];

    // Préparer la requête SQL
    $sql = "SELECT * FROM Utilisateur WHERE email=?";
    $stmt = $connexion->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultat = $stmt->get_result();

        // Vérifier si un utilisateur est trouvé
        if ($resultat->num_rows > 0) {
            $user = $resultat->fetch_assoc();

            // Vérifier le mot de passe
            if (password_verify($motdepasse, $user['mot_de_passe'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];

                // Rediriger en fonction du rôle
                if ($user['role'] == 'patient') {
                    header("Location: profil.php");
                    exit();
                } elseif ($user['role'] == 'medecin') {
                    header("Location: profil_medecin.php");
                    exit();
                }
            } else {
                $message = "<div class='alert alert-danger' role='alert'>Identifiants incorrects. Veuillez réessayer avec une adresse e-mail et un mot de passe valides.</div>";
            }
        } else {
            $message = "<div class='alert alert-danger' role='alert'>Identifiants incorrects. Veuillez réessayer avec une adresse e-mail et un mot de passe valides.</div>";
        }
        $stmt->close();
    } else {
        $message = "<div class='alert alert-danger' role='alert'>Erreur de préparation de la requête SQL.</div>";
    }
}

$connexion->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
    <?php include 'nav-bar.php'; ?>
<section>
    <div class="container">
        <div class="row">
            <div class="col-lg-6 offset-lg-3">
                <div class="card mt-5">
                    <div class="card-body">
                        <h2 class="text-center mb-4">Connexion</h2>
                        <?php if (isset($message)) { echo $message; } ?>
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail :</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="motdepasse" class="form-label">Mot de passe :</label>
                                <input type="password" class="form-control" id="motdepasse" name="motdepasse" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-block">Se Connecter</button>
                            </div>
                        </form>
                        <div class="mt-3">
                            <a href="inscription_user.php">Créer un compte</a> | <a href="motdepasse_oublie.php">Mot de passe oublié?</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section><br>
    <?php include 'footer.php'; ?>
</body>
</html>


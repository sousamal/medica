
    <?php include 'nav-bar.php'; ?>
    <br>
    <?php
require 'db.php'; // Inclure le fichier de connexion à la base de données

// Traitement du formulaire lorsqu'il est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire
    $nom = $_POST["nom"];
    $specialite = $_POST["specialite"];
    $adresse = $_POST["adresse"];
    $email = $_POST["email"];
    $motdepasse = $_POST["motdepasse"];
    $numero_telephone = $_POST["numero_telephone"];

    // Vérifier si le mot de passe respecte les critères de sécurité
    if (verifier_mot_de_passe($motdepasse)) {
        // Hachage du mot de passe
        $motdepasse_hache = password_hash($motdepasse, PASSWORD_DEFAULT);

        // Préparation de la requête SQL pour insérer l'utilisateur
        $requete = $connexion->prepare("INSERT INTO Utilisateur (nom, email, mot_de_passe, numero_telephone, role) VALUES (?, ?, ?, ?, ?)");
        $role = 'medecin'; // Par défaut, un nouvel utilisateur inscrit en tant que médecin
        $requete->bind_param("sssss", $nom, $email, $motdepasse_hache, $numero_telephone, $role);

        // Exécution de la requête
        if ($requete->execute()) {
            // Récupérer l'ID de l'utilisateur inséré
            $user_id = $requete->insert_id;

            // Préparation de la requête SQL pour insérer le médecin
            $requete_medecin = $connexion->prepare("INSERT INTO Medecin (user_id, specialite, adresse) VALUES (?, ?, ?)");
            $requete_medecin->bind_param("iss", $user_id, $specialite, $adresse);

            // Exécution de la requête pour insérer le médecin
            if ($requete_medecin->execute()) {
                // Afficher un message de succès
                echo "<div class='alert alert-success' role='alert'>Inscription réussie !</div>";
            } else {
                // Afficher un message d'erreur en cas d'échec de l'insertion du médecin
                echo "<div class='alert alert-danger' role='alert'>Erreur lors de l'inscription du médecin : " . $requete_medecin->error . "</div>";
            }
            $requete_medecin->close();
        } else {
            // Afficher un message d'erreur en cas d'échec de l'insertion de l'utilisateur
            echo "<div class='alert alert-danger' role='alert'>Erreur lors de l'inscription : " . $requete->error . "</div>";
        }
        $requete->close();
    } else {
        // Afficher un message d'erreur si le mot de passe ne respecte pas les critères
        echo "<div class='alert alert-danger' role='alert'>Le mot de passe doit contenir au moins 8 caractères, dont au moins une lettre majuscule et un chiffre.</div>";
    }
}

// Fonction de vérification de mot de passe
function verifier_mot_de_passe($motdepasse) {
    // Vérifier la longueur minimale (8 caractères)
    if (strlen($motdepasse) < 8) {
        return false;
    }
    
    // Vérifier s'il contient au moins une lettre majuscule
    if (!preg_match('/[A-Z]/', $motdepasse)) {
        return false;
    }
    
    // Vérifier s'il contient au moins un chiffre
    if (!preg_match('/[0-9]/', $motdepasse)) {
        return false;
    }
    
    // Si toutes les vérifications sont passées, le mot de passe est valide
    return true;
}
?>

<div class="container">
    <div class="card">
        <div class="card-body">
            <h2 class="text-center mb-4">Inscription Médecin</h2>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="mb-3">
                    <label for="nom" class="form-label">Nom :</label>
                    <input type="text" class="form-control" id="nom" name="nom" required>
                </div>
                <div class="mb-3">
                    <label for="specialite" class="form-label">Spécialité :</label>
                    <input type="text" class="form-control" id="specialite" name="specialite" required>
                </div>
                <div class="mb-3">
                    <label for="adresse" class="form-label">Adresse :</label>
                    <input type="text" class="form-control" id="adresse" name="adresse" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">E-mail :</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="motdepasse" class="form-label">Mot de passe :</label>
                    <input type="password" class="form-control" id="motdepasse" name="motdepasse" name="motdepasse" required>
                </div>
                <div class="mb-3">
                    <label for="numero_telephone" class="form-label">Numéro de Téléphone :</label>
                    <input type="text" class="form-control" id="numero_telephone" name="numero_telephone" required>
                </div>
                <div class="mb-3">
                    <button type="submit" class="btn btn-primary">S'inscrire</button>
                </div>
            </form>
        </div>
    </div>
</div>
<br>
<?php include 'footer.php' ?>
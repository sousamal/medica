
    <?php include 'nav-bar.php'; ?>
    <br>

    <?php
    // Inclusion du fichier de connexion à la base de données
    require 'db.php';

    // Traitement du formulaire lorsque soumis
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Récupérer les données du formulaire
        $nom = $_POST["nom"];
        $prenom = $_POST["prenom"];
        $email = $_POST["email"];
        $motdepasse = $_POST["motdepasse"];
        $numero_telephone = $_POST["numero_telephone"];
        $adresse = $_POST["adresse"];

        // Vérifier si le mot de passe est valide
        if (verifier_mot_de_passe($motdepasse)) {
            // Hachage du mot de passe
            $motdepasse_hache = password_hash($motdepasse, PASSWORD_DEFAULT);

            // Préparation de la requête SQL pour insérer le patient
            $requete = $connexion->prepare("INSERT INTO Utilisateur (nom, prenom, email, mot_de_passe, numero_telephone, role) VALUES (?, ?, ?, ?, ?, 'patient')");
            $requete->bind_param("sssss", $nom, $prenom, $email, $motdepasse_hache, $numero_telephone);

            // Exécution de la requête
            if ($requete->execute()) {
                // Récupérer l'ID de l'utilisateur inséré
                $user_id = $requete->insert_id;

                // Insérer les données spécifiques au patient dans la table Patient
                $requete_patient = $connexion->prepare("INSERT INTO Patient (user_id, adresse) VALUES (?, ?)");
                $requete_patient->bind_param("is", $user_id, $adresse);
                $requete_patient->execute();

                // Afficher le message d'inscription réussie
                echo "<div class='alert alert-success' role='alert'>Inscription réussie !</div>";
            } else {
                // Afficher un message d'erreur en cas d'échec de l'inscription
                echo "<div class='alert alert-danger' role='alert'>Erreur lors de l'inscription : " . $requete->error . "</div>";
            }

            // Fermeture de la requête
            $requete->close();
        } else {
            // Afficher un message d'erreur si le mot de passe ne respecte pas les critères
            echo "<div class='alert alert-danger' role='alert'>Le mot de passe doit contenir au moins 8 caractères, dont au moins une lettre majuscule et une lettre minuscule.</div>";
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
        
        // Vérifier s'il contient au moins une lettre minuscule
        if (!preg_match('/[a-z]/', $motdepasse)) {
            return false;
        }
        
        // Si toutes les vérifications sont passées, le mot de passe est valide
        return true;
    }
    ?>

    <div class="container">
        <div class="card">
            <div class="card-body">
                <h2 class="text-center mb-4">Inscription Patient</h2>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="mb-3">
                        <label for="nom" class="form-label">Nom :</label>
                        <input type="text" class="form-control" id="nom" name="nom" required>
                    </div>
                    <div class="mb-3">
                        <label for="prenom" class="form-label">Prénom :</label>
                        <input type="text" class="form-control" id="prenom" name="prenom" required>
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
                        <input type="password" class="form-control" id="motdepasse" name="motdepasse" required>
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
<?php
session_start();

// Inclusion du fichier de connexion à la base de données
require_once('db.php');

// Vérifier si l'utilisateur est connecté et s'il est médecin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'medecin') {
    header("Location: connexion.php");
    exit();
}

// Récupérer l'ID du médecin connecté
$user_id = $_SESSION['user_id'];

// Récupérer le nom du médecin connecté
$sqlMedecin = "SELECT nom, prenom FROM Utilisateur WHERE id = ?";
$stmtMedecin = $connexion->prepare($sqlMedecin);
$stmtMedecin->bind_param("i", $user_id);
$stmtMedecin->execute();
$resultMedecin = $stmtMedecin->get_result();

if ($resultMedecin->num_rows > 0) {
    $medecin = $resultMedecin->fetch_assoc();
    $username = $medecin['prenom'] . ' ' . $medecin['nom'];
} else {
    $username = "Utilisateur";
}

// Gestion des rendez-vous
$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete_appointment'])) {
        $rendezvous_id = $_POST['rendezvous_id'];
        $sqlDelete = "DELETE FROM RendezVous WHERE rendezvous_id = ?";
        $stmtDelete = $connexion->prepare($sqlDelete);
        $stmtDelete->bind_param("i", $rendezvous_id);
        if ($stmtDelete->execute()) {
            $message = "Rendez-vous supprimé avec succès.";
        } else {
            $message = "Erreur lors de la suppression du rendez-vous.";
        }
    }

    if (isset($_POST['confirm_appointment'])) {
        $rendezvous_id = $_POST['rendezvous_id'];
        $sqlConfirm = "UPDATE RendezVous SET etat = 'confirme' WHERE rendezvous_id = ?";
        $stmtConfirm = $connexion->prepare($sqlConfirm);
        $stmtConfirm->bind_param("i", $rendezvous_id);
        if ($stmtConfirm->execute()) {
            $message = "Rendez-vous confirmé avec succès.";
        } else {
            $message = "Erreur lors de la confirmation du rendez-vous.";
        }
    }
}

// Récupérer les rendez-vous du médecin connecté
$sqlAppointments = "SELECT r.*, u.nom AS nom_patient 
                    FROM RendezVous r 
                    JOIN Utilisateur u ON r.patient_id = u.id 
                    WHERE r.medecin_id = ?";
$stmtAppointments = $connexion->prepare($sqlAppointments);
$stmtAppointments->bind_param("i", $user_id);
$stmtAppointments->execute();
$resultAppointments = $stmtAppointments->get_result();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Médecin</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-primary">
    <a class="navbar-brand text-white" href="#">Medica</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link text-white" href="modifier_mot_de_passe.php">Modifier mot de passe</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="déconnexion.php">Déconnexion</a>
            </li>
        </ul>
    </div>
</nav>

<div class="container">
    <h1 class="mt-5">Bonjour <?php echo htmlspecialchars($username); ?></h1>
    <?php if ($message) { ?>
        <div class="alert alert-info"><?php echo $message; ?></div>
    <?php } ?>

    <h2 class="mt-3">Gérer les rendez-vous</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Heure</th>
                <th>Patient</th>
                <th>État</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $resultAppointments->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['date']); ?></td>
                    <td><?php echo htmlspecialchars($row['heure']); ?></td>
                    <td><?php echo htmlspecialchars($row['nom_patient']); ?></td>
                    <td><?php echo htmlspecialchars($row['etat']); ?></td>
                    <td>
                        <?php if ($row['etat'] == 'non confirme') { ?>
                            <form method="post" style="display: inline-block;">
                                <input type="hidden" name="rendezvous_id" value="<?php echo htmlspecialchars($row['rendezvous_id']); ?>">
                                <button type="submit" name="confirm_appointment" class="btn btn-success">Confirmer</button>
                            </form>
                        <?php } ?>
                        <form method="post" style="display: inline-block;">
                            <input type="hidden" name="rendezvous_id" value="<?php echo htmlspecialchars($row['rendezvous_id']); ?>">
                            <button type="submit" name="delete_appointment" class="btn btn-danger">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<footer class="footer mt-auto py-3 bg-light">
    <div class="container text-center">
        <span class="text-muted">© 2024 Medica. Tous droits réservés.</span><br>
        <span class="text-muted">Développé par Soufiane AALLA</span>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>

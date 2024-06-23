<?php
session_start();

// Inclusion du fichier de connexion à la base de données
require_once('db.php');

// Vérifier si l'utilisateur est connecté et s'il est patient
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    header("Location: connexion.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Récupérer le nom du patient connecté
$sqlPatient = "SELECT nom, prenom FROM Utilisateur WHERE id = ?";
$stmtPatient = $connexion->prepare($sqlPatient);
$stmtPatient->bind_param("i", $user_id);
$stmtPatient->execute();
$resultPatient = $stmtPatient->get_result();

if ($resultPatient->num_rows > 0) {
    $patient = $resultPatient->fetch_assoc();
    $username = $patient['prenom'] . ' ' . $patient['nom'];
} else {
    $username = "Utilisateur";
}

// Gestion des rendez-vous
$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_appointment'])) {
        $date = $_POST['date'];
        $heure = $_POST['heure'];
        $medecin_id = $_POST['medecin_id'];

        // Vérifier si le rendez-vous est déjà réservé
        $sqlCheck = "SELECT * FROM RendezVous WHERE date = ? AND heure = ? AND medecin_id = ?";
        $stmtCheck = $connexion->prepare($sqlCheck);
        $stmtCheck->bind_param("ssi", $date, $heure, $medecin_id);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();

        if ($resultCheck->num_rows > 0) {
            $message = "Le rendez-vous est déjà réservé.";
        } else {
            $sqlAdd = "INSERT INTO RendezVous (date, heure, patient_id, medecin_id, etat) VALUES (?, ?, ?, ?, 'non confirme')";
            $stmtAdd = $connexion->prepare($sqlAdd);
            $stmtAdd->bind_param("ssii", $date, $heure, $user_id, $medecin_id);
            if ($stmtAdd->execute()) {
                $message = "Rendez-vous pris avec succès.";
            } else {
                $message = "Erreur lors de la prise de rendez-vous.";
            }
        }
    }

    if (isset($_POST['delete_appointment'])) {
        $rendezvous_id = $_POST['rendezvous_id'];
        $sqlDelete = "DELETE FROM RendezVous WHERE rendezvous_id = ?";
        $stmtDelete = $connexion->prepare($sqlDelete);
        $stmtDelete->bind_param("i", $rendezvous_id);
        $stmtDelete->execute();
    }

    if (isset($_POST['modify_appointment'])) {
        $rendezvous_id = $_POST['rendezvous_id'];
        $date = $_POST['date'];
        $heure = $_POST['heure'];
        $sqlModify = "UPDATE RendezVous SET date = ?, heure = ? WHERE rendezvous_id = ?";
        $stmtModify = $connexion->prepare($sqlModify);
        $stmtModify->bind_param("ssi", $date, $heure, $rendezvous_id);
        $stmtModify->execute();
    }

    if (isset($_POST['confirm_appointment'])) {
        $rendezvous_id = $_POST['rendezvous_id'];
        $sqlConfirm = "UPDATE RendezVous SET etat = 'confirme' WHERE rendezvous_id = ?";
        $stmtConfirm = $connexion->prepare($sqlConfirm);
        $stmtConfirm->bind_param("i", $rendezvous_id);
        $stmtConfirm->execute();
    }
}

// Recherche du médecin
$searchTerm = '';
$doctors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search_doctor'])) {
    $searchTerm = $_POST['search_term'];
    $sqlDoctors = "SELECT m.user_id, u.nom, u.prenom, m.specialite, m.adresse 
                   FROM Medecin m 
                   JOIN Utilisateur u ON m.user_id = u.id 
                   WHERE u.nom LIKE ? OR u.prenom LIKE ? OR m.adresse LIKE ?";
    $stmtDoctors = $connexion->prepare($sqlDoctors);
    $likeTerm = '%' . $searchTerm . '%';
    $stmtDoctors->bind_param("sss", $likeTerm, $likeTerm, $likeTerm);
    $stmtDoctors->execute();
    $resultDoctors = $stmtDoctors->get_result();
    while ($row = $resultDoctors->fetch_assoc()) {
        $doctors[] = $row;
    }
}

// Récupérer les rendez-vous du patient connecté
$sqlAppointments = "SELECT r.*, u.nom AS nom_medecin 
                    FROM RendezVous r 
                    JOIN Medecin m ON r.medecin_id = m.user_id 
                    JOIN Utilisateur u ON m.user_id = u.id 
                    WHERE r.patient_id = ?";
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
    <title>Profil Patient</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-primary">
    <a class="navbar-brand text-white" href="#">
        Medica
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link text-white" href="modifier_mot_de_passe.php">Modifier mot de passe</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="connexion.php">Déconnexion</a>
            </li>
        </ul>
    </div>
</nav>

<div class="container">
    <h1 class="mt-5">Bonjour <?php echo htmlspecialchars($username); ?></h1>
    <?php if ($message) { ?>
        <div class="alert alert-info"><?php echo $message; ?></div>
    <?php } ?>

    <h2 class="mt-3">Rechercher un médecin</h2>
    <form method="post" class="mb-5">
        <div class="form-group">
            <label for="search_term">Nom, Prénom ou Adresse du Médecin</label>
            <input type="text" name="search_term" id="search_term" class="form-control" value="<?php echo htmlspecialchars($searchTerm); ?>" required>
        </div>
        <button type="submit" name="search_doctor" class="btn btn-primary">Rechercher</button>
    </form>

    <?php if (!empty($doctors)) { ?>
        <h2 class="mt-3">Médecins trouvés</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Spécialité</th>
                    <th>Adresse</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($doctors as $doctor) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($doctor['nom']); ?></td>
                        <td><?php echo htmlspecialchars($doctor['prenom']); ?></td>
                        <td><?php echo htmlspecialchars($doctor['specialite']); ?></td>
                        <td><?php echo htmlspecialchars($doctor['adresse']); ?></td>
                        <td>
                            <form method="post" class="mb-5">
                                <div class="form-group">
                                    <input type="hidden" name="medecin_id" value="<?php echo $doctor['user_id']; ?>">
                                    <label for="date">Date</label>
                                    <input type="date" name="date" id="date" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="heure">Heure</label>
                                    <input type="time" name="heure" id="heure" class="form-control" required>
                                </div>
                                <button type="submit" name="add_appointment" class="btn btn-primary">Prendre rendez-vous</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } else if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search_doctor'])) { ?>
        <div class="alert alert-warning">Aucun médecin trouvé pour cette recherche.</div>
    <?php } ?>

    <h2 class="mt-3">Vos rendez-vous</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Heure</th>
                <th>Médecin</th>
                <th>État</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $resultAppointments->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['date']); ?></td>
                    <td><?php echo htmlspecialchars($row['heure']); ?></td>
                    <td><?php echo htmlspecialchars($row['nom_medecin']); ?></td>
                    <td><?php echo htmlspecialchars($row['etat']); ?></td>
                    <td>
                        <form method="post" style="display: inline-block;">
                            <input type="hidden" name="rendezvous_id" value="<?php echo htmlspecialchars($row['rendezvous_id']); ?>">
                            <button type="submit" name="delete_appointment" class="btn btn-danger">Supprimer</button>
                        </form>
                        <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#modifyModal<?php echo $row['rendezvous_id']; ?>">Modifier</button>

                        <div class="modal fade" id="modifyModal<?php echo $row['rendezvous_id']; ?>" tabindex="-1" role="dialog" aria-labelledby="modifyModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modifyModalLabel">Modifier rendez-vous</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="post">
                                            <input type="hidden" name="rendezvous_id" value="<?php echo htmlspecialchars($row['rendezvous_id']); ?>">
                                            <div class="form-group">
                                                <label for="date">Date</label>
                                                <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($row['date']); ?>" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="heure">Heure</label>
                                                <input type="time" name="heure" class="form-control" value="<?php echo htmlspecialchars($row['heure']); ?>" required>
                                            </div>
                                            <button type="submit" name="modify_appointment" class="btn btn-primary">Modifier</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
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

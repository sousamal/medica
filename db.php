<?php
// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$database = "Medica";

$connexion = new mysqli($servername, $username, $password, $database);

// Activer les rapports d'erreur
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($connexion->connect_error) {
    die("Échec de la connexion à la base de données: " . $connexion->connect_error);
}
?>

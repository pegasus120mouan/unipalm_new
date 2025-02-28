<?php
// Connexion à la base de données
require_once '../../inc/functions/connexion.php';
session_start(); 

// Récupération des données soumises via le formulaire
$id_banner = $_POST['id'];
$description = $_POST['description'];

// Requête SQL d'update
$sql = "UPDATE banner SET description = :description WHERE id = :id";

// Préparation de la requête
$requete = $conn->prepare($sql);

// Exécution de la requête avec les nouvelles valeurs
$query_execute = $requete->execute(array(
    ':description' => $description,
    ':id' => $id_banner,
));

// Redirection vers une page de confirmation ou de retour
if ($query_execute) {
    $_SESSION['popup'] = true;
    header('Location: ../banner_admin.php');
    exit(0);
} else {
    $_SESSION['delete_pop'] = true;
    header('Location: ../banner_admin.php');
    exit(0);
}
?>

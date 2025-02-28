<?php

// Connexion à la base de données (à adapter avec vos informations)
require_once '../../inc/functions/connexion.php';
//session_start(); 

// Récupération des données soumises via le formulaire
$id = $_POST['id'];
$nom_zone = $_POST['zones'];

// Requête SQL d'update
$sql = "UPDATE zones
        SET nom_zone = :nom_zone WHERE zone_id = :id";

// Préparation de la requête
$requete = $conn->prepare($sql);

// Exécution de la requête avec les nouvelles valeurs
$query_execute = $requete->execute(array(
   ':id' => $id,
   ':nom_zone' => $nom_zone
));

// Redirection vebarrs une page de confirmation ou de retour
//$query_execute = $requete->execute($data);

if ($query_execute) {
   // $_SESSION['message'] = "Insertion reussie";
   $_SESSION['popup'] = true;
   header('Location: ../cout_livraison.php');
   exit(0);
} else {
   $_SESSION['delete_pop'] = true;
   header('Location: ../cout_livraison.php');
   exit(0);
}
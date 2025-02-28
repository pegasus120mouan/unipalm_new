<?php

// Connexion à la base de données (à adapter avec vos informations)
require_once '../../inc/functions/connexion.php';
//session_start(); 

// Récupération des données soumises via le formulaire
    $id_contrat = $_POST['id'];
    $vignette_date_debut = $_POST['vignette_date_debut'];
    $vignette_date_fin = $_POST['vignette_date_fin'];
    $assurance_date_debut = $_POST['assurance_date_debut'];
    $assurance_date_fin = $_POST['assurance_date_fin'];

// Requête SQL d'update
$sql = "UPDATE contrats
        SET vignette_date_debut = :vignette_date_debut,
        vignette_date_fin = :vignette_date_fin, 
        assurance_date_debut = :assurance_date_debut, 
        assurance_date_fin = :assurance_date_fin
        WHERE contrat_id = :id";

// Préparation de la requête
$requete = $conn->prepare($sql);

// Exécution de la requête avec les nouvelles valeurs
$query_execute = $requete->execute(array(
   ':id' => $id_contrat,
   ':vignette_date_debut' => $vignette_date_debut,
   ':vignette_date_fin' =>   $vignette_date_fin,
   ':assurance_date_debut' => $assurance_date_debut,
   ':assurance_date_fin' => $assurance_date_fin
));

// Redirection vebarrs une page de confirmation ou de retour
//$query_execute = $requete->execute($data);

if ($query_execute) {
   // $_SESSION['message'] = "Insertion reussie";
   $_SESSION['popup'] = true;
   header('Location: ../contrats.php');
   exit(0);
} else {
   $_SESSION['delete_pop'] = true;
   header('Location: ../contrats.php');
   exit(0);
}
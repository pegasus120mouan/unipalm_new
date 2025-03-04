<?php

// Connexion à la base de données (à adapter avec vos informations)
require_once '../../inc/functions/connexion.php';
//session_start(); 
// Récupération des données soumises via le formulaire
$id = $_POST['id'];
$montant= $_POST['montant'];
$date_contraction = $_POST['date_contraction'];
$motif = $_POST['motif'];

// Requête SQL d'update
$sql = "UPDATE imprevu
        SET montant = :montant, date_contraction = :date_contraction, motif = :motif
        WHERE id = :id";

// Préparation de la requête
$requete = $conn->prepare($sql);

// Exécution de la requête avec les nouvelles valeurs
$query_execute = $requete->execute(array(
   ':id' => $id,
   ':montant' => $montant,
   ':date_contraction' => $date_contraction,
   ':motif' => $motif
));

// Redirection vebarrs une page de confirmation ou de retour
//$query_execute = $requete->execute($data);

if ($query_execute) {
   // $_SESSION['message'] = "Insertion reussie";
   $_SESSION['popup'] = true;
   header('Location: ../imprevus.php');
   exit(0);
} else {
   $_SESSION['delete_pop'] = true;
   header('Location: ../imprevus.php');
   exit(0);
}
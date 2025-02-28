<?php

// Connexion à la base de données (à adapter avec vos informations)
require_once '../../inc/functions/connexion.php';
//session_start(); 
// Récupération des données soumises via le formulaire
$id = $_POST['id'];
$montant_actuel = $_POST['montant_actuel'];
$date_contraction = $_POST['date_contraction'];
$motifs = $_POST['motifs'];

// Requête SQL d'update
$sql = "UPDATE dette
        SET montant_actuel = :montant_actuel, date_contraction = :date_contraction, motifs = :motifs
        WHERE id = :id";

// Préparation de la requête
$requete = $conn->prepare($sql);

// Exécution de la requête avec les nouvelles valeurs
$query_execute = $requete->execute(array(
   ':id' => $id,
   ':montant_actuel' => $montant_actuel,
   ':date_contraction' => $date_contraction,
   ':motifs' => $motifs
));

// Redirection vebarrs une page de confirmation ou de retour
//$query_execute = $requete->execute($data);

if ($query_execute) {
   // $_SESSION['message'] = "Insertion reussie";
   $_SESSION['popup'] = true;
   header('Location: ../dettes.php');
   exit(0);
} else {
   $_SESSION['delete_pop'] = true;
   header('Location: ../dettes.php');
   exit(0);
}
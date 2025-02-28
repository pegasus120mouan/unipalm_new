<?php

// Connexion à la base de données (à adapter avec vos informations)
require_once '../../inc/functions/connexion.php';
//session_start(); 
// Récupération des données soumises via le formulaire
$id = $_POST['id'];
$id_dette = $_POST['id_dette'];
$versement = $_POST['versement'];
$date = $_POST['date'];

echo $id_dette=$_POST['id_dette'];

// Requête SQL d'update
$sql = "UPDATE versement
        SET montant_versement = :montant_versement, date_versement = :date_versement
        WHERE id = :id";

// Préparation de la requête
$requete = $conn->prepare($sql);

// Exécution de la requête avec les nouvelles valeurs
$query_execute = $requete->execute(array(
   ':id' => $id,
   ':montant_versement' => $montant_versement,
   ':date_versement' => $date,
));

// Redirection vebarrs une page de confirmation ou de retour
//$query_execute = $requete->execute($data);

if ($query_execute) {
   // $_SESSION['message'] = "Insertion reussie";
   $_SESSION['popup'] = true;
   header('Location: ../versement_detaille.php?id='.$id_dette);
   exit(0);
} else {
   $_SESSION['delete_pop'] = true;
   header('Location: ../versement_detaille.php?id='.$id_dette);
   exit(0);
}
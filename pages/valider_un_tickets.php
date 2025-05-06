<?php

// Connexion à la base de données (à adapter avec vos informations)
require_once '../inc/functions/connexion.php';

$id_ticket = $_POST['id_ticket'];
$prix_unitaire = $_POST['prix_unitaire'];




$sql = "UPDATE tickets 
            SET date_validation_boss = NOW(),
                prix_unitaire = :prix_unitaire,
                montant_paie = :prix_unitaire * poids
            WHERE id_ticket = :id_ticket";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':prix_unitaire', $prix_unitaire);
$stmt->bindParam(':id_ticket', $id_ticket);

if ($stmt->execute()) {
    $_SESSION['popup'] = true;
    header('Location: tickets_attente.php');
    exit(0);
} else {
    $_SESSION['delete_pop'] = true;
   header('Location: tickets_attente.php');
   exit(0);
}               
$conn = null; // Fermer la connexion    


?>
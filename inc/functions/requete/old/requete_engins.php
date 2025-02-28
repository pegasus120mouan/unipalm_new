<?php

// Toutes les commandes
$stmt_engins = $conn->prepare(
    "SELECT 
    engins.engin_id AS engin_id, 
    concat(utilisateurs.nom, ' ', utilisateurs.prenoms) AS nom_livreur,
    utilisateurs.avatar AS avatar,
    engins.type_engin AS type_engin, 
    engins.annee_fabrication AS annee_fabrication,
    engins.numero_chassis  AS numero_chassis,
    engins.plaque_immatriculation AS plaque_immatriculation,
    engins.couleur AS couleur,
    engins.marque AS marque,
    engins.date_ajout AS date_ajout,
    engins.statut AS statut
FROM engins
JOIN utilisateurs ON engins.utilisateur_id = utilisateurs.id");
//$stmt->bindParam(':user_id', $user_id);
$stmt_engins->execute();
$engins = $stmt_engins->fetchAll();



$type_engins = $conn->query("SELECT type FROM type_engins");


$getLivreurs = $conn->query("SELECT id, CONCAT(nom, ' ', prenoms) AS nom_livreur 
FROM utilisateurs  where role like 'livreur' ");



/*$cout_livraison = $conn->query("SELECT cout_livraison FROM cout_livraison");


$getLivreurs = $conn->query("SELECT id, CONCAT(nom, ' ', prenoms) AS livreur_name 
FROM utilisateurs  where role like 'livreur' ");

$getStatut = $conn->query("SELECT statut FROM statut_livraison");*/


?>
<?php

// Toutes les commandes
$stmt_contrats = $conn->prepare(
    "SELECT 
    e.engin_id,
    e.utilisateur_id,
    e.type_engin,
    e.annee_fabrication,
    e.plaque_immatriculation,
    e.couleur,
    e.date_ajout,
    e.marque,
    e.numero_chassis AS numero_chassis,
    e.statut AS statut_engin,
    CONCAT(u.nom, ' ', u.prenoms) AS fullname,
    u.contact,
    u.login,
    u.avatar,
    u.role,
    u.boutique_id,
    c.contrat_id,
    c.vignette_date_debut,
    c.vignette_date_fin,
    c.assurance_date_debut,
    c.assurance_date_fin
FROM 
    engins e
JOIN 
    utilisateurs u ON e.utilisateur_id = u.id
JOIN 
    contrats c ON e.engin_id = c.id_engin");
//$stmt->bindParam(':user_id', $user_id);
$stmt_contrats->execute();
$contrats = $stmt_contrats->fetchAll();



$type_engins = $conn->query("SELECT engin_id, plaque_immatriculation FROM engins");


//$getLivreurs = $conn->query("SELECT id, CONCAT(nom, ' ', prenoms) AS nom_livreur 
//FROM utilisateurs  where role like 'livreur' ");



/*$cout_livraison = $conn->query("SELECT cout_livraison FROM cout_livraison");


$getLivreurs = $conn->query("SELECT id, CONCAT(nom, ' ', prenoms) AS livreur_name 
FROM utilisateurs  where role like 'livreur' ");

$getStatut = $conn->query("SELECT statut FROM statut_livraison");*/


?>
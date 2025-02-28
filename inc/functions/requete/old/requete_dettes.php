<?php

//$nombreParPage = 10;
//$pageCourante = isset($_GET['page']) ? $_GET['page'] : 1;
//$offset = ($pageCourante - 1) * $nombreParPage;

//$stmt = $conn->prepare("SELECT * FROM commandes ORDER BY date DESC limit $offset,$pageCourante ");
//$stmt = $conn->prepare("SELECT * FROM commandes");

//$stmt->execute();
//$commandes = $stmt->fetchAll();

//
// Toutes les commandes
$stmt = $conn->prepare(
    "SELECT id AS dette_id,
montant_actuel as montant_actuel,
montants_payes as montants_payes,
date_contraction as date_contraction,
motifs as motifs
FROM dette");
//$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$dettes = $stmt->fetchAll();


// Commandes livrees

$stmt_livres = $conn->prepare(
    "SELECT 
    commandes.id AS commande_id, 
    commandes.communes AS commande_communes, 
    commandes.cout_global AS commande_cout_global, 
    commandes.cout_livraison AS commande_cout_livraison, 
    commandes.cout_reel AS commande_cout_reel, 
    commandes.statut AS commande_statut, 
    commandes.date_commande, 
    utilisateurs.nom AS nom_utilisateur, 
    utilisateurs.prenoms AS prenoms_utilisateur,
    boutiques.nom AS nom_boutique, 
    livreur.nom AS nom_livreur, 
    livreur.prenoms AS prenoms_livreur, 
    concat(livreur.nom, ' ',livreur.prenoms) AS fullname,
    utilisateurs.role
FROM commandes
JOIN utilisateurs ON commandes.utilisateur_id = utilisateurs.id
JOIN boutiques ON utilisateurs.boutique_id = boutiques.id
LEFT JOIN utilisateurs AS livreur ON commandes.livreur_id = livreur.id 
WHERE commandes.statut='Livré'
ORDER BY commandes.date_commande  DESC limit 15"
);
//$stmt->bindParam(':user_id', $user_id);
$stmt_livres->execute();
$commandes_livrees = $stmt_livres->fetchAll();

// Commandes non livrees
$stmt_non_livres = $conn->prepare(
    "SELECT 
    commandes.id AS commande_id, 
    commandes.communes AS commande_communes, 
    commandes.cout_global AS commande_cout_global, 
    commandes.cout_livraison AS commande_cout_livraison, 
    commandes.cout_reel AS commande_cout_reel, 
    commandes.statut AS commande_statut, 
    commandes.date_commande, 
    utilisateurs.nom AS nom_utilisateur, 
    utilisateurs.prenoms AS prenoms_utilisateur,
    boutiques.nom AS nom_boutique, 
    livreur.nom AS nom_livreur, 
    livreur.prenoms AS prenoms_livreur, 
    concat(livreur.nom, ' ',livreur.prenoms) AS fullname,
    utilisateurs.role
FROM commandes
JOIN utilisateurs ON commandes.utilisateur_id = utilisateurs.id
JOIN boutiques ON utilisateurs.boutique_id = boutiques.id
LEFT JOIN utilisateurs AS livreur ON commandes.livreur_id = livreur.id 
WHERE commandes.statut='Non Livré'
ORDER BY commandes.date_commande  DESC limit 15"
);
//$stmt->bindParam(':user_id', $user_id);
$stmt_non_livres->execute();
$commandes_non_livrees = $stmt_non_livres->fetchAll();



















$getClientsQuery = "SELECT utilisateurs.id as id, boutiques.nom as nom_boutique
                   FROM utilisateurs 
                    join boutiques on utilisateurs.boutique_id=boutiques.id
                    WHERE role NOT IN ('admin', 'livreur')";
$getClientsStmt = $conn->query($getClientsQuery);
//$clients = $getClientsStmt->fetchAll(PDO::FETCH_ASSOC);




//foreach($commandes as $commande)
//$sqlCount = "SELECT COUNT(*) as total FROM commandes";
//$totalEnregistrements = $conn->query($sqlCount)->fetchColumn();
// $nombrePages = ceil($totalEnregistrements / $nombreParPage);


//$montantClient = $conn->prepare('SFELECT SUM(cout_reel) AS reel_somme FROM commandes where client="Uniko Perfume"');
//$montantClient->execute();

//$row = $montantClient->fetch(PDO::FETCH_ASSOC);
//$sum = $row['reel_somme'];


//$montantGlobal = $conn->prepare('SELECT SUM(cout_global) AS montant_global_colis FROM commandes where date=CURRENT_DATE() AND client="Uniko Perfume"');
//$montantGlobal->execute();

//$rowMontant = $montantGlobal->fetch(PDO::FETCH_ASSOC);
//$montantColisGlobal = $rowMontant['montant_global_colis'];



//$requete = $conn->query("SELECT nom_boutique FROM clients");   

//$livreurs_selection = $conn->query("SELECT prenom_livreur FROM livreurs"); 

$cout_livraison = $conn->query("SELECT cout_livraison FROM cout_livraison");


$getLivreurs = $conn->query("SELECT id, CONCAT(nom, ' ', prenoms) AS livreur_name 
FROM utilisateurs  where role like 'livreur' ");

$getStatut = $conn->query("SELECT statut FROM statut_livraison");
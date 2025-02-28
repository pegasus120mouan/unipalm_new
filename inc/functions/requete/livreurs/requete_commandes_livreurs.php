<?php
$id_user = $_SESSION['user_id'];

$stmt = $conn->prepare(
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
    utilisateurs.role
FROM commandes
JOIN utilisateurs ON commandes.utilisateur_id = utilisateurs.id
JOIN boutiques ON utilisateurs.boutique_id = boutiques.id
LEFT JOIN utilisateurs AS livreur ON commandes.livreur_id = livreur.id ORDER BY commandes.date_commande DESC limit 15"


);
//$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$commandes = $stmt->fetchAll();


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

$cout_livraison = $conn->query("SELECT cout_livraison FROM cout_livraison");


$getLivreurs = $conn->query("SELECT id, CONCAT(nom, ' ', prenoms) AS livreur_name 
FROM utilisateurs  where role like 'livreur' AND statut_compte=1");

$getStatut = $conn->query("SELECT statut FROM statut_livraison");




// Les colis livrés

$requete_colis = $conn->prepare("SELECT
commandes.id as commande_id,
utilisateur_id,
livreur_id,
communes,
cout_global,
cout_livraison,
cout_reel,
statut,
date_commande,
clients.id as id_client,
clients.nom as client_nom,
clients.prenoms,
clients.contact,
clients.login,
clients.avatar,
clients.boutique_id,
boutiques.nom as nom_boutique,
CONCAT(livreurs.nom, ' ', livreurs.prenoms) as fullname
FROM 
commandes  
JOIN 
(SELECT * FROM utilisateurs WHERE role = 'clients') as clients ON clients.id = commandes.utilisateur_id
JOIN 
boutiques ON clients.boutique_id = boutiques.id
JOIN 
livreurs ON livreurs.id = commandes.livreur_id
WHERE 
livreur_id = :id_user
AND commandes.statut = 'Livré'
ORDER BY 
date_commande DESC
LIMIT 
15"
);

// Liaison de la variable avec le paramètre de la requête
$requete_colis->bindParam(':id_user', $id_user, PDO::PARAM_INT);
$requete_colis->execute();
$commandes_livrees = $requete_colis->fetchAll();




// Commandes non livrés
$requete_non_colis = $conn->prepare("SELECT
commandes.id as commande_id,
utilisateur_id,
livreur_id,
communes AS commande_communes,
cout_global AS commande_cout_global,
cout_livraison AS commande_cout_livraison,
cout_reel AS commande_cout_reel,
statut AS commande_statut,
date_commande,
clients.id as id_client,
clients.nom as client_nom,
clients.prenoms,
clients.contact,
clients.login,
clients.avatar,
clients.boutique_id,
boutiques.nom as nom_boutique,
CONCAT(livreurs.nom, ' ', livreurs.prenoms) as fullname
FROM 
commandes  
JOIN 
(SELECT * FROM utilisateurs WHERE role = 'clients') as clients ON clients.id = commandes.utilisateur_id
JOIN 
boutiques ON clients.boutique_id = boutiques.id
JOIN 
livreurs ON livreurs.id = commandes.livreur_id
WHERE 
livreur_id = :id_user
AND commandes.statut = 'Non Livré'
ORDER BY 
date_commande DESC
LIMIT 
15"
);

// Liaison de la variable avec le paramètre de la requête
$requete_non_colis->bindParam(':id_user', $id_user, PDO::PARAM_INT);
$requete_non_colis->execute();
$commandes_non_livrees = $requete_non_colis->fetchAll();
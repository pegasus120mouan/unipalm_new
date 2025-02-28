<?php
   
$id_user=$_SESSION['user_id'];
//$nombreParPage = 10;
//$pageCourante = isset($_GET['page']) ? $_GET['page'] : 1;
//$offset = ($pageCourante - 1) * $nombreParPage;

//$stmt = $conn->prepare("SELECT * FROM commandes ORDER BY date DESC limit $offset,$pageCourante ");
//$stmt = $conn->prepare("SELECT * FROM commandes");

//$stmt->execute();
//$commandes = $stmt->fetchAll();

/*$stmt_select_pdf = $conn->prepare(
    "SELECT 
commandes.id as commande_id,
utilisateur_id, livreur_id, communes, cout_global,
cout_livraison, cout_reel, statut, date_commande, clients.id as id_client,
clients.nom as client_nom, prenoms, contact, login, avatar, boutique_id, boutiques.nom as boutique_nom

FROM `commandes`  
join (select * from utilisateurs where role = 'clients')  as clients on clients.id=commandes.utilisateur_id
join boutiques on clients.boutique_id=boutiques.id"
);

$stmt_select_boutique = $conn->prepare(
    "SELECT * from boutiques join utilisateurs on where"
);
$stmt_select_boutique->execute();
$selections = $stmt_select_boutqiue->fetchAll();*/



$sql = "SELECT utilisateurs.id as utilisateur_id, 
 utilisateurs.nom as utilisateur_nom, 
 utilisateurs.prenoms as utilisateur_prenoms, 
 utilisateurs.contact as utilisateur_contact,
 utilisateurs.avatar as utilisateur_avatar,
 boutiques.nom as boutique_nom 
 FROM utilisateurs 
 JOIN boutiques ON utilisateurs.boutique_id = boutiques.id 
 WHERE utilisateurs.id = :id_user";

 $requete = $conn->prepare($sql);
 $requete->bindParam(':id_user', $id_user, PDO::PARAM_INT);
 $requete->execute();
 //$selections = $requete->fetch(PDO::FETCH_ASSOC);




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


/*$getClientsQuery = "SELECT utilisateurs.id as id, boutiques.nom as nom_boutique
                   FROM utilisateurs 
                    join boutiques on utilisateurs.boutique_id=boutiques.id
                    WHERE role NOT IN ('admin', 'livreur')";
$getClientsStmt = $conn->query($getClientsQuery);*/

?>
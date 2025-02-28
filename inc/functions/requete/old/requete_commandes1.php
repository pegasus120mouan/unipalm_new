<?php
                       
//$nombreParPage = 10;
//$pageCourante = isset($_GET['page']) ? $_GET['page'] : 1;
//$offset = ($pageCourante - 1) * $nombreParPage;

//$stmt = $conn->prepare("SELECT * FROM commandes ORDER BY date DESC limit $offset,$pageCourante ");
$stmt = $conn->prepare("SELECT * FROM commandes ORDER BY date DESC LIMIT 15");
$stmt->execute();
$commandes = $stmt->fetchAll();

foreach($commandes as $commande)
//$sqlCount = "SELECT COUNT(*) as total FROM commandes";
//$totalEnregistrements = $conn->query($sqlCount)->fetchColumn();
// $nombrePages = ceil($totalEnregistrements / $nombreParPage);


$montantClient = $conn->prepare('SELECT SUM(cout_reel) AS reel_somme FROM commandes where client="Uniko Perfume"');
$montantClient->execute();

$row = $montantClient->fetch(PDO::FETCH_ASSOC);
$sum = $row['reel_somme'];


$montantGlobal = $conn->prepare('SELECT SUM(cout_global) AS montant_global_colis FROM commandes where date=CURRENT_DATE() AND client="Uniko Perfume"');
$montantGlobal->execute();

$rowMontant = $montantGlobal->fetch(PDO::FETCH_ASSOC);
$montantColisGlobal = $rowMontant['montant_global_colis'];



$requete = $conn->query("SELECT nom_boutique FROM clients");   

$livreurs_selection = $conn->query("SELECT prenom_livreur FROM livreurs"); 

$cout_livraison = $conn->query("SELECT cout_livraison FROM cout_livraison"); 

?>
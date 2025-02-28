<?php
// require_once '../inc/functions/connexion.php';
// Return current date from the remote server
$date_actuelle = date('Y-m-d');

// Soustrayez un jour à la date d'aujourd'hui
$date_hier = date('d-m-Y', strtotime($date_actuelle . ' -1 day'));



$montantClient = $conn->prepare('SELECT SUM(cout_reel) AS reel_somme FROM commandes where date=CURRENT_DATE()-1 and statut="Livré"');
$montantClient->execute();

$row = $montantClient->fetch(PDO::FETCH_ASSOC);
$sum = $row['reel_somme'];

/*Gestion Livraisons Montant Global */
$montantLivraison = $conn->prepare('SELECT SUM(recette) AS somme_livraison FROM points_livreurs where date=CURRENT_DATE()-1');
$montantLivraison->execute();

$rowMontant = $montantLivraison->fetch(PDO::FETCH_ASSOC);
$sum_livraison = $rowMontant['somme_livraison'];

/*Gestion Livraisons Depenses */
$montantDepenses = $conn->prepare('SELECT SUM(depenses) AS somme_depenses FROM points_livreurs where date=CURRENT_DATE()-1');
$montantDepenses->execute();

$rowDepenses = $montantDepenses->fetch(PDO::FETCH_ASSOC);
$sum_depenses = $rowDepenses['somme_depenses'];


$nombreColisLivrehier = $conn->prepare('SELECT COUNT(*) AS nbColishier FROM commandes where date=CURRENT_DATE()-1 and statut="Livré"');
$nombreColisLivrehier->execute();

$rownombreColisLivrehier = $nombreColisLivrehier->fetch(PDO::FETCH_ASSOC);
$nombreColisLivre_hier = $rownombreColisLivrehier['nbColishier'];


//$montantClientLass = $conn->prepare('SELECT SUM(cout_reel) AS reel_somme FROM commandes where date=CURRENT_DATE()-1 and statut="Livré"');
//$montantClient->execute();

//$row = $montantClient->fetch(PDO::FETCH_ASSOC);
//$sum = $row['reel_somme'];

$montantGlobalLass_hier = $conn->prepare('SELECT SUM(cout_global) AS montant_global_colis_lassina_hier FROM commandes where date=CURRENT_DATE()-1 and statut="Livré" and livreur="Lassina"');
$montantGlobalLass_hier->execute();
$rowLass_hier = $montantGlobalLass_hier->fetch(PDO::FETCH_ASSOC);
$montantColisGlobalLass_hier = $rowLass_hier['montant_global_colis_lassina_hier'];


$montantDepensesLass_hier = $conn->prepare('SELECT SUM(depenses) AS depenses_hier_lass FROM points_livreurs where date=CURRENT_DATE()-1 and prenoms_livreur="Lassina"');
$montantDepensesLass_hier ->execute();
$rowDepensesLass_hier = $montantDepensesLass_hier->fetch(PDO::FETCH_ASSOC);
$montantDepensesjourLass_hier = $rowDepensesLass_hier['depenses_hier_lass'];

$montantOVLLass_hier=$montantColisGlobalLass_hier-$montantDepensesjourLass_hier;


// Point Fofana
$montantGlobalFof_hier = $conn->prepare('SELECT SUM(cout_global) AS montant_global_colis_Fofana_hier FROM commandes where date=CURRENT_DATE()-1 and statut="Livré" and livreur="Fofana"');
$montantGlobalFof_hier->execute();
$rowFof_hier = $montantGlobalFof_hier->fetch(PDO::FETCH_ASSOC);
$montantColisGlobalFof_hier = $rowFof_hier['montant_global_colis_Fofana_hier'];


$montantDepensesFof_hier = $conn->prepare('SELECT SUM(depenses) AS depenses_hier_fof FROM points_livreurs where date=CURRENT_DATE()-1 and prenoms_livreur="Fofana"');
$montantDepensesFof_hier ->execute();
$rowDepensesFof_hier = $montantDepensesFof_hier->fetch(PDO::FETCH_ASSOC);
$montantDepensesjourFof_hier = $rowDepensesFof_hier['depenses_hier_fof'];

$montantOVLFof_hier=$montantColisGlobalFof_hier-$montantDepensesjourFof_hier;

$montantGeneralverser=$montantOVLLass_hier + $montantOVLFof_hier;

?>
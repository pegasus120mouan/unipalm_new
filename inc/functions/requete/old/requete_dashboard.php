<?php
//Requete montant à global des colis aux clients.
$montantGlobal = $conn->prepare('SELECT SUM(cout_global) AS montant_global_colis FROM commandes where date_commande=CURRENT_DATE() and statut="Livré"');
$montantGlobal->execute();
$rowMontant = $montantGlobal->fetch(PDO::FETCH_ASSOC);
$montantColisGlobal = $rowMontant['montant_global_colis'];

//Requete montant à donner aux clients.
$montantADonner = $conn->prepare('SELECT SUM(cout_reel) AS montant_reel_colis FROM commandes where date_commande=CURRENT_DATE() and statut="Livré"');
$montantADonner->execute();

$rowMontantmontantADonner = $montantADonner->fetch(PDO::FETCH_ASSOC);
$montantColismontantADonner = $rowMontantmontantADonner['montant_reel_colis'];


//Requete montant livreur.
$recetteLivreur = $conn->prepare('SELECT SUM(cout_livraison) AS montant_recette FROM commandes where date_commande=CURRENT_DATE() and statut="Livré"');
$recetteLivreur->execute();

$rowrecetteLivreur = $recetteLivreur->fetch(PDO::FETCH_ASSOC);
$montantrecetteLivreur = $rowrecetteLivreur['montant_recette'];

//Nombre de colis Livré
$nombreColisLivre = $conn->prepare('SELECT COUNT(*) AS nbColis FROM commandes where date_commande=CURRENT_DATE() and statut="Livré"');
$nombreColisLivre->execute();

$rownombreColisLivre = $nombreColisLivre->fetch(PDO::FETCH_ASSOC);
$nombreColisLivre_aujourdhui = $rownombreColisLivre['nbColis'];


//Requete montant à donner aux clients Uniko Perfume.
$montantADonner_Uniko = $conn->prepare("
SELECT SUM(commandes.cout_reel) AS montant_reel_colis_uniko FROM commandes JOIN utilisateurs ON commandes.utilisateur_id = utilisateurs.id JOIN boutiques ON utilisateurs.boutique_id = boutiques.id WHERE commandes.date_commande = CURRENT_DATE() AND commandes.statut = 'Livré' AND boutiques.nom = 'Uniko Perfume'");
$montantADonner_Uniko->execute();

$rowmontantADonner_Uniko = $montantADonner_Uniko->fetch(PDO::FETCH_ASSOC);
$montantColismontantADonner_up = $rowmontantADonner_Uniko['montant_reel_colis_uniko'];


// Montant que le livreur doit verser
$montantDepenses = $conn->prepare('SELECT SUM(depense) AS depenses FROM points_livreurs where date_commande=CURRENT_DATE()');
$montantDepenses ->execute();
$rowDepenses = $montantDepenses->fetch(PDO::FETCH_ASSOC);
$montantDepensesjour = $rowDepenses['depenses'];




$sqlpoints="SELECT
u.id AS utilisateur_id,
b.nom AS boutique_nom,
SUM(CASE WHEN c.statut = 'Livré' THEN c.cout_global ELSE 0 END) AS total_amount,
SUM(CASE WHEN c.statut = 'Livré' THEN c.cout_reel ELSE 0 END) AS total_cout_reel,
SUM(CASE WHEN c.statut = 'Livré' THEN c.cout_livraison ELSE 0 END) AS total_cout_livraison,
COUNT(c.id) AS total_orders,
SUM(CASE WHEN c.statut = 'Livré' THEN 1 ELSE 0 END) AS total_delivered_orders,
SUM(CASE WHEN c.statut != 'Livré' THEN 1 ELSE 0 END) AS total_undelivered_orders
FROM
commandes c
JOIN utilisateurs u ON c.utilisateur_id = u.id
JOIN boutiques b ON u.boutique_id = b.id
WHERE
c.date_commande = CURRENT_DATE()
AND u.role = 'clients'
GROUP BY
u.id, b.nom";
$getPoints_clients= $conn->prepare($sqlpoints);
$getPoints_clients->execute();

// requete statistiques livreurs
$sqlpoints_somme="SELECT 
CONCAT(livreur.nom, ' ', livreur.prenoms) AS nom_livreur,
SUM(commandes.cout_livraison) AS somme_cout_livraison,
MAX(points_livreurs.depense) AS somme_depenses,
SUM(commandes.cout_livraison) - MAX(points_livreurs.depense) AS gain_par_livreur
FROM commandes
JOIN utilisateurs AS livreur ON commandes.livreur_id = livreur.id
LEFT JOIN points_livreurs ON livreur.id = points_livreurs.utilisateur_id AND DATE(points_livreurs.date_commande) = CURDATE()
WHERE DATE(commandes.date_commande) = CURDATE() AND commandes.statut = 'livré'
GROUP BY livreur.nom, livreur.prenoms";



$getPoints_Livreurs= $conn->prepare($sqlpoints_somme);
$getPoints_Livreurs->execute();

$sql_a_donner="SELECT
u.id AS livreur_id,
CONCAT(u.nom, ' ', u.prenoms) AS fullname,
COALESCE(SUM(c.cout_global), 0) AS cout_global,
COALESCE(SUM(pl.depense), 0) AS depense,
COALESCE(SUM(c.cout_global) - SUM(pl.depense), 0) AS montant_a_remettre
FROM
utilisateurs u
LEFT JOIN
boutiques b ON u.boutique_id = b.id
LEFT JOIN (
SELECT
    livreur_id,
    COALESCE(SUM(cout_global), 0) AS cout_global
FROM
    commandes
WHERE
    DATE(date_commande) = CURRENT_DATE
    AND statut = 'Livré'
GROUP BY
    livreur_id
) c ON u.id = c.livreur_id
LEFT JOIN (
SELECT
    utilisateur_id,
    COALESCE(SUM(depense), 0) AS depense
FROM
    points_livreurs
WHERE
    DATE(date_commande) = CURRENT_DATE
GROUP BY
    utilisateur_id
) pl ON u.id = pl.utilisateur_id
WHERE
c.livreur_id IS NOT NULL OR pl.utilisateur_id IS NOT NULL
GROUP BY
b.nom, u.id, u.nom, u.prenoms;";
$getPoints_a_donners= $conn->prepare($sql_a_donner);
$getPoints_a_donners->execute();






//Point par livreur Lassina
//$montantGlobalLass = $conn->prepare('SELECT SUM(cout_global) AS montant_global_colis_lassina FROM commandes where date_commamnde=CURRENT_DATE() and statut="Livré" and livreur="Lassina"');
//$montantGlobalLass->execute();
//$rowLass = $montantGlobalLass->fetch(PDO::FETCH_ASSOC);
//$montantColisGlobalLass = $rowLass['montant_global_colis_lassina'];


//$montantDepensesLass = $conn->prepare('SELECT SUM(depenses) AS depenses FROM points_livreurs where date=CURRENT_DATE() and prenoms_livreur="Lassina"');
//$montantDepensesLass ->execute();
//$rowDepensesLass = $montantDepensesLass->fetch(PDO::FETCH_ASSOC);
//$montantDepensesjourLass = $rowDepensesLass['depenses'];
//$montantOVLLass=$montantColisGlobalLass-$montantDepensesjourLass;


//Point par livreur Fofana
//$montantGlobalFof = $conn->prepare('SELECT SUM(cout_global) AS montant_global_colis_fofana FROM commandes where date=CURRENT_DATE() and statut="Livré" and livreur="Fofana"');
//$montantGlobalFof->execute();
//$rowFof = $montantGlobalFof->fetch(PDO::FETCH_ASSOC);
//$montantColisGlobalFof = $rowFof['montant_global_colis_fofana'];


//$montantDepensesFof = $conn->prepare('SELECT SUM(depenses) AS depenses_fof FROM points_livreurs where date=CURRENT_DATE() and prenoms_livreur="Fofana"');
//$montantDepensesFof->execute();
//$rowDepensesFof  = $montantDepensesFof->fetch(PDO::FETCH_ASSOC);
//$montantDepensesjourFof = $rowDepensesFof['depenses_fof'];
//$montantOVLFof=$montantColisGlobalFof-$montantDepensesjourFof;


?>
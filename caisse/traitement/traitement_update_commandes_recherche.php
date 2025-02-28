<?php

// Connexion à la base de données (à adapter avec vos informations)
require_once '../../inc/functions/connexion.php';

// Récupération des données soumises via le formulaire
$recherche = isset($_GET['recherche']) ? $_GET['recherche'] : '';
$id = $_POST['id'];
$communes = $_POST['communes'];
$cout_global = $_POST['cout_global'];
$livraison = $_POST['livraison'];
$statut_livraison = $_POST['statut_livraison'];
$cout_reel = $cout_global - $livraison;
$date = $_POST['date'];

// Requête SQL d'update
$sql = "UPDATE commandes
        SET communes = :communes, cout_global = :cout_global, cout_livraison = :livraison, 
        cout_reel = :cout_reel, statut = :statut, date_commande = :date
        WHERE id = :id";

// Préparation de la requête
$requete = $conn->prepare($sql);

// Exécution de la requête avec les nouvelles valeurs
$query_execute = $requete->execute(array(
   ':id' => $id,
   ':communes' => $communes,
   ':cout_global' => $cout_global,
   ':livraison' => $livraison,
   ':statut' => $statut_livraison,
   ':cout_reel' => $cout_reel,
   ':date' => $date
));

// Redirection vers la page de recherche avec le paramètre `recherche`
if ($query_execute) {
   $_SESSION['popup'] = true;
   header('Location: ../page_recherche.php?recherche=' . urlencode($recherche));
   exit(0);
} else {
   $_SESSION['delete_pop'] = true;
   header('Location: ../page_recherche.php?recherche=' . urlencode($recherche));
   exit(0);
}

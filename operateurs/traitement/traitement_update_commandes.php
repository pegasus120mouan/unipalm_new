<?php
// Connexion à la base de données (à adapter avec vos informations)
require_once '../../inc/functions/connexion.php';
//session_start(); 

// Récupération des données soumises via le formulaire
//$utilisateur_id = $_SESSION['user_id'];
$id_commande = $_POST['id'];
$communes = $_POST['communes'];
$cout_global = $_POST['cout_global'];
$livraison = $_POST['livraison'];
$cout_reel = $cout_global - $livraison;
$date = $_POST['date'];

// Requête SQL d'update
$sql = "UPDATE commandes
        SET communes = :communes, cout_global = :cout_global, cout_livraison = :livraison, 
        cout_reel = :cout_reel, date_commande = :date
        WHERE id = :id";

// Préparation de la requête
$requete = $conn->prepare($sql);

// Exécution de la requête avec les nouvelles valeurs
$query_execute = $requete->execute(array(
   ':id' => $id_commande,
   // 'utilisateur_id'=>utilisateur_id,
   ':communes' => $communes,
   ':cout_global' => $cout_global,
   ':livraison' => $livraison,
   ':cout_reel' => $cout_reel,
   ':date' => $date
));

// Redirection vebarrs une page de confirmation ou de retour
$query_execute = $requete->execute($data);

if ($query_execute) {
   // $_SESSION['message'] = "Insertion reussie";
   $_SESSION['popup'] = true;
   header('Location: ../livreur_dashboard.php');
   exit(0);
} else {
   $_SESSION['delete_pop'] = true;
   header('Location: ../livreur_dashboard.php');
   exit(0);
}
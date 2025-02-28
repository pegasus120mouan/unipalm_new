<?php
require_once '../inc/functions/connexion.php';   
//session_start(); 

// Récupération des données soumises via le formulaire
$id = $_POST['id'];
$utilisateur_id = $_GET['id_user'];
$communes = $_POST['communes'];
$cout_global = $_POST['cout_global'];
$livraison = $_POST['livraison'];
$cout_reel = $cout_global - $livraison;  
$date= $_POST['date'];

// Requête SQL d'update
$sql = "UPDATE commandes
        SET communes = :communes, cout_global = :cout_global, cout_livraison = :livraison, 
        cout_reel = :cout_reel, date_commande = :date
        WHERE id = :id";

// Préparation de la requête
   $requete = $conn->prepare($sql);

// Exécution de la requête avec les nouvelles valeurs
    $query_execute = $requete->execute(array(
    ':id' => $id,
    ':communes' => $communes,
    ':cout_global' => $cout_global,
    ':livraison' => $livraison,
    ':cout_reel' => $cout_reel,
    ':date' => $date
   ));

// Redirection vebarrs une page de confirmation ou de retour
$query_execute = $requete->execute($data);

if($query_execute)
        {
           // $_SESSION['message'] = "Insertion reussie";
            $_SESSION['popup'] = true;
	       header('Location: commandes_clients.php?id='.$utilisateur_id);
	      exit(0);

            // Redirigez l'utilisateur vers la page d'accueil
            //header("Location: home1.php");
           // exit();
        }
?>

<?php
// Connexion à la base de données (à adapter avec vos informations)
require_once '../inc/functions/connexion.php';   
//session_start(); 

// Récupération des données soumises via le formulaire
$id = $_POST['id'];
$prenoms_livreur = $_POST['prenoms_livreur'];
$recette = $_POST['recette'];
$depenses = $_POST['depenses'];
$gain_jour = $recette-$depenses;
$date= $_POST['date'];

// Requête SQL d'update
$sql = "UPDATE points_livreurs
        SET prenoms_livreur = :prenoms_livreur, recette = :recette, depenses = :depenses, 
        gain_jour = :gain_jour, date = :date
        WHERE id = :id";

// Préparation de la requête
   $requete = $conn->prepare($sql);

// Exécution de la requête avec les nouvelles valeurs
    $query_execute = $requete->execute(array(
    ':id' => $id,
    ':prenoms_livreur' => $prenoms_livreur,
    ':recette' => $recette,
    ':depenses' => $depenses,
    ':gain_jour' => $gain_jour,
    ':date' => $date
   ));

// Redirection vebarrs une page de confirmation ou de retour
$query_execute = $requete->execute($data);

if($query_execute)
        {
           // $_SESSION['message'] = "Insertion reussie";
            $_SESSION['popup'] = true;
	      header('Location: point_livraison.php');
	      exit(0);

            // Redirigez l'utilisateur vers la page d'accueil
            //header("Location: home1.php");
           // exit();
        }
?>

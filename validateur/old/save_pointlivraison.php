<?php
require_once '../inc/functions/connexion.php';
//session_start(); 
if(isset($_POST['savePLivraison']))
{   
    
    $livreur_id = $_POST['livreur_id'];
    $recette = $_POST['recette'];
    $depenses = $_POST['depenses'];
    $gain = $recette-$depenses;
    $date= date("Y-m-d");

    if(empty($livreur_id)){
        $_SESSION['message'] = "Champs obligatoires";
        header('Location: point_livraison.php');
    } 
    else {
        $query = "INSERT INTO points_livreurs (utilisateur_id, recette,depense,gain_jour,date_commande) 
        VALUES (:utilisateur_id, :recette, :depenses,:gain_jour,:date_commande)";
        $query_run = $conn->prepare($query);
    
        $data = [
            ':utilisateur_id' => $livreur_id,
            ':recette' => $recette,
            ':depenses' => $depenses,
            ':gain_jour' => $gain,
            ':date_commande' => $date,

        ];
        $query_execute = $query_run->execute($data);
    
        if($query_execute)
        {
            $_SESSION['popup'] = true;
	       header('Location: point_livraison.php');
	       exit(0);

        }
        else
        {
            $_SESSION['delete_pop'] = true;
            header('Location: point_livraison.php');
            exit(0);
        }
    }

    
}

?>
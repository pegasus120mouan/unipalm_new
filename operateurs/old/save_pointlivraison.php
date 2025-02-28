<?php
require_once '../inc/functions/connexion.php';
//session_start(); 
if(isset($_POST['savePLivraison']))
{   
    $livreur_id = $_SESSION['user_id'];
    $depenses = $_POST['depenses'];
    $date= date("Y-m-d");

    if(empty($livreur_id)){
        $_SESSION['message'] = "Champs obligatoires";
        header('Location: livreurs_points.php');
    } 
    else {
        $query = "INSERT INTO points_livreurs (utilisateur_id,depense,date_commande) 
        VALUES (:utilisateur_id,:depenses,:date_commande)";
        $query_run = $conn->prepare($query);
    
        $data = [
            ':utilisateur_id' => $livreur_id,
            ':depenses' => $depenses,
            ':date_commande' => $date,

        ];
        $query_execute = $query_run->execute($data);
    
        if($query_execute)
        {
            $_SESSION['popup'] = true;
	       header('Location: livreurs_points.php');
	       exit(0);

        }
        else
        {
            $_SESSION['delete_pop'] = true;
            header('Location: livreurs_points.php');
            exit(0);
        }
    }

    
}

?>
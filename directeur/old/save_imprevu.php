<?php
require_once '../inc/functions/connexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $montant = $_POST['montant'];
    $motif = $_POST['motif'];
    $livreur_id = $_POST['livreur_id'];
    $date= date("Y-m-d");
    


   $query = "INSERT INTO imprevu (montant,motif, livreur_id,date_contraction) 
    VALUES (:montant,:motif,:livreur_id,:date_contraction)";
    $query_run = $conn->prepare($query);
    
    $data = [
        ':montant'=>$montant,
        ':motif' => $motif,
        ':livreur_id' => $livreur_id,
        ':date_contraction' => $date,
    ];
    $query_execute = $query_run->execute($data);
   
    if($query_execute)
    {
       // $_SESSION['message'] = "Insertion reussie";
        $_SESSION['popup'] = true;
       header('Location: imprevus.php');
       exit(0);
    }
    else
    {
        $_SESSION['delete_pop'] = true;
        header('Location: imprevus.php');
        exit(0);
    }
}
?>
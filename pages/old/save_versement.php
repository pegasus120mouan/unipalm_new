<?php
require_once '../inc/functions/connexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_dette = $_GET['id'];
    $montant_versement = $_POST['montant_versement'];
    $date_versement= date("Y-m-d");
    

    
    $query = "INSERT INTO versements (dette_id,montant_versement, date_versement) 
    VALUES (:dette_id,:montant_versement, :date_versement)";
    $query_run = $conn->prepare($query);
        
    $data = [
        ':dette_id'=>$id_dette,
        ':montant_versement' => $montant_versement,
        ':date_versement' => $date_versement,

    ];
    $query_execute = $query_run->execute($data);
   
    if($query_execute)
    {
       // $_SESSION['message'] = "Insertion reussie";
        $_SESSION['popup'] = true;
    header('Location: versement_detaille.php?id='.$id_dette);

       exit(0);
    }
    else
    {
        $_SESSION['delete_pop'] = true;
        header('Location: versement_detaille.php?id='.$id_dette);
        exit(0);
    }
}
?>
<?php
require_once '../inc/functions/connexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_engin = $_POST['id_engin'];
    $vignette_date_debut = $_POST['vignette_date_debut'];
    $vignette_date_fin = $_POST['vignette_date_fin'];
    $assurance_date_debut = $_POST['assurance_date_debut'];
    $assurance_date_fin = $_POST['assurance_date_fin'];


    $query = "INSERT INTO contrats (id_engin,vignette_date_debut,vignette_date_fin,assurance_date_debut,assurance_date_fin) 
    VALUES (:id_engin,:vignette_date_debut,:vignette_date_fin,:assurance_date_debut,:assurance_date_fin)";
    $query_run = $conn->prepare($query);
    
    $data = [
        ':id_engin'=>$id_engin,
        ':vignette_date_debut'=>$vignette_date_debut,
        ':vignette_date_fin' => $vignette_date_fin,
        ':assurance_date_debut' => $assurance_date_debut,
        ':assurance_date_fin' => $assurance_date_fin,


    ];
    $query_execute = $query_run->execute($data);
   
    if($query_execute)
    {
       // $_SESSION['message'] = "Insertion reussie";
        $_SESSION['popup'] = true;
       header('Location: contrats.php');
       exit(0);
    }
    else
    {
        $_SESSION['delete_pop'] = true;
        header('Location: contrats.php');
        exit(0);
    }
}
?>
<?php
require_once '../inc/functions/connexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $montant_dette = $_POST['montant'];
    $montant_payes = 0;
    $reste_dette = $montant_dette - $montant_payes;
    $motifs = $_POST['motifs'];
    $date= date("Y-m-d");
    

    $query = "INSERT INTO dette (montant_actuel,montants_payes, reste,date_contraction,motifs) 
    VALUES (:montant_actuel,:montants_payes,:reste,:date_contraction,:motifs)";
    $query_run = $conn->prepare($query);
    
    $data = [
        ':montant_actuel'=>$montant_dette,
        ':montants_payes' => $montant_payes,
        ':reste' => $reste_dette,
        ':date_contraction' => $date,
        ':motifs' => $motifs,
    ];
    $query_execute = $query_run->execute($data);
   
    if($query_execute)
    {
       // $_SESSION['message'] = "Insertion reussie";
        $_SESSION['popup'] = true;
       header('Location: dettes.php');
       exit(0);
    }
    else
    {
        $_SESSION['delete_pop'] = true;
        header('Location: dettes.php');
        exit(0);
    }
}
?>
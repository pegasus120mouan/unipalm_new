<?php
require_once '../inc/functions/connexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $utilisateur_id = $_POST['utilisateur_id'];
    $annee_fabrication = $_POST['annee_fabrication'];
    $plaque_immatriculation = $_POST['plaque_immatriculation'];
    $couleur = $_POST['couleur'];
    $marque = $_POST['marque'];
    $type_engin = $_POST['type_engin'];
    $date_ajout= date("Y-m-d");



    $query = "INSERT INTO engins (utilisateur_id,type_engin, annee_fabrication,plaque_immatriculation,couleur,date_ajout,marque) 
    VALUES (:utilisateur_id,:type_engin,:annee_fabrication,:plaque_immatriculation,:couleur,:date_ajout,:marque)";
    $query_run = $conn->prepare($query);
    
    $data = [
        ':utilisateur_id'=>$utilisateur_id,
        ':type_engin'=>$type_engin,
        ':annee_fabrication' => $annee_fabrication,
        ':plaque_immatriculation' => $plaque_immatriculation,
        ':couleur' => $couleur,
        ':date_ajout' => $date_ajout,
        ':marque' => $marque,

    ];
    $query_execute = $query_run->execute($data);
   
    if($query_execute)
    {
       // $_SESSION['message'] = "Insertion reussie";
        $_SESSION['popup'] = true;
       header('Location: listes_engins.php');
       exit(0);
    }
    else
    {
        $_SESSION['delete_pop'] = true;
        header('Location: listes_engins.php');
        exit(0);
    }
}
?>
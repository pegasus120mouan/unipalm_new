<?php
require('connexion.php');
session_start(); 
if(isset($_POST['saveLivreur']))
{
    $nom_livreur = $_POST['nom_livreur'];
    $prenom_livreur = $_POST['prenom_livreur'];
    $contact = $_POST['contact_livreur'];
    $contact2 = $_POST['contact_whapp'];
    $lieu_habitation = $_POST['lieu_habitation'];

    if(empty($nom_livreur)){
        $_SESSION['message'] = "Champs obligatoires";
        header('Location: home.php');
    } 
    else {
        $query = "INSERT INTO livreurs (nom_livreur, prenom_livreur,contact_livreur,contact_whapp,lieu_habitation) 
        VALUES (:nom_livreur, :prenom_livreur, :contact_livreur,:contact_whapp,:lieu_habitation)";
        $query_run = $conn->prepare($query);
    
        $data = [
            ':nom_livreur' => $nom_livreur,
            ':prenom_livreur' => $prenom_livreur,
            ':contact_livreur' => $contact,
            ':contact_whapp' => $contact2,
            ':lieu_habitation' => $lieu_habitation,
        ];
        $query_execute = $query_run->execute($data);
    
        if($query_execute)
        {
            $_SESSION['message'] = "Inserted Successfully";
            header('Location: livreurs.php');
            exit(0);
        }
        else
        {
            $_SESSION['message'] = "Not Inserted";
            header('Location: livreurs.php');
            exit(0);
        }
    }

    
}

?>
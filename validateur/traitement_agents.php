<?php

require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_tickets.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

   $nom = $_POST["nom"] ?? null;
   $prenoms = $_POST["prenoms"] ?? null;
   $contact = $_POST["contact"] ?? null;
   $id_chef = $_POST["chef_equipe"] ?? null;
   $id_utilisateur = $_SESSION['user_id'] ?? null;
   $date=date("Y-m-d");

   $query = "INSERT INTO agents (nom,prenom,contact,id_chef,date_ajout,date_modification,cree_par) 
    VALUES (:nom,:prenoms,:contact,:id_chef,:date_ajout,:date_modification,:cree_par)";

    $query_run = $conn->prepare($query);
    
    $data = [
        ':nom'=>$nom,
        ':prenoms' => $prenoms,
        ':contact' => $contact,
        ':id_chef' => $id_chef,
        ':date_ajout' => $date,
        ':date_modification' => $date,
        ':cree_par'=> $id_utilisateur

    ];
    $query_execute = $query_run->execute($data);
   
    if($query_execute)
    {
       // $_SESSION['message'] = "Insertion reussie";
        $_SESSION['popup'] = true;
       header('Location: agents.php');
       exit(0);
    }
    else
    {
        $_SESSION['delete_pop'] = true;
            header('Location: agents.php');
        exit(0);
    }

}

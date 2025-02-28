<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/verification_password.php';
//session_start(); 
if ($_SERVER["REQUEST_METHOD"] == "POST") 
{
    $nom = $_POST['nom'];
    $prenoms = $_POST['prenoms'];

        $query = "INSERT INTO chef_equipe (nom, prenoms) VALUES (:nom, :prenoms)";
        $query_run = $conn->prepare($query);
    
        $data = [
            ':nom' => $nom,
            ':prenoms' => $prenoms,
        ];
        $query_execute = $query_run->execute($data);
    
        if($query_execute)
        {
            $_SESSION['popup'] = true;
            header('Location: chef_equipe.php');
            exit(0);
        }
        else
        {
            $_SESSION['message'] = "Not Inserted";
            header('Location: chef_equipe.php');
            exit(0);
        }

    
}

?>
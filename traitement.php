<?php
require('connexion.php');
session_start(); 
if(isset($_POST['signup']))
{
    $nom = $_POST['nom'];
    $prenoms = $_POST['prenoms'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $login = $_POST['login'];
    $password = $_POST['password'];
    $retype_password = $_POST['retype_password'];
    $hasedpassword=hash('sha256',$password);

    if($password=!$retype_password ){
        $_SESSION['message'] = "Les mots de passe sont differents";
        header('Location: register.php');
    } 
    else {
        $query = "INSERT INTO users (nom, prenoms,email,contact,login,password) VALUES (:nom, :prenoms, :email,:contact,:login,:password)";
        $query_run = $conn->prepare($query);
    
        $data = [
            ':nom' => $nom,
            ':prenoms' => $prenoms,
            ':email' => $email,
            ':contact' => $contact,
            ':login' => $login,
            ':password' => $hasedpassword,
        ];
        $query_execute = $query_run->execute($data);
    
        if($query_execute)
        {
            $_SESSION['message'] = "Inserted Successfully";
            header('Location: index.php');
            exit(0);
        }
        else
        {
            $_SESSION['message'] = "Not Inserted";
            header('Location: index.php');
            exit(0);
        }
    }

    
}

?>
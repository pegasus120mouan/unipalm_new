<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/verification_password.php';
//session_start(); 
if(isset($_POST['signup']))
{
    $nom = $_POST['nom'];
    $prenoms = $_POST['prenoms'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $login = $_POST['login'];
    $role = $_POST['role'];
    $password = $_POST['password'];
    $retype_password = $_POST['retype_password'];
    $hasedpassword=hash('sha256',$password);



    if (!isPasswordComplex($password, $retype_password)) {
        $_SESSION['delete_pop'] = true;
        header('Location: utilisateurs.php');
        exit(0);
    } 
    elseif (!isPhoneNumberValid($contact)) {
        $_SESSION['delete_pop'] = true;
        header('Location: utilisateurs.php');
        exit(0);
    }
    else {
        $query = "INSERT INTO users (nom, prenoms,email,contact,login,role,password,avatar) VALUES (:nom, :prenoms, :email,:contact,:login,:role,:password,'default.jpg')";
        $query_run = $conn->prepare($query);
    
        $data = [
            ':nom' => $nom,
            ':prenoms' => $prenoms,
            ':email' => $email,
            ':contact' => $contact,
            ':login' => $login,
            ':role' => $role,
            ':password' => $hasedpassword,
        ];
        $query_execute = $query_run->execute($data);
    
        if($query_execute)
        {
            $_SESSION['popup'] = true;
            header('Location: utilisateurs.php');
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
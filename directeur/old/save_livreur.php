<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/verification_password.php';
//session_start(); 
if ($_SERVER["REQUEST_METHOD"] == "POST") 
{
    $nom = $_POST['nom'];
    $prenoms = $_POST['prenoms'];
    $contact = $_POST['contact'];
    $login = $_POST['login'];
    $password = $_POST['password'];
    $retype_password = $_POST['retype_password'];
    $hasedpassword=hash('sha256',$password);



    if (!isPasswordComplex($password, $retype_password)) {
        $_SESSION['delete_pop'] = true;
        header('Location: liste_livreurs.php');
        exit(0);
    } 
    elseif (!isPhoneNumberValid($contact)) {
        $_SESSION['delete_pop'] = true;
        header('Location: liste_livreurs.php');
        exit(0);
    }
    else {
        $query = "INSERT INTO utilisateurs (nom, prenoms,contact,login,avatar,password,role) VALUES (:nom, :prenoms,:contact,:login,'default.jpg',:password,'livreur')";
        $query_run = $conn->prepare($query);
    
        $data = [
            ':nom' => $nom,
            ':prenoms' => $prenoms,
            ':contact' => $contact,
            ':login' => $login,
            ':password' => $hasedpassword,
        ];
        $query_execute = $query_run->execute($data);
    
        if($query_execute)
        {
            $_SESSION['popup'] = true;
            header('Location: liste_livreurs.php');
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
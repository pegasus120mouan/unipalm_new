<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/verification_password.php';
//session_start(); 
if ($_SERVER["REQUEST_METHOD"] == "POST") 
{
   $boutique_nom = $_POST['boutique_nom'];
  


// Partenaires
$nom = $_POST['nom'];
$prenoms = $_POST['prenoms'];
$contact = $_POST['contact'];
$login = $_POST['login'];
$password = $_POST['password'];
$retype_password = $_POST['retype_password'];
$role = $_POST['role'];
$hasedpassword=hash('sha256',$password);


// Les conditions de validation


/*if (!isPasswordComplex($password, $retype_password)) {
    $_SESSION['delete_pop'] = true;
    header('Location: clients.php');
    exit(0);
} 
elseif (!isPhoneNumberValid($contact)) {
    $_SESSION['delete_pop'] = true;
    header('Location: clients.php');
    exit(0);*/
    if ($password!==$retype_password){

        $_SESSION['delete_pop'] = true;
        header('Location: clients.php');
        exit(0);
    } 

    elseif (!isPasswordComplex($password, $retype_password)) {
        $_SESSION['delete_pop'] = true;
        header('Location: clients.php');
        exit(0);
    }
    elseif (!isPhoneNumberValid($contact)) {
        $_SESSION['delete_pop'] = true;
        header('Location: clients.php');
        exit(0);
    
    } else
    {
        $stmt = $conn->prepare("INSERT INTO boutiques (nom) VALUES (?)");
        $stmt->execute([$boutique_nom]);
    
        // Récupération de l'ID de l'utilisateur que vous venez de créer
        $lastUserId = $conn->lastInsertId();
    
        // Création du client associé à cet utilisateur
        $stmt = $conn->prepare("INSERT INTO utilisateurs (nom,prenoms,contact,login,avatar,password,role,boutique_id) VALUES (?, ?, ?, ?, ?,?,?,?)");
        $stmt->execute([$nom, $prenoms, $contact,$login,'default.jpg',$hasedpassword,$role,$lastUserId]);
    
    
          if($stmt)
           {
               $_SESSION['popup'] = true;
                header('Location: clients.php');
                exit(0);
            }
    
            else
            {
                $_SESSION['delete_pop'] = true;
                header('Location: clients.php');
                exit(0);
            }
    




    }





  
    
}

?>
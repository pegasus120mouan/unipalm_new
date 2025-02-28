<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/verification_password.php';
//session_start(); 
if ($_SERVER["REQUEST_METHOD"] == "POST") 
{
    $nom = $_POST['nom'];
    $prenoms = $_POST['prenoms'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $login = $_POST['login'];
    $password = $_POST['password'];
    $retype_password = $_POST['retype_password'];
    $hasedpassword=hash('sha256',$password);

// Partenaires
    $nom_boutique = $_POST['nom_boutique'];
    $contact_boutique = $_POST['contact_boutique'];
    $localisation_boutique = $_POST['localisation_boutique'];
    $type_partenaire = $_POST['type_partenaire'];
    $date_creation= date("Y-m-d");
// Les conditions de validation

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
elseif (!isContactNumberValid($contact_boutique)) {
    $_SESSION['delete_pop'] = true;
    header('Location: utilisateurs.php');
    exit(0);

} else {
    $stmt = $conn->prepare("INSERT INTO utilisateurs (nom, prenom, email, contact,login, password,avatar) VALUES (?, ?, ?, ?, ?, ?,?)");
    $stmt->execute([$nom, $prenoms, $email,$contact,$login, $hasedpassword,'default.jpg']);

    // Récupération de l'ID de l'utilisateur que vous venez de créer
    $lastUserId = $conn->lastInsertId();

    // Création du client associé à cet utilisateur
    $stmt = $conn->prepare("INSERT INTO partenaires (utilisateur_id, nom_boutique, contact_boutique,localisation_boutique,type_partenaire,logo,date_creation) VALUES (?, ?, ?, ?, ?,?,?)");
    $stmt->execute([$lastUserId, $nom_boutique, $contact_boutique, $localisation_boutique,$localisation_boutique,'default.jpg',$date_creation]);


      if($stmt)
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
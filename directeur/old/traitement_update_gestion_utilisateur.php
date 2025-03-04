<?php
// Connexion à la base de données (à adapter avec vos informations)
require_once '../inc/functions/connexion.php';  
require_once '../inc/functions/verification_password.php';

//session_start(); 

// Récupération des données soumises via le formulaire
$id = $_POST['id'];
$nom = $_POST['nom_utilisateur'];
$prenom = $_POST['prenom_utilisateur'];
$contact = $_POST['contact_utilisateur'];
 
if (!isPhoneNumberValid($contact)) {
    $_SESSION['delete_pop'] = true;
    header('Location: gestion_access.php');
    exit(0);
} else {
// Requête SQL d'update
$sql = "UPDATE utilisateurs
        SET nom = :nom, prenoms = :prenom, contact = :contact WHERE id = :id";

// Préparation de la requête
$requete = $conn->prepare($sql);

// Exécution de la requête avec les nouvelles valeurs
$query_execute = $requete->execute(array(
    ':id' => $id,
    ':nom' => $nom,
    ':prenom' => $prenom,
    ':contact' => $contact
));

// Redirection vebarrs une page de confirmation ou de retour
//$query_execute = $requete->execute($data);
    
//var_dump($query_execute);
//die();
if($query_execute)
        {
           // $_SESSION['message'] = "Insertion reussie";
            $_SESSION['popup'] = true;
	       header('Location: gestion_access.php');
	       exit(0);

            // Redirigez l'utilisateur vers la page d'accueil
            //header("Location: home1.php");
           // exit();
        }
    }

?>

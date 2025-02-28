<?php
// Connexion à la base de données (à adapter avec vos informations)
require_once '../inc/functions/connexion.php';   
//session_start(); 

// Récupération des données soumises via le formulaire
$engin_id = $_POST['engin_id'];
$utilisateur_id = $_POST['utilisateur_id'];

// Requête SQL d'update
$sql = "UPDATE engins
        SET utilisateur_id = :utilisateur_id
        WHERE engin_id = :id";

// Préparation de la requête
$requete = $conn->prepare($sql);

// Exécution de la requête avec les nouvelles valeurs
$query_execute = $requete->execute(array(
    ':utilisateur_id' => $utilisateur_id,
    ':id' => $engin_id
));

// Redirection vebarrs une page de confirmation ou de retour
//$query_execute = $requete->execute($data);
    
//var_dump($query_execute);
//die();
if($query_execute)
        {
           // $_SESSION['message'] = "Insertion reussie";
            $_SESSION['popup'] = true;
	       header('Location: listes_engins.php');
	       exit(0);

            // Redirigez l'utilisateur vers la page d'accueil
            //header("Location: home1.php");
           // exit();
        }
?>

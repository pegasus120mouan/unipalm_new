<?php
// Connexion à la base de données (à adapter avec vos informations)
require_once '../../inc/functions/connexion.php'; 

//require_once '../../inc/functions/verification_password.php';  

//session_start(); 

// Récupération des données soumises via le formulaire
$id_boutique=$_POST['id'];
$nom_boutique = $_POST['nom_boutique'];
$type_articles = $_POST['type_articles'];

        $sql = "UPDATE boutiques
        SET nom = :nom_boutique, type_articles = :type_articles 
        WHERE id = :id_boutique";

// Préparation de la requête
$requete = $conn->prepare($sql);

// Exécution de la requête avec les nouvelles valeurs
$query_execute = $requete->execute(array(
    ':id_boutique' => $id_boutique,
    ':nom_boutique' => $nom_boutique,
    ':type_articles' => $type_articles
));

  
//var_dump($query_exec/die();
if($query_execute)
        {
           // $_SESSION['message'] = "Insertion reussie";
            $_SESSION['popup'] = true;
	       header('Location: ../gestion_access.php');
	       exit(0);

            // Redirigez l'utilisateur vers la page d'accueil
            //header("Location: home1.php");
           // exit();
        }




?>

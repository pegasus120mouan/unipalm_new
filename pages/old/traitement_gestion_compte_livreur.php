<?php
// Connexion à la base de données (à adapter avec vos informations)
require_once '../inc/functions/connexion.php';   
//session_start(); 

// Récupération des données soumises via le formulaire
$utilisateur_id = $_POST['id'];
$statut_compte = $_POST['valeur'];

// Requête SQL d'update
$sql = "UPDATE utilisateurs
        SET statut_compte = :statut_compte
        WHERE id = :id";

// Préparation de la requête
$requete = $conn->prepare($sql);

// Exécution de la requête avec les nouvelles valeurs
$query_execute = $requete->execute(array(
    ':id' => $utilisateur_id,
    ':statut_compte' => $statut_compte
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
?>

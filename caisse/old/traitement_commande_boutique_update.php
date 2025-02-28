<?php
// Connexion à la base de données (à adapter avec vos informations)
require_once '../inc/functions/connexion.php';   
//session_start(); 

// Récupération des données soumises via le formulaire
$commande_id = $_POST['commande_id'];
$id_boutique = $_POST['id_boutique'];

// Requête SQL d'update
$sql = "UPDATE commandes
        SET utilisateur_id= :id_boutique
        WHERE id = :id";

// Préparation de la requête
$requete = $conn->prepare($sql);

// Exécution de la requête avec les nouvelles valeurs
$query_execute = $requete->execute(array(
    ':id_boutique' => $id_boutique,
    ':id' => $commande_id
));

// Redirection vebarrs une page de confirmation ou de retour
//$query_execute = $requete->execute($data);
    
//var_dump($query_execute);
//die();
if($query_execute)
        {
           // $_SESSION['message'] = "Insertion reussie";
            $_SESSION['popup'] = true;
	       header('Location: commandes.php');
	       exit(0);

            // Redirigez l'utilisateur vers la page d'accueil
            //header("Location: home1.php");
           // exit();
        }

        else {
 $errorInfo = $requete->errorInfo();
    // Affichez les informations sur l'erreur
    echo "Erreur SQL : " . $errorInfo[2];

        }
?>

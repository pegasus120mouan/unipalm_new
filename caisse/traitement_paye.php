<?php

require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_tickets.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

   $id_ticket = $_POST["id_ticket"] ?? null;
   $montant_paye = $_POST["montant_paye"] ?? null;
   $date=date("Y-m-d");

  // Requête SQL d'update
$sql = "UPDATE ticket
        SET montant_paie = :montant_paie, date_paie = :date_paie WHERE id_ticket = :id_ticket";

// Préparation de la requête
   $requete = $conn->prepare($sql);

// Exécution de la requête avec les nouvelles valeurs
    $query_execute = $requete->execute(array(
    ':id_ticket' => $id_ticket,
    ':montant_paie' => $montant_paye,
    ':date_paie' => $date
   )); 

// Redirection vebarrs une page de confirmation ou de retour
$query_execute = $requete->execute($data);

if($query_execute)
        {
           // $_SESSION['message'] = "Insertion reussie";
            $_SESSION['popup'] = true;
	       header('Location: tickets.php');
	      exit(0);

            // Redirigez l'utilisateur vers la page d'accueil
            //header("Location: home1.php");
           // exit();
        }

}

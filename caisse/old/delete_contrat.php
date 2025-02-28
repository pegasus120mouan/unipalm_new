<?php
	//session_start();
	require_once '../inc/functions/connexion.php';

	if(isset($_GET['id'])){
		$id=$_GET['id'];
		$requete = $conn->prepare("DELETE FROM contrats WHERE contrat_id = :id");

		// Liaison de la variable avec le paramètre de la requête
		$requete->bindParam(':id', $id, PDO::PARAM_INT);
	
		// Exécution de la requête DELETE
		$requete->execute();
	
		// Vérification du nombre de lignes affectées (1 signifie que la suppression a réussi)
if($requete)
{
	$_SESSION['popup'] = true;
	       header('Location: contrats.php');
	       exit(0);
}	
}
?>
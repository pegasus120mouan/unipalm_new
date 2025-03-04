<?php
	session_start();
	require_once '../inc/functions/connexion.php';

	if(isset($_GET['id'])){
		$id_engin=$_GET['id'];
		$requete = $conn->prepare("DELETE FROM engins WHERE engin_id = :id");

		// Liaison de la variable avec le paramètre de la requête
		$requete->bindParam(':id', $id_engin, PDO::PARAM_INT);
	
		// Exécution de la requête DELETE
		$requete->execute();
	
		// Vérification du nombre de lignes affectées (1 signifie que la suppression a réussi)
if($requete)
{
	$_SESSION['popup'] = true;
	header('Location: listes_engins.php');
	exit(0);
}	
}
?>
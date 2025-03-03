<?php
	session_start();
	require_once '../inc/functions/connexion.php';

	if(isset($_GET['id'])) {
		$id = $_GET['id'];
		try {
			$requete = $conn->prepare("DELETE FROM utilisateurs WHERE id = :id");
			$requete->bindParam(':id', $id, PDO::PARAM_INT);
			
			if($requete->execute()) {
				$_SESSION['popup'] = true;
				$_SESSION['message'] = "L'utilisateur a été supprimé avec succès";
				$_SESSION['status'] = "success";
			} else {
				$_SESSION['popup'] = true;
				$_SESSION['message'] = "Erreur lors de la suppression de l'utilisateur";
				$_SESSION['status'] = "error";
			}
		} catch(PDOException $e) {
			$_SESSION['popup'] = true;
			$_SESSION['message'] = "Erreur: " . $e->getMessage();
			$_SESSION['status'] = "error";
		}
	} else {
		$_SESSION['popup'] = true;
		$_SESSION['message'] = "ID utilisateur non spécifié";
		$_SESSION['status'] = "error";
	}

	header('Location: utilisateurs.php');
	exit();
?>
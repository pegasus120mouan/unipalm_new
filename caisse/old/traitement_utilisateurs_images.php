<?php
require_once '../inc/functions/connexion.php';   

    $id_utilisateur = $_POST['id'];

    // Vérifiez si le dossier de stockage des photos existe, sinon, créez-le
   $dossier_de_stockage = "../dossiers_images/";
   if (!is_dir($dossier_de_stockage)) {
   mkdir($dossier_de_stockage);
    }

   // // Déplacez la photo téléchargée vers le dossier de stockage
   if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
     $photoPath = $dossier_de_stockage . $_FILES['photo']['name'];
        move_uploaded_file($_FILES['photo']['tmp_name'], $photoPath);
    }

    // Préparez et exécutez la requête d'insertion pour stocker les données dans la base de données
   // $stmt = $pdo->prepare("INSERT INTO clients set logo = ? where id= ? ");
    $stmt = $conn->prepare("UPDATE utilisateurs SET avatar = ? WHERE id = ?");
   $stmt->bindParam(1, $_FILES['photo']['name']);
   $stmt->bindParam(2, $id_utilisateur);
    $stmt->execute();


    if($stmt)
        {
           // $_SESSION['message'] = "Insertion reussie";
            $_SESSION['popup'] = true;
	      header('Location: liste_livreurs.php');
	      exit(0);

            // Redirigez l'utilisateur vers la page d'accueil
            //header("Location: home1.php");
           // exit();
        }
?>

<?php
session_start(); 

require_once '../inc/functions/connexion.php';   

    // Récupérez l'ID de l'engin à partir des données POST
    $id_engin = $_POST['id'];

    // Vérifiez si le dossier de stockage des photos existe, sinon, créez-le
    $dossier_de_stockage = "../dossiers_motos/";
    if (!is_dir($dossier_de_stockage)) {
        mkdir($dossier_de_stockage);
    }

    // Mettez à jour les images dans la base de données et déplacez les fichiers téléchargés
    // Vous devez ajuster les noms des variables pour correspondre à votre formulaire HTML
    $image1Path = null;
    if (isset($_FILES['photo1']) && $_FILES['photo1']['error'] === UPLOAD_ERR_OK) {
        $image1Path = $dossier_de_stockage . $_FILES['photo1']['name'];
        move_uploaded_file($_FILES['photo1']['tmp_name'], $image1Path);
    }

    $image2Path = null;
    if (isset($_FILES['photo2']) && $_FILES['photo2']['error'] === UPLOAD_ERR_OK) {
        $image2Path = $dossier_de_stockage . $_FILES['photo2']['name'];
        move_uploaded_file($_FILES['photo2']['tmp_name'], $image2Path);
    }

    $image3Path = null;
    if (isset($_FILES['photo3']) && $_FILES['photo3']['error'] === UPLOAD_ERR_OK) {
        $image3Path = $dossier_de_stockage . $_FILES['photo3']['name'];
        move_uploaded_file($_FILES['photo3']['tmp_name'], $image3Path);
    }

    $image4Path = null;
    if (isset($_FILES['photo4']) && $_FILES['photo4']['error'] === UPLOAD_ERR_OK) {
        $image4Path = $dossier_de_stockage . $_FILES['photo4']['name'];
        move_uploaded_file($_FILES['photo4']['tmp_name'], $image4Path);
    }

    // Préparez et exécutez la requête d'insertion pour stocker les données dans la base de données
    $stmt = $conn->prepare("UPDATE engins SET image_1 = ?, image_2 = ?, image_3 = ?, image_4 = ? WHERE engin_id = ?");
    $stmt->bindParam(1, $image1Path);
    $stmt->bindParam(2, $image2Path);
    $stmt->bindParam(3, $image3Path);
    $stmt->bindParam(4, $image4Path);
    $stmt->bindParam(5, $id_engin);
    $stmt->execute();

    // Vérifiez si la mise à jour a réussi
    if ($stmt) {
        $_SESSION['popup'] = true; // Indicateur de réussite pour une boîte de dialogue ou autre
          header('Location: infos_engins.php?id=' . $id_engin);
        exit(0);
    }
?>

<?php
session_start();
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_prix_unitaires.php';

// Traitement de l'ajout d'un prix unitaire
if (isset($_POST['savePrixUnitaire'])) {
    $id_usine = $_POST['id_usine'];
    $prix = $_POST['prix'];
    $date_debut = $_POST['date_debut'];
    $date_fin = !empty($_POST['date_fin']) ? $_POST['date_fin'] : null;

    if (createPrixUnitaire($conn, $id_usine, $prix, $date_debut, $date_fin)) {
        $_SESSION['success'] = "Prix unitaire ajouté avec succès";
    } else {
        $_SESSION['error'] = "Erreur lors de l'ajout du prix unitaire";
    }
    header('Location: prix_unitaires.php');
    exit();
}

// Traitement de la mise à jour d'un prix unitaire
if (isset($_POST['updatePrixUnitaire'])) {
    $id = $_POST['id'];
    $id_usine = $_POST['id_usine'];
    $prix = $_POST['prix'];
    $date_debut = $_POST['date_debut'];
    $date_fin = !empty($_POST['date_fin']) ? $_POST['date_fin'] : null;

    if (updatePrixUnitaire($conn, $id, $id_usine, $prix, $date_debut, $date_fin)) {
        $_SESSION['success'] = "Prix unitaire mis à jour avec succès";
    } else {
        $_SESSION['error'] = "Erreur lors de la mise à jour du prix unitaire";
    }
    header('Location: prix_unitaires.php');
    exit();
}

// Traitement de la suppression d'un prix unitaire
if (isset($_POST['deletePrixUnitaire'])) {
    $id = $_POST['id'];

    if (deletePrixUnitaire($conn, $id)) {
        $_SESSION['success'] = "Prix unitaire supprimé avec succès";
    } else {
        $_SESSION['error'] = "Erreur lors de la suppression du prix unitaire";
    }
    header('Location: prix_unitaires.php');
    exit();
}

// Si aucune action n'est spécifiée, redirection vers la page des prix unitaires
header('Location: prix_unitaires.php');
exit();
?>

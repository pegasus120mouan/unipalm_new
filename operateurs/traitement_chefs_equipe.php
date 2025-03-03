<?php
require_once '../inc/functions/connexion.php';
session_start(); // Démarrer la session pour utiliser les variables de session

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = trim($_POST['nom']);
    $prenoms = trim($_POST['prenoms']);
    $id_chef = isset($_POST['id_chef']) ? intval($_POST['id_chef']) : null;

    // Vérification des champs obligatoires
    if (empty($nom) || empty($prenoms)) {
        $_SESSION['popup'] = true;
        $_SESSION['message'] = "Nom et prénoms sont obligatoires.";
        header('Location: chef_equipe.php');
        exit;
    }

    try {
        if ($id_chef) {
            // Mise à jour d'un chef d'équipe
            $query = "UPDATE chef_equipe SET nom = :nom, prenoms = :prenoms WHERE id_chef = :id_chef";
            $query_run = $conn->prepare($query);
            $data = [
                ':nom' => $nom,
                ':prenoms' => $prenoms,
                ':id_chef' => $id_chef,
            ];
            $query_execute = $query_run->execute($data);

            if ($query_execute) {
                $_SESSION['popup'] = true;
                $_SESSION['message'] = "Chef d'équipe mis à jour avec succès.";
            } else {
                $_SESSION['popup'] = true;
                $_SESSION['message'] = "Erreur lors de la mise à jour des données.";
            }
        } else {
            // Insertion d'un nouveau chef d'équipe
            $query = "INSERT INTO chef_equipe (nom, prenoms) VALUES (:nom, :prenoms)";
            $query_run = $conn->prepare($query);
            $data = [
                ':nom' => $nom,
                ':prenoms' => $prenoms,
            ];
            $query_execute = $query_run->execute($data);

            if ($query_execute) {
                $_SESSION['popup'] = true;
                $_SESSION['message'] = "Chef d'équipe ajouté avec succès.";
            } else {
                $_SESSION['popup'] = true;
                $_SESSION['message'] = "Erreur lors de l'ajout du chef d'équipe.";
            }
        }
    } catch (PDOException $e) {
        $_SESSION['popup'] = true;
        $_SESSION['message'] = "Erreur : " . $e->getMessage();
    }

    header('Location: chef_equipe.php');
    exit;
}

// Code pour la suppression d'un chef d'équipe
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id_chef = intval($_GET['id']);

    try {
        // Suppression d'un chef d'équipe
        $query = "DELETE FROM chef_equipe WHERE id_chef = :id_chef";
        $query_run = $conn->prepare($query);
        $query_run->bindParam(':id_chef', $id_chef, PDO::PARAM_INT);

        if ($query_run->execute()) {
            $_SESSION['popup'] = true;
            $_SESSION['message'] = "Chef d'équipe supprimé avec succès.";
        } else {
            $_SESSION['popup'] = true;
            $_SESSION['message'] = "Erreur lors de la suppression du chef d'équipe.";
        }
    } catch (PDOException $e) {
        $_SESSION['popup'] = true;
        $_SESSION['message'] = "Erreur : " . $e->getMessage();
    }

    header('Location: chef_equipe.php');
    exit;
}
?>

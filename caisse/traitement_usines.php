<?php
require_once '../inc/functions/connexion.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ajout d'une usine
    if (isset($_POST['add_usine'])) {
        $nom_usine = $_POST['nom_usine'];
        $created_by = $_SESSION['user_id'];

        try {
            $stmt = $conn->prepare("INSERT INTO usines (nom_usine, created_by) VALUES (?, ?)");
            $result = $stmt->execute([$nom_usine, $created_by]);

            if ($result) {
                $_SESSION['popup'] = true;
                $_SESSION['message'] = "Usine ajoutée avec succès !";
                $_SESSION['status'] = "success";
            } else {
                $_SESSION['popup'] = true;
                $_SESSION['message'] = "Erreur lors de l'ajout de l'usine";
                $_SESSION['status'] = "error";
            }
        } catch(PDOException $e) {
            $_SESSION['popup'] = true;
            $_SESSION['message'] = "Erreur : " . $e->getMessage();
            $_SESSION['status'] = "error";
        }
    }

    // Modification d'une usine
    if (isset($_POST['update_usine'])) {
        $id_usine = $_POST['id_usine'];
        $nom_usine = $_POST['nom_usine'];

        try {
            $stmt = $conn->prepare("UPDATE usines SET nom_usine = ? WHERE id_usine = ?");
            $result = $stmt->execute([$nom_usine, $id_usine]);

            if ($result) {
                $_SESSION['popup'] = true;
                $_SESSION['message'] = "Usine modifiée avec succès !";
                $_SESSION['status'] = "success";
            } else {
                $_SESSION['popup'] = true;
                $_SESSION['message'] = "Erreur lors de la modification de l'usine";
                $_SESSION['status'] = "error";
            }
        } catch(PDOException $e) {
            $_SESSION['popup'] = true;
            $_SESSION['message'] = "Erreur : " . $e->getMessage();
            $_SESSION['status'] = "error";
        }
    }
}

// Suppression d'une usine (méthode GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id_usine = $_GET['id'];

    try {
        $stmt = $conn->prepare("DELETE FROM usines WHERE id_usine = ?");
        $result = $stmt->execute([$id_usine]);

        if ($result) {
            $_SESSION['popup'] = true;
            $_SESSION['message'] = "Usine supprimée avec succès !";
            $_SESSION['status'] = "success";
        } else {
            $_SESSION['popup'] = true;
            $_SESSION['message'] = "Erreur lors de la suppression de l'usine";
            $_SESSION['status'] = "error";
        }
    } catch(PDOException $e) {
        $_SESSION['popup'] = true;
        $_SESSION['message'] = "Erreur : " . $e->getMessage();
        $_SESSION['status'] = "error";
    }
}

// Redirection vers la page des usines
header('Location: usines.php');
exit;

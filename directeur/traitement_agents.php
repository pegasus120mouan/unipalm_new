<?php
require_once '../inc/functions/connexion.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ajout d'un agent
    if (isset($_POST['add_agent'])) {
        $nom = $_POST['nom'];
        $prenom = $_POST['prenom'];
        $contact = $_POST['contact'];
        $id_chef = $_POST['id_chef'];
        $cree_par = $_SESSION['user_id']; // ID de l'utilisateur connecté

        try {
            $stmt = $conn->prepare("INSERT INTO agents (nom, prenom, contact, id_chef, cree_par, date_ajout) VALUES (?, ?, ?, ?, ?, NOW())");
            $result = $stmt->execute([$nom, $prenom, $contact, $id_chef, $cree_par]);

            if ($result) {
                $_SESSION['popup'] = true;
                $_SESSION['message'] = "Agent ajouté avec succès !";
                $_SESSION['status'] = "success";
            } else {
                $_SESSION['popup'] = true;
                $_SESSION['message'] = "Erreur lors de l'ajout de l'agent";
                $_SESSION['status'] = "error";
            }
        } catch(PDOException $e) {
            $_SESSION['popup'] = true;
            $_SESSION['message'] = "Erreur : " . $e->getMessage();
            $_SESSION['status'] = "error";
        }
        
        header('Location: agents.php');
        exit;
    }

    // Modification d'un agent
    if (isset($_POST['update_agent'])) {
        $id_agent = $_POST['id_agent'];
        $nom = $_POST['nom'];
        $prenoms = $_POST['prenoms'];
        $contact = $_POST['contact'];

        try {
            $stmt = $conn->prepare("UPDATE agents SET nom = ?, prenom = ?, contact = ?, date_modification = NOW() WHERE id_agent = ?");
            $result = $stmt->execute([$nom, $prenoms, $contact, $id_agent]);

            if ($result) {
                $_SESSION['popup'] = true;
                $_SESSION['message'] = "Agent modifié avec succès !";
                $_SESSION['status'] = "success";
            } else {
                $_SESSION['popup'] = true;
                $_SESSION['message'] = "Erreur lors de la modification de l'agent";
                $_SESSION['status'] = "error";
            }
        } catch(PDOException $e) {
            $_SESSION['popup'] = true;
            $_SESSION['message'] = "Erreur : " . $e->getMessage();
            $_SESSION['status'] = "error";
        }

        header('Location: agents.php');
        exit;
    }
}

// Suppression d'un agent (méthode GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id_agent = $_GET['id'];

    try {
        // Vérifier si l'agent existe
        $stmt = $conn->prepare("SELECT * FROM agents WHERE id_agent = ?");
        $stmt->execute([$id_agent]);
        $agent = $stmt->fetch();

        if (!$agent) {
            $_SESSION['popup'] = true;
            $_SESSION['message'] = "Agent non trouvé !";
            $_SESSION['status'] = "error";
        } else {
            // Supprimer l'agent
            $stmt = $conn->prepare("DELETE FROM agents WHERE id_agent = ?");
            $result = $stmt->execute([$id_agent]);

            if ($result) {
                $_SESSION['popup'] = true;
                $_SESSION['message'] = "Agent supprimé avec succès !";
                $_SESSION['status'] = "success";
            } else {
                $_SESSION['popup'] = true;
                $_SESSION['message'] = "Erreur lors de la suppression de l'agent";
                $_SESSION['status'] = "error";
            }
        }
    } catch(PDOException $e) {
        $_SESSION['popup'] = true;
        $_SESSION['message'] = "Erreur : " . $e->getMessage();
        $_SESSION['status'] = "error";
    }

    header('Location: agents.php');
    exit;
}

// Redirection par défaut
header('Location: agents.php');
exit;
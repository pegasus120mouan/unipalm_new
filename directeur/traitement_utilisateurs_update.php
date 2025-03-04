<?php
require_once '../inc/functions/connexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nom = $_POST['nom'];
    $prenoms = $_POST['prenoms'];
    $contact = $_POST['contact'];
    $login = $_POST['login'];
    $role = $_POST['role'];
    
    try {
        $stmt = $conn->prepare("UPDATE utilisateurs SET nom = ?, prenoms = ?, contact = ?, login = ?, role = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$nom, $prenoms, $contact, $login, $role, $id]);
        
        $_SESSION['popup'] = true;
        $_SESSION['message'] = "L'utilisateur a été mis à jour avec succès !";
        $_SESSION['status'] = "success";
        
    } catch(PDOException $e) {
        $_SESSION['popup'] = true;
        $_SESSION['message'] = "Erreur lors de la mise à jour : " . $e->getMessage();
        $_SESSION['status'] = "error";
    }
    
    header('Location: utilisateurs.php');
    exit;
} else {
    header('Location: utilisateurs.php');
    exit;
}
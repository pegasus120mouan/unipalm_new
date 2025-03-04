<?php
require_once '../inc/functions/connexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nom = $_POST['nom'];
    $prenoms = $_POST['prenoms'];
    
    try {
        $stmt = $conn->prepare("UPDATE chef_equipe SET nom = ?, prenoms = ? WHERE id_chef  = ?");
        $stmt->execute([$nom, $prenoms, $id]);  
        
        $_SESSION['popup'] = true;
        $_SESSION['message'] = "Le chef d'équipe a été mis à jour avec succès !";
        $_SESSION['status'] = "success";
        
    } catch(PDOException $e) {
        $_SESSION['popup'] = true;
        $_SESSION['message'] = "Erreur lors de la mise à jour : " . $e->getMessage();
        $_SESSION['status'] = "error";
    }
    
    header('Location: chef_equipe.php');
    exit;
} else {
    header('Location: chef_equipe.php');
    exit;
}

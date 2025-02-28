<?php
require_once '../inc/functions/connexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $nouveau_statut = $_POST['statut_compte'];
    
    try {
        $stmt = $conn->prepare("UPDATE utilisateurs SET statut_compte = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$nouveau_statut, $user_id]);
        
        $_SESSION['popup'] = true;
        $_SESSION['message'] = "Le statut du compte a été mis à jour avec succès !";
        $_SESSION['status'] = "success";
        
    } catch(PDOException $e) {
        $_SESSION['popup'] = true;
        $_SESSION['message'] = "Erreur lors de la mise à jour du statut : " . $e->getMessage();
        $_SESSION['status'] = "error";
    }
    
    header('Location: utilisateurs.php');
    exit;
} else {
    header('Location: utilisateurs.php');
    exit;
}

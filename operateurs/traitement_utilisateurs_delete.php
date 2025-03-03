<?php
require_once '../inc/functions/connexion.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    try {
        // Vérifier si l'utilisateur existe
        $stmt = $conn->prepare("SELECT * FROM utilisateurs WHERE id = ?");
        $stmt->execute([$id]);
        $utilisateur = $stmt->fetch();
        
        if (!$utilisateur) {
            $_SESSION['popup'] = true;
            $_SESSION['message'] = "Utilisateur non trouvé !";
            $_SESSION['status'] = "error";
            header('Location: utilisateurs.php');
            exit;
        }
        
        // Supprimer l'utilisateur
        $stmt = $conn->prepare("DELETE FROM utilisateurs WHERE id = ?");
        $stmt->execute([$id]);
        
        $_SESSION['popup'] = true;
        $_SESSION['message'] = "L'utilisateur a été supprimé avec succès !";
        $_SESSION['status'] = "success";
        
    } catch(PDOException $e) {
        $_SESSION['popup'] = true;
        $_SESSION['message'] = "Erreur lors de la suppression : " . $e->getMessage();
        $_SESSION['status'] = "error";
    }
    
    header('Location: utilisateurs.php');
    exit;
} else {
    header('Location: utilisateurs.php');
    exit;
}

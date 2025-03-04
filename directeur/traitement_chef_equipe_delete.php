<?php
require_once '../inc/functions/connexion.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    try {
        // Vérifier si le chef d'équipe existe
        $stmt = $conn->prepare("SELECT * FROM chef_equipe WHERE id_chef = ?");
        $stmt->execute([$id]);
        $chef = $stmt->fetch();
        
        if (!$chef) {
            $_SESSION['popup'] = true;
            $_SESSION['message'] = "Chef d'équipe non trouvé !";
            $_SESSION['status'] = "error";
            header('Location: chef_equipe.php');
            exit;
        }
        
        // Supprimer le chef d'équipe
        $stmt = $conn->prepare("DELETE FROM chef_equipe WHERE id_chef = ?");
        $stmt->execute([$id]);
        
        $_SESSION['popup'] = true;
        $_SESSION['message'] = "Le chef d'équipe a été supprimé avec succès !";
        $_SESSION['status'] = "success";
        
    } catch(PDOException $e) {
        $_SESSION['popup'] = true;
        $_SESSION['message'] = "Erreur lors de la suppression : " . $e->getMessage();
        $_SESSION['status'] = "error";
    }
    
    header('Location: chef_equipe.php');
    exit;
} else {
    header('Location: chef_equipe.php');
    exit;
}

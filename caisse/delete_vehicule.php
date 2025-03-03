<?php
require_once '../inc/functions/connexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    
    if (!empty($id)) {
        try {
            // Vérifier si le véhicule est utilisé dans d'autres tables
            $stmt = $conn->prepare("SELECT COUNT(*) FROM tickets WHERE vehicule_id = ?");
            $stmt->execute([$id]);
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                $_SESSION['error'] = "Impossible de supprimer ce véhicule car il est utilisé dans des tickets";
                header('Location: vehicules.php');
                exit();
            }
            
            $stmt = $conn->prepare("DELETE FROM vehicules WHERE vehicules_id = ?");
            $stmt->execute([$id]);
            
            $_SESSION['success'] = "Véhicule supprimé avec succès";
            header('Location: vehicules.php');
            exit();
        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur lors de la suppression du véhicule";
            header('Location: vehicules.php');
            exit();
        }
    } else {
        $_SESSION['error'] = "ID du véhicule non spécifié";
        header('Location: vehicules.php');
        exit();
    }
}
?>

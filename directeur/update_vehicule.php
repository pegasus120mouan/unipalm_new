<?php
require_once '../inc/functions/connexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $matricule = $_POST['matricule'] ?? '';
    
    if (!empty($id) && !empty($matricule)) {
        try {
            $stmt = $conn->prepare("UPDATE vehicules SET matricule_vehicule = ?, updated_at = NOW() WHERE vehicules_id = ?");
            $stmt->execute([$matricule, $id]);
            
            $_SESSION['success'] = "Véhicule modifié avec succès";
            header('Location: vehicules.php');
            exit();
        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur lors de la modification du véhicule";
            header('Location: vehicules.php');
            exit();
        }
    } else {
        $_SESSION['error'] = "Veuillez remplir tous les champs";
        header('Location: vehicules.php');
        exit();
    }
}
?>

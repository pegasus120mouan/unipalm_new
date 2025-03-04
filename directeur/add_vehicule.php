<?php
require_once '../inc/functions/connexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matricule = $_POST['matricule'] ?? '';
    
    if (!empty($matricule)) {
        try {
            $stmt = $conn->prepare("INSERT INTO vehicules (matricule_vehicule, created_at, updated_at) VALUES (?, NOW(), NOW())");
            $stmt->execute([$matricule]);
            
            $_SESSION['success'] = "Véhicule ajouté avec succès";
            header('Location: vehicules.php');
            exit();
        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur lors de l'ajout du véhicule";
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

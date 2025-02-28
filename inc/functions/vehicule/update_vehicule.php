<?php
require_once '../../functions/connexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['vehicules_id'];
    $matricule = $_POST['matricule_vehicule'];
    
    try {
        $query = "UPDATE vehicules SET matricule_vehicule = :matricule, updated_at = CURRENT_TIMESTAMP WHERE vehicules_id = :id";
        $stmt = $conn->prepare($query);
        $stmt->execute([
            'matricule' => $matricule,
            'id' => $id
        ]);
        
        header('Location: ../../../pages/vehicules.php?success=update');
    } catch(PDOException $e) {
        header('Location: ../../../pages/vehicules.php?error=update');
    }
} else {
    header('Location: ../../../pages/vehicules.php');
}

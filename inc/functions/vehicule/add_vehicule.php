<?php
require_once '../../functions/connexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matricule = $_POST['matricule_vehicule'];
    
    try {
        $query = "INSERT INTO vehicules (matricule_vehicule) VALUES (:matricule)";
        $stmt = $conn->prepare($query);
        $stmt->execute(['matricule' => $matricule]);
        
        header('Location: ../../../pages/vehicules.php?success=add');
    } catch(PDOException $e) {
        header('Location: ../../../pages/vehicules.php?error=add');
    }
} else {
    header('Location: ../../../pages/vehicules.php');
}

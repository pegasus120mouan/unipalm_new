<?php
require_once '../../functions/connexion.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    try {
        $query = "DELETE FROM vehicules WHERE vehicules_id = :id";
        $stmt = $conn->prepare($query);
        $stmt->execute(['id' => $id]);
        
        header('Location: ../../../pages/vehicules.php?success=delete');
    } catch(PDOException $e) {
        header('Location: ../../../pages/vehicules.php?error=delete');
    }
} else {
    header('Location: ../../../pages/vehicules.php');
}

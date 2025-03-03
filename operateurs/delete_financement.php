<?php
session_start();
require_once '../inc/functions/connexion.php';

if (isset($_GET['id'])) {
    try {
        $numero_financement = $_GET['id'];
        
        // Préparer la requête de suppression
        $sql = "DELETE FROM financement WHERE Numero_financement = :numero_financement";
        $stmt = $conn->prepare($sql);
        
        // Exécuter la requête
        $stmt->execute([':numero_financement' => $numero_financement]);
        
        // Vérifier si la suppression a réussi
        if ($stmt->rowCount() > 0) {
            $_SESSION['popup'] = true;
            $_SESSION['success_modal'] = true;
        } else {
            $_SESSION['delete_pop'] = true;
        }
    } catch (PDOException $e) {
        $_SESSION['delete_pop'] = true;
    }
}

// Rediriger vers la page des financements
header('Location: financements.php');
exit();

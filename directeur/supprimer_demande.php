<?php
session_start();
require_once '../inc/functions/connexion.php';

if (isset($_POST['id_demande'])) {
    try {
        $id_demande = $_POST['id_demande'];
        
        // Vérifier si la demande existe et n'est pas déjà approuvée
        $check_sql = "SELECT statut FROM demande_sortie WHERE id_demande = :id_demande";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bindParam(':id_demande', $id_demande);
        $check_stmt->execute();
        $demande = $check_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($demande) {
            if ($demande['statut'] === 'en_attente') {
                // Supprimer la demande
                $sql = "DELETE FROM demande_sortie WHERE id_demande = :id_demande";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id_demande', $id_demande);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = "La demande a été supprimée avec succès.";
                } else {
                    $_SESSION['error'] = "Erreur lors de la suppression de la demande.";
                }
            } else {
                $_SESSION['error'] = "Impossible de supprimer une demande déjà approuvée ou payée.";
            }
        } else {
            $_SESSION['error'] = "Demande introuvable.";
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la suppression : " . $e->getMessage();
    }
} else {
    $_SESSION['error'] = "ID de demande non spécifié.";
}

header('Location: demandes.php');
exit();
?>

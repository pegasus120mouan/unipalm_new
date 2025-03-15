<?php
session_start();
require_once '../inc/functions/connexion.php';

if (isset($_POST['update_demande'])) {
    try {
        $id_demande = $_POST['id_demande'];
        $montant = $_POST['montant'];
        $motif = $_POST['motif'];
        
        // Vérifier si la demande existe et n'est pas déjà approuvée
        $check_sql = "SELECT statut FROM demande_sortie WHERE id_demande = :id_demande";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bindParam(':id_demande', $id_demande);
        $check_stmt->execute();
        $demande = $check_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($demande) {
            if ($demande['statut'] === 'en_attente') {
                // Mettre à jour la demande
                $sql = "UPDATE demande_sortie SET 
                        montant = :montant,
                        motif = :motif,
                        updated_at = NOW()
                        WHERE id_demande = :id_demande";
                
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':montant', $montant);
                $stmt->bindParam(':motif', $motif);
                $stmt->bindParam(':id_demande', $id_demande);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = "La demande a été mise à jour avec succès.";
                } else {
                    $_SESSION['error'] = "Erreur lors de la mise à jour de la demande.";
                }
            } else {
                $_SESSION['error'] = "Impossible de modifier une demande déjà approuvée ou payée.";
            }
        } else {
            $_SESSION['error'] = "Demande introuvable.";
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la mise à jour : " . $e->getMessage();
    }
} else {
    $_SESSION['error'] = "Données de mise à jour non spécifiées.";
}

header('Location: demandes.php');
exit();
?>

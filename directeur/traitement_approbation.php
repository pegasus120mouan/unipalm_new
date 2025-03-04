<?php
session_start();
require_once '../inc/functions/connexion.php';

if (isset($_GET['action']) && isset($_GET['id'])) {
    $id_demande = $_GET['id'];
    $action = $_GET['action'];
    
    try {
        // Vérifier si la demande existe et n'est pas déjà traitée
        $check_sql = "SELECT * FROM demande_sortie WHERE id_demande = :id_demande AND date_approbation IS NULL";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bindParam(':id_demande', $id_demande);
        $check_stmt->execute();
        $demande = $check_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($demande) {
            $now = date('Y-m-d H:i:s');
            $user_id = $_SESSION['user_id']; // ID de l'utilisateur connecté
            
            if ($action === 'approuver') {
                // Approuver la demande
                $sql = "UPDATE demande_sortie SET 
                        statut = 'approuve',
                        date_approbation = :date_approbation,
                        approuve_par = :user_id,
                        updated_at = NOW()
                        WHERE id_demande = :id_demande";
                
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':date_approbation', $now);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':id_demande', $id_demande);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = "La demande a été approuvée avec succès.";
                } else {
                    $_SESSION['error'] = "Erreur lors de l'approbation de la demande.";
                }
            } elseif ($action === 'rejeter') {
                // Rejeter la demande
                $sql = "UPDATE demande_sortie SET 
                        statut = 'rejete',
                        date_approbation = :date_approbation,
                        approuve_par = :user_id,
                        updated_at = NOW()
                        WHERE id_demande = :id_demande";
                
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':date_approbation', $now);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':id_demande', $id_demande);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = "La demande a été rejetée.";
                } else {
                    $_SESSION['error'] = "Erreur lors du rejet de la demande.";
                }
            }
        } else {
            $_SESSION['error'] = "La demande n'existe pas ou a déjà été traitée.";
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = "Erreur lors du traitement : " . $e->getMessage();
    }
} else {
    $_SESSION['error'] = "Paramètres manquants.";
}

header('Location: demande_attente.php');
exit();
?>

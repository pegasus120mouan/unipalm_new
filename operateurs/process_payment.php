<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_usines.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usine = $_POST['id_usine'];
    $montant_paiement = $_POST['montant_paiement'];
    $date_paiement = $_POST['date_paiement'];
    $mode_paiement = $_POST['mode_paiement'];
    $reference = $_POST['reference'];
    
    try {
        // Début de la transaction
        $conn->beginTransaction();
        
        // 1. Mettre à jour les montants dans la table usines
        $stmt = $conn->prepare("
            UPDATE usines 
            SET montant_paye = montant_paye + :montant_paiement,
                montant_restant = montant_restant - :montant_paiement,
                derniere_date_paiement = :date_paiement
            WHERE id_usine = :id_usine
        ");
        
        $stmt->execute([
            ':montant_paiement' => $montant_paiement,
            ':date_paiement' => $date_paiement,
            ':id_usine' => $id_usine
        ]);
        
        // 2. Enregistrer l'historique du paiement
        $stmt = $conn->prepare("
            INSERT INTO historique_paiements (
                id_usine, montant, date_paiement, mode_paiement, 
                reference, created_by, created_at
            ) VALUES (
                :id_usine, :montant, :date_paiement, :mode_paiement,
                :reference, :created_by, NOW()
            )
        ");
        
        $stmt->execute([
            ':id_usine' => $id_usine,
            ':montant' => $montant_paiement,
            ':date_paiement' => $date_paiement,
            ':mode_paiement' => $mode_paiement,
            ':reference' => $reference,
            ':created_by' => $_SESSION['user_id']
        ]);
        
        // Valider la transaction
        $conn->commit();
        
        $_SESSION['success'] = "Le paiement a été enregistré avec succès.";
        
    } catch (Exception $e) {
        // Annuler la transaction en cas d'erreur
        $conn->rollBack();
        $_SESSION['error'] = "Une erreur est survenue lors de l'enregistrement du paiement.";
    }
}

header('Location: gestion_usines.php');
exit();
?>

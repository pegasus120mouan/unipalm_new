<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/log_functions.php';


if (!isset($_POST['id_recu']) || empty($_POST['id_recu']) || !is_numeric($_POST['id_recu'])) {
    $_SESSION['error_message'] = "ID du reçu invalide";
    header('Location: recus.php');
    exit;
}

$id_recu = intval($_POST['id_recu']);
writeLog("Début de la suppression du reçu #$id_recu");

try {
    $conn->beginTransaction();
    
    
    // Récupérer les informations du reçu et du document
    $stmt = $conn->prepare("
        SELECT r.*, 
            CASE 
                WHEN r.type_document = 'ticket' THEN t.date_validation_boss
                ELSE b.date_validation_boss
            END as date_validation_boss,
            r.id_transaction
        FROM recus_paiements r
        LEFT JOIN tickets t ON r.type_document = 'ticket' AND r.id_document = t.id_ticket
        LEFT JOIN bordereau b ON r.type_document = 'bordereau' AND r.id_document = b.id_bordereau
        WHERE r.id_recu = ?
    ");
    $stmt->execute([$id_recu]);
    $recu = $stmt->fetch(PDO::FETCH_ASSOC);
   
    if (!$recu) {
        throw new Exception("Reçu non trouvé");
    }
    
   

    // Récupérer le solde actuel
    $stmt = $conn->prepare("SELECT COALESCE(MAX(solde), 0) as solde FROM transactions");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $solde_actuel = floatval($result['solde']);
    
    // Calculer le nouveau solde
    $nouveau_solde = $solde_actuel + $recu['montant_paye'];
    writeLog("Solde actuel: $solde_actuel, Nouveau solde après annulation: $nouveau_solde");
    

    
    // Créer la transaction d'annulation
    $motifs = "Annulation de paiement (Reçu N°" . $recu['numero_recu'] . ")";
    $stmt = $conn->prepare("
        INSERT INTO transactions (
            type_transaction, 
            montant, 
            date_transaction, 
            motifs, 
            id_utilisateur,
            solde
        ) VALUES (
            'approvisionnement',
            :montant,
            NOW(),
            :motifs,
            :id_utilisateur,
            :solde
        )
    ");
    $stmt->bindValue(':montant', $recu['montant_paye'], PDO::PARAM_STR);
    $stmt->bindValue(':motifs', $motifs, PDO::PARAM_STR);
    $stmt->bindValue(':id_utilisateur', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->bindValue(':solde', $nouveau_solde, PDO::PARAM_STR);
    $stmt->execute();
    writeLog("Transaction d'annulation créée, nouveau solde: $nouveau_solde");
    
    // Mettre à jour le montant payé dans le document original
    if ($recu['type_document'] === 'ticket') {
        $stmt = $conn->prepare("
            UPDATE tickets 
            SET montant_payer = COALESCE(montant_payer, 0) - ?,
                montant_reste = montant_paie - (COALESCE(montant_payer, 0) - ?)
            WHERE id_ticket = ?
        ");
        $stmt->execute([$recu['montant_paye'], $recu['montant_paye'], $recu['id_document']]);
        writeLog("Montant du ticket #" . $recu['id_document'] . " mis à jour (-" . $recu['montant_paye'] . " FCFA)");
    } else {
        $stmt = $conn->prepare("
            UPDATE bordereau 
            SET montant_payer = COALESCE(montant_payer, 0) - ?,
                montant_reste = montant_total - (COALESCE(montant_payer, 0) - ?)
            WHERE id_bordereau = ?
        ");
        $stmt->execute([$recu['montant_paye'], $recu['montant_paye'], $recu['id_document']]);
        writeLog("Montant du bordereau #" . $recu['id_document'] . " mis à jour (-" . $recu['montant_paye'] . " FCFA)");
    }
    
    // Supprimer le reçu
    $stmt = $conn->prepare("DELETE FROM recus_paiements WHERE id_recu = ?");
    $stmt->execute([$id_recu]);
    writeLog("Reçu #" . $recu['numero_recu'] . " supprimé");
    
    $conn->commit();
    $_SESSION['success_message'] = "Paiement annulé avec succès. Nouveau solde : " . number_format($nouveau_solde, 0, ',', ' ') . " FCFA";
    
    header('Location: recus.php');
    exit;

} catch (Exception $e) {
     var_dump($e->getMessage()); die();
    $conn->rollBack();
    writeLog("ERREUR: " . $e->getMessage());
    $_SESSION['error_message'] = "Erreur lors de l'annulation : " . $e->getMessage();
    header('Location: recus.php');
    exit;
}

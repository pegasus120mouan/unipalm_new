<?php
//session_start();
require_once '../inc/functions/connexion.php';

header('Content-Type: application/json');

try {
    if (!isset($_POST['id_ticket'])) {
        echo json_encode(['success' => false, 'message' => 'Aucun ticket sélectionné']);
        exit;
    }

    $date_validation = date('Y-m-d H:i:s', time());

   
    $prix_unitaire = isset($_POST['prix_unitaire']) ? floatval($_POST['prix_unitaire']) : null;

    if (isset($_POST['id_ticket'])) {
        // Cas d'un seul ticket
        $ticket_ids = [$_POST['id_ticket']];
    } else {
        // Cas de plusieurs tickets
        $ticket_ids = $_POST['id_tickets'];
    }

    $successCount = 0;
    $failedCount = 0;

    foreach ($ticket_ids as $ticket_id) {
        if (!is_numeric($ticket_id)) {
            $failedCount++;
            continue;
        }

        if ($prix_unitaire !== null) {
            $stmt = $conn->prepare("
                UPDATE tickets 
                SET prix_unitaire = ?, 
                    date_validation_boss = ?, 
                    montant_paie = ? * poids 
                WHERE id_ticket = ? AND (date_validation_boss IS NULL )
            ");
            $result = $stmt->execute([$prix_unitaire, $date_validation, $prix_unitaire, $ticket_id]);
        } else {
            $stmt = $conn->prepare("
                UPDATE tickets 
                SET date_validation_boss = ?
                WHERE id_ticket = ? AND (date_validation_boss IS NULL )
            ");
            $result = $stmt->execute([$date_validation, $ticket_id ]);
        }

        if ($result && $stmt->rowCount() > 0) {
            $successCount++;
        } else {
            $failedCount++;
        }
    }

    if ($successCount > 0) {
        $_SESSION['popup'] = true; // Pour afficher la popup après validation
        echo json_encode(['success' => true, 'message' => "$successCount ticket(s) validé(s) avec succès"]);
    } else {
        echo json_encode(['success' => false, 'message' => "Aucun ticket n'a été validé"]);
    }
} catch (Exception $e) {
    error_log('Erreur valider_tickets.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}

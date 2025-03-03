<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_tickets.php';

header('Content-Type: application/json');

if (!isset($_POST['ticket_id']) || empty($_POST['ticket_id'])) {
    echo json_encode(['success' => false, 'error' => 'ID du ticket non fourni']);
    exit;
}

$ticket_id = intval($_POST['ticket_id']);

try {
    $success = validerTicket($conn, $ticket_id);

    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Erreur lors de la mise à jour du ticket']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

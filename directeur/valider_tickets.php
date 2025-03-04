<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_tickets.php';

header('Content-Type: application/json');

// Récupérer les données JSON envoyées
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['tickets']) || empty($data['tickets'])) {
    echo json_encode(['success' => false, 'error' => 'Aucun ticket sélectionné']);
    exit;
}

try {
    $success = validerTickets($conn, $data['tickets']);

    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Erreur lors de la validation des tickets']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

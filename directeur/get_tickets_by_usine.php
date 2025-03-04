<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_tickets.php';

header('Content-Type: application/json');

// Vérifier si l'ID de l'usine est fourni
if (!isset($_GET['id_usine']) || !is_numeric($_GET['id_usine'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID usine invalide ou non fourni']);
    exit;
}

$id_usine = intval($_GET['id_usine']);

try {
    // Récupérer les tickets en attente pour cette usine
    $tickets = getTicketsAttenteByUsine($conn, $id_usine);
    
    if ($tickets === false) {
        throw new Exception('Erreur lors de la récupération des tickets');
    }
    
    echo json_encode([
        'success' => true,
        'data' => $tickets
    ]);
} catch (Exception $e) {
    error_log("Erreur dans get_tickets_by_usine.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur lors de la récupération des tickets'
    ]);
}

        'data' => $tickets
    ]);
} catch (Exception $e) {
    error_log("Erreur dans get_tickets_by_usine.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur lors de la récupération des tickets'
    ]);
}

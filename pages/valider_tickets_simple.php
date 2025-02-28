<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_tickets.php';

header('Content-Type: application/json');

if (!isset($_POST['ticket_ids']) || !is_array($_POST['ticket_ids'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Liste des tickets invalide ou non fournie'
    ]);
    exit;
}

$ticket_ids = array_map('intval', $_POST['ticket_ids']);

try {
    $placeholders = str_repeat('?,', count($ticket_ids) - 1) . '?';
    $sql = "UPDATE tickets SET date_validation_boss = NOW() WHERE id_ticket IN ($placeholders)";
    
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute($ticket_ids);
    
    if ($result && $stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Tickets validés avec succès'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de la validation des tickets ou tickets déjà validés'
        ]);
    }
} catch (PDOException $e) {
    error_log("Erreur dans valider_tickets_simple.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la validation des tickets'
    ]);
}

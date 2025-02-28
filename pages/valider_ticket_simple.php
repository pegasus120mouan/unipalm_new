<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_tickets.php';

header('Content-Type: application/json');

if (!isset($_POST['id_ticket']) || !is_numeric($_POST['id_ticket'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID ticket invalide ou non fourni'
    ]);
    exit;
}

$id_ticket = intval($_POST['id_ticket']);

try {
    $stmt = $conn->prepare("UPDATE tickets SET date_validation_boss = NOW() WHERE id_ticket = ?");
    $result = $stmt->execute([$id_ticket]);
    
    if ($result && $stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Ticket validé avec succès'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de la validation du ticket ou ticket déjà validé'
        ]);
    }
} catch (PDOException $e) {
    error_log("Erreur dans valider_ticket_simple.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la validation du ticket'
    ]);
}

<?php
require_once '../inc/functions/connexion.php';

if (isset($_POST['numero_ticket'])) {
    $numero_ticket = trim($_POST['numero_ticket']);
    
    try {
        $sql = "SELECT COUNT(*) as count FROM tickets WHERE numero_ticket = :numero_ticket";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':numero_ticket' => $numero_ticket]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        header('Content-Type: application/json');
        echo json_encode([
            'exists' => $result['count'] > 0,
            'numero_ticket' => $numero_ticket
        ]);
        exit;
    } catch(PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode([
            'error' => true,
            'message' => 'Erreur lors de la vérification'
        ]);
        exit;
    }
}

header('Content-Type: application/json');
echo json_encode([
    'error' => true,
    'message' => 'Numéro de ticket non fourni'
]);
exit;

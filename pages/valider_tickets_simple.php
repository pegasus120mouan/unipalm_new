<?php
require_once('../includes/config.php');
require_once('../includes/functions.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticket_ids = $_POST['ticket_ids'] ?? [];
    $redirect_params = $_POST['redirect_params'] ?? [];

    if (empty($ticket_ids)) {
        echo json_encode(['success' => false, 'message' => 'Aucun ticket sélectionné']);
        exit;
    }

    try {
        $success = true;
        $errors = [];

        foreach ($ticket_ids as $id_ticket) {
            $result = validerTicket($conn, $id_ticket);
            if (!$result) {
                $success = false;
                $errors[] = "Erreur lors de la validation du ticket #$id_ticket";
            }
        }

        if ($success) {
            // Construire l'URL de redirection avec les paramètres
            $params = http_build_query($redirect_params);
            $redirect_url = 'tickets_attente.php' . ($params ? '?' . $params : '');
            
            echo json_encode([
                'success' => true, 
                'message' => 'Tous les tickets ont été validés avec succès',
                'redirect_url' => $redirect_url
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Erreurs lors de la validation : ' . implode(', ', $errors)
            ]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}

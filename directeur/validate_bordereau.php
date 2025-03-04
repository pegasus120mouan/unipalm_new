<?php
require_once '../inc/functions/connexion.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_bordereau = $_POST['id_bordereau'] ?? null;
    $action = $_POST['action'] ?? 'validate';

    if ($id_bordereau) {
        try {
            if ($action === 'validate') {
                // Valider le bordereau
                $stmt = $conn->prepare("
                    UPDATE bordereau 
                    SET date_validation_boss = NOW() 
                    WHERE id_bordereau = ?
                ");
                $stmt->execute([$id_bordereau]);

                // Récupérer la date de validation
                $stmt = $conn->prepare("
                    SELECT date_validation_boss 
                    FROM bordereau 
                    WHERE id_bordereau = ?
                ");
                $stmt->execute([$id_bordereau]);
                $date_validation = $stmt->fetchColumn();

                echo json_encode([
                    'success' => true,
                    'date_validation' => $date_validation
                ]);
            } else {
                // Annuler la validation
                $stmt = $conn->prepare("
                    UPDATE bordereau 
                    SET date_validation_boss = NULL 
                    WHERE id_bordereau = ?
                ");
                $stmt->execute([$id_bordereau]);

                echo json_encode([
                    'success' => true
                ]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Une erreur est survenue lors de la validation du bordereau.'
            ]);
        }
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'ID du bordereau manquant.'
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Méthode non autorisée.'
    ]);
}

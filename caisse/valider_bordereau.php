<?php
require_once '../inc/functions/connexion.php';

// Vérifier si la requête est en AJAX
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    
    // Récupérer l'ID du bordereau
    $id_bordereau = isset($_POST['id_bordereau']) ? intval($_POST['id_bordereau']) : 0;
    
    if ($id_bordereau > 0) {
        try {
            // Mettre à jour la date de validation
            $sql = "UPDATE bordereau SET date_validation_boss = NOW() WHERE id_bordereau = :id";
            $stmt = $conn->prepare($sql);
            $result = $stmt->execute(['id' => $id_bordereau]);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Bordereau validé avec succès'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Erreur lors de la validation du bordereau'
                ]);
            }
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Erreur de base de données : ' . $e->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'ID du bordereau invalide'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Requête invalide'
    ]);
}

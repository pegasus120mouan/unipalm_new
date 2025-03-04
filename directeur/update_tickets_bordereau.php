<?php
// Activer l'affichage des erreurs pour le debug
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Définir le chemin complet vers le dossier racine
define('ROOT_PATH', realpath(__DIR__ . '/..'));
require_once ROOT_PATH . '/inc/functions/connexion.php';

header('Content-Type: application/json');

// Debug - Log les données reçues
error_log('POST data: ' . print_r($_POST, true));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['tickets']) && !empty($_POST['bordereau']) && !empty($_POST['prix_unitaire'])) {
    try {
        $tickets = $_POST['tickets'];
        $numero_bordereau = $_POST['bordereau'];
        $prix_unitaire = floatval($_POST['prix_unitaire']);
        
        // Vérifier que le prix unitaire n'est pas 0
        if ($prix_unitaire <= 0) {
            error_log('Erreur: Le prix unitaire doit être supérieur à 0');
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Le prix unitaire doit être supérieur à 0']);
            exit();
        }
        
        // Debug - Log les variables
        error_log('Tickets: ' . print_r($tickets, true));
        error_log('Bordereau: ' . $numero_bordereau);
        error_log('Prix unitaire: ' . $prix_unitaire);
        error_log('Chemin du fichier: ' . __FILE__);
        error_log('Dossier racine: ' . ROOT_PATH);
        
        $conn->beginTransaction();
        
        foreach ($tickets as $id_ticket) {
            $stmt = $conn->prepare("UPDATE tickets SET numero_bordereau = ?, prix_unitaire = ? WHERE id_ticket = ?");
            $stmt->execute([$numero_bordereau, $prix_unitaire, $id_ticket]);
            
            // Debug - Log chaque mise à jour
            error_log("Mise à jour du ticket {$id_ticket} avec bordereau {$numero_bordereau} et prix unitaire {$prix_unitaire}");
        }
        
        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        if (isset($conn)) {
            $conn->rollBack();
        }
        error_log('Erreur: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    error_log('Données manquantes dans la requête POST');
    error_log('POST: ' . print_r($_POST, true));
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Données manquantes']);
}

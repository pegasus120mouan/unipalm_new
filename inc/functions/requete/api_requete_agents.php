<?php
require_once dirname(__FILE__) . '/../../functions/connexion.php';

// Activer l'affichage des erreurs (en développement uniquement)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Définir les en-têtes de réponse
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

try {
    // Vérifier la connexion
    if (!$conn) {
        throw new Exception('Échec de la connexion à la base de données.');
    }

    // Préparer la requête pour récupérer les agents
    $stmt = $conn->prepare("SELECT id_agent, CONCAT(nom, ' ', prenom) as nom_complet_agent FROM agents ORDER BY nom ASC");
    $stmt->execute();

    $agents = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Vérifier si des données ont été trouvées
    if (empty($agents)) {
        echo json_encode([
            'success' => false,
            'message' => 'Aucun agent trouvé.',
            'data' => []
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'Liste des agents récupérée avec succès.',
            'data' => $agents
        ]);
    }

} catch (PDOException $e) {
    // Gestion des erreurs SQL
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur SQL : ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    // Gestion des erreurs générales
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur : ' . $e->getMessage()
    ]);
}

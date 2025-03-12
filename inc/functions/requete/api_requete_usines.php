<?php
require_once dirname(__FILE__) . '/../../functions/connexion.php';


// Activer l'affichage des erreurs (en développement uniquement)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Définir les en-têtes de réponse
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); // Autorise les requêtes cross-origin

try {
    // Vérifier la connexion
    if (!$conn) {
        throw new Exception('Échec de la connexion à la base de données.');
    }

    // Préparer la requête pour récupérer les usines
    $stmt = $conn->prepare("SELECT id_usine, nom_usine FROM usines ORDER BY nom_usine ASC");
    $stmt->execute();

    $usines = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Vérifier si des données ont été trouvées
    if (empty($usines)) {
        echo json_encode([
            'success' => false,
            'message' => 'Aucune usine trouvée.',
            'data' => []
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'Liste des usines récupérée avec succès.',
            'data' => $usines
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
        'error' => $e->getMessage()
    ]);
}
?>

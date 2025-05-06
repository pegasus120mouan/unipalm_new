<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_tickets.php';

header('Content-Type: application/json');

// Vérifier si la requête est en POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les paramètres
    $agent_id = isset($_POST['agent_id']) ? intval($_POST['agent_id']) : null;
    $usine_id = isset($_POST['usine_id']) ? intval($_POST['usine_id']) : null;
    $date_debut = isset($_POST['date_debut']) ? $_POST['date_debut'] : null;
    $date_fin = isset($_POST['date_fin']) ? $_POST['date_fin'] : null;

    // Valider les paramètres
    if (!$agent_id || !$usine_id || !$date_debut || !$date_fin) {
        echo json_encode([
            'success' => false,
            'message' => 'Paramètres manquants'
        ]);
        exit;
    }

    try {
        // Requête pour récupérer les tickets
        $sql = "SELECT t.*, 
                       a.nom as nom_agent, a.prenom as prenom_agent,
                       u.nom_usine,
                       CONCAT(a.nom, ' ', a.prenom) as agent_nom_complet
                FROM tickets t
                LEFT JOIN agents a ON t.id_agent = a.id_agent
                LEFT JOIN usines u ON t.id_usine = u.id_usine
                WHERE t.id_agent = :agent_id 
                AND t.id_usine = :usine_id
                AND DATE(t.date_ticket) BETWEEN :date_debut AND :date_fin
                
                AND t.date_validation_boss IS NULL
                ORDER BY t.date_ticket DESC";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':agent_id' => $agent_id,
            ':usine_id' => $usine_id,
            ':date_debut' => $date_debut,
            ':date_fin' => $date_fin
        ]);

        $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Retourner les résultats
        echo json_encode([
            'success' => true,
            'tickets' => $tickets
        ]);

    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de la recherche des tickets: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée'
    ]);
}

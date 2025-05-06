<?php
require_once 'inc/functions/connexion.php';

// Vérifier si l'utilisateur est connecté
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit;
}

// Récupérer les paramètres
$agent_id = $_POST['agent_id'] ?? null;
$usine_id = $_POST['usine_id'] ?? null;
$date_debut = $_POST['date_debut'] ?? null;
$date_fin = $_POST['date_fin'] ?? null;

// Valider les paramètres
if (!$agent_id || !$usine_id || !$date_debut || !$date_fin) {
    echo json_encode(['success' => false, 'message' => 'Paramètres manquants']);
    exit;
}

try {
    // Préparer la requête
    $sql = "SELECT 
                t.id_ticket,
                t.numero_ticket,
                DATE_FORMAT(t.date_ticket, '%d/%m/%Y') as date_ticket,
                a.nom as nom_agent,
                a.prenom as prenom_agent,
                u.nom_usine,
                t.montant,
                CASE 
                    WHEN t.date_validation IS NOT NULL THEN 'Validé'
                    ELSE 'En attente'
                END as statut
            FROM tickets t
            INNER JOIN agents a ON t.id_agent = a.id_agent
            INNER JOIN usines u ON t.id_usine = u.id_usine
            WHERE t.id_agent = :agent_id
            AND t.id_usine = :usine_id
            AND DATE(t.date_ticket) BETWEEN :date_debut AND :date_fin
            AND t.date_validation IS NULL
            ORDER BY t.date_ticket DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':agent_id' => $agent_id,
        ':usine_id' => $usine_id,
        ':date_debut' => $date_debut,
        ':date_fin' => $date_fin
    ]);

    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'tickets' => $tickets
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la recherche : ' . $e->getMessage()
    ]);
}

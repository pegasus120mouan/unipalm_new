<?php
require_once '../../functions/connexion.php';
require_once '../../functions/requete/requete_agents.php';

header('Content-Type: application/json');

if (!isset($_GET['id_chef'])) {
    echo json_encode(['error' => 'ID du chef d\'équipe manquant']);
    exit;
}

$id_chef = intval($_GET['id_chef']);

// Si id_chef est 0 ou -1, retourner tous les agents
if ($id_chef <= 0) {
    $agents = getAllAgentsForSelect($conn);
} else {
    $agents = getAgentsByChef($conn, $id_chef);
}

echo json_encode($agents);
?>

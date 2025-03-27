<?php
require_once '../../functions/connexion.php';
require_once '../../functions/requete/requete_usines.php';

header('Content-Type: application/json');

if (!isset($_GET['id_chef'])) {
    echo json_encode(['error' => 'ID du chef d\'équipe manquant']);
    exit;
}

$id_chef = intval($_GET['id_chef']);

// Récupérer les usines qui ont des tickets pour un chef d'équipe donné
$query = "SELECT DISTINCT u.id_usine, u.nom_usine
          FROM usines u
          INNER JOIN tickets t ON u.id_usine = t.id_usine
          INNER JOIN agents a ON t.id_agent = a.id_agent
          WHERE a.id_chef = :id_chef
          ORDER BY u.nom_usine";

$stmt = $conn->prepare($query);
$stmt->execute(['id_chef' => $id_chef]);
$usines = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($usines);
?>

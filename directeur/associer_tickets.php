<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_tickets.php';

header('Content-Type: application/json');

// Vérifier si la connexion est établie
$conn = getConnexion();
if (!$conn) {
    echo json_encode(['success' => false, 'error' => 'Erreur de connexion à la base de données']);
    exit;
}

// Récupérer le numéro du bordereau et les tickets
$numero_bordereau = isset($_POST['bordereau']) ? $_POST['bordereau'] : '';
$selected_tickets = isset($_POST['tickets']) ? (array)$_POST['tickets'] : [];

// Vérifier si les données nécessaires sont présentes
if (empty($numero_bordereau) || empty($selected_tickets)) {
    echo json_encode(['success' => false, 'error' => 'Données manquantes']);
    exit;
}

try {
    $conn->beginTransaction();
    
    // Mise à jour des tickets
    $stmt = $conn->prepare("UPDATE tickets SET numero_bordereau = :numero_bordereau WHERE id_ticket = :id_ticket");
    
    foreach ($selected_tickets as $id_ticket) {
        $stmt->execute([
            ':numero_bordereau' => $numero_bordereau,
            ':id_ticket' => $id_ticket
        ]);
    }

    // Mettre à jour le montant total et le poids total du bordereau
    $sql_update_bordereau = "UPDATE bordereau b 
                           SET b.montant_total = (
                               SELECT CAST(SUM(t.prix_unitaire * t.poids) AS DECIMAL(20,2))
                               FROM tickets t 
                               WHERE t.numero_bordereau = b.numero_bordereau
                           ),
                           b.poids_total = (
                               SELECT CAST(SUM(t.poids) AS DECIMAL(10,2))
                               FROM tickets t 
                               WHERE t.numero_bordereau = b.numero_bordereau
                           )
                           WHERE b.numero_bordereau = :numero_bordereau";
    
    $stmt = $conn->prepare($sql_update_bordereau);
    $stmt->execute([':numero_bordereau' => $numero_bordereau]);
    
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Tickets associés avec succès']);
    exit;
} catch (Exception $e) {
    if ($conn) {
        $conn->rollBack();
    }
    echo json_encode(['success' => false, 'error' => 'Erreur lors de la mise à jour : ' . $e->getMessage()]);
    exit;
}
?>

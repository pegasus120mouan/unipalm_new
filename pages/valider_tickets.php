<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_tickets.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$is_mass_validation = isset($_POST['is_mass_validation']) ? $_POST['is_mass_validation'] : false;
$ticket_ids = [];
$prix_unitaire = 1000; // Valeur par défaut pour la validation en masse

if ($is_mass_validation) {
    // Validation en masse
    if (!isset($_POST['ticket_ids']) || empty($_POST['ticket_ids'])) {
        echo json_encode(['success' => false, 'message' => 'Aucun ticket sélectionné']);
        exit;
    }
    $ticket_ids = is_array($_POST['ticket_ids']) ? $_POST['ticket_ids'] : [$_POST['ticket_ids']];
} else {
    // Validation individuelle
    if (!isset($_POST['ticket_id']) || !isset($_POST['prix_unitaire'])) {
        echo json_encode(['success' => false, 'message' => 'Données manquantes pour la validation']);
        exit;
    }
    $ticket_ids = [$_POST['ticket_id']];
    $prix_unitaire = $_POST['prix_unitaire'];
}

try {
    $conn->beginTransaction();

    $sql = "UPDATE tickets 
            SET date_validation_boss = NOW(),
                prix_unitaire = :prix_unitaire,
                updated_at = NOW()
            WHERE id_ticket = :ticket_id 
            AND date_validation_boss IS NULL";

    $stmt = $conn->prepare($sql);
    $validated = 0;

    foreach ($ticket_ids as $ticket_id) {
        $result = $stmt->execute([
            ':prix_unitaire' => $prix_unitaire,
            ':ticket_id' => $ticket_id
        ]);

        if ($result && $stmt->rowCount() > 0) {
            $validated++;
        }
    }

    if ($validated > 0) {
        $conn->commit();
        echo json_encode([
            'success' => true,
            'message' => $validated . ' ticket(s) validé(s) avec succès'
        ]);
    } else {
        $conn->rollBack();
        echo json_encode([
            'success' => false,
            'message' => 'Aucun ticket n\'a pu être validé'
        ]);
    }

} catch (PDOException $e) {
    $conn->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la validation des tickets: ' . $e->getMessage()
    ]);
}

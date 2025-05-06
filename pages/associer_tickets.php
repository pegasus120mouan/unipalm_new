<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_tickets.php';

session_start();

// Vérifier si la connexion est établie
$conn = getConnexion();
if (!$conn) {
    $_SESSION['error'] = 'Erreur de connexion à la base de données';
    header('Location: bordereaux.php');
    exit;
}

// Récupérer les données du formulaire
$id_agent = $_POST['id_agent'] ?? '';
$numero_bordereau = $_POST['numero_bordereau'] ?? '';
$selected_tickets = $_POST['tickets'] ?? [];

// Vérifier si les données nécessaires sont présentes
if (empty($numero_bordereau) || empty($selected_tickets) || empty($id_agent)) {
    $_SESSION['error'] = 'Données manquantes pour l\'association des tickets';
    header('Location: bordereaux.php');
    exit;
}

try {
    $conn->beginTransaction();
    
    // Mise à jour des tickets
    $stmt = $conn->prepare("UPDATE tickets 
                           SET numero_bordereau = :numero_bordereau 
                           WHERE id_ticket = :id_ticket 
                           AND id_agent = :id_agent");
    
    foreach ($selected_tickets as $id_ticket) {
        $stmt->execute([
            ':numero_bordereau' => $numero_bordereau,
            ':id_ticket' => $id_ticket,
            ':id_agent' => $id_agent
        ]);
    }

    // Mettre à jour le montant total et le poids total du bordereau
    $sql_update_bordereau = "UPDATE bordereau b 
                           SET b.montant_total = (
                               SELECT COALESCE(CAST(SUM(t.prix_unitaire * t.poids) AS DECIMAL(20,2)), 0)
                               FROM tickets t 
                               WHERE t.numero_bordereau = b.numero_bordereau
                           ),
                           b.poids_total = (
                               SELECT COALESCE(CAST(SUM(t.poids) AS DECIMAL(10,2)), 0)
                               FROM tickets t 
                               WHERE t.numero_bordereau = b.numero_bordereau
                           )
                           WHERE b.numero_bordereau = :numero_bordereau";
    
    $stmt = $conn->prepare($sql_update_bordereau);
    $stmt->execute([':numero_bordereau' => $numero_bordereau]);
    
    $conn->commit();
    $_SESSION['success'] = count($selected_tickets) . ' ticket(s) associé(s) avec succès au bordereau ' . $numero_bordereau;
} catch (Exception $e) {
    $conn->rollBack();
    error_log("Erreur dans associer_tickets.php: " . $e->getMessage());
    $_SESSION['error'] = 'Erreur lors de l\'association des tickets : ' . $e->getMessage();
}

header('Location: bordereaux.php');
exit;
?>

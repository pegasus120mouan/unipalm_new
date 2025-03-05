<?php
require_once('../inc/functions/connexion.php');
require_once('../inc/functions/requete/requete_tickets.php');

// S'assurer qu'aucune sortie n'a été envoyée avant
ob_clean();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id_ticket = isset($_POST['id_ticket']) ? $_POST['id_ticket'] : null;
        $prix_unitaire = isset($_POST['prix_unitaire']) ? $_POST['prix_unitaire'] : null;

        if (empty($id_ticket) || empty($prix_unitaire)) {
            throw new Exception('Paramètres manquants');
        }

        // Mettre à jour le ticket avec le nouveau prix unitaire
        $date_validation = date('Y-m-d H:i:s');
        
        // Première requête pour mettre à jour le prix unitaire et la date de validation
        $stmt = $conn->prepare("UPDATE tickets SET prix_unitaire = ?, date_validation_boss = ? WHERE id_ticket = ?");
        if (!$stmt->execute([$prix_unitaire, $date_validation, $id_ticket])) {
            throw new Exception('Erreur lors de la mise à jour du prix unitaire');
        }
        
        // Deuxième requête pour calculer et mettre à jour le montant_paie
        $stmt = $conn->prepare("UPDATE tickets SET montant_paie = prix_unitaire * poids WHERE id_ticket = ?");
        if (!$stmt->execute([$id_ticket])) {
            throw new Exception('Erreur lors de la mise à jour du montant');
        }
        
        // Vérifier que la mise à jour a bien été effectuée
        $stmt = $conn->prepare("SELECT prix_unitaire, date_validation_boss, montant_paie FROM tickets WHERE id_ticket = ?");
        if (!$stmt->execute([$id_ticket])) {
            throw new Exception('Erreur lors de la vérification des données');
        }
        
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$ticket) {
            throw new Exception('Ticket non trouvé après mise à jour');
        }

        $response = [
            'success' => true,
            'message' => 'Ticket validé avec succès',
            'data' => [
                'prix_unitaire' => $ticket['prix_unitaire'],
                'date_validation' => $ticket['date_validation_boss'],
                'montant_paie' => $ticket['montant_paie']
            ]
        ];

        die(json_encode($response));

    } catch (Exception $e) {
        error_log("Erreur dans valider_ticket_simple.php: " . $e->getMessage());
        die(json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]));
    }
} else {
    die(json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée'
    ]));
}

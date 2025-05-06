<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/update_tickets_prix_unitaire.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_prix_unitaire') {
    try {
        $conn->beginTransaction();

        // Validation des données
        if (!isset($_POST['prix_unitaire']) || empty($_POST['prix_unitaire'])) {
            throw new Exception("Le prix unitaire est requis");
        }
        if (!isset($_POST['id_ticket']) || empty($_POST['id_ticket'])) {
            throw new Exception("ID du ticket manquant");
        }

        $prix_unitaire = floatval($_POST['prix_unitaire']);
        if ($prix_unitaire <= 0) {
            throw new Exception("Le prix unitaire doit être supérieur à 0");
        }

        // Récupérer les informations du ticket
        $stmt = $conn->prepare("
            SELECT t.*, 
                   COALESCE(t.montant_payer, 0) as montant_deja_paye,
                   COALESCE(t.montant_paie, 0) as montant_paie_actuel,
                   COALESCE(t.prix_unitaire, 0) as ancien_prix_unitaire,
                   t.usine_id,
                   t.periode_id
            FROM tickets t 
            WHERE t.id_ticket = ?
        ");
        $stmt->execute([$_POST['id_ticket']]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$ticket) {
            throw new Exception("Ticket non trouvé");
        }

        // Calculer le nouveau montant_paie
        $poids = floatval($ticket['poids']);
        $montant_paie = $prix_unitaire * $poids;
        $montant_reste = $montant_paie - floatval($ticket['montant_deja_paye']);

        if ($montant_reste < 0) {
            throw new Exception("Le nouveau prix unitaire créerait un montant restant négatif. Opération impossible car " . 
                              number_format($montant_paie, 0, ',', ' ') . " FCFA (nouveau montant) < " . 
                              number_format($ticket['montant_deja_paye'], 0, ',', ' ') . " FCFA (montant déjà payé)");
        }

        // Mettre à jour le ticket
        $stmt = $conn->prepare("
            UPDATE tickets 
            SET prix_unitaire = ?,
                montant_paie = ?,
                montant_reste = ?,
                date_modification = NOW()
            WHERE id_ticket = ?
        ");
        $stmt->execute([
            $prix_unitaire,
            $montant_paie,
            $montant_reste,
            $_POST['id_ticket']
        ]);

        writeLog("Mise à jour du prix unitaire pour le ticket #" . $_POST['id_ticket'] . 
                ". Ancien prix: " . $ticket['ancien_prix_unitaire'] .
                ", Nouveau prix: " . $prix_unitaire . 
                ", Ancien montant: " . $ticket['montant_paie_actuel'] .
                ", Nouveau montant: " . $montant_paie . 
                ", Montant déjà payé: " . $ticket['montant_deja_paye'] .
                ", Nouveau montant restant: " . $montant_reste);

        $conn->commit();

        // Mettre à jour automatiquement les autres tickets de la même usine et période sans prix unitaire
        $updateResult = updateTicketsPrixUnitaire($ticket['usine_id'], $ticket['periode_id'], $prix_unitaire);
        
        $_SESSION['success_message'] = "Prix unitaire mis à jour avec succès : " . 
                                     number_format($ticket['ancien_prix_unitaire'], 0, ',', ' ') . " → " . 
                                     number_format($prix_unitaire, 0, ',', ' ') . " FCFA. " .
                                     "Nouveau montant à payer : " . number_format($montant_paie, 0, ',', ' ') . " FCFA";
        
        if ($updateResult['updated_count'] > 0) {
            $_SESSION['success_message'] .= "\n" . $updateResult['message'];
        }

    } catch (Exception $e) {
        $conn->rollBack();
        writeLog("Erreur lors de la mise à jour du prix unitaire: " . $e->getMessage());
        $_SESSION['error_message'] = "Erreur lors de la mise à jour : " . $e->getMessage();
    }
    
    header("Location: tickets_modifications.php");
    exit();
} else {
    $_SESSION['error_message'] = "Méthode non autorisée";
    header("Location: tickets_modifications.php");
    exit();
}
?>
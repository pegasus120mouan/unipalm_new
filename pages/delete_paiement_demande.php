<?php
require_once '../inc/functions/connexion.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (isset($_POST['id_recu']) && !empty($_POST['id_recu'])) {
    try {
        $conn->beginTransaction();

        // Récupérer les informations du reçu et de la demande
        $stmt = $conn->prepare("
            SELECT r.*, d.montant_payer, d.id_demande
            FROM recus_demandes r
            JOIN demande_sortie d ON r.demande_id = d.id_demande
            WHERE r.id = ?
        ");
        $stmt->execute([$_POST['id_recu']]);
        $recu = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$recu) {
            throw new Exception("Reçu non trouvé");
        }

        // Récupérer le solde actuel
        $stmt = $conn->prepare("SELECT COALESCE(MAX(solde), 0) as solde FROM transactions");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $solde_actuel = floatval($result['solde']);

        // Ajouter le montant au solde (annulation du paiement)
        $nouveau_solde = $solde_actuel + $recu['montant'];

        // Créer une transaction d'annulation
        $stmt = $conn->prepare("
            INSERT INTO transactions (
                type_transaction,
                montant,
                date_transaction,
                motifs,
                id_utilisateur,
                solde
            ) VALUES (
                'annulation',
                :montant,
                NOW(),
                :motifs,
                :id_utilisateur,
                :solde
            )
        ");

        $motifs = "Annulation du paiement de la demande " . $recu['numero_demande'];
        
        $stmt->execute([
            ':montant' => $recu['montant'],
            ':motifs' => $motifs,
            ':id_utilisateur' => $_SESSION['user_id'],
            ':solde' => $nouveau_solde
        ]);

        // Mettre à jour le montant payé de la demande
        $nouveau_montant_paye = $recu['montant_payer'] - $recu['montant'];
        $stmt = $conn->prepare("
            UPDATE demande_sortie 
            SET 
                montant_payer = :montant_payer,
                statut = 'approuve'
            WHERE id_demande = :id_demande
        ");
        
        $stmt->execute([
            ':montant_payer' => $nouveau_montant_paye,
            ':id_demande' => $recu['id_demande']
        ]);

        // Supprimer le reçu
        $stmt = $conn->prepare("DELETE FROM recus_demandes WHERE id = ?");
        $stmt->execute([$_POST['id_recu']]);

        $conn->commit();
        $_SESSION['success'] = "Le paiement a été supprimé avec succès";

    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        $_SESSION['error'] = $e->getMessage();
    }
}

header('Location: recus_demandes.php');
exit();

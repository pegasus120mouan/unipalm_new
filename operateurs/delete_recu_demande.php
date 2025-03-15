<?php
require_once '../inc/functions/connexion.php';
//session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Vous devez être connecté pour effectuer cette action.";
    header('Location: recus_demandes.php');
    exit();
}

// Vérifier si l'ID du reçu est fourni
if (!isset($_POST['id_recu']) || empty($_POST['id_recu'])) {
    $_SESSION['error_message'] = "ID du reçu manquant.";
    header('Location: recus_demandes.php');
    exit();
}

$id_recu = intval($_POST['id_recu']);

try {
    // Démarrer la transaction
    $conn->beginTransaction();

    // Vérifier si le reçu existe et n'est pas validé
    $stmt = $conn->prepare("
        SELECT r.*, d.montant_payer, d.id_demande
        FROM recus_demandes r
        JOIN demande_sortie d ON r.demande_id = d.id_demande
        WHERE r.id = ?
        AND (r.date_validation_boss IS NULL)
    ");
    $stmt->execute([$id_recu]);
    $recu = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$recu) {
        throw new Exception("Reçu non trouvé ou déjà validé.");
    }

    // Mettre à jour le montant payé de la demande
    $nouveau_montant_paye = $recu['montant_payer'] - $recu['montant'];
    
    $stmt = $conn->prepare("
        UPDATE demande_sortie 
        SET montant_payer = ?,
            statut = CASE 
                WHEN ? <= 0 THEN 'approuve'
                ELSE statut
            END
        WHERE id_demande = ?
    ");
    $stmt->execute([$nouveau_montant_paye, $nouveau_montant_paye, $recu['id_demande']]);

    // Journaliser l'action
    $stmt = $conn->prepare("
        INSERT INTO journal_actions (
            action_type,
            description,
            id_utilisateur,
            date_action,
            donnees_supplementaires
        ) VALUES (
            'suppression_recu',
            :description,
            :id_utilisateur,
            NOW(),
            :donnees
        )
    ");

    $description = "Suppression du reçu de la demande " . $recu['numero_demande'];
    $donnees = json_encode([
        'id_recu' => $recu['id'],
        'numero_demande' => $recu['numero_demande'],
        'montant' => $recu['montant'],
        'date_paiement' => $recu['date_paiement'],
        'source_paiement' => $recu['source_paiement']
    ]);

    $stmt->bindValue(':description', $description, PDO::PARAM_STR);
    $stmt->bindValue(':id_utilisateur', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->bindValue(':donnees', $donnees, PDO::PARAM_STR);
    $stmt->execute();

    // Supprimer le reçu
    $stmt = $conn->prepare("
        DELETE FROM recus_demandes 
        WHERE id = ? 
        AND date_validation_boss IS NULL
    ");
    $rows_affected = $stmt->execute([$id_recu]);

    if ($rows_affected === 0) {
        throw new Exception("Le reçu ne peut pas être supprimé car il a déjà été validé.");
    }

    // Valider la transaction
    $conn->commit();
    $_SESSION['success_message'] = "Le reçu a été supprimé avec succès.";

} catch (Exception $e) {
    // Annuler la transaction en cas d'erreur
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    $_SESSION['error_message'] = "Erreur lors de la suppression du reçu : " . $e->getMessage();
}

// Rediriger vers la liste des reçus
header('Location: recus_demandes.php');
exit();

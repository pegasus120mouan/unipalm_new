<?php
require_once '../inc/functions/connexion.php';


if (isset($_POST['save_paiement_demande']) && $_POST['save_paiement_demande'] == 1) {
    try {
        if (!isset($_POST['id_demande']) || empty($_POST['id_demande'])) {
            throw new Exception("ID de la demande manquant");
        }

        if (!isset($_POST['montant']) || empty($_POST['montant'])) {
            throw new Exception("Le montant est requis");
        }

        if (!isset($_POST['source_paiement']) || empty($_POST['source_paiement'])) {
            throw new Exception("La source de paiement est requise");
        }

        $conn->beginTransaction();

        // Récupérer les informations de la demande
        $stmt = $conn->prepare("
            SELECT 
                d.montant,
                d.statut,
                d.numero_demande,
                COALESCE(d.montant_payer, 0) as montant_paye,
                d.montant - COALESCE(d.montant_payer, 0) as montant_reste
            FROM demande_sortie d
            WHERE d.id_demande = ?
        ");
        $stmt->execute([$_POST['id_demande']]);
        $demande = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$demande) {
            throw new Exception("Demande non trouvée");
        }

        if ($demande['statut'] !== 'approuve' && $demande['statut'] !== 'paye') {
            throw new Exception("Cette demande n'est pas approuvée");
        }

        // Nettoyer et valider le montant
        $montant = preg_replace('/[^0-9]/', '', $_POST['montant']);
        $montant = floatval($montant);

        if ($montant <= 0) {
            throw new Exception("Le montant doit être supérieur à 0");
        }

        if ($montant > $demande['montant_reste']) {
            throw new Exception("Le montant ne peut pas dépasser le reste à payer (" . number_format($demande['montant_reste'], 0, ',', ' ') . " FCFA)");
        }

        // Récupérer le solde actuel
        $stmt = $conn->prepare("SELECT COALESCE(MAX(solde), 0) as solde FROM transactions");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $solde_actuel = floatval($result['solde']);

        // Vérifier si le solde est suffisant
        if ($solde_actuel < $montant) {
            throw new Exception("Solde insuffisant dans la caisse");
        }

        // Récupérer les informations du caissier
        $stmt = $conn->prepare("SELECT CONCAT(nom, ' ', prenoms) as nom_caissier FROM utilisateurs WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $caissier = $stmt->fetch(PDO::FETCH_ASSOC);

        // Calculer le nouveau solde
        $nouveau_solde = $solde_actuel - $montant;

        // Créer la transaction
        $stmt = $conn->prepare("
            INSERT INTO transactions (
                type_transaction,
                montant,
                date_transaction,
                motifs,
                id_utilisateur,
                solde
            ) VALUES (
                'paiement',
                :montant,
                NOW(),
                :motifs,
                :id_utilisateur,
                :solde
            )
        ");

        // Préparer le motif détaillé
        $motifs = "Paiement de la demande " . $demande['numero_demande'] . " - Par " . $caissier['nom_caissier'];

        $stmt->bindValue(':montant', $montant, PDO::PARAM_STR);
        $stmt->bindValue(':motifs', $motifs, PDO::PARAM_STR);
        $stmt->bindValue(':id_utilisateur', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->bindValue(':solde', $nouveau_solde, PDO::PARAM_STR);
        $stmt->execute();

        // Mettre à jour la demande
        $nouveau_montant_paye = $demande['montant_paye'] + $montant;
        $nouveau_montant_reste = $demande['montant'] - $nouveau_montant_paye;
        $nouveau_statut = $nouveau_montant_reste <= 0 ? 'paye' : 'approuve';

        $stmt = $conn->prepare("
            UPDATE demande_sortie 
            SET 
                montant_payer = :montant_payer,
                montant_reste = :montant_reste,
                statut = :statut,
                date_paiement = NOW(),
                paye_par = :paye_par
            WHERE id_demande = :id_demande
        ");

        $stmt->bindValue(':montant_payer', $nouveau_montant_paye, PDO::PARAM_STR);
        $stmt->bindValue(':montant_reste', $nouveau_montant_reste, PDO::PARAM_STR);
        $stmt->bindValue(':statut', $nouveau_statut, PDO::PARAM_STR);
        $stmt->bindValue(':paye_par', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->bindValue(':id_demande', $_POST['id_demande'], PDO::PARAM_INT);
        $stmt->execute();

        // Créer le reçu
        // Générer un numéro de reçu unique
        $numero_recu = 'DEM-' . date('Ymd') . sprintf("%04d", rand(1, 9999));
        
        $stmt = $conn->prepare("
            INSERT INTO recus_demandes (
                numero_recu,
                numero_demande,
                demande_id,
                montant,
                date_paiement,
                caissier_id,
                source_paiement
            ) VALUES (
                :numero_recu,
                :numero_demande,
                :demande_id,
                :montant,
                NOW(),
                :caissier_id,
                :source_paiement
            )
        ");

        $stmt->bindValue(':numero_recu', $numero_recu, PDO::PARAM_STR);
        $stmt->bindValue(':numero_demande', $demande['numero_demande'], PDO::PARAM_STR);
        $stmt->bindValue(':demande_id', $_POST['id_demande'], PDO::PARAM_INT);
        $stmt->bindValue(':montant', $montant, PDO::PARAM_STR);
        $stmt->bindValue(':caissier_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->bindValue(':source_paiement', $_POST['source_paiement'], PDO::PARAM_STR);
        $stmt->execute();

        $conn->commit();
        $_SESSION['success_message'] = "Paiement effectué avec succès";
        header('Location: recu_demande_pdf.php?id=' . $conn->lastInsertId());
        exit();

    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log("Erreur lors du paiement : " . $e->getMessage());
        $_SESSION['error_message'] = $e->getMessage();
        header('Location: paiements_demande.php');
        exit();
    }
} else {
    header('Location: paiements_demande.php');
    exit();
}

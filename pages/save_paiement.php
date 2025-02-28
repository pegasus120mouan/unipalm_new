<?php
require_once '../inc/functions/connexion.php';

if (isset($_POST['save_paiement'])) {
    $type_transaction = $_POST['type_transaction'];
    $montant = $_POST['montant'];
    $date_transaction = date("Y-m-d H:i:s");
    $id_utilisateur = $_SESSION['user_id'];
    
    // Déterminer si c'est un paiement de ticket ou de bordereau
    $is_bordereau = isset($_POST['id_bordereau']);
    
    if ($is_bordereau) {
        $id_bordereau = $_POST['id_bordereau'];
        $numero_bordereau = $_POST['numero_bordereau'];
        $motifs = "Paiement du bordereau " . $numero_bordereau;
    } else {
        $id_ticket = $_POST['id_ticket'];
        $numero_ticket = $_POST['numero_ticket'];
        $motifs = "Paiement du ticket " . $numero_ticket;
    }

    try {
        // Démarrer une transaction
        $conn->beginTransaction();

        // 1. Insertion dans la table transactions
        $stmt = $conn->prepare("INSERT INTO transactions (type_transaction, montant, date_transaction, id_utilisateur, motifs) 
                              VALUES (:type_transaction, :montant, :date_transaction, :id_utilisateur, :motifs)");
        $stmt->bindParam(':type_transaction', $type_transaction);
        $stmt->bindParam(':montant', $montant);
        $stmt->bindParam(':date_transaction', $date_transaction);
        $stmt->bindParam(':id_utilisateur', $id_utilisateur);
        $stmt->bindParam(':motifs', $motifs);

        if ($stmt->execute()) {
            if ($is_bordereau) {
                // Mise à jour du bordereau
                $stmtTotal = $conn->prepare("SELECT COALESCE(montant_payer, 0) as total_paye, montant_total 
                                           FROM bordereau 
                                           WHERE id_bordereau = :id_bordereau");
                $stmtTotal->bindParam(':id_bordereau', $id_bordereau);
                $stmtTotal->execute();
                $result = $stmtTotal->fetch(PDO::FETCH_ASSOC);
                
                // Calculer le nouveau total payé et le reste
                $nouveau_total_paye = $result['total_paye'] + $montant;
                $montant_reste = $result['montant_total'] - $nouveau_total_paye;
                
                // Déterminer le statut
                $statut_bordereau = $montant_reste <= 0 ? 'soldé' : 'non soldé';
                
                // Mise à jour du bordereau
                $stmtUpdate = $conn->prepare("UPDATE bordereau 
                                            SET montant_payer = :nouveau_total_paye,
                                                montant_reste = :montant_reste,
                                                date_paie = :date_transaction,
                                                statut_bordereau = :statut_bordereau
                                            WHERE id_bordereau = :id_bordereau");
                
                $stmtUpdate->bindParam(':nouveau_total_paye', $nouveau_total_paye);
                $stmtUpdate->bindParam(':montant_reste', $montant_reste);
                $stmtUpdate->bindParam(':date_transaction', $date_transaction);
                $stmtUpdate->bindParam(':statut_bordereau', $statut_bordereau);
                $stmtUpdate->bindParam(':id_bordereau', $id_bordereau);
                
                if (!$stmtUpdate->execute()) {
                    throw new PDOException("Erreur lors de la mise à jour du bordereau");
                }
            } else {
                // Mise à jour du ticket
                $stmtTotal = $conn->prepare("SELECT COALESCE(SUM(montant_payer), 0) as total_paye, montant_paie 
                                           FROM tickets 
                                           WHERE id_ticket = :id_ticket");
                $stmtTotal->bindParam(':id_ticket', $id_ticket);
                $stmtTotal->execute();
                $result = $stmtTotal->fetch(PDO::FETCH_ASSOC);
                
                // Calculer le nouveau total payé et le reste
                $nouveau_total_paye = $result['total_paye'] + $montant;
                $montant_reste = $result['montant_paie'] - $nouveau_total_paye;
                
                // Mise à jour du ticket
                $stmtUpdate = $conn->prepare("UPDATE tickets 
                                            SET montant_payer = :nouveau_total_paye,
                                                montant_reste = :montant_reste,
                                                date_paie = :date_transaction 
                                            WHERE id_ticket = :id_ticket");
                
                $stmtUpdate->bindParam(':nouveau_total_paye', $nouveau_total_paye);
                $stmtUpdate->bindParam(':montant_reste', $montant_reste);
                $stmtUpdate->bindParam(':date_transaction', $date_transaction);
                $stmtUpdate->bindParam(':id_ticket', $id_ticket);
                
                if (!$stmtUpdate->execute()) {
                    throw new PDOException("Erreur lors de la mise à jour du ticket");
                }
            }
            
            // Validation de la transaction
            $conn->commit();
            $_SESSION['popup'] = true;
            $filter = isset($_POST['filter']) ? '?filter=' . $_POST['filter'] : '';
            header('Location: paiements.php' . $filter);
            exit();
        } else {
            throw new PDOException("Erreur lors de l'enregistrement de la transaction");
        }
    } catch (PDOException $e) {
        $conn->rollBack();
        $_SESSION['error'] = "Erreur : " . $e->getMessage();
        $filter = isset($_POST['filter']) ? '?filter=' . $_POST['filter'] : '';
        header('Location: paiements.php' . $filter);
        exit();
    }
}

// Redirection par défaut si aucune action n'est effectuée
$filter = isset($_POST['filter']) ? '?filter=' . $_POST['filter'] : '';
header('Location: paiements.php' . $filter);
exit();
?>

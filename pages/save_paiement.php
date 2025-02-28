<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/log_functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_paiement'])) {
    try {
        $conn->beginTransaction();

        // Log des données reçues
        writeLog("Données reçues : " . print_r($_POST, true));

        $montant = floatval($_POST['montant']);
        $source_paiement = $_POST['source_paiement'];
        $type = $_POST['type'];
        $status = $_POST['status'];

        // Log des variables
        writeLog("Montant : " . $montant);
        writeLog("Source : " . $source_paiement);

        // Vérifier si c'est un ticket ou un bordereau
        if (isset($_POST['id_ticket'])) {
            $id_ticket = $_POST['id_ticket'];
            $numero_ticket = $_POST['numero_ticket'];
            
            // Récupérer l'ID de l'agent
            $stmt = $conn->prepare("SELECT id_agent FROM tickets WHERE id_ticket = ?");
            $stmt->execute([$id_ticket]);
            $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
            $id_agent = $ticket['id_agent'];

            writeLog("Traitement ticket ID : " . $id_ticket);

            if ($source_paiement === 'financement') {
                // Vérifier le solde total du financement
                $stmt = $conn->prepare("SELECT SUM(montant) as montant_total FROM financement WHERE id_agent = ? AND montant > 0");
                $stmt->execute([$id_agent]);
                $financement = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$financement || $financement['montant_total'] < $montant) {
                    throw new Exception("Solde de financement insuffisant");
                }
                
                // Mettre à jour le financement - on prend d'abord le plus ancien
                $stmt = $conn->prepare("
                    UPDATE financement 
                    SET montant = CASE
                        WHEN montant >= ? THEN montant - ?
                        ELSE 0
                    END
                    WHERE id_agent = ? 
                    AND montant > 0
                    ORDER BY Numero_financement ASC
                    LIMIT 1
                ");
                $stmt->execute([$montant, $montant, $id_agent]);
                writeLog("Financement mis à jour");
            } else {
                // Créer une transaction (sortie de caisse)
                try {
                    writeLog("Tentative de création de la transaction");
                    $motifs = "Paiement du ticket " . $numero_ticket;
                    $stmt = $conn->prepare("INSERT INTO transactions (type_transaction, montant, date_transaction, motifs, id_utilisateur) VALUES ('paiement', ?, NOW(), ?, ?)");
                    $stmt->execute([$montant, $motifs, $_SESSION['user_id']]);
                    writeLog("Transaction créée avec succès");
                } catch (Exception $e) {
                    writeLog("Erreur lors de la création de la transaction : " . $e->getMessage());
                    throw $e;
                }
            }
            
            // Mettre à jour le ticket
            try {
                writeLog("Tentative de mise à jour du ticket");
                // D'abord, vérifions l'état actuel
                $stmt = $conn->prepare("SELECT montant_payer, montant_paie FROM tickets WHERE id_ticket = ?");
                $stmt->execute([$id_ticket]);
                $avant = $stmt->fetch(PDO::FETCH_ASSOC);
                writeLog("État avant mise à jour : " . print_r($avant, true));

                $stmt = $conn->prepare("UPDATE tickets SET montant_payer = COALESCE(montant_payer, 0) + ?, date_paie = NOW() WHERE id_ticket = ?");
                $stmt->execute([$montant, $id_ticket]);
                writeLog("Requête de mise à jour exécutée");

                // Vérifier la mise à jour
                $stmt = $conn->prepare("SELECT montant_payer, montant_paie FROM tickets WHERE id_ticket = ?");
                $stmt->execute([$id_ticket]);
                $apres = $stmt->fetch(PDO::FETCH_ASSOC);
                writeLog("État après mise à jour : " . print_r($apres, true));
            } catch (Exception $e) {
                writeLog("Erreur lors de la mise à jour du ticket : " . $e->getMessage());
                throw $e;
            }
            
        } else {
            $id_bordereau = $_POST['id_bordereau'];
            $numero_bordereau = $_POST['numero_bordereau'];
            
            // Récupérer l'ID de l'agent
            $stmt = $conn->prepare("SELECT id_agent FROM bordereau WHERE id_bordereau = ?");
            $stmt->execute([$id_bordereau]);
            $bordereau = $stmt->fetch(PDO::FETCH_ASSOC);
            $id_agent = $bordereau['id_agent'];

            writeLog("Traitement bordereau ID : " . $id_bordereau);

            if ($source_paiement === 'financement') {
                // Vérifier le solde total du financement
                $stmt = $conn->prepare("SELECT SUM(montant) as montant_total FROM financement WHERE id_agent = ? AND montant > 0");
                $stmt->execute([$id_agent]);
                $financement = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$financement || $financement['montant_total'] < $montant) {
                    throw new Exception("Solde de financement insuffisant");
                }
                
                // Mettre à jour le financement - on prend d'abord le plus ancien
                $stmt = $conn->prepare("
                    UPDATE financement 
                    SET montant = CASE
                        WHEN montant >= ? THEN montant - ?
                        ELSE 0
                    END
                    WHERE id_agent = ? 
                    AND montant > 0
                    ORDER BY Numero_financement ASC
                    LIMIT 1
                ");
                $stmt->execute([$montant, $montant, $id_agent]);
                writeLog("Financement mis à jour");
            } else {
                // Créer une transaction (sortie de caisse)
                try {
                    writeLog("Tentative de création de la transaction");
                    $motifs = "Paiement du bordereau " . $numero_bordereau;
                    $stmt = $conn->prepare("INSERT INTO transactions (type_transaction, montant, date_transaction, motifs, id_utilisateur) VALUES ('paiement', ?, NOW(), ?, ?)");
                    $stmt->execute([$montant, $motifs, $_SESSION['user_id']]);
                    writeLog("Transaction créée avec succès");
                } catch (Exception $e) {
                    writeLog("Erreur lors de la création de la transaction : " . $e->getMessage());
                    throw $e;
                }
            }
            
            // Mettre à jour le bordereau
            try {
                writeLog("Tentative de mise à jour du bordereau");
                // D'abord, vérifions l'état actuel
                $stmt = $conn->prepare("SELECT montant_payer, montant_total FROM bordereau WHERE id_bordereau = ?");
                $stmt->execute([$id_bordereau]);
                $avant = $stmt->fetch(PDO::FETCH_ASSOC);
                writeLog("État avant mise à jour : " . print_r($avant, true));

                $stmt = $conn->prepare("UPDATE bordereau SET montant_payer = COALESCE(montant_payer, 0) + ?, date_paie = NOW() WHERE id_bordereau = ?");
                $stmt->execute([$montant, $id_bordereau]);
                writeLog("Requête de mise à jour exécutée");

                // Vérifier la mise à jour
                $stmt = $conn->prepare("SELECT montant_payer, montant_total FROM bordereau WHERE id_bordereau = ?");
                $stmt->execute([$id_bordereau]);
                $apres = $stmt->fetch(PDO::FETCH_ASSOC);
                writeLog("État après mise à jour : " . print_r($apres, true));
            } catch (Exception $e) {
                writeLog("Erreur lors de la mise à jour du bordereau : " . $e->getMessage());
                throw $e;
            }
        }

        $conn->commit();
        writeLog("Transaction commit avec succès");
        $_SESSION['success_message'] = "Paiement effectué avec succès.";
        
    } catch (Exception $e) {
        $conn->rollBack();
        writeLog("Erreur finale : " . $e->getMessage());
        writeLog("Trace : " . $e->getTraceAsString());
        $_SESSION['error_message'] = "Erreur lors du paiement : " . $e->getMessage();
    }
    
    // Rediriger vers la page des paiements avec les mêmes filtres
    header("Location: paiements.php?type=" . urlencode($type) . "&status=" . urlencode($status));
    exit;
}

// Si on arrive ici, c'est qu'il y a eu une erreur
$_SESSION['error_message'] = "Erreur : requête invalide";
header("Location: paiements.php");
exit;

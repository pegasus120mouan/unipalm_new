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

        // Générer un numéro de reçu unique
        $numero_recu = date('Ymd') . sprintf("%04d", rand(1, 9999));

        // Variables pour le reçu
        $id_document = null;
        $numero_document = null;
        $id_agent = null;
        $nom_agent = null;
        $contact_agent = null;
        $nom_usine = null;
        $matricule_vehicule = null;
        $montant_total = 0;
        $montant_precedent = 0;

        // Vérifier si c'est un ticket ou un bordereau
        if (isset($_POST['id_ticket'])) {
            $id_ticket = $_POST['id_ticket'];
            $numero_ticket = $_POST['numero_ticket'];
            
            // Récupérer les informations du ticket et de l'agent
            $stmt = $conn->prepare("
                SELECT t.*, 
                    CONCAT(a.nom, ' ', a.prenom) as agent_nom,
                    a.contact as agent_contact,
                    a.id_agent,
                    us.nom_usine,
                    v.matricule_vehicule
                FROM tickets t
                LEFT JOIN agents a ON t.id_agent = a.id_agent
                LEFT JOIN usines us ON t.id_usine = us.id_usine
                LEFT JOIN vehicules v ON t.vehicule_id = v.vehicules_id
                WHERE t.id_ticket = ?
            ");
            $stmt->execute([$id_ticket]);
            $ticket_info = $stmt->fetch(PDO::FETCH_ASSOC);

            $id_document = $id_ticket;
            $numero_document = $numero_ticket;
            $id_agent = $ticket_info['id_agent'];
            $nom_agent = $ticket_info['agent_nom'];
            $contact_agent = $ticket_info['agent_contact'];
            $nom_usine = $ticket_info['nom_usine'];
            $matricule_vehicule = $ticket_info['matricule_vehicule'];
            $montant_total = $ticket_info['montant_paie'];
            $montant_precedent = $ticket_info['montant_payer'];
            $type_document = 'ticket';

            // Mettre à jour le ticket
            $stmt = $conn->prepare("UPDATE tickets SET montant_payer = COALESCE(montant_payer, 0) + ?, date_paie = NOW() WHERE id_ticket = ?");
            $stmt->execute([$montant, $id_ticket]);

            if ($source_paiement === 'transactions') {
                $motifs = "Paiement du ticket " . $numero_ticket;
                $stmt = $conn->prepare("INSERT INTO transactions (type_transaction, montant, date_transaction, motifs, id_utilisateur) VALUES ('paiement', ?, NOW(), ?, ?)");
                $stmt->execute([$montant, $motifs, $_SESSION['user_id']]);
            }
        } else {
            $id_bordereau = $_POST['id_bordereau'];
            $numero_bordereau = $_POST['numero_bordereau'];
            
            // Récupérer les informations du bordereau et de l'agent
            $stmt = $conn->prepare("
                SELECT b.*, 
                    CONCAT(a.nom, ' ', a.prenom) as agent_nom,
                    a.contact as agent_contact,
                    a.id_agent
                FROM bordereau b
                LEFT JOIN agents a ON b.id_agent = a.id_agent
                WHERE b.id_bordereau = ?
            ");
            $stmt->execute([$id_bordereau]);
            $bordereau_info = $stmt->fetch(PDO::FETCH_ASSOC);

            $id_document = $id_bordereau;
            $numero_document = $numero_bordereau;
            $id_agent = $bordereau_info['id_agent'];
            $nom_agent = $bordereau_info['agent_nom'];
            $contact_agent = $bordereau_info['agent_contact'];
            $montant_total = $bordereau_info['montant_total'];
            $montant_precedent = $bordereau_info['montant_payer'];
            $type_document = 'bordereau';

            // Mettre à jour le bordereau
            $stmt = $conn->prepare("UPDATE bordereau SET montant_payer = COALESCE(montant_payer, 0) + ?, date_paie = NOW() WHERE id_bordereau = ?");
            $stmt->execute([$montant, $id_bordereau]);

            if ($source_paiement === 'transactions') {
                $motifs = "Paiement du bordereau " . $numero_bordereau;
                $stmt = $conn->prepare("INSERT INTO transactions (type_transaction, montant, date_transaction, motifs, id_utilisateur) VALUES ('paiement', ?, NOW(), ?, ?)");
                $stmt->execute([$montant, $motifs, $_SESSION['user_id']]);
            }
        }

        // Récupérer le nom du caissier
        $stmt = $conn->prepare("SELECT CONCAT(nom, ' ', prenoms) as nom_caissier FROM utilisateurs WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $caissier = $stmt->fetch(PDO::FETCH_ASSOC);

        // Enregistrer le reçu
        $stmt = $conn->prepare("
            INSERT INTO recus_paiements (
                numero_recu, type_document, id_document, numero_document,
                montant_total, montant_paye, montant_precedent, reste_a_payer,
                id_agent, nom_agent, contact_agent, nom_usine, matricule_vehicule,
                id_caissier, nom_caissier, source_paiement
            ) VALUES (
                ?, ?, ?, ?, 
                ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?
            )
        ");
        
        $reste_a_payer = $montant_total - ($montant_precedent + $montant);
        
        $stmt->execute([
            $numero_recu, $type_document, $id_document, $numero_document,
            $montant_total, $montant, $montant_precedent, $reste_a_payer,
            $id_agent, $nom_agent, $contact_agent, $nom_usine, $matricule_vehicule,
            $_SESSION['user_id'], $caissier['nom_caissier'], $source_paiement
        ]);

        // Si tout est OK, on valide la transaction
        $conn->commit();
        writeLog("Transaction validée avec succès");
        $_SESSION['success_message'] = "Paiement effectué avec succès";
        
        // Stocker le montant et le numéro de reçu dans la session
        $_SESSION['montant_paiement'] = $montant;
        $_SESSION['numero_recu'] = $numero_recu;
        
        // Rediriger vers le reçu de paiement en PDF
        if (isset($_POST['id_ticket'])) {
            header("Location: recu_paiement_pdf.php?id_ticket=" . $_POST['id_ticket']);
        } else {
            header("Location: recu_paiement_pdf.php?id_bordereau=" . $_POST['id_bordereau']);
        }
        exit;
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

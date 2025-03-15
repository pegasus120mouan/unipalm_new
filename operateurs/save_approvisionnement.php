<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/log_functions.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_approvisionnement'])) {
    try {
        $conn->beginTransaction();
        writeLog("Début de l'enregistrement de l'approvisionnement");

        // Validation des données
        if (!isset($_POST['montant']) || empty($_POST['montant'])) {
            throw new Exception("Le montant est requis");
        }
        
        // Nettoyer le montant des espaces et autres caractères non numériques
        $montant = preg_replace('/[^0-9]/', '', $_POST['montant']);
        $montant = floatval($montant);
        
        if ($montant <= 0) {
            throw new Exception("Le montant doit être supérieur à 0");
        }

        // Debug log
        writeLog("Montant reçu: " . $montant);

        // Récupérer le solde actuel
        $stmt = $conn->prepare("SELECT COALESCE(MAX(solde), 0) as solde FROM transactions");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $solde_actuel = floatval($result['solde']);
        
        writeLog("Solde actuel avant approvisionnement: " . $solde_actuel);

        // Calculer le nouveau solde
        $nouveau_solde = $solde_actuel + $montant;
        writeLog("Nouveau solde calculé: " . $nouveau_solde);

        // Créer la transaction d'approvisionnement
        $stmt = $conn->prepare("
            INSERT INTO transactions (
                type_transaction, 
                montant, 
                date_transaction, 
                motifs, 
                id_utilisateur,
                solde
            ) VALUES (
                'approvisionnement',
                :montant,
                NOW(),
                'approvisionnement de la caisse',
                :id_utilisateur,
                :solde
            )
        ");
        
        $stmt->bindValue(':montant', $montant, PDO::PARAM_STR);
        //$stmt->bindValue(':motifs', $_POST['motifs'], PDO::PARAM_STR);
        $stmt->bindValue(':id_utilisateur', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->bindValue(':solde', $nouveau_solde, PDO::PARAM_STR);
        
        $stmt->execute();
        
        $id_transaction = $conn->lastInsertId();
        writeLog("Transaction d'approvisionnement créée #$id_transaction, nouveau solde: $nouveau_solde");

        $conn->commit();
        $_SESSION['success_message'] = "Approvisionnement de " . number_format($montant, 0, ',', ' ') . " FCFA effectué avec succès. Nouveau solde : " . number_format($nouveau_solde, 0, ',', ' ') . " FCFA";
        
        header("Location: approvisionnement.php");
        exit;

    } catch (Exception $e) {
        $conn->rollBack();
        writeLog("ERREUR: " . $e->getMessage());
        writeLog("Trace: " . $e->getTraceAsString());
        $_SESSION['error_message'] = "Erreur lors de l'approvisionnement : " . $e->getMessage();
        header("Location: approvisionnement.php");
        exit;
    }
}

$_SESSION['error_message'] = "Erreur : requête invalide";
header("Location: approvisionnement.php");
exit;

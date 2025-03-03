<?php
require_once '../inc/functions/connexion.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_usine'])) {
    $id_usine = $_POST['id_usine'];
    $montant = $_POST['montant'];
    $date_paiement = $_POST['date_paiement'];
    $mode_paiement = $_POST['mode_paiement'];
    $reference = $_POST['reference'] ?? null;
    $created_by = $_SESSION['user_id'];

    try {
        // Début de la transaction
        $conn->beginTransaction();

        // 1. Insérer le paiement dans la table historique_paiements
        $sql = "INSERT INTO historique_paiements (id_usine, montant, date_paiement, mode_paiement, reference, created_by) 
                VALUES (:id_usine, :montant, :date_paiement, :mode_paiement, :reference, :created_by)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':id_usine' => $id_usine,
            ':montant' => $montant,
            ':date_paiement' => $date_paiement,
            ':mode_paiement' => $mode_paiement,
            ':reference' => $reference,
            ':created_by' => $created_by
        ]);

        // 2. Mettre à jour les montants dans la table usines
        $sql = "UPDATE usines 
                SET montant_paye = montant_paye + :montant,
                    montant_restant = montant_restant - :montant 
                WHERE id_usine = :id_usine";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':montant' => $montant,
            ':id_usine' => $id_usine
        ]);

        // Valider la transaction
        $conn->commit();

        $_SESSION['popup'] = true;
        $_SESSION['message'] = "Le paiement a été enregistré avec succès.";
        header('Location: gestion_usines.php');
        exit();

    } catch(PDOException $e) {
        // Annuler la transaction en cas d'erreur
        $conn->rollBack();
        $_SESSION['delete_pop'] = true;
        $_SESSION['message'] = "Erreur lors de l'enregistrement du paiement : " . $e->getMessage();
        header('Location: gestion_usines.php');
        exit();
    }
} else {
    header('Location: gestion_usines.php');
    exit();
}

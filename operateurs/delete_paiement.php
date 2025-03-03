<?php
require_once '../inc/functions/connexion.php';
session_start();

if (!isset($_GET['id'])) {
    header('Location: gestion_usines.php');
    exit();
}

$id_paiement = $_GET['id'];

try {
    $conn->beginTransaction();

    // 1. Récupérer les informations du paiement avant suppression
    $sql = "SELECT * FROM historique_paiements WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $id_paiement]);
    $paiement = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($paiement) {
        // 2. Mettre à jour les montants de l'usine
        $sql = "UPDATE usines 
                SET montant_paye = montant_paye - :montant,
                    montant_restant = montant_restant + :montant 
                WHERE id_usine = :id_usine";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':montant' => $paiement['montant'],
            ':id_usine' => $paiement['id_usine']
        ]);

        // 3. Supprimer le paiement
        $sql = "DELETE FROM historique_paiements WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id_paiement]);

        $conn->commit();
        $_SESSION['success'] = "Le paiement a été supprimé avec succès.";
    }

    header("Location: details_usine.php?id=" . $paiement['id_usine']);
    exit();

} catch(PDOException $e) {
    $conn->rollBack();
    $_SESSION['error'] = "Erreur lors de la suppression du paiement : " . $e->getMessage();
    header('Location: gestion_usines.php');
    exit();
}

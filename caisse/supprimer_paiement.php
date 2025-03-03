<?php
require_once '../inc/functions/connexion.php';
session_start();

if (!isset($_GET['id']) || !isset($_GET['id_usine'])) {
    $_SESSION['error'] = "Paramètres manquants";
    header('Location: gestion_usines.php');
    exit();
}

$id_paiement = $_GET['id'];
$id_usine = $_GET['id_usine'];

try {
    $conn->beginTransaction();

    // 1. Récupérer les informations du paiement
    $sql = "SELECT * FROM historique_paiements WHERE id = :id AND id_usine = :id_usine";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':id' => $id_paiement,
        ':id_usine' => $id_usine
    ]);
    $paiement = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($paiement) {
        // 2. Mettre à jour le montant payé et restant de l'usine
        $sql = "UPDATE usines 
                SET montant_paye = montant_paye - :montant,
                    montant_restant = montant_restant + :montant 
                WHERE id_usine = :id_usine";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':montant' => $paiement['montant'],
            ':id_usine' => $id_usine
        ]);

        // 3. Supprimer le paiement
        $sql = "DELETE FROM historique_paiements WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id_paiement]);

        $conn->commit();
        $_SESSION['success'] = "Le paiement a été supprimé avec succès";
    } else {
        $_SESSION['error'] = "Paiement non trouvé";
    }

} catch(PDOException $e) {
    $conn->rollBack();
    $_SESSION['error'] = "Erreur lors de la suppression : " . $e->getMessage();
}

header("Location: details_usine.php?id=" . $id_usine);
exit();

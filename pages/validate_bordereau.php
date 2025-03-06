<?php
require_once '../inc/functions/connexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_bordereau = $_POST['id_bordereau'] ?? null;
    $action = $_POST['action'] ?? 'validate';

    if ($id_bordereau) {
        try {
            if ($action === 'validate') {
                // Valider le bordereau
                $stmt = $conn->prepare("
                    UPDATE bordereau 
                    SET date_validation_boss = NOW() 
                    WHERE id_bordereau = ?
                ");
                $stmt->execute([$id_bordereau]);
            } else {
                // Annuler la validation
                $stmt = $conn->prepare("
                    UPDATE bordereau 
                    SET date_validation_boss = NULL 
                    WHERE id_bordereau = ?
                ");
                $stmt->execute([$id_bordereau]);
            }

            // Redirection vers la page des bordereaux après l'action
            header('Location: bordereaux.php');
            exit;
        } catch (PDOException $e) {
            // En cas d'erreur, tu peux aussi rediriger vers bordereaux.php avec un message d'erreur (optionnel)
            header('Location: bordereaux.php?error=1');
            exit;
        }
    } else {
        // Redirection avec message d'erreur si id_bordereau manquant
        header('Location: bordereaux.php?error=missing_id');
        exit;
    }
} else {
    // Si la méthode n'est pas POST, redirection simple
    header('Location: bordereaux.php');
    exit;
}

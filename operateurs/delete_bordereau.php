<?php
require_once '../inc/functions/connexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_bordereau = $_POST['id_bordereau'] ?? null;

    if ($id_bordereau) {
        try {
            $stmt = $conn->prepare("DELETE FROM bordereau WHERE id_bordereau = ?");
            $stmt->execute([$id_bordereau]);

            // Redirection simple après suppression
            header('Location: bordereaux.php');
            exit;
        } catch (Exception $e) {
            // En cas d'erreur, tu peux afficher directement le message
            echo "Erreur lors de la suppression : " . htmlspecialchars($e->getMessage());
            exit;
        }
    } else {
        echo "ID du bordereau non spécifié.";
        exit;
    }
} else {
    echo "Méthode non autorisée.";
    exit;
}

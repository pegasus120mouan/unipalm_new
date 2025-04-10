<?php
session_start();
require_once '../inc/functions/connexion.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Vérifier si l'ID du ticket est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "ID du ticket manquant";
    header('Location: tickets_modifications.php');
    exit();
}

// Vérifier si la nouvelle usine est fournie
if (!isset($_POST['usine']) || empty($_POST['usine'])) {
    $_SESSION['error'] = "L'usine est requise";
    header('Location: tickets_modifications.php');
    exit();
}

$id_ticket = intval($_GET['id']);
$nouvelle_usine = intval($_POST['usine']);

try {
    // Vérifier si le ticket existe et n'est pas déjà payé
    $stmt = $conn->prepare("SELECT date_paie FROM tickets WHERE id_ticket = ?");
    $stmt->execute([$id_ticket]);
    $ticket = $stmt->fetch();

    if (!$ticket) {
        $_SESSION['error'] = "Ticket introuvable";
        header('Location: tickets_modifications.php');
        exit();
    }

    if ($ticket['date_paie'] !== null) {
        $_SESSION['error'] = "Impossible de modifier l'usine d'un ticket déjà payé";
        header('Location: tickets_modifications.php');
        exit();
    }

    // Récupérer l'ancienne valeur avant la mise à jour
    $stmt = $conn->prepare("SELECT id_usine FROM tickets WHERE id_ticket = ?");
    $stmt->execute([$id_ticket]);
    $ancienne_usine = $stmt->fetch()['id_usine'];

    // Mettre à jour l'usine
    $stmt = $conn->prepare("UPDATE tickets SET id_usine = ? WHERE id_ticket = ?");
    $stmt->execute([$nouvelle_usine, $id_ticket]);

    $_SESSION['success'] = "L'usine a été mise à jour avec succès";
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors de la mise à jour de l'usine : " . $e->getMessage();
}

header('Location: tickets_modifications.php');
exit();
?>

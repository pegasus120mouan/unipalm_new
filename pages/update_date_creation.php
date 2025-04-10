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

// Vérifier si la nouvelle date est fournie
if (!isset($_POST['date_creation']) || empty($_POST['date_creation'])) {
    $_SESSION['error'] = "La date de création est requise";
    header('Location: tickets_modifications.php');
    exit();
}

$id_ticket = intval($_GET['id']);
$nouvelle_date = $_POST['date_creation'];

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
        $_SESSION['error'] = "Impossible de modifier la date d'un ticket déjà payé";
        header('Location: tickets_modifications.php');
        exit();
    }

    // Mettre à jour la date de création
    $stmt = $conn->prepare("UPDATE tickets SET created_at = ? WHERE id_ticket = ?");
    $stmt->execute([$nouvelle_date, $id_ticket]);

    if($stmt->execute()) {
        // Mise à jour réussie
        $response['status'] = 'success';
        $response['message'] = 'Date de création mise à jour avec succès';
    }

    $_SESSION['success'] = "La date de création a été mise à jour avec succès";
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors de la mise à jour de la date : " . $e->getMessage();
}

header('Location: tickets_modifications.php');
exit();
?>

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

// Vérifier si le nouveau chef de mission est fourni
if (!isset($_POST['chef_equipe']) || empty($_POST['chef_equipe'])) {
    $_SESSION['error'] = "Le chef de mission est requis";
    header('Location: tickets_modifications.php');
    exit();
}

$id_ticket = intval($_GET['id']);
$nouveau_chef = intval($_POST['chef_equipe']);

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
        $_SESSION['error'] = "Impossible de modifier le chef de mission d'un ticket déjà payé";
        header('Location: tickets_modifications.php');
        exit();
    }

    // Récupérer l'ancienne valeur avant la mise à jour
    $stmt = $conn->prepare("SELECT id_agent FROM tickets WHERE id_ticket = ?");
    $stmt->execute([$id_ticket]);
    $ancien_chef = $stmt->fetch()['id_agent'];

    // Mettre à jour le chef de mission
    $stmt = $conn->prepare("UPDATE tickets SET id_agent = ? WHERE id_ticket = ?");
    $stmt->execute([$nouveau_chef, $id_ticket]);

    $_SESSION['success'] = "Le chef de mission a été mis à jour avec succès";
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors de la mise à jour du chef de mission : " . $e->getMessage();
}

header('Location: tickets_modifications.php');
exit();
?>

<?php
session_start();
require_once '../inc/functions/connexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_ticket = $_POST['id_ticket'];
    $id_usine = $_POST['usine'];

    try {
        // Vérifier si le ticket n'est pas déjà payé
        $check_stmt = $conn->prepare("SELECT date_paie FROM tickets WHERE id_ticket = ?");
        $check_stmt->execute([$id_ticket]);
        $ticket = $check_stmt->fetch();

        if ($ticket['date_paie'] !== null) {
            $_SESSION['delete_pop'] = true;
            header('Location: tickets_modifications.php');
            exit;
        }

        // Mettre à jour l'usine
        $stmt = $conn->prepare("UPDATE tickets SET id_usine = ? WHERE id_ticket = ?");
        $stmt->execute([$id_usine, $id_ticket]);

        $_SESSION['popup'] = true;
        header('Location: tickets_modifications.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['delete_pop'] = true;
        header('Location: tickets_modifications.php');
        exit;
    }
}
?>

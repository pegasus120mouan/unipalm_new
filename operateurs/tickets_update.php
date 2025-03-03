<?php
session_start();
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_tickets.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['id'])) {
    $id_ticket = $_GET['id'];
    
    // Vérifier si le ticket n'est pas déjà payé
    $stmt = $conn->prepare("SELECT date_paie FROM tickets WHERE id_ticket = ?");
    $stmt->execute([$id_ticket]);
    $ticket = $stmt->fetch();
    
    if ($ticket['date_paie'] !== null) {
        $_SESSION['error'] = "Ce ticket a déjà été payé. Modifications impossibles.";
        header("Location: tickets.php");
        exit();
    }

    // Récupérer les données du formulaire
    $date_ticket = $_POST['date_ticket'];
    $numero_ticket = $_POST['numero_ticket'];
    $id_usine = $_POST['usine'];
    $id_agent = $_POST['id_agent'];
    $vehicules_id = $_POST['vehicule'];

    try {
        // Mettre à jour les informations du ticket
        $stmt = $conn->prepare("UPDATE tickets SET 
            date_ticket = ?,
            numero_ticket = ?,
            id_usine = ?,
            id_agent = ?,
            vehicules_id = ?
            WHERE id_ticket = ?");
            
        $stmt->execute([
            $date_ticket,
            $numero_ticket,
            $id_usine,
            $id_agent,
            $vehicules_id,
            $id_ticket
        ]);

        $_SESSION['popup'] = true;
        header("Location: tickets.php");
        exit();
    } catch(PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la modification : " . $e->getMessage();
        header("Location: tickets.php");
        exit();
    }
} else {
    header("Location: tickets.php");
    exit();
}
?>

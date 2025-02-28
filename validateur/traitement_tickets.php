<?php

require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_tickets.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Vérifiez si l'action concerne l'insertion ou autre chose
    if (isset($_POST["usine"]) && isset($_POST["date_ticket"])) {
        // Traitement de l'insertion du ticket
        $id_usine = $_POST["usine"] ?? null;
        $date_ticket = $_POST["date_ticket"] ?? null;
        $id_chef_equipe = $_POST["chef_equipe"] ?? null;
        $numero_ticket = $_POST["numero_ticket"] ?? null;
        $vehicule_id = $_POST["vehicule"] ?? null;
        $poids = $_POST["poids"] ?? null;
        $id_utilisateur = $_SESSION['user_id'] ?? null;

        // Validation des données
        if (!$id_usine || !$date_ticket || !$id_chef_equipe || !$numero_ticket || !$vehicule_id || !$poids || !$id_utilisateur) {
            $_SESSION['delete_pop'] = true; // Message d'erreur
            header('Location: tickets.php');
            exit;
        }

        // Appel de la fonction pour insérer le ticket
        try {
            if (insertTicket($conn, $id_usine, $date_ticket, $id_chef_equipe, $numero_ticket, $vehicule_id, $poids, $id_utilisateur)) {
                $_SESSION['popup'] = true; // Message de succès
            } else {
                $_SESSION['delete_pop'] = true; // Message d'erreur
            }
        } catch (Exception $e) {
            error_log("Erreur lors de l'enregistrement du ticket : " . $e->getMessage());
            $_SESSION['delete_pop'] = true; // Message d'erreur
        }
        header('Location: tickets.php');
        exit;
    } elseif (isset($_POST["id_ticket"]) && isset($_POST["prix_unitaire"])) {
        // Traitement des données supplémentaires
        $id_ticket = $_POST["id_ticket"] ?? null;
        $prix_unitaire = $_POST["prix_unitaire"] ?? null;
        $date = date("Y-m-d");

        // Validation des données
        if (!$id_ticket || !$prix_unitaire) {
            $_SESSION['delete_pop'] = true; // Message d'erreur
            header('Location: tickets.php');
            exit;
        }

        // Requête SQL d'update
        $sql = "UPDATE tickets
                SET prix_unitaire = :prix_unitaire, date_validation_boss = :date_validation_boss 
                WHERE id_ticket = :id_ticket";

        // Préparation de la requête
         // Appel de la fonction
        $result = updateTicketPrixUnitaire($conn, $id_ticket, $prix_unitaire, $date);

        if ($result) {
            $_SESSION['popup'] = true; // Message de succès
        } else {
            $_SESSION['delete_pop'] = true; // Message d'erreur
        }

        // Redirection
        header('Location: tickets.php');
        exit;
    }
}

<?php
session_start();
require_once '../inc/functions/connexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    try {
        // Récupérer les données du formulaire
        $numero_financement = $_POST['numero_financement'];
        $id_agent = $_POST['id_agent'];
        $montant = $_POST['montant'];
        $motif = $_POST['motif'];

        // Préparer la requête de mise à jour
        $sql = "UPDATE financement 
                SET id_agent = :id_agent, 
                    montant = :montant, 
                    motif = :motif 
                WHERE Numero_financement = :numero_financement";
        
        $stmt = $conn->prepare($sql);
        
        // Exécuter la requête avec les paramètres
        $stmt->execute([
            ':id_agent' => $id_agent,
            ':montant' => $montant,
            ':motif' => $motif,
            ':numero_financement' => $numero_financement
        ]);

        // Vérifier si la mise à jour a réussi
        if ($stmt->rowCount() > 0) {
            $_SESSION['popup'] = true;
            $_SESSION['success_modal'] = true;
        } else {
            $_SESSION['delete_pop'] = true;
        }
    } catch (PDOException $e) {
        $_SESSION['delete_pop'] = true;
    }
}

// Rediriger vers la page des financements
header('Location: financements.php');
exit();

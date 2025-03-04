<?php
require_once '../inc/functions/connexion.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Récupération des données du formulaire
        $numero_demande = $_POST['numero_demande'];
        $date_demande = $_POST['date_demande'];
        $montant = $_POST['montant'];
        $motif = $_POST['motif'];

        // Préparation de la requête
        $query = "INSERT INTO demande_sortie (numero_demande, date_demande, montant, motif) 
                 VALUES (:numero_demande, :date_demande, :montant, :motif)";
        
        $stmt = $conn->prepare($query);
        
        // Exécution de la requête avec les paramètres
        $result = $stmt->execute([
            ':numero_demande' => $numero_demande,
            ':date_demande' => $date_demande,
            ':montant' => $montant,
            ':motif' => $motif
        ]);

        if ($result) {
            $_SESSION['success_message'] = "La demande a été enregistrée avec succès.";
        } else {
            $_SESSION['error_message'] = "Erreur lors de l'enregistrement de la demande.";
        }
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Code d'erreur pour duplicate entry
            $_SESSION['error_message'] = "Ce numéro de demande existe déjà.";
        } else {
            $_SESSION['error_message'] = "Une erreur est survenue : " . $e->getMessage();
        }
    }
}

// Redirection vers la page des demandes
header('Location: divers.php');
exit();
?>

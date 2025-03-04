<?php
require_once '../inc/functions/connexion.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_transaction'])) {
    $type_transaction = $_POST['type_transaction'];
    $montant = $_POST['montant'];
    $id_utilisateur = $_SESSION['user_id'];
    $motifs = ($type_transaction == 'approvisionnement') ? "Refoulement de la caisse" : "";
    
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error_message'] = "Erreur: Utilisateur non connecté";
        $_SESSION['delete_pop'] = true;
        header('Location: approvisionnement.php');
        exit();
    }
    
    try {
        $query = "INSERT INTO transactions (type_transaction, montant, id_utilisateur, motifs, date_transaction) 
                 VALUES (:type, :montant, :id_utilisateur, :motifs, NOW())";
        
        $stmt = $conn->prepare($query);
        $result = $stmt->execute([
            ':type' => $type_transaction,
            ':montant' => $montant,
            ':id_utilisateur' => $id_utilisateur,
            ':motifs' => $motifs
        ]);
        
        if (!$result) {
            error_log("Erreur d'insertion: " . print_r($stmt->errorInfo(), true));
            $_SESSION['error_message'] = "Erreur lors de l'insertion: " . $stmt->errorInfo()[2];
            $_SESSION['delete_pop'] = true;
        } else {
            $_SESSION['popup'] = true;
        }
        
        header('Location: approvisionnement.php');
        exit();
    } catch(PDOException $e) {
        error_log("Exception PDO: " . $e->getMessage());
        $_SESSION['error_message'] = "Erreur: " . $e->getMessage();
        $_SESSION['delete_pop'] = true;
        header('Location: approvisionnement.php');
        exit();
    }
} else {
    header('Location: approvisionnement.php');
    exit();
}

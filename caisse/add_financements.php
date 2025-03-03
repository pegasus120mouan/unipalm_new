<?php
require_once '../inc/functions/connexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $id_agent = $_POST['id_agent'];
        $montant = $_POST['montant'];
        $motif = $_POST['motif'];

        if ($_POST['action'] === 'add') {
            // Récupérer le dernier numéro
            $sql = "SELECT MAX(Numero_financement) as max_num FROM financement";
            $stmt = $conn->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $nouveau_numero = ($result['max_num'] ?? 0) + 1;

            $sql = "INSERT INTO financement (Numero_financement, id_agent, montant, motif, date_financement) VALUES (:numero, :id_agent, :montant, :motif, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':numero', $nouveau_numero);
            $stmt->bindParam(':id_agent', $id_agent);
            $stmt->bindParam(':montant', $montant);
            $stmt->bindParam(':motif', $motif);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Financement ajouté avec succès";
            } else {
                $_SESSION['error'] = "Erreur lors de l'ajout du financement";
            }
        } elseif ($_POST['action'] === 'edit') {
            $numero = $_POST['numero_financement'];
            $sql = "UPDATE financement 
                   SET id_agent = :id_agent, 
                       montant = :montant, 
                       motif = :motif,
                       date_financement = date_financement
                   WHERE Numero_financement = :numero";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_agent', $id_agent);
            $stmt->bindParam(':montant', $montant);
            $stmt->bindParam(':motif', $motif);
            $stmt->bindParam(':numero', $numero);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Financement modifié avec succès";
            } else {
                $_SESSION['error'] = "Erreur lors de la modification du financement";
            }
        }
        header('Location: financements.php');
        exit();
    }
}
?>

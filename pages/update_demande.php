<?php
require_once '../inc/functions/connexion.php';
//session_start();

if (isset($_GET['id']) && isset($_GET['action'])) {
    $id_demande = $_GET['id'];
    $action = $_GET['action'];
    $user_id = $_SESSION['user_id']; // ID de l'utilisateur connecté
    
    try {
        switch ($action) {
            case 'approuver':
                $query = "UPDATE demande_sortie 
                         SET statut = 'approuve', 
                             date_approbation = NOW(), 
                             approuve_par = :user_id 
                         WHERE id_demande = :id_demande";
                break;
                
            case 'rejeter':
                $query = "UPDATE demande_sortie 
                         SET statut = 'rejete', 
                             date_approbation = NOW(), 
                             approuve_par = :user_id 
                         WHERE id_demande = :id_demande";
                break;
                
            case 'payer':
                $query = "UPDATE demande_sortie 
                         SET statut = 'paye', 
                             date_paiement = NOW(), 
                             paye_par = :user_id 
                         WHERE id_demande = :id_demande";
                break;
                
            default:
                $_SESSION['error_message'] = "Action non valide.";
                header('Location: divers.php');
                exit();
        }
        
        $stmt = $conn->prepare($query);
        $result = $stmt->execute([
            ':user_id' => $user_id,
            ':id_demande' => $id_demande
        ]);

        if ($result) {
            $_SESSION['success_message'] = "La demande a été mise à jour avec succès.";
        } else {
            $_SESSION['error_message'] = "Erreur lors de la mise à jour de la demande.";
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Une erreur est survenue : " . $e->getMessage();
    }
}

// Redirection vers la page des demandes
header('Location: divers.php');
exit();
?>

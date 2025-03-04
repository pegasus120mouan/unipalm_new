<?php
session_start();
require_once '../inc/functions/connexion.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Vous devez être connecté pour effectuer cette action.";
    header('Location: demande_attente.php');
    exit;
}

if (!isset($_POST['id_demande'])) {
    $_SESSION['error'] = "ID de demande manquant.";
    header('Location: demande_attente.php');
    exit;
}

try {
    $id_demande = intval($_POST['id_demande']);
    $user_id = $_SESSION['user_id'];
    $date_actuelle = date('Y-m-d H:i:s');

    // Vérifier si la demande existe et n'est pas déjà validée
    $check_sql = "SELECT id_demande FROM demande_sortie WHERE id_demande = ? AND date_approbation IS NULL";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->execute([$id_demande]);
    
    if ($check_stmt->rowCount() === 0) {
        $_SESSION['error'] = "Demande non trouvée ou déjà validée.";
        header('Location: demande_attente.php');
        exit;
    }

    // Mettre à jour la demande
    $sql = "UPDATE demande_sortie 
            SET date_approbation = ?, 
                statut = 'approuve', 
                approuve_par = ?,
                updated_at = NOW()
            WHERE id_demande = ?";
    
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute([$date_actuelle, $user_id, $id_demande]);

    if ($result) {
        $_SESSION['success'] = "La demande a été validée avec succès.";
    } else {
        $_SESSION['error'] = "Erreur lors de la validation de la demande.";
    }

} catch(Exception $e) {
    $_SESSION['error'] = "Erreur : " . $e->getMessage();
}

header('Location: demande_attente.php');
exit;
?>

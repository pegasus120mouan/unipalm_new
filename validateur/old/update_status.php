<?php
// Connexion à la base de données
require_once '../inc/functions/connexion.php';

// Vérifier si les données ont été envoyées en POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['userId']) && isset($_POST['status'])) {
    // Récupérer les données postées
    $userId = $_POST['userId'];
    $status = $_POST['status'];

    // Préparer la requête SQL
    $stmt = $conn->prepare("UPDATE utilisateurs SET statut_compte = :status WHERE id = :userId");

    // Liaison des paramètres
    $stmt->bindParam(':status', $status, PDO::PARAM_INT);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);

    // Exécuter la requête
    if ($stmt->execute()) {
        // Retourner une réponse JSON indiquant le succès
        echo json_encode(['success' => true]);
    } else {
        // Retourner une réponse JSON indiquant une erreur
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour du statut.']);
    }
} else {
    // Retourner une réponse JSON indiquant une erreur si les données POST sont absentes
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
}
?>

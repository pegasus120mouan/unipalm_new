<?php
session_start();
require_once '../inc/functions/connexion.php';

function genererNumeroDemande($conn) {
    $annee = date('Y');
    $mois = date('m');
    
    // Récupérer le dernier numéro pour ce mois
    $sql = "SELECT MAX(CAST(SUBSTRING_INDEX(numero_demande, '-', -1) AS UNSIGNED)) as dernier_numero 
            FROM demande_sortie 
            WHERE numero_demande LIKE :pattern";
    
    $stmt = $conn->prepare($sql);
    $pattern = "DEM-$annee$mois-%";
    $stmt->bindParam(':pattern', $pattern);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $numero = ($result['dernier_numero'] ?? 0) + 1;
    return "DEM-$annee$mois-" . str_pad($numero, 4, '0', STR_PAD_LEFT);
}

if (isset($_POST['saveDemande'])) {
    try {
        // Récupération des données du formulaire
        $motif = $_POST['motif'];
        $montant = $_POST['montant'];
        $statut = $_POST['statut'];
        $numero_demande = genererNumeroDemande($conn);
        
        // Date actuelle pour la demande
        $date_demande = date('Y-m-d H:i:s');

        // Préparation de la requête SQL
        $sql = "INSERT INTO demande_sortie (
            numero_demande,
            date_demande, 
            montant,
            motif, 
            statut,
            created_at
        ) VALUES (
            :numero_demande,
            :date_demande, 
            :montant,
            :motif, 
            :statut,
            NOW()
        )";

        $stmt = $conn->prepare($sql);

        // Liaison des paramètres
        $stmt->bindParam(':numero_demande', $numero_demande);
        $stmt->bindParam(':date_demande', $date_demande);
        $stmt->bindParam(':montant', $montant);
        $stmt->bindParam(':motif', $motif);
        $stmt->bindParam(':statut', $statut);

        // Exécution de la requête
        $stmt->execute();

        // Message de succès
        $_SESSION['success'] = "La demande de sortie N° $numero_demande a été enregistrée avec succès.";
        header('Location: demandes.php');
        exit();

    } catch(PDOException $e) {
        // En cas d'erreur
        $_SESSION['error'] = "Erreur lors de l'enregistrement de la demande : " . $e->getMessage();
        header('Location: demandes.php');
        exit();
    }
} else {
    // Si accès direct au fichier sans soumission de formulaire
    header('Location: demandes.php');
    exit();
}
?>

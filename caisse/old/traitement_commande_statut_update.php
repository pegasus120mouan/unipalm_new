<?php
// Connexion à la base de données
require_once '../inc/functions/connexion.php';
session_start(); // Ensure session is started

// Validate and sanitize inputs
$commande_id = filter_input(INPUT_POST, 'commande_id', FILTER_VALIDATE_INT);
$statut = filter_input(INPUT_POST, 'statut', FILTER_SANITIZE_STRING);

if ($commande_id === false || $statut === false) {
    // Handle validation error
    $_SESSION['error'] = "Invalid input data.";
    header('Location: commandes.php');
    exit(0);
}

try {
    // Begin a transaction
    $conn->beginTransaction();

    // Update the order status
    $sql = "UPDATE commandes SET statut = :statut WHERE id = :id";
    $requete = $conn->prepare($sql);
    $query_execute = $requete->execute([
        ':id' => $commande_id,
        ':statut' => $statut
    ]);

    if (!$query_execute) {
        throw new Exception("Failed to update the order status.");
    }

    // If the status is changed to "livrée", update the points_livreurs table
    if ($statut === "livrée") {
        // Retrieve the livreur_id and cout_livraison for the order
        $select_query = "SELECT utilisateur_id AS livreur_id, cout_livraison FROM commandes WHERE id = :id";
        $select_stmt = $conn->prepare($select_query);
        $select_stmt->execute([':id' => $commande_id]);
        $order = $select_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            throw new Exception("Order not found.");
        }

        $livreur_id = $order['livreur_id'];
        $cout_livraison = $order['cout_livraison'];
        $date = date("Y-m-d");

        // Check if record already exists in points_livreurs for the same day
        $check_query = "SELECT id FROM points_livreurs WHERE utilisateur_id = :utilisateur_id AND date_commande = :date_commande";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->execute([
            ':utilisateur_id' => $livreur_id,
            ':date_commande' => $date
        ]);
        $existing_record = $check_stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_record) {
            // Update the existing record
            $update_query = "UPDATE points_livreurs 
                             SET recette = recette + :recette, gain_jour = (recette + :recette) - depense 
                             WHERE utilisateur_id = :utilisateur_id AND date_commande = :date_commande";
            $update_stmt = $conn->prepare($update_query);
            $update_execute = $update_stmt->execute([
                ':recette' => $cout_livraison,
                ':utilisateur_id' => $livreur_id,
                ':date_commande' => $date
            ]);

            if (!$update_execute) {
                throw new Exception("Failed to update points_livreurs.");
            }
        } else {
            // Insert a new record
            $insert_query = "INSERT INTO points_livreurs (utilisateur_id, recette, depense, gain_jour, date_commande) 
                             VALUES (:utilisateur_id, :recette, 0, :recette, :date_commande)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_execute = $insert_stmt->execute([
                ':utilisateur_id' => $livreur_id,
                ':recette' => $cout_livraison,
                ':date_commande' => $date
            ]);

            if (!$insert_execute) {
                throw new Exception("Failed to insert into points_livreurs.");
            }
        }
    }

    // Commit the transaction
    $conn->commit();
    $_SESSION['popup'] = true;
    header('Location: commandes.php');
    exit(0);

} catch (Exception $e) {
    // Roll back the transaction if something failed
    $conn->rollBack();
    $_SESSION['error'] = "An error occurred: " . $e->getMessage();
    header('Location: commandes.php');
    exit(0);
}
?>

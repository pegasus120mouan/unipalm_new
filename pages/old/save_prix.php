<?php
require_once '../inc/functions/connexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_zones = $_GET['id'];
    
    $commune_destination = $_POST['liste_commune'];
    $cout_livraison = $_POST['cout_livraison'];

    try {
        // Begin a transaction
        $conn->beginTransaction();

        // Insert into prix table
        $query_prix = "INSERT INTO prix (montant, commune_id, zone_id) VALUES (:montant, :commune_id, :zone_id)";
        $query_run_prix = $conn->prepare($query_prix);

        $data_prix = [
            ':montant' => $cout_livraison,
            ':commune_id' => $commune_destination,
            ':zone_id' => $id_zones,
        ];

        $query_run_prix->execute($data_prix);

        // Insert into communes_zones table
        $query_communes_zones = "INSERT INTO communes_zones (commune_id, zone_id) VALUES (:commune_id, :zone_id)";
        $query_run_communes_zones = $conn->prepare($query_communes_zones);

        $data_communes_zones = [
            ':commune_id' => $commune_destination,
            ':zone_id' => $id_zones,
        ];

        $query_run_communes_zones->execute($data_communes_zones);

        // Commit the transaction
        $conn->commit();

        $_SESSION['popup'] = true;
        header('Location: prix_zones.php?id='.$id_zones);
        exit(0);
    } catch (PDOException $e) {
        // An error occurred, rollback the transaction
        $conn->rollBack();

        $_SESSION['delete_pop'] = true;
        header('Location: prix_zones.php?id='.$id_zones);
        exit(0);
    }
}
?>

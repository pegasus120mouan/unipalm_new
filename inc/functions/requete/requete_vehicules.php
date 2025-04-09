<?php

function getVehicules($conn) {
    $stmt = $conn->prepare(
        "SELECT vehicules_id, matricule_vehicule FROM vehicules"
    );

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>
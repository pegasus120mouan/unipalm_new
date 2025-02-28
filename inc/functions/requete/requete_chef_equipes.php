<?php

function getChefEquipes($conn) {
    $stmt = $conn->prepare(
        "SELECT id_chef, CONCAT(nom, ' ', prenoms) AS chef_nom_complet FROM chef_equipe"
    );

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getChefEquipesFull($conn) {
    $stmt = $conn->prepare(
        "SELECT * FROM chef_equipe"
    );

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>
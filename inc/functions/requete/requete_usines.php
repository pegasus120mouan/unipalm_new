<?php

function getUsines($conn) {
    $stmt = $conn->prepare(
        "SELECT id_usine, nom_usine from usines"
    );

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getMontantUsines($conn) {
    $stmt = $conn->prepare(
        "SELECT u.id_usine, u.nom_usine, 
        COALESCE(SUM(t.montant_paie), 0) as montant_total,
        u.montant_paye,
        COALESCE(SUM(t.montant_paie), 0) - u.montant_paye as montant_restant,
        u.derniere_date_paiement, u.created_at 
        FROM usines u 
        LEFT JOIN tickets t ON u.id_usine = t.id_usine 
        GROUP BY u.id_usine, u.nom_usine, u.montant_paye, u.derniere_date_paiement, u.created_at
        ORDER BY u.nom_usine ASC"
    );

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUsineDetails($conn, $id_usine) {
    $stmt = $conn->prepare(
        "SELECT u.id_usine, u.nom_usine, 
        COALESCE(SUM(t.montant_paie), 0) as montant_total,
        u.montant_paye,
        COALESCE(SUM(t.montant_paie), 0) - u.montant_paye as montant_restant,
        u.derniere_date_paiement, u.created_at 
        FROM usines u 
        LEFT JOIN tickets t ON u.id_usine = t.id_usine 
        WHERE u.id_usine = :id_usine
        GROUP BY u.id_usine, u.nom_usine, u.montant_paye, u.derniere_date_paiement, u.created_at"
    );

    $stmt->execute([':id_usine' => $id_usine]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getHistoriquePaiements($conn, $id_usine) {
    $sql = "SELECT h.*, 
            CONCAT(u.nom, ' ', u.prenoms) as agent_name 
            FROM historique_paiements h
            LEFT JOIN utilisateurs u ON h.created_by = u.id
            WHERE h.id_usine = :id_usine 
            ORDER BY h.created_at DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id_usine' => $id_usine]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getHistoriquePaiementsOld($conn, $id_usine) {
    $stmt = $conn->prepare(
        "SELECT h.*, u.nom_usine, CONCAT(ut.nom, ' ', ut.prenoms) as agent_name
        FROM historique_paiements h
        JOIN usines u ON h.id_usine = u.id_usine
        LEFT JOIN utilisateurs ut ON h.created_by = ut.id
        WHERE h.id_usine = :id_usine
        ORDER BY h.date_paiement DESC"
    );

    $stmt->execute([':id_usine' => $id_usine]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>
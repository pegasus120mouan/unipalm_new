<?php

function getTransactions($conn) {
    try {
        $sql = "SELECT 
                    t.*,
                    CONCAT(u.nom, ' ', u.prenoms) as nom_utilisateur
                FROM 
                    transactions t
                LEFT JOIN 
                    utilisateurs u ON t.id_utilisateur = u.id
                ORDER BY 
                    t.date_transaction DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
    }
}

function insertTransaction($conn, $type_transaction, $montant, $description, $id_utilisateur) {
    try {
        $sql = "INSERT INTO transactions (type_transaction, montant, description, id_utilisateur, date_transaction) 
                VALUES (:type_transaction, :montant, :description, :id_utilisateur, NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':type_transaction' => $type_transaction,
            ':montant' => $montant,
            ':description' => $description,
            ':id_utilisateur' => $id_utilisateur
        ]);
        
        return true;
    } catch(PDOException $e) {
        return false;
    }
}

function getTransactionsByPeriod($conn, $date_debut, $date_fin) {
    try {
        $sql = "SELECT 
                    t.*,
                    CONCAT(u.nom, ' ', u.prenoms) as nom_utilisateur
                FROM 
                    transactions t
                LEFT JOIN 
                    utilisateurs u ON t.id_utilisateur = u.id
                WHERE 
                    DATE(t.date_transaction) BETWEEN :date_debut AND :date_fin
                ORDER BY 
                    t.date_transaction ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':date_debut' => $date_debut,
            ':date_fin' => $date_fin
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
    }
}

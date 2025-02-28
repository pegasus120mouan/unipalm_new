<?php
require_once dirname(__FILE__) . '/../../functions/connexion.php';

// Fonction pour créer un nouveau prix unitaire
function createPrixUnitaire($conn, $id_usine, $prix, $date_debut, $date_fin = null) {
    try {
        $sql = "INSERT INTO prix_unitaires (id_usine, prix, date_debut, date_fin) VALUES (:id_usine, :prix, :date_debut, :date_fin)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id_usine', $id_usine, PDO::PARAM_INT);
        $stmt->bindParam(':prix', $prix);
        $stmt->bindParam(':date_debut', $date_debut);
        $stmt->bindParam(':date_fin', $date_fin);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Erreur lors de la création du prix unitaire: " . $e->getMessage());
        return false;
    }
}

// Fonction pour mettre à jour un prix unitaire
function updatePrixUnitaire($conn, $id, $id_usine, $prix, $date_debut, $date_fin = null) {
    try {
        $sql = "UPDATE prix_unitaires SET id_usine = :id_usine, prix = :prix, date_debut = :date_debut, date_fin = :date_fin WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':id_usine', $id_usine, PDO::PARAM_INT);
        $stmt->bindParam(':prix', $prix);
        $stmt->bindParam(':date_debut', $date_debut);
        $stmt->bindParam(':date_fin', $date_fin);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Erreur lors de la mise à jour du prix unitaire: " . $e->getMessage());
        return false;
    }
}

// Fonction pour supprimer un prix unitaire
function deletePrixUnitaire($conn, $id) {
    try {
        $sql = "DELETE FROM prix_unitaires WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Erreur lors de la suppression du prix unitaire: " . $e->getMessage());
        return false;
    }
}

// Fonction pour récupérer un prix unitaire par son ID
function getPrixUnitaireById($conn, $id) {
    try {
        $sql = "SELECT * FROM prix_unitaires WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération du prix unitaire: " . $e->getMessage());
        return false;
    }
}

// Fonction pour récupérer tous les prix unitaires
function getAllPrixUnitaires($conn) {
    try {
        $sql = "SELECT prix_unitaires.*, usines.nom_usine 
                FROM prix_unitaires 
                INNER JOIN usines ON prix_unitaires.id_usine = usines.id_usine 
                ORDER BY prix_unitaires.date_debut DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération des prix unitaires: " . $e->getMessage());
        return false;
    }
}

// Fonction pour récupérer le dernier prix unitaire d'une usine
function getLastPrixUnitaire($conn, $id_usine) {
    try {
        $sql = "SELECT prix 
                FROM prix_unitaires 
                WHERE id_usine = :id_usine 
                ORDER BY date_debut DESC 
                LIMIT 1";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id_usine', $id_usine, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['prix'] : 0;
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération du dernier prix unitaire: " . $e->getMessage());
        return 0;
    }
}

// Fonction pour récupérer le prix unitaire en fonction de la date et de l'usine
function getPrixUnitaireByDateAndUsine($conn, $date_ticket, $id_usine) {
    try {
        // Vérifier si la date du ticket correspond à une période de prix unitaire pour cette usine
        $sql = "SELECT prix 
                FROM prix_unitaires 
                WHERE id_usine = :id_usine 
                AND :date_ticket BETWEEN date_debut AND COALESCE(date_fin, :date_ticket)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id_usine', $id_usine, PDO::PARAM_INT);
        $stmt->bindParam(':date_ticket', $date_ticket);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            return ['prix' => $result['prix'], 'is_default' => false];
        }
        
        // Si aucun prix n'est trouvé, retourner la valeur par défaut (0.00)
        return ['prix' => 0.00, 'is_default' => true];
        
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération du prix unitaire: " . $e->getMessage());
        return ['prix' => 0.00, 'is_default' => true];
    }
}
?>

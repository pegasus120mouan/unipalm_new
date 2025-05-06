<?php
require_once 'connexion.php';

function updateTicketsFromPrixUnitaire() {
    global $conn;
    
    try {
        $conn->beginTransaction();
        
        // Mettre à jour tous les tickets non soldés avec le prix unitaire de leur période
        $stmt = $conn->prepare("
            UPDATE tickets t
            INNER JOIN prix_unitaires pu ON 
                t.id_usine = pu.id_usine AND 
                t.date_ticket BETWEEN pu.date_debut AND pu.date_fin
            SET 
                t.prix_unitaire = pu.prix,
                t.montant_paie = t.poids * pu.prix,
                t.montant_reste = (t.poids * pu.prix) - COALESCE(t.montant_payer, 0),
                t.updated_at = NOW()
            WHERE 
                t.statut_ticket = 'non soldé' AND
                pu.prix > 0 AND
                (t.prix_unitaire != pu.prix OR t.prix_unitaire IS NULL)
        ");
        
        $stmt->execute();
        $updated_count = $stmt->rowCount();
        
        if ($updated_count > 0) {
            writeLog("Mise à jour automatique de " . $updated_count . " ticket(s) avec les prix unitaires correspondants à leurs dates");
        }
        
        $conn->commit();
        return [
            'success' => true,
            'message' => $updated_count . " ticket(s) mis à jour avec les prix unitaires correspondants",
            'updated_count' => $updated_count
        ];
        
    } catch (Exception $e) {
        $conn->rollBack();
        writeLog("Erreur lors de la mise à jour automatique des prix unitaires: " . $e->getMessage());
        return [
            'success' => false,
            'message' => "Erreur lors de la mise à jour : " . $e->getMessage(),
            'updated_count' => 0
        ];
    }
}

// Fonction pour mettre à jour un ticket spécifique
function updateSpecificTicketPrixUnitaire($id_ticket) {
    global $conn;
    
    try {
        $conn->beginTransaction();
        
        // Mettre à jour un ticket spécifique
        $stmt = $conn->prepare("
            UPDATE tickets t
            INNER JOIN prix_unitaires pu ON 
                t.id_usine = pu.id_usine AND 
                t.date_ticket BETWEEN pu.date_debut AND pu.date_fin
            SET 
                t.prix_unitaire = pu.prix,
                t.montant_paie = t.poids * pu.prix,
                t.montant_reste = (t.poids * pu.prix) - COALESCE(t.montant_payer, 0),
                t.updated_at = NOW()
            WHERE 
                t.id_ticket = :id_ticket AND
                t.statut_ticket = 'non soldé' AND
                pu.prix > 0 AND
                (t.prix_unitaire != pu.prix OR t.prix_unitaire IS NULL)
        ");
        
        $stmt->execute(['id_ticket' => $id_ticket]);
        $updated = $stmt->rowCount() > 0;
        
        if ($updated) {
            writeLog("Mise à jour du prix unitaire pour le ticket #" . $id_ticket);
            $conn->commit();
            return [
                'success' => true,
                'message' => "Prix unitaire mis à jour avec succès pour le ticket #" . $id_ticket
            ];
        } else {
            $conn->rollBack();
            return [
                'success' => false,
                'message' => "Aucun prix unitaire correspondant trouvé pour ce ticket ou le prix est déjà à jour"
            ];
        }
        
    } catch (Exception $e) {
        $conn->rollBack();
        writeLog("Erreur lors de la mise à jour du prix unitaire du ticket #" . $id_ticket . ": " . $e->getMessage());
        return [
            'success' => false,
            'message' => "Erreur lors de la mise à jour : " . $e->getMessage()
        ];
    }
}

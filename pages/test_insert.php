<?php
require_once '../inc/functions/connexion.php';

try {
    $sql = "INSERT INTO bordereau (numero_bordereau, id_agent, date_debut, date_fin, poids_total, montant_total)
            SELECT 
                CONCAT('BORD-', DATE_FORMAT(NOW(), '%Y%m%d-%H%i%s'), '-', FLOOR(RAND() * 9000) + 1000), 
                125, 
                '2025-02-01', 
                '2025-02-28', 
                COALESCE(SUM(t.poids), 0), 
                COALESCE(SUM(t.prix_unitaire * t.poids), 0)
            FROM tickets t 
            WHERE t.id_agent = 125 
            AND t.created_at BETWEEN '2025-02-01' AND '2025-02-28'";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    $id_bordereau = $conn->lastInsertId();
    
    echo "Bordereau créé avec succès ! ID: " . $id_bordereau . "\n";
    
    // Afficher les détails du bordereau créé
    if ($id_bordereau) {
        $sql = "SELECT * FROM bordereau WHERE id_bordereau = :id_bordereau";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id_bordereau', $id_bordereau);
        $stmt->execute();
        $bordereau = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "\nDétails du bordereau :\n";
        print_r($bordereau);
    }

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}
?>

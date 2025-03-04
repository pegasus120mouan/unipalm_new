<?php
require_once '../inc/functions/connexion.php';

try {
    // Vérifier les tickets existants sans filtre de date
    $sql = "SELECT t.*, a.nom, a.prenom
            FROM tickets t 
            INNER JOIN agents a ON t.id_agent = a.id_agent
            WHERE t.id_agent = :id_agent";

    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':id_agent', 125);
    $stmt->execute();
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Tous les tickets pour l'agent 125 (" . count($tickets) . ") :\n";
    foreach ($tickets as $ticket) {
        echo "ID: " . $ticket['id_ticket'] . 
             " | Date ticket: " . $ticket['date_ticket'] . 
             " | Created at: " . $ticket['created_at'] . 
             " | Agent: " . $ticket['nom'] . " " . $ticket['prenom'] .
             " | Statut: " . $ticket['statut_ticket'] . "\n";
    }

    // Afficher la structure de la table tickets
    $sql = "DESCRIBE tickets";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nStructure de la table tickets :\n";
    foreach ($columns as $column) {
        echo $column['Field'] . " | " . $column['Type'] . " | " . $column['Null'] . " | " . $column['Key'] . " | " . $column['Default'] . "\n";
    }

} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>

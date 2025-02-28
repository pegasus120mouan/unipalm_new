<?php
require_once '../inc/functions/connexion.php';

if (isset($_GET['term'])) {
    $term = $_GET['term'];
    
    $sql = "SELECT id_agent, CONCAT(nom, ' ', prenom) as nom_complet_agent 
            FROM agents 
            WHERE CONCAT(nom, ' ', prenom) LIKE :term 
            ORDER BY nom_complet_agent 
            LIMIT 10";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute(['term' => '%' . $term . '%']);
    $agents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($agents);
}
?>

<?php
     
function getAgents($conn) {
    $stmt = $conn->prepare(
        "SELECT 
    agents.id_agent,
    agents.nom AS nom_agent,
    agents.prenom AS prenom_agent,
    CONCAT(agents.nom, ' ', agents.prenom) AS nom_complet_agent,
    CONCAT(chef_equipe.nom, ' ', chef_equipe.prenoms) AS chef_equipe,
    CONCAT(utilisateurs.nom, ' ', utilisateurs.prenoms) AS utilisateur_createur,
    agents.contact,
    agents.date_ajout,
    agents.date_modification
FROM 
    agents
LEFT JOIN 
    chef_equipe ON agents.id_chef = chef_equipe.id_chef
LEFT JOIN 
    utilisateurs ON agents.cree_par = utilisateurs.id ORDER BY date_ajout DESC"
    );

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllAgentsForSelect($conn) {
    $stmt = $conn->prepare(
        "SELECT 
            agents.id_agent,
            CONCAT(agents.nom, ' ', agents.prenom) AS nom_complet_agent
        FROM 
            agents
        ORDER BY 
            agents.nom, agents.prenom"
    );
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAgentsByChef($conn, $id_chef) {
    $stmt = $conn->prepare(
        "SELECT 
            agents.id_agent,
            CONCAT(agents.nom, ' ', agents.prenom) AS nom_complet_agent
        FROM 
            agents
        WHERE 
            agents.id_chef = :id_chef
        ORDER BY 
            agents.nom, agents.prenom"
    );
    
    $stmt->bindParam(':id_chef', $id_chef, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
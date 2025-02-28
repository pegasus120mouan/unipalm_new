<?php
     
function getAgents($conn) {
    $stmt = $conn->prepare(
        "SELECT 
    agents.id_agent,
    agents.nom AS nom_agent,
    agents.prenom AS prenom_agent,
    CONCAT(agents.nom, ' ', agents.prenom) AS nom_complet_agent, -- Concatenation du nom et prénom de l'agent
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

?>
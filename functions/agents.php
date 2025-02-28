<?php
require_once '../connexion.php';

function getAgents($agentName = null, $agentPrenom = null, $chefId = null)
{
    try {
        // Récupération de la connexion depuis connexion.php
        $pdo = getConnexion();

        // Requête SQL de base
        $sql = "SELECT 
                    id_agent, nom, prenom, contact, id_chef, date_ajout, 
                    date_modification, date_suppression, cree_par
                FROM agents
                WHERE date_suppression IS NULL";

        // Tableau pour les filtres
        $conditions = [];
        $parameters = [];

        // Ajout des filtres dynamiques
        if ($agentName) {
            $conditions[] = "nom LIKE :agentName";
            $parameters[':agentName'] = "%$agentName%";
        }
        if ($agentPrenom) {
            $conditions[] = "prenom LIKE :agentPrenom";
            $parameters[':agentPrenom'] = "%$agentPrenom%";
        }
        if ($chefId) {
            $conditions[] = "id_chef = :chefId";
            $parameters[':chefId'] = $chefId;
        }

        // Ajout des conditions à la requête
        if (!empty($conditions)) {
            $sql .= " AND " . implode(" AND ", $conditions);
        }

        // Préparation et exécution de la requête
        $stmt = $pdo->prepare($sql);
        $stmt->execute($parameters);

        // Récupération des résultats
        return $stmt->fetchAll();

    } catch (PDOException $e) {
        // Gestion des erreurs
        die("Erreur : " . $e->getMessage());
    }

    return $stmt->fetchAll() ?: [];
}

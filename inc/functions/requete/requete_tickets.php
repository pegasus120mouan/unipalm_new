<?php

function getTickets($conn, $filters = []) {
    $where_conditions = [];
    $params = [];

    // Filtre par agent
    if (!empty($filters['agent'])) {
        $where_conditions[] = "t.id_agent = :agent";
        $params[':agent'] = $filters['agent'];
    }

    // Filtre par usine
    if (!empty($filters['usine'])) {
        $where_conditions[] = "t.id_usine = :usine";
        $params[':usine'] = $filters['usine'];
    }

    // Filtre par véhicule
    if (!empty($filters['vehicule'])) {
        $where_conditions[] = "t.vehicule_id = :vehicule";
        $params[':vehicule'] = $filters['vehicule'];
    }

    // Filtre par numéro de ticket
    if (!empty($filters['numero_ticket'])) {
        $where_conditions[] = "t.numero_ticket = :numero_ticket";
        $params[':numero_ticket'] = $filters['numero_ticket'];
    }

    // Filtre par date
    if (!empty($filters['date_debut']) && !empty($filters['date_fin'])) {
        $where_conditions[] = "DATE(t.date_ticket) BETWEEN :date_debut AND :date_fin";
        $params[':date_debut'] = $filters['date_debut'];
        $params[':date_fin'] = $filters['date_fin'];
    } elseif (!empty($filters['date_debut'])) {
        $where_conditions[] = "DATE(t.date_ticket) >= :date_debut";
        $params[':date_debut'] = $filters['date_debut'];
    } elseif (!empty($filters['date_fin'])) {
        $where_conditions[] = "DATE(t.date_ticket) <= :date_fin";
        $params[':date_fin'] = $filters['date_fin'];
    }

    $sql = "SELECT 
        t.*,
        CONCAT(a.nom, ' ', a.prenom) as nom_complet_agent,
        a.id_agent,
        CONCAT(u.nom, ' ', u.prenoms) AS utilisateur_nom_complet,
        v.matricule_vehicule,
        us.nom_usine,
        us.id_usine
    FROM 
        tickets t
    LEFT JOIN
        utilisateurs u ON t.id_utilisateur = u.id
    LEFT JOIN 
        agents a ON t.id_agent = a.id_agent
    LEFT JOIN 
        vehicules v ON t.vehicule_id = v.vehicules_id
    LEFT JOIN 
        usines us ON t.id_usine = us.id_usine";

    if (!empty($where_conditions)) {
        $sql .= " WHERE " . implode(" AND ", $where_conditions);
    }

    $sql .= " ORDER BY t.date_ticket DESC";

    try {
        $stmt = $conn->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur dans getTickets: " . $e->getMessage());
        return [];
    }
}

function getTicketsJour($conn, $agent_id = null, $usine_id = null, $date_debut = null, $date_fin = null, $numero_ticket = null) {
    $sql = "SELECT t.*, 
            CONCAT(u.nom, ' ', u.prenoms) AS utilisateur_nom_complet,
            u.contact AS utilisateur_contact,
            u.role AS utilisateur_role,
            v.matricule_vehicule,
            CONCAT(a.nom, ' ', a.prenom) AS agent_nom_complet,
            us.nom_usine,
            us.id_usine
            FROM tickets t
            INNER JOIN utilisateurs u ON t.id_utilisateur = u.id
            INNER JOIN vehicules v ON t.vehicule_id = v.vehicules_id
            INNER JOIN agents a ON t.id_agent = a.id_agent
            INNER JOIN usines us ON t.id_usine = us.id_usine
            WHERE DATE(t.created_at) = CURDATE()";

    if ($agent_id) {
        $sql .= " AND t.id_agent = :agent_id";
    }
    
    if ($usine_id) {
        $sql .= " AND t.id_usine = :usine_id";
    }

    if ($date_debut) {
        $sql .= " AND DATE(t.created_at) >= :date_debut";
    }

    if ($date_fin) {
        $sql .= " AND DATE(t.created_at) <= :date_fin";
    }

    if ($numero_ticket) {
        $sql .= " AND t.numero_ticket LIKE :numero_ticket";
    }

    $sql .= " ORDER BY t.created_at DESC";

    try {
        $stmt = $conn->prepare($sql);
        
        if ($agent_id) {
            $stmt->bindValue(':agent_id', $agent_id, PDO::PARAM_INT);
        }
        if ($usine_id) {
            $stmt->bindValue(':usine_id', $usine_id, PDO::PARAM_INT);
        }
        if ($date_debut) {
            $stmt->bindValue(':date_debut', $date_debut, PDO::PARAM_STR);
        }
        if ($date_fin) {
            $stmt->bindValue(':date_fin', $date_fin, PDO::PARAM_STR);
        }
        if ($numero_ticket) {
            $stmt->bindValue(':numero_ticket', '%' . $numero_ticket . '%', PDO::PARAM_STR);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur dans getTicketsJour: " . $e->getMessage());
        return array();
    }
}

function getTicketsAttente($conn, $agent_id = null, $usine_id = null, $date_debut = null, $date_fin = null, $numero_ticket = null) {
    $sql = "SELECT t.*, 
            CONCAT(u.nom, ' ', u.prenoms) AS utilisateur_nom_complet,
            u.contact AS utilisateur_contact,
            u.role AS utilisateur_role,
            0 AS montant_total,
            v.matricule_vehicule,
            CONCAT(a.nom, ' ', a.prenom) AS agent_nom_complet,
            us.nom_usine,
            us.id_usine
            FROM tickets t
            INNER JOIN utilisateurs u ON t.id_utilisateur = u.id
            INNER JOIN vehicules v ON t.vehicule_id = v.vehicules_id
            INNER JOIN agents a ON t.id_agent = a.id_agent
            INNER JOIN usines us ON t.id_usine = us.id_usine
            WHERE t.date_validation_boss IS NULL";

    if ($agent_id) {
        $sql .= " AND t.id_agent = :agent_id";
    }
    
    if ($usine_id) {
        $sql .= " AND t.id_usine = :usine_id";
    }

    if ($date_debut) {
        $sql .= " AND DATE(t.created_at) >= :date_debut";
    }

    if ($date_fin) {
        $sql .= " AND DATE(t.created_at) <= :date_fin";
    }
    
    if ($numero_ticket) {
        $sql .= " AND t.numero_ticket LIKE :numero_ticket";
    }

    $sql .= " ORDER BY t.created_at DESC";

    try {
        $stmt = $conn->prepare($sql);
        
        if ($agent_id) {
            $stmt->bindValue(':agent_id', $agent_id, PDO::PARAM_INT);
        }
        if ($usine_id) {
            $stmt->bindValue(':usine_id', $usine_id, PDO::PARAM_INT);
        }
        if ($date_debut) {
            $stmt->bindValue(':date_debut', $date_debut, PDO::PARAM_STR);
        }
        if ($date_fin) {
            $stmt->bindValue(':date_fin', $date_fin, PDO::PARAM_STR);
        }
        if ($numero_ticket) {
            $stmt->bindValue(':numero_ticket', '%' . $numero_ticket . '%', PDO::PARAM_STR);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur dans getTicketsAttente: " . $e->getMessage());
        return array();
    }
}

function getTicketsNonAssigne($conn, $agent_id = null, $usine_id = null, $date_debut = null, $date_fin = null, $numero_ticket = null, $numero_bordereau = null) {
    $sql = "SELECT 
            t.*,
            u.nom_usine,
            v.matricule_vehicule,
            v.type_vehicule,
            CONCAT(COALESCE(a.nom, ''), ' ', COALESCE(a.prenom, '')) AS nom_complet_agent,
            DATE(t.date_ticket) as date_ticket_only,
            DATE(t.created_at) as date_reception,
            CAST(t.poids AS DECIMAL(10,2)) as poids,
            CAST(t.prix_unitaire AS DECIMAL(10,2)) as prix_unitaire,
            CAST((t.poids * t.prix_unitaire) AS DECIMAL(15,2)) as montant_total
            FROM tickets t
            INNER JOIN usines u ON t.id_usine = u.id_usine
            INNER JOIN vehicules v ON t.vehicule_id = v.vehicules_id
            INNER JOIN agents a ON t.id_agent = a.id_agent
            WHERE 1=1
            AND t.date_validation_boss IS NOT NULL
            AND t.prix_unitaire > 0";

    if ($numero_bordereau) {
        $sql .= " AND t.numero_bordereau = :numero_bordereau";
    } else {
        $sql .= " AND t.numero_bordereau IS NULL";
    }

    if ($agent_id) {
        $sql .= " AND t.id_agent = :agent_id";
    }
    
    if ($usine_id) {
        $sql .= " AND t.id_usine = :usine_id";
    }

    if ($date_debut) {
        $sql .= " AND t.created_at >= :date_debut";
    }

    if ($date_fin) {
        $sql .= " AND t.created_at <= :date_fin";
    }
    
    if ($numero_ticket) {
        $sql .= " AND t.numero_ticket LIKE :numero_ticket";
    }

    $sql .= " ORDER BY u.nom_usine ASC, t.date_ticket ASC, t.created_at ASC";

    try {
        $stmt = $conn->prepare($sql);
        
        if ($numero_bordereau) {
            $stmt->bindValue(':numero_bordereau', $numero_bordereau, PDO::PARAM_STR);
        }
        
        if ($agent_id) {
            $stmt->bindValue(':agent_id', $agent_id, PDO::PARAM_INT);
        }
        if ($usine_id) {
            $stmt->bindValue(':usine_id', $usine_id, PDO::PARAM_INT);
        }
        if ($date_debut) {
            $stmt->bindValue(':date_debut', $date_debut, PDO::PARAM_STR);
        }
        if ($date_fin) {
            $stmt->bindValue(':date_fin', $date_fin, PDO::PARAM_STR);
        }
        if ($numero_ticket) {
            $stmt->bindValue(':numero_ticket', '%' . $numero_ticket . '%', PDO::PARAM_STR);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur dans getTicketsNonAssigne: " . $e->getMessage());
        return array();
    }
}

function getTicketsAttenteByUsine($conn, $id_usine) {
    $sql = "SELECT 
        t.id_ticket,
        t.date_ticket,
        t.numero_ticket,
        t.poids,
        t.prix_unitaire,
        t.date_validation_boss,
        t.montant_paie,
        t.date_paie,
        t.montant_payer,
        t.montant_reste,
        t.created_at,
        CONCAT(u.nom, ' ', u.prenoms) AS utilisateur_nom_complet,
        v.matricule_vehicule,
        CONCAT(a.nom, ' ', a.prenom) AS agent_nom_complet,
        us.nom_usine
    FROM 
        tickets t
    INNER JOIN 
        utilisateurs u ON t.id_utilisateur = u.id
    LEFT JOIN 
        vehicules v ON t.vehicule_id = v.vehicules_id
    LEFT JOIN 
        agents a ON t.id_agent = a.id_agent
    INNER JOIN 
        usines us ON t.id_usine = us.id_usine
    WHERE 
        t.date_validation_boss IS NULL
        AND t.id_usine = :id_usine
    ORDER BY 
        t.date_ticket DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':id_usine' => $id_usine]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTicketsValides($conn, $agent_id = null, $usine_id = null, $date_debut = null, $date_fin = null) {
    $sql = "SELECT 
            t.*, 
            CONCAT(u.nom, ' ', u.prenoms) AS utilisateur_nom_complet,
            u.contact AS utilisateur_contact,
            u.role AS utilisateur_role,
            v.matricule_vehicule,
            CONCAT(a.nom, ' ', a.prenom) AS agent_nom_complet,
            us.nom_usine,
            us.id_usine
            FROM tickets t
            INNER JOIN utilisateurs u ON t.id_utilisateur = u.id
            INNER JOIN vehicules v ON t.vehicule_id = v.vehicules_id
            INNER JOIN agents a ON t.id_agent = a.id_agent
            INNER JOIN usines us ON t.id_usine = us.id_usine
            WHERE t.date_validation_boss IS NOT NULL";

    if ($agent_id) {
        $sql .= " AND t.id_agent = :agent_id";
    }
    
    if ($usine_id) {
        $sql .= " AND t.id_usine = :usine_id";
    }

    if ($date_debut) {
        $sql .= " AND DATE(t.created_at) >= :date_debut";
    }

    if ($date_fin) {
        $sql .= " AND DATE(t.created_at) <= :date_fin";
    }

    $sql .= " ORDER BY t.date_validation_boss DESC";

    try {
        $stmt = $conn->prepare($sql);
        
        if ($agent_id) {
            $stmt->bindValue(':agent_id', $agent_id, PDO::PARAM_INT);
        }
        if ($usine_id) {
            $stmt->bindValue(':usine_id', $usine_id, PDO::PARAM_INT);
        }
        if ($date_debut) {
            $stmt->bindValue(':date_debut', $date_debut, PDO::PARAM_STR);
        }
        if ($date_fin) {
            $stmt->bindValue(':date_fin', $date_fin, PDO::PARAM_STR);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur dans getTicketsValides: " . $e->getMessage());
        return array();
    }
}

function getTicketsPayes($conn, $agent_id = null, $usine_id = null, $date_debut = null, $date_fin = null, $numero_ticket = null) {
    $sql = "SELECT 
        t.id_ticket,
        t.date_ticket,
        t.numero_ticket,
        t.poids,
        t.prix_unitaire,
        t.date_validation_boss,
        t.montant_paie,
        t.date_paie,
        t.montant_payer,
        (t.montant_paie - COALESCE(t.montant_payer, 0)) as montant_reste,
        CONCAT(u.nom, ' ', u.prenoms) AS utilisateur_nom_complet,
        u.contact AS utilisateur_contact,
        u.role AS utilisateur_role,
        v.matricule_vehicule,
        CONCAT(a.nom, ' ', a.prenom) AS agent_nom_complet,
        us.nom_usine
    FROM 
        tickets t
    INNER JOIN 
        utilisateurs u ON t.id_utilisateur = u.id
    INNER JOIN 
        vehicules v ON t.vehicule_id = v.vehicules_id
    INNER JOIN 
        agents a ON t.id_agent = a.id_agent
    INNER JOIN 
        usines us ON t.id_usine = us.id_usine
    WHERE t.date_paie IS NOT NULL AND DATE(t.date_ticket) IS NOT NULL";

    if ($agent_id) {
        $sql .= " AND t.id_agent = :agent_id";
    }
    
    if ($usine_id) {
        $sql .= " AND t.id_usine = :usine_id";
    }

    if ($date_debut) {
        $sql .= " AND DATE(t.date_ticket) >= :date_debut";
    }

    if ($date_fin) {
        $sql .= " AND DATE(t.date_ticket) <= :date_fin";
    }

    if ($numero_ticket) {
        $sql .= " AND t.numero_ticket LIKE :numero_ticket";
    }

    $sql .= " ORDER BY t.date_ticket DESC";

    try {
        $stmt = $conn->prepare($sql);
        
        if ($agent_id) {
            $stmt->bindValue(':agent_id', $agent_id, PDO::PARAM_INT);
        }
        if ($usine_id) {
            $stmt->bindValue(':usine_id', $usine_id, PDO::PARAM_INT);
        }
        if ($date_debut) {
            $stmt->bindValue(':date_debut', $date_debut, PDO::PARAM_STR);
        }
        if ($date_fin) {
            $stmt->bindValue(':date_fin', $date_fin, PDO::PARAM_STR);
        }
        if ($numero_ticket) {
            $stmt->bindValue(':numero_ticket', '%' . $numero_ticket . '%', PDO::PARAM_STR);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur dans getTicketsPayes: " . $e->getMessage());
        return array();
    }
}

function getTicketsNonSoldes($conn) {
    $stmt = $conn->prepare(
        "SELECT 
            t.id_ticket,
            t.date_ticket,
            t.numero_ticket,
            t.poids,
            t.prix_unitaire,
            t.date_validation_boss,
            t.montant_paie,
            t.date_paie,
            t.montant_payer,
            t.montant_reste,
            t.created_at,
            CONCAT(u.nom, ' ', u.prenoms) AS utilisateur_nom_complet,
            u.contact AS utilisateur_contact,
            u.role AS utilisateur_role,
            v.matricule_vehicule,
            CONCAT(a.nom, ' ', a.prenom) AS agent_nom_complet,
            us.nom_usine
        FROM 
            tickets t
        INNER JOIN 
            utilisateurs u ON t.id_utilisateur = u.id
        INNER JOIN 
            vehicules v ON t.vehicule_id = v.vehicules_id
        INNER JOIN 
            agents a ON t.id_agent = a.id_agent
        INNER JOIN 
            usines us ON t.id_usine = us.id_usine
        WHERE 
            t.montant_reste > 0
            AND t.prix_unitaire IS NOT NULL
            AND t.prix_unitaire > 0
        ORDER BY 
            t.created_at DESC"
    );
    $stmt->execute();
    return $stmt->fetchAll();
}

function getTicketsSoldes($conn) {
    $stmt = $conn->prepare(
        "SELECT 
            t.id_ticket,
            t.date_ticket,
            t.numero_ticket,
            t.poids,
            t.prix_unitaire,
            t.date_validation_boss,
            t.montant_paie,
            t.montant_payer,
            t.montant_reste,
            t.date_paie,
            t.created_at,
            CONCAT(u.nom, ' ', u.prenoms) AS utilisateur_nom_complet,
            u.contact AS utilisateur_contact,
            u.role AS utilisateur_role,
            v.matricule_vehicule,
            CONCAT(a.nom, ' ', a.prenom) AS agent_nom_complet,
            us.nom_usine
        FROM 
            tickets t
        INNER JOIN 
            utilisateurs u ON t.id_utilisateur = u.id
        INNER JOIN 
            vehicules v ON t.vehicule_id = v.vehicules_id
        INNER JOIN 
            agents a ON t.id_agent = a.id_agent
        INNER JOIN 
            usines us ON t.id_usine = us.id_usine
        WHERE 
            t.montant_reste = 0
            AND t.prix_unitaire IS NOT NULL
            AND t.prix_unitaire > 0
        ORDER BY 
            t.created_at DESC"
    );
    $stmt->execute();
    return $stmt->fetchAll();
}

function insertTicket($conn, $id_usine, $date_ticket, $id_agent, $numero_ticket, $vehicule_id, $poids, $id_utilisateur, $prix_unitaire = null) {
    try {
        // Vérifier si le ticket existe déjà
        $check_sql = "SELECT COUNT(*) as count FROM tickets WHERE numero_ticket = :numero_ticket";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->execute([':numero_ticket' => $numero_ticket]);
        $result = $check_stmt->fetch(PDO::FETCH_ASSOC);

        if ($result['count'] > 0) {
            return [
                'success' => false,
                'exists' => true,
                'numero_ticket' => $numero_ticket
            ];
        }

        // Insérer le nouveau ticket
        $sql = "INSERT INTO tickets (numero_ticket, id_usine, date_ticket, id_agent, 
                vehicule_id, poids, id_utilisateur, prix_unitaire, created_at) 
                VALUES (:numero_ticket, :id_usine, :date_ticket, :id_agent,
                :vehicule_id, :poids, :id_utilisateur, :prix_unitaire, NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':numero_ticket' => $numero_ticket,
            ':id_usine' => $id_usine,
            ':date_ticket' => $date_ticket,
            ':id_agent' => $id_agent,
            ':vehicule_id' => $vehicule_id,
            ':poids' => $poids,
            ':id_utilisateur' => $id_utilisateur,
            ':prix_unitaire' => $prix_unitaire
        ]);

        return [
            'success' => true,
            'message' => 'Ticket enregistré avec succès'
        ];

    } catch(PDOException $e) {
        if ($e->getCode() == 23000) { // Violation de contrainte unique
            return [
                'success' => false,
                'exists' => true,
                'numero_ticket' => $numero_ticket
            ];
        }
        return [
            'success' => false,
            'message' => 'Erreur lors de l\'enregistrement: ' . $e->getMessage()
        ];
    }
}

function updateTicketPrixUnitaire($conn, $id_ticket, $prix_unitaire, $date) {
    // Requête SQL d'update
    $sql = "UPDATE tickets
            SET prix_unitaire = :prix_unitaire, date_validation_boss = :date_validation_boss 
            WHERE id_ticket = :id_ticket";

    try {
        // Préparation de la requête
        $requete = $conn->prepare($sql);

        // Exécution de la requête avec les nouvelles valeurs
        $query_execute = $requete->execute([
            ':id_ticket' => $id_ticket,
            ':prix_unitaire' => $prix_unitaire,
            ':date_validation_boss' => $date
        ]);

        // Vérification de l'exécution
        return $query_execute;
    } catch (Exception $e) {
        error_log("Erreur lors de la mise à jour du ticket : " . $e->getMessage());
        return false;
    }
}

function updateTicket($conn, $id_ticket, $date_ticket, $numero_ticket) {
    try {
        $stmt = $conn->prepare("
            UPDATE tickets 
            SET date_ticket = :date_ticket,
                numero_ticket = :numero_ticket
            WHERE id_ticket = :id_ticket
        ");
        
        $stmt->bindParam(':date_ticket', $date_ticket);
        $stmt->bindParam(':numero_ticket', $numero_ticket);
        $stmt->bindParam(':id_ticket', $id_ticket);
        
        return $stmt->execute();
    } catch(PDOException $e) {
        error_log("Erreur lors de la mise à jour du ticket: " . $e->getMessage());
        return false;
    }
}

function searchTickets($conn, $usine = null, $date = null, $chauffeur = null, $agent = null, $numero_ticket = null) {
    $sql = "SELECT 
        t.*,
        CONCAT(u.nom, ' ', u.prenoms) AS utilisateur_nom_complet,
        v.matricule_vehicule,
        CONCAT(a.nom, ' ', a.prenom) AS nom_complet_agent,
        us.nom_usine,
        us.id_usine,
        a.id_agent,
        v.vehicules_id,
        t.date_paie,
        t.vehicule_id
    FROM tickets t
    LEFT JOIN utilisateurs u ON t.id_utilisateur = u.id
    LEFT JOIN vehicules v ON t.vehicule_id = v.vehicules_id
    LEFT JOIN agents a ON t.id_agent = a.id_agent
    LEFT JOIN usines us ON t.id_usine = us.id_usine
    WHERE 1=1";

    $params = array();

    if ($usine) {
        $sql .= " AND t.id_usine = :usine";
        $params[':usine'] = $usine;
    }

    if ($date) {
        $sql .= " AND DATE(t.date_ticket) = :date";
        $params[':date'] = $date;
    }

    if ($chauffeur) {
        $sql .= " AND t.vehicule_id = :chauffeur";
        $params[':chauffeur'] = $chauffeur;
    }

    if ($agent) {
        $sql .= " AND t.id_agent = :agent";
        $params[':agent'] = $agent;
    }

    if ($numero_ticket) {
        $sql .= " AND t.numero_ticket = :numero_ticket";
        $params[':numero_ticket'] = $numero_ticket;
    }

    try {
        $stmt = $conn->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur dans searchTickets: " . $e->getMessage());
        return array();
    }
}

function searchTicketsByDateRange($conn, $date_debut, $date_fin) {
    $stmt = $conn->prepare(
        "SELECT 
            t.*,
            CONCAT(u.nom, ' ', u.prenoms) AS utilisateur_nom_complet,
            u.contact AS utilisateur_contact,
            u.role AS utilisateur_role,
            v.matricule_vehicule,
            CONCAT(a.nom, ' ', a.prenom) AS agent_nom_complet,
            us.nom_usine
        FROM 
            tickets t
        INNER JOIN 
            utilisateurs u ON t.id_utilisateur = u.id
        INNER JOIN 
            vehicules v ON t.vehicule_id = v.vehicules_id
        INNER JOIN 
            agents a ON t.id_agent = a.id_agent
        INNER JOIN 
            usines us ON t.id_usine = us.id_usine
        WHERE 
            DATE(t.date_ticket) BETWEEN :date_debut AND :date_fin
        ORDER BY 
            t.date_ticket DESC"
    );
    
    $stmt->bindParam(':date_debut', $date_debut);
    $stmt->bindParam(':date_fin', $date_fin);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTicketsForBordereau($conn, $agent_id, $date_debut, $date_fin) {
    $stmt = $conn->prepare(
        "SELECT 
            t.id_ticket,
            t.date_ticket,
            t.numero_ticket,
            t.poids,
            t.prix_unitaire,
            t.numero_bordereau,
            v.matricule_vehicule,
            us.nom_usine
        FROM 
            tickets t
        INNER JOIN 
            vehicules v ON t.vehicule_id = v.vehicules_id
        INNER JOIN 
            usines us ON t.id_usine = us.id_usine
        WHERE 
            t.id_agent = :agent_id 
            AND t.date_ticket BETWEEN :date_debut AND :date_fin
        ORDER BY 
            t.date_ticket DESC"
    );

    $stmt->execute([
        ':agent_id' => $agent_id,
        ':date_debut' => $date_debut,
        ':date_fin' => $date_fin
    ]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function updateTicketsBordereau($conn, $ticket_ids, $numero_bordereau) {
    $logFile = dirname(dirname(dirname(__FILE__))) . '/pages/ajax/debug.log';
    
    try {
        file_put_contents($logFile, "\nDébut updateTicketsBordereau\n", FILE_APPEND);
        file_put_contents($logFile, "Tickets IDs: " . print_r($ticket_ids, true) . "\n", FILE_APPEND);
        file_put_contents($logFile, "Numéro bordereau: " . $numero_bordereau . "\n", FILE_APPEND);
        
        // Commencer une transaction
        $conn->beginTransaction();
        
        // Préparer les paramètres pour la requête
        $placeholders = str_repeat('?,', count($ticket_ids) - 1) . '?';
        
        // Construire la requête SQL
        $sql = "UPDATE tickets 
                SET 
                    numero_bordereau = ?,
                    updated_at = NOW()
                WHERE id_ticket IN ($placeholders)";
        
        file_put_contents($logFile, "Requête SQL: " . $sql . "\n", FILE_APPEND);
        
        // Préparer la requête
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
            throw new Exception("Erreur de préparation de la requête: " . implode(", ", $conn->errorInfo()));
        }
        
        // Ajouter le numéro de bordereau comme premier paramètre, suivi des IDs des tickets
        $params = array_merge([$numero_bordereau], $ticket_ids);
        file_put_contents($logFile, "Paramètres: " . print_r($params, true) . "\n", FILE_APPEND);
        
        // Exécuter la requête
        $result = $stmt->execute($params);
        
        if ($result === false) {
            $error = $stmt->errorInfo();
            file_put_contents($logFile, "Erreur SQL: " . print_r($error, true) . "\n", FILE_APPEND);
            throw new Exception("Erreur lors de la mise à jour des tickets: " . implode(", ", $error));
        }
        
        $rowCount = $stmt->rowCount();
        file_put_contents($logFile, "Nombre de lignes affectées: " . $rowCount . "\n", FILE_APPEND);
        
        // Valider la transaction seulement si des lignes ont été affectées
        if ($rowCount > 0) {
            $conn->commit();
            file_put_contents($logFile, "Transaction validée avec succès\n", FILE_APPEND);
            return true;
        } else {
            $conn->rollBack();
            file_put_contents($logFile, "Aucune ligne affectée, rollback effectué\n", FILE_APPEND);
            throw new Exception("Aucun ticket n'a été mis à jour");
        }
    } catch (Exception $e) {
        // En cas d'erreur, annuler la transaction
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        file_put_contents($logFile, "Erreur et rollback: " . $e->getMessage() . "\n", FILE_APPEND);
        throw $e;
    }
}

function getTicketsByBordereau($conn, $id_bordereau) {
    try {
        // D'abord, récupérer le bordereau pour avoir ses dates et l'agent
        $sql_bordereau = "SELECT id_agent, date_debut, date_fin, numero_bordereau FROM bordereau WHERE id_bordereau = :id_bordereau";
        $stmt = $conn->prepare($sql_bordereau);
        $stmt->execute([':id_bordereau' => $id_bordereau]);
        $bordereau = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$bordereau) {
            return [];
        }

        // Ensuite, récupérer tous les tickets disponibles pour ce bordereau
        $sql = "SELECT 
            t.id_ticket,
            t.numero_ticket,
            t.date_ticket,
            t.poids,
            t.prix_unitaire,
            (t.poids * t.prix_unitaire) as montant_total,
            t.numero_bordereau,
            v.matricule_vehicule,
            us.nom_usine
        FROM tickets t
        INNER JOIN vehicules v ON t.vehicule_id = v.vehicules_id
        INNER JOIN usines us ON t.id_usine = us.id_usine
        WHERE (t.numero_bordereau = :numero_bordereau 
              OR (t.id_agent = :id_agent 
                  AND t.created_at BETWEEN :date_debut AND :date_fin
                  AND t.date_validation_boss IS NOT NULL
                  AND t.prix_unitaire IS NOT NULL 
                  AND t.prix_unitaire > 0
                  AND (t.numero_bordereau IS NULL OR t.numero_bordereau = :numero_bordereau)))
        ORDER BY t.date_ticket ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':numero_bordereau' => $bordereau['numero_bordereau'],
            ':id_agent' => $bordereau['id_agent'],
            ':date_debut' => $bordereau['date_debut'],
            ':date_fin' => $bordereau['date_fin']
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur dans getTicketsByBordereau: " . $e->getMessage());
        return [];
    }
}

function validerTicket($conn, $id_ticket) {
    try {
        $stmt = $conn->prepare("UPDATE tickets SET date_validation_boss = NOW() WHERE id_ticket = ? AND date_validation_boss IS NULL");
        $result = $stmt->execute([$id_ticket]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log("Erreur dans validerTicket: " . $e->getMessage());
        return false;
    }
}

function validerTickets($conn, $ticket_ids) {
    try {
        $placeholders = str_repeat('?,', count($ticket_ids) - 1) . '?';
        $sql = "UPDATE tickets SET date_validation_boss = NOW() WHERE id_ticket IN ($placeholders) AND date_validation_boss IS NULL";
        
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute($ticket_ids);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log("Erreur dans validerTickets: " . $e->getMessage());
        return false;
    }
}

function getTicketsAssociation($conn, $agent_id, $date_debut, $date_fin) {
    $sql = "SELECT 
    t.date_ticket,
    t.numero_ticket,
    t.vehicule_id,
    t.poids,
    t.id_ticket,
    DATE(t.created_at) as date_reception,
    v.matricule_vehicule as vehicule,
      CAST(t.poids AS DECIMAL(10,2)) as poids,
            CAST(t.prix_unitaire AS DECIMAL(10,2)) as prix_unitaire,
            CAST((t.poids * t.prix_unitaire) AS DECIMAL(15,2)) as montant_total
 FROM tickets t
 INNER JOIN vehicules v ON t.vehicule_id = v.vehicules_id
 WHERE t.id_agent = :id_agent
    AND t.created_at BETWEEN CONCAT(:date_debut, ' 00:00:00') AND CONCAT(:date_fin, ' 23:59:59')
    AND t.prix_unitaire > 0
 ORDER BY 
    t.date_ticket ASC,
    t.created_at ASC";

    try {
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id_agent', $agent_id, PDO::PARAM_INT);
        $stmt->bindValue(':date_debut', $date_debut, PDO::PARAM_STR);
        $stmt->bindValue(':date_fin', $date_fin, PDO::PARAM_STR);
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur dans getTicketsAssociation: " . $e->getMessage());
        return array();
    }
}

?>

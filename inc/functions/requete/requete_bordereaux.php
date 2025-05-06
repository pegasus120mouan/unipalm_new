<?php

function getBordereaux($conn, $page = 1, $limit = 15, $filters = []) {
    try {
        // Calculate the offset
        $offset = ($page - 1) * $limit;
        
        // Get total count
        $count_query = "SELECT COUNT(*) FROM bordereau b INNER JOIN agents a ON b.id_agent = a.id_agent";
        $count_stmt = $conn->prepare($count_query);
        $count_stmt->execute();
        $total_records = $count_stmt->fetchColumn();
        
        // Calculate total pages
        $total_pages = ceil($total_records / $limit);
        
        // Base query
        $query = "SELECT 
    b.id_bordereau,
    b.numero_bordereau,
    b.date_debut,
    b.date_fin,
    b.poids_total,
    b.montant_total,
    b.montant_payer,
    b.montant_reste,
    b.statut_bordereau,
    b.date_validation_boss,
    b.created_at AS date_creation_bordereau,
    a.id_agent,
    CONCAT(COALESCE(a.nom, ''), ' ', COALESCE(a.prenom, '')) AS nom_complet_agent,
    a.contact,
    (SELECT COUNT(*) FROM tickets t 
     WHERE t.id_agent = b.id_agent 
     AND t.date_validation_boss is not null 
     AND t.prix_unitaire > 0 
     AND DATE(t.created_at) BETWEEN DATE(b.date_debut) AND DATE(b.date_fin)) as nombre_tickets
FROM bordereau b
INNER JOIN agents a ON b.id_agent = a.id_agent";

        // Add WHERE clause if filters exist
        $params = [];
        $where_conditions = [];

        if (!empty($filters['numero'])) {
            $where_conditions[] = "b.numero_bordereau LIKE :numero";
            $params[':numero'] = '%' . $filters['numero'] . '%';
        }

        if (!empty($filters['agent'])) {
            $where_conditions[] = "b.id_agent = :agent_id";
            $params[':agent_id'] = $filters['agent'];
        }

        if (!empty($filters['date_debut'])) {
            $where_conditions[] = "b.date_debut >= :date_debut";
            $params[':date_debut'] = $filters['date_debut'];
        }

        if (!empty($filters['date_fin'])) {
            $where_conditions[] = "b.date_fin <= :date_fin";
            $params[':date_fin'] = $filters['date_fin'];
        }

        if (!empty($filters['date'])) {
            $where_conditions[] = "DATE(b.created_at) = :date";
            $params[':date'] = $filters['date'];
        }

        if (!empty($where_conditions)) {
            $query .= " WHERE " . implode(" AND ", $where_conditions);
        }

        // Add GROUP BY
        $query .= " GROUP BY b.id_bordereau, b.numero_bordereau, b.date_debut, b.date_fin, b.poids_total, 
                 b.montant_total, b.montant_payer, b.montant_reste, b.statut_bordereau, 
                 b.date_validation_boss, b.created_at, a.id_agent, a.nom, a.prenom, a.contact";

        // Add ORDER BY
        $query .= " ORDER BY b.created_at DESC";
        
        // Add LIMIT and OFFSET
       // $query .= " LIMIT :limit OFFSET :offset";
        
        $stmt = $conn->prepare($query);
        
        // Bind the parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
       // $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        //$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        $bordereaux = $stmt->fetchAll(PDO::FETCH_ASSOC);

        
        
        return [
            'data' => $bordereaux,
            'total' => $total_records,
            'total_pages' => $total_pages,
            'current_page' => $page,
            'limit' => $limit
        ];
        
    } catch(PDOException $e) {
        error_log($e->getMessage());
        return false;
    }
}

function getBordereauById($conn, $id_bordereau) {
    $stmt = $conn->prepare(
        "SELECT 
    b.id_bordereau,
    b.numero_bordereau,
    b.date_debut,
    b.date_fin,
    b.poids_total,
    b.montant_total,
    b.montant_payer,
    b.montant_reste,
    b.statut_bordereau,
    b.date_validation_boss,
    b.created_at AS date_creation_bordereau,
    a.id_agent,
    CONCAT(COALESCE(a.nom, ''), ' ', COALESCE(a.prenom, '')) AS nom_complet_agent,
    a.contact
FROM bordereau b
INNER JOIN agents a ON b.id_agent = a.id_agent
WHERE b.id_bordereau = :id_bordereau");

    $stmt->bindParam(':id_bordereau', $id_bordereau, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function updateBordereau($conn, $id_bordereau, $date_debut, $date_fin) {
    try {
        $stmt = $conn->prepare(
            "UPDATE bordereau 
            SET date_debut = :date_debut,
                date_fin = :date_fin
            WHERE id_bordereau = :id_bordereau");

        $stmt->bindParam(':id_bordereau', $id_bordereau, PDO::PARAM_INT);
        $stmt->bindParam(':date_debut', $date_debut);
        $stmt->bindParam(':date_fin', $date_fin);

        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Erreur lors de la mise à jour du bordereau: " . $e->getMessage());
        return false;
    }
}

function deleteBordereau($conn, $id_bordereau, $numero_bordereau) {


    try {
        $stmt = $conn->prepare("update tickets set numero_bordereau = null where numero_bordereau = :numero_bordereau");
        $stmt->bindParam(':numero_bordereau', $numero_bordereau);
        $stmt->execute();


        $stmt = $conn->prepare("DELETE FROM bordereau WHERE id_bordereau = :id_bordereau");
        $stmt->bindParam(':id_bordereau', $id_bordereau, PDO::PARAM_INT);
        return $stmt->execute();
    } catch (PDOException $e) {
        
        error_log("Erreur lors de la suppression du bordereau: " . $e->getMessage());
        return false;
    }
}

function searchBordereaux($conn, $date_debut = null, $date_fin = null, $agent_id = null) {
    $sql = "SELECT 
        b.id_bordereau,
        b.numero_bordereau,
        b.date_debut,
        b.date_fin,
        b.poids_total,
        b.montant_total,
        b.montant_payer,
        b.montant_reste,
        b.statut_bordereau,
        b.date_validation_boss,
        b.created_at AS date_creation_bordereau,
        a.id_agent,
        CONCAT(COALESCE(a.nom, ''), ' ', COALESCE(a.prenom, '')) AS nom_complet_agent,
        a.contact
    FROM bordereau b
    INNER JOIN agents a ON b.id_agent = a.id_agent
    WHERE 1=1";
    
    $params = array();
    
    if ($date_debut) {
        $sql .= " AND b.date_debut >= :date_debut";
        $params[':date_debut'] = $date_debut;
    }
    
    if ($date_fin) {
        $sql .= " AND b.date_fin <= :date_fin";
        $params[':date_fin'] = $date_fin;
    }
    
    if ($agent_id) {
        $sql .= " AND b.id_agent = :agent_id";
        $params[':agent_id'] = $agent_id;
    }
    
    $sql .= " ORDER BY b.created_at DESC";
    
    try {
        $stmt = $conn->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur lors de la recherche des bordereaux: " . $e->getMessage());
        return array();
    }
}

function saveBordereau($conn, $id_agent, $date_debut, $date_fin) {
    try {
        // Commencer une transaction
        $conn->beginTransaction();

        // 1. Créer le numéro de bordereau
        $numero_bordereau = 'BDR-' . date('Ymd-His') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

        // 2. Insérer le bordereau avec les totaux calculés directement
        $sql = "INSERT INTO bordereau (
            numero_bordereau, 
            id_agent, 
            date_debut, 
            date_fin, 
            poids_total, 
            montant_total,
            statut_bordereau,
            created_at
        )
        SELECT 
            ?, 
            ?, 
            ?, 
            ?, 
            COALESCE(SUM(t.poids), 0), 
            COALESCE(SUM(t.prix_unitaire * t.poids), 0),
            'non soldé',
            NOW()
        FROM tickets t 
        WHERE t.id_agent = ? 
        AND t.created_at BETWEEN ? AND ?
        AND NOT EXISTS (
            SELECT 1 FROM bordereau_tickets bt WHERE bt.id_ticket = t.id_ticket
        )
        AND t.date_validation_boss IS NOT NULL
        AND t.prix_unitaire > 0
        AND t.statut_ticket = 'disponible'";

        $stmt = $conn->prepare($sql);
        $stmt->execute([$numero_bordereau, $id_agent, $date_debut, $date_fin, $id_agent, $date_debut, $date_fin]);
        
        // Récupérer l'ID du bordereau inséré
        $id_bordereau = $conn->lastInsertId();
        
        // Vérifier si des lignes ont été affectées
        if ($stmt->rowCount() == 0) {
            throw new Exception("Aucun ticket disponible pour cette période");
        }

        // 3. Insérer les tickets dans bordereau_tickets et mettre à jour leur numéro de bordereau
        $sql = "INSERT INTO bordereau_tickets (id_bordereau, id_ticket)
                SELECT ?, t.id_ticket
                FROM tickets t
                WHERE t.id_agent = ? 
                AND t.created_at BETWEEN ? AND ?
                AND NOT EXISTS (
                    SELECT 1 FROM bordereau_tickets bt WHERE bt.id_ticket = t.id_ticket
                )
                AND t.date_validation_boss IS NOT NULL
                AND t.prix_unitaire > 0
                AND t.statut_ticket = 'disponible'";

        $stmt = $conn->prepare($sql);
        $stmt->execute([$id_bordereau, $id_agent, $date_debut, $date_fin]);

        if ($stmt->rowCount() == 0) {
            throw new Exception("Aucun ticket n'a pu être associé au bordereau");
        }

        // 4. Mettre à jour les tickets avec le numéro de bordereau et le statut
        $sql = "UPDATE tickets t
                SET t.numero_bordereau = ?,
                    t.statut_ticket = 'non soldé',
                    t.updated_at = NOW()
                WHERE t.id_ticket IN (
                    SELECT bt.id_ticket 
                    FROM bordereau_tickets bt 
                    WHERE bt.id_bordereau = ?
                )";

        $stmt = $conn->prepare($sql);
        $stmt->execute([$numero_bordereau, $id_bordereau]);

        if ($stmt->rowCount() == 0) {
            throw new Exception("Aucun ticket n'a été mis à jour");
        }

        // Valider la transaction
        $conn->commit();
        return [
            'success' => true,
            'message' => 'Bordereau créé avec succès',
            'id_bordereau' => $id_bordereau
        ];

    } catch (Exception $e) {
        // En cas d'erreur, annuler la transaction
        $conn->rollBack();
        error_log("Erreur dans saveBordereau: " . $e->getMessage());
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

function getBordereauxValides($conn) {
    try {
        $stmt = $conn->prepare(
            "SELECT 
                b.id_bordereau,
                b.numero_bordereau,
                b.date_debut,
                b.date_fin,
                b.poids_total,
                b.montant_total,
                b.montant_payer,
                b.montant_reste,
                b.statut_bordereau,
                b.date_validation_boss,
                b.date_paie,
                b.created_at AS date_creation_bordereau,
                CONCAT(COALESCE(a.nom, ''), ' ', COALESCE(a.prenom, '')) AS nom_complet_agent,
                a.contact AS agent_contact
            FROM 
                bordereau b
            INNER JOIN 
                agents a ON b.id_agent = a.id_agent
            WHERE 
                b.date_validation_boss IS NOT NULL
            ORDER BY 
                CASE 
                    WHEN b.statut_bordereau = 'non soldé' THEN 1
                    ELSE 2
                END,
                b.date_validation_boss DESC"
        );
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error in getBordereauxValides: " . $e->getMessage());
        return [];
    }
}

function updateBordereauStatus($conn, $id_bordereau) {
    try {
        // Calculer le montant total payé
        $stmt = $conn->prepare("
            SELECT 
                b.montant_total,
                COALESCE(SUM(p.montant_paie), 0) as montant_paye
            FROM bordereau b
            LEFT JOIN bordereau_tickets bt ON b.id_bordereau = bt.id_bordereau
            LEFT JOIN tickets t ON bt.id_ticket = t.id_ticket
            LEFT JOIN paiements p ON t.id_ticket = p.id_ticket
            WHERE b.id_bordereau = ?
            GROUP BY b.id_bordereau
        ");
        $stmt->execute([$id_bordereau]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $montant_payer = $result['montant_paye'];
            $montant_total = $result['montant_total'];
            $montant_reste = $montant_total - $montant_payer;
            $statut = $montant_payer >= $montant_total ? 'soldé' : 'non soldé';

            // Mettre à jour le bordereau
            $update = $conn->prepare("
                UPDATE bordereau 
                SET montant_payer = ?,
                    montant_reste = ?,
                    statut_bordereau = ?,
                    date_paie = CASE WHEN ? >= ? THEN NOW() ELSE NULL END
                WHERE id_bordereau = ?
            ");
            $update->execute([
                $montant_payer,
                $montant_reste,
                $statut,
                $montant_payer,
                $montant_total,
                $id_bordereau
            ]);
        }
    } catch(PDOException $e) {
        error_log($e->getMessage());
        return false;
    }
}



?>

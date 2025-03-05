<?php

require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_tickets.php';
require_once '../inc/functions/requete/requete_prix_unitaires.php';

session_start();

// Traitement de la suppression
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id_ticket = $_GET['id'];
    
    try {
        // Vérifier si le ticket existe et n'est pas déjà payé
        $sql = "SELECT date_paie FROM tickets WHERE id_ticket = :id_ticket";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id_ticket', $id_ticket, PDO::PARAM_INT);
        $stmt->execute();
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$ticket) {
            $_SESSION['delete_pop'] = true;
            header('Location: tickets.php');
            exit();
        }

        if ($ticket['date_paie'] !== null) {
            $_SESSION['warning'] = "Impossible de supprimer un ticket déjà payé.";
            header('Location: tickets.php');
            exit();
        }

        // Supprimer le ticket
        $sql = "DELETE FROM tickets WHERE id_ticket = :id_ticket";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id_ticket', $id_ticket, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $_SESSION['success_modal'] = true;
            $_SESSION['message'] = "Ticket supprimé avec succès.";
        } else {
            $_SESSION['delete_pop'] = true;
        }
    } catch (PDOException $e) {
        error_log("Erreur lors de la suppression du ticket: " . $e->getMessage());
        $_SESSION['delete_pop'] = true;
    }
    
    header('Location: tickets.php');
    exit();
}

// Traitement de l'ajout de ticket
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Vérifiez si l'action concerne l'insertion ou autre chose
    if (isset($_POST["usine"]) && isset($_POST["date_ticket"])) {
        // Traitement de l'insertion du ticket
        $id_usine = $_POST["usine"] ?? null;
        $date_ticket = $_POST["date_ticket"] ?? null;
        $id_agent = $_POST["id_agent"] ?? null;
        $numero_ticket = $_POST["numero_ticket"] ?? null;
        $vehicule_id = $_POST["vehicule"] ?? null;
        $poids = $_POST["poids"] ?? null;
        $id_utilisateur = $_SESSION['user_id'] ?? null;

        // Validation des données
        if (!$id_usine || !$date_ticket || !$id_agent || !$numero_ticket || !$vehicule_id || !$poids || !$id_utilisateur) {
            $_SESSION['delete_pop'] = true; // Message d'erreur
            header('Location: ' . ($_POST['redirect'] ?? 'tickets.php'));
            exit;
        }

        // Récupérer le prix unitaire
        $prix_info = getPrixUnitaireByDateAndUsine($conn, $date_ticket, $id_usine);
        $prix_unitaire = $prix_info['prix'];
        
        // Appel de la fonction pour insérer le ticket
        try {
            $result = insertTicket(
                $conn,
                $id_usine,
                $date_ticket,
                $id_agent,
                $numero_ticket,
                $vehicule_id,
                $poids,
                $id_utilisateur,
                $prix_unitaire ?? null
            );

            if (!$result['success']) {
                if (isset($result['exists'])) {
                    $_SESSION['error'] = "Le ticket numéro " . htmlspecialchars($result['numero_ticket']) . " existe déjà.";
                } else {
                    $_SESSION['error'] = $result['message'];
                }
            } else {
                if ($prix_info['is_default']) {
                    $_SESSION['warning'] = "Aucun prix unitaire n'est défini pour cette période. La valeur par défaut (0,00 FCFA) a été utilisée.";
                } else {
                    $_SESSION['success_modal'] = true;
                    $_SESSION['prix_unitaire'] = $prix_unitaire;
                }
            }
        } catch (Exception $e) {
            error_log("Erreur lors de l'enregistrement du ticket : " . $e->getMessage());
            $_SESSION['delete_pop'] = true; // Message d'erreur
        }
        header('Location: ' . ($_POST['redirect'] ?? 'tickets.php'));
        exit;
    } elseif (isset($_POST["id_ticket"]) && isset($_POST["prix_unitaire"])) {
        // Traitement des données supplémentaires
        $id_ticket = $_POST["id_ticket"] ?? null;
        $prix_unitaire = $_POST["prix_unitaire"] ?? null;
        $redirect_url = $_POST['redirect'] ?? 'tickets_attente.php';

        try {
            if ($id_ticket && $prix_unitaire) {
                // Mettre à jour le ticket avec le nouveau prix unitaire
                $date_validation = date('Y-m-d H:i:s');
                $stmt = $conn->prepare("UPDATE tickets SET prix_unitaire = ?, date_validation_boss = ? WHERE id_ticket = ?");
                $result = $stmt->execute([$prix_unitaire, $date_validation, $id_ticket]);
                
                if ($result) {
                    // Calculer et mettre à jour le montant_paie
                    $stmt = $conn->prepare("UPDATE tickets SET montant_paie = prix_unitaire * poids WHERE id_ticket = ?");
                    $stmt->execute([$id_ticket]);
                    
                    $_SESSION['success'] = "Prix unitaire mis à jour avec succès";
                } else {
                    $_SESSION['error'] = "Erreur lors de la mise à jour du prix unitaire";
                }
            } else {
                $_SESSION['error'] = "Données manquantes";
            }
        } catch (PDOException $e) {
            error_log("Erreur dans traitement_tickets.php: " . $e->getMessage());
            $_SESSION['error'] = "Erreur lors de la mise à jour du prix unitaire";
        }

        // Rediriger vers l'URL d'origine avec tous les paramètres
        header("Location: " . $redirect_url);
        exit;
    }

    // Vérifier si le ticket existe et n'est pas payé
    if (isset($_POST['id_ticket'])) {
        $check_stmt = $conn->prepare("SELECT date_paie FROM tickets WHERE id_ticket = ?");
        $check_stmt->execute([$_POST['id_ticket']]);
        $ticket = $check_stmt->fetch();

        if ($ticket['date_paie'] !== null) {
            $_SESSION['delete_pop'] = true;
            header('Location: ' . ($_POST['redirect'] ?? 'tickets_modifications.php'));
            exit;
        }
    }

    // Modification de l'usine
    if (isset($_POST["usine"]) && isset($_POST["id_ticket"]) && !isset($_POST["date_ticket"])) {
        $id_usine = $_POST["usine"];
        $id_ticket = $_POST["id_ticket"];

        try {
            $stmt = $conn->prepare("UPDATE tickets SET id_usine = ? WHERE id_ticket = ?");
            $stmt->execute([$id_usine, $id_ticket]);
            $_SESSION['popup'] = true;
        } catch (PDOException $e) {
            $_SESSION['delete_pop'] = true;
        }
        header('Location: ' . ($_POST['redirect'] ?? 'tickets_modifications.php'));
        exit;
    }

    // Modification de l'agent (chef de mission)
    if (isset($_POST["chef_equipe"]) && isset($_POST["id_ticket"])) {
        $id_agent = $_POST["chef_equipe"];
        $id_ticket = $_POST["id_ticket"];

        try {
            $stmt = $conn->prepare("UPDATE tickets SET id_agent = ? WHERE id_ticket = ?");
            $stmt->execute([$id_agent, $id_ticket]);
            $_SESSION['popup'] = true;
        } catch (PDOException $e) {
            $_SESSION['delete_pop'] = true;
        }
        header('Location: ' . ($_POST['redirect'] ?? 'tickets_modifications.php'));
        exit;
    }

    // Modification du véhicule
    if (isset($_POST["vehicule"]) && isset($_POST["id_ticket"])) {
        $id_vehicule = $_POST["vehicule"];
        $id_ticket = $_POST["id_ticket"];

        try {
            $stmt = $conn->prepare("UPDATE tickets SET vehicule_id = ? WHERE id_ticket = ?");
            $stmt->execute([$id_vehicule, $id_ticket]);
            $_SESSION['popup'] = true;
            header('Location: ' . ($_POST['redirect'] ?? 'tickets_modifications.php'));
        } catch (PDOException $e) {
            $_SESSION['delete_pop'] = true;
            error_log("Erreur lors de la mise à jour du véhicule : " . $e->getMessage());
            header('Location: ' . ($_POST['redirect'] ?? 'tickets_modifications.php'));
        }
        exit;
    }
}

// Traitement pour retourner du JSON et gérer l'affichage via AJAX
require_once '../inc/functions/requete/requete_usines.php';
require_once '../inc/functions/requete/requete_chef_equipes.php';
require_once '../inc/functions/requete/requete_vehicules.php';
require_once '../inc/functions/requete/requete_agents.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $success = false;
        $message = '';
        
        // Validation d'un seul ticket
        if (isset($_POST['id_ticket']) && isset($_POST['prix_unitaire'])) {
            $id_ticket = $_POST['id_ticket'];
            $prix_unitaire = $_POST['prix_unitaire'];
            
            // Mettre à jour le ticket avec le nouveau prix unitaire
            $date_validation = date('Y-m-d H:i:s');
            $stmt = $conn->prepare("UPDATE tickets SET prix_unitaire = ?, date_validation_boss = ? WHERE id_ticket = ?");
            $success = $stmt->execute([$prix_unitaire, $date_validation, $id_ticket]);
            
            if ($success) {
                // Calculer et mettre à jour le montant_paie
                $stmt = $conn->prepare("UPDATE tickets SET montant_paie = prix_unitaire * poids WHERE id_ticket = ?");
                $stmt->execute([$id_ticket]);
                $message = 'Ticket validé avec succès';
            } else {
                $message = 'Erreur lors de la validation du ticket';
            }
        }
        // Validation multiple
        elseif (isset($_POST['ticket_ids'])) {
            $ticket_ids = json_decode($_POST['ticket_ids']);
            $success = true;
            $errors = [];
            
            foreach ($ticket_ids as $id_ticket) {
                $date_validation = date('Y-m-d H:i:s');
                $stmt = $conn->prepare("UPDATE tickets SET date_validation_boss = ? WHERE id_ticket = ?");
                if (!$stmt->execute([$date_validation, $id_ticket])) {
                    $success = false;
                    $errors[] = "Erreur pour le ticket #$id_ticket";
                }
            }
            
            $message = $success ? 'Tous les tickets ont été validés' : 'Erreurs: ' . implode(', ', $errors);
        }
        else {
            $message = 'Paramètres invalides';
        }

        echo json_encode([
            'success' => $success,
            'message' => $message
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée'
    ]);
}

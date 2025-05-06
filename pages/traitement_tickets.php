<?php
session_start();

require_once '../inc/functions/connexion.php';
require_once '../inc/functions/update_tickets_prix_unitaire.php';
require_once '../inc/functions/requete/requete_tickets.php';
require_once '../inc/functions/requete/requete_prix_unitaires.php';
require_once '../inc/functions/log_functions.php';
require_once '../inc/functions/requete/requete_usines.php';
require_once '../inc/functions/requete/requete_chef_equipes.php';
require_once '../inc/functions/requete/requete_vehicules.php';
require_once '../inc/functions/requete/requete_agents.php';

$conn = getConnexion();

// Mettre à jour les tickets sans prix unitaire
$updateResult = updateTicketsFromPrixUnitaire();
if ($updateResult['updated_count'] > 0) {
    $_SESSION['success_message'] = $updateResult['message'];
}

//session_start();

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
    if (isset($_POST["id_usine"]) && isset($_POST["date_ticket"])) {
        // Traitement de l'insertion du ticket
        $id_usine = $_POST["id_usine"] ?? null;
        $date_ticket = $_POST["date_ticket"] ?? null;
        $id_agent = $_POST["id_agent"] ?? null;
        $numero_ticket = $_POST["numero_ticket"] ?? null;
        $vehicule_id = $_POST["vehicule_id"] ?? null;
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
    if (isset($_POST["id_usine"]) && isset($_POST["id_ticket"]) && !isset($_POST["date_ticket"])) {
        $id_usine = $_POST["id_usine"];
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

    // Mise à jour du véhicule
    if (isset($_POST['updateVehicule']) && isset($_POST['id_ticket']) && isset($_POST['vehicule'])) {
        $id_ticket = $_POST['id_ticket'];
        $nouveau_vehicule = $_POST['vehicule'];

        try {
            // Vérifier si le ticket existe et n'est pas déjà payé
            $stmt = $conn->prepare("SELECT date_paie FROM tickets WHERE id_ticket = ?");
            $stmt->execute([$id_ticket]);
            $ticket = $stmt->fetch();

            if (!$ticket) {
                $_SESSION['error'] = "Ticket introuvable";
                header('Location: tickets_modifications.php');
                exit();
            }

            if ($ticket['date_paie'] !== null) {
                $_SESSION['error'] = "Impossible de modifier le véhicule d'un ticket déjà payé";
                header('Location: tickets_modifications.php');
                exit();
            }

            // Mettre à jour le véhicule
            $sql = "UPDATE tickets SET vehicule_id = ? WHERE id_ticket = ?";
            error_log("SQL Query: " . $sql . " [vehicule_id: " . $nouveau_vehicule . ", ticket_id: " . $id_ticket . "]");
            
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':vehicule_id', $nouveau_vehicule, PDO::PARAM_INT);
            $stmt->bindParam(':id_ticket', $id_ticket, PDO::PARAM_INT);

            $result = $stmt->execute();
            error_log("Résultat de l'exécution: " . ($result ? "true" : "false"));
            
            if ($result) {
                $_SESSION['success'] = "Véhicule mis à jour avec succès.";
                error_log("Succès de la mise à jour");
            } else {
                $_SESSION['error'] = "Erreur lors de la mise à jour du véhicule.";
                error_log("Erreur lors de la mise à jour - errorInfo: " . print_r($stmt->errorInfo(), true));
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur lors de la mise à jour du véhicule : " . $e->getMessage();
            error_log("Exception PDO: " . $e->getMessage());
        }

        header('Location: tickets_modifications.php');
        exit();
    }

    // Mise à jour du prix unitaire
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_prix_unitaire') {
        try {
            $conn->beginTransaction();

            // Validation des données
            if (!isset($_POST['prix_unitaire']) || empty($_POST['prix_unitaire'])) {
                throw new Exception("Le prix unitaire est requis");
            }
            if (!isset($_POST['id_ticket']) || empty($_POST['id_ticket'])) {
                throw new Exception("ID du ticket manquant");
            }

            $prix_unitaire = floatval($_POST['prix_unitaire']);
            if ($prix_unitaire <= 0) {
                throw new Exception("Le prix unitaire doit être supérieur à 0");
            }

            // Récupérer les informations du ticket
            $stmt = $conn->prepare("
                SELECT t.*, 
                       COALESCE(t.montant_payer, 0) as montant_deja_paye,
                       COALESCE(t.montant_paie, 0) as montant_paie_actuel,
                       COALESCE(t.prix_unitaire, 0) as ancien_prix_unitaire
                FROM tickets t 
                WHERE t.id_ticket = ?
            ");
            $stmt->execute([$_POST['id_ticket']]);
            $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$ticket) {
                throw new Exception("Ticket non trouvé");
            }

            // Calculer le nouveau montant_paie
            $poids = floatval($ticket['poids']);
            $montant_paie = $prix_unitaire * $poids;
            $montant_reste = $montant_paie - floatval($ticket['montant_deja_paye']);

            if ($montant_reste < 0) {
                throw new Exception("Le nouveau prix unitaire créerait un montant restant négatif. Opération impossible car " . 
                                  number_format($montant_paie, 0, ',', ' ') . " FCFA (nouveau montant) < " . 
                                  number_format($ticket['montant_deja_paye'], 0, ',', ' ') . " FCFA (montant déjà payé)");
            }

            // Mettre à jour le ticket
            $stmt = $conn->prepare("
                UPDATE tickets 
                SET prix_unitaire = ?,
                    montant_paie = ?,
                    montant_reste = ?,
                    date_modification = NOW()
                WHERE id_ticket = ?
            ");
            $stmt->execute([
                $prix_unitaire,
                $montant_paie,
                $montant_reste,
                $_POST['id_ticket']
            ]);

            writeLog("Mise à jour du prix unitaire pour le ticket #" . $_POST['id_ticket'] . 
                    ". Ancien prix: " . $ticket['ancien_prix_unitaire'] .
                    ", Nouveau prix: " . $prix_unitaire . 
                    ", Ancien montant: " . $ticket['montant_paie_actuel'] .
                    ", Nouveau montant: " . $montant_paie . 
                    ", Montant déjà payé: " . $ticket['montant_deja_paye'] .
                    ", Nouveau montant restant: " . $montant_reste);

            $conn->commit();
            $_SESSION['success_message'] = "Prix unitaire mis à jour avec succès : " . 
                                         number_format($ticket['ancien_prix_unitaire'], 0, ',', ' ') . " → " . 
                                         number_format($prix_unitaire, 0, ',', ' ') . " FCFA. " .
                                         "Nouveau montant à payer : " . number_format($montant_paie, 0, ',', ' ') . " FCFA";
        } catch (Exception $e) {
            $conn->rollBack();
            writeLog("Erreur lors de la mise à jour du prix unitaire: " . $e->getMessage());
            $_SESSION['error_message'] = "Erreur lors de la mise à jour : " . $e->getMessage();
        }
        
        header("Location: tickets_modifications.php");
        exit();
    } 

    // Mise à jour du numéro de ticket
    if (isset($_POST['updateNumeroTicket'])) {
        $id_ticket = $_POST['id_ticket'];
        $numero_ticket = $_POST['numero_ticket'];

        try {
            $stmt = $conn->prepare("UPDATE tickets SET numero_ticket = ? WHERE id_ticket = ?");
            $stmt->execute([$numero_ticket, $id_ticket]);

            $_SESSION['success'] = "Le numéro de ticket a été mis à jour avec succès.";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur lors de la mise à jour du numéro de ticket : " . $e->getMessage();
        }

        header("Location: tickets_modifications.php");
        exit();
    }

    // Mise à jour de l'usine
    if (isset($_POST['updateUsine']) && isset($_POST['id_ticket']) && isset($_POST['usine'])) {
        $id_ticket = $_POST['id_ticket'];
        $nouvelle_usine = $_POST['usine'];

        try {
            // Vérifier si le ticket existe et n'est pas déjà payé
            $stmt = $conn->prepare("SELECT date_paie, id_usine FROM tickets WHERE id_ticket = ?");
            $stmt->execute([$id_ticket]);
            $ticket = $stmt->fetch();

            if (!$ticket) {
                $_SESSION['error'] = "Ticket introuvable";
                header('Location: tickets_modifications.php');
                exit();
            }

            if ($ticket['date_paie'] !== null) {
                $_SESSION['error'] = "Impossible de modifier l'usine d'un ticket déjà payé";
                header('Location: tickets_modifications.php');
                exit();
            }

            // Mettre à jour l'usine
            $stmt = $conn->prepare("UPDATE tickets SET id_usine = ? WHERE id_ticket = ?");
            if ($stmt->execute([$nouvelle_usine, $id_ticket])) {
                $_SESSION['success'] = "L'usine a été mise à jour avec succès";
            } else {
                $_SESSION['error'] = "Erreur lors de la mise à jour de l'usine";
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur lors de la mise à jour de l'usine : " . $e->getMessage();
        }

        header('Location: tickets_modifications.php');
        exit();
    }

    // Mise à jour du chef de mission
    if (isset($_POST['updateChefEquipe']) && isset($_POST['id_ticket']) && isset($_POST['chef_equipe'])) {
        $id_ticket = $_POST['id_ticket'];
        $nouveau_chef = $_POST['chef_equipe'];

        try {
            // Vérifier si le ticket existe et n'est pas déjà payé
            $stmt = $conn->prepare("SELECT date_paie, id_agent FROM tickets WHERE id_ticket = ?");
            $stmt->execute([$id_ticket]);
            $ticket = $stmt->fetch();

            if (!$ticket) {
                $_SESSION['error'] = "Ticket introuvable";
                header('Location: tickets_modifications.php');
                exit();
            }

            if ($ticket['date_paie'] !== null) {
                $_SESSION['error'] = "Impossible de modifier le chef de mission d'un ticket déjà payé";
                header('Location: tickets_modifications.php');
                exit();
            }

            // Mettre à jour le chef de mission
            $stmt = $conn->prepare("UPDATE tickets SET id_agent = ? WHERE id_ticket = ?");
            if ($stmt->execute([$nouveau_chef, $id_ticket])) {
                $_SESSION['success'] = "Le chef de mission a été mis à jour avec succès";
            } else {
                $_SESSION['error'] = "Erreur lors de la mise à jour du chef de mission";
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur lors de la mise à jour du chef de mission : " . $e->getMessage();
        }

        header('Location: tickets_modifications.php');
        exit();
    }

    // Mise à jour du véhicule
    if (isset($_POST['updateVehicule']) && isset($_POST['id_ticket']) && isset($_POST['vehicule'])) {
        $id_ticket = $_POST['id_ticket'];
        $nouveau_vehicule = $_POST['vehicule'];

        try {
            $sql = "UPDATE tickets SET vehicules_id = :vehicule WHERE id_ticket = :id_ticket";
            error_log("SQL Query: " . $sql);
            
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':vehicule', $nouveau_vehicule, PDO::PARAM_INT);
            $stmt->bindParam(':id_ticket', $id_ticket, PDO::PARAM_INT);

            $result = $stmt->execute();
            error_log("Résultat de l'exécution: " . ($result ? "true" : "false"));
            
            if ($result) {
                $_SESSION['success'] = "Véhicule mis à jour avec succès.";
                error_log("Succès de la mise à jour");
            } else {
                $_SESSION['error'] = "Erreur lors de la mise à jour du véhicule.";
                error_log("Erreur lors de la mise à jour - errorInfo: " . print_r($stmt->errorInfo(), true));
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur lors de la mise à jour du véhicule : " . $e->getMessage();
            error_log("Exception PDO: " . $e->getMessage());
        }

        header('Location: tickets_modifications.php');
        exit();
    }

    // Traitement des requêtes AJAX
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
        header('Content-Type: application/json');
        
        switch ($_GET['action']) {
            case 'get_usines':
                echo json_encode(getUsines($conn));
                break;
            case 'get_chef_equipes':
                echo json_encode(getChefEquipes($conn));
                break;
            case 'get_vehicules':
                echo json_encode(getVehicules($conn));
                break;
            case 'get_agents':
                echo json_encode(getAgents($conn));
                break;
            default:
                echo json_encode([
                    'success' => false,
                    'message' => 'Action non reconnue'
                ]);
        }
        exit();
    }

    // Traitement des autres requêtes POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        
        try {
            $success = false;
            $message = '';
            
            if (isset($_POST['action'])) {
                switch ($_POST['action']) {
                    case 'update_usine':
                        // Code existant pour update_usine
                        break;
                    case 'update_chef_mission':
                        // Code existant pour update_chef_mission
                        break;
                    case 'update_vehicule':
                        // Code existant pour update_vehicule
                        break;
                    default:
                        throw new Exception('Action non reconnue');
                }
            } else {
                throw new Exception('Action non spécifiée');
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
        exit();
    }

    // Traitement des autres requêtes POST pour les mises à jour
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        
        try {
            $success = false;
            $message = '';
            
            if (!isset($_POST['action'])) {
                throw new Exception('Action non spécifiée');
            }

            switch ($_POST['action']) {
                case 'update_usine':
                    if (!isset($_POST['id_ticket']) || !isset($_POST['id_usine'])) {
                        throw new Exception('Paramètres manquants pour la mise à jour de l\'usine');
                    }
                    // Code pour update_usine
                    break;

                case 'update_chef_mission':
                    if (!isset($_POST['id_ticket']) || !isset($_POST['id_chef_mission'])) {
                        throw new Exception('Paramètres manquants pour la mise à jour du chef de mission');
                    }
                    // Code pour update_chef_mission
                    break;

                case 'update_vehicule':
                    if (!isset($_POST['id_ticket']) || !isset($_POST['id_vehicule'])) {
                        throw new Exception('Paramètres manquants pour la mise à jour du véhicule');
                    }
                    // Code pour update_vehicule
                    break;

                default:
                    throw new Exception('Action non reconnue');
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
        exit();
    }
}

?>

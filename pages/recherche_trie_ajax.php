<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_tickets.php';
require_once '../inc/functions/requete/requete_usines.php';
require_once '../inc/functions/requete/requete_vehicules.php';
require_once '../inc/functions/requete/requete_agents.php';

// Récupérer les filtres depuis la requête GET
$filters = [];
if (!empty($_GET['numero_ticket'])) $filters['numero_ticket'] = $_GET['numero_ticket'];
if (!empty($_GET['vehicule'])) $filters['vehicule'] = $_GET['vehicule'];
if (!empty($_GET['id_agent'])) $filters['id_agent'] = $_GET['id_agent'];
if (!empty($_GET['usine'])) $filters['usine'] = $_GET['usine'];
if (!empty($_GET['date_ticket_debut'])) $filters['date_ticket_debut'] = $_GET['date_ticket_debut'];
if (!empty($_GET['date_ticket_fin'])) $filters['date_ticket_fin'] = $_GET['date_ticket_fin'];
if (!empty($_GET['date_ajout_debut'])) $filters['date_ajout_debut'] = $_GET['date_ajout_debut'];
if (!empty($_GET['date_ajout_fin'])) $filters['date_ajout_fin'] = $_GET['date_ajout_fin'];

// Récupérer les tickets filtrés
$conn = getConnection();
$tickets_list = getTickets($conn, $filters);

// Générer le HTML des résultats
foreach ($tickets_list as $ticket): ?>
    <tr>
        <td><?= isset($ticket['created_at']) ? date('d/m/Y', strtotime($ticket['created_at'])) : '-' ?></td>
        <td><?= isset($ticket['date_ticket']) ? date('d/m/Y', strtotime($ticket['date_ticket'])) : '-' ?></td>
        <td><?= isset($ticket['numero_ticket']) ? $ticket['numero_ticket'] : '-' ?></td>
        <td><?= isset($ticket['nom_usine']) ? $ticket['nom_usine'] : '-' ?></td>
        <td><?= isset($ticket['poids']) ? $ticket['poids'] : '-' ?></td>
        <td><?= isset($ticket['prix_unitaire']) ? $ticket['prix_unitaire'] : '-' ?></td>
        <td><?= isset($ticket['agent_nom_complet']) ? $ticket['agent_nom_complet'] : '-' ?></td>
        <td><?= isset($ticket['matricule_vehicule']) ? $ticket['matricule_vehicule'] : '-' ?></td>
        <td>
            <button type="button" class="btn btn-info btn-sm rounded-circle" data-toggle="modal" data-target="#ticketModal<?= $ticket['id_ticket'] ?>" title="Voir les détails">
                <i class="fas fa-eye"></i>
            </button>
        </td>
    </tr>
<?php endforeach; ?>

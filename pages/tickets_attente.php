<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_tickets.php';
require_once '../inc/functions/requete/requete_usines.php';
require_once '../inc/functions/requete/requete_chef_equipes.php';
require_once '../inc/functions/requete/requete_vehicules.php';
require_once '../inc/functions/requete/requete_agents.php';

include('header.php');

$limit = $_GET['limit'] ?? 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Récupérer les paramètres de filtrage
$agent_id = $_GET['agent_id'] ?? null;
$usine_id = $_GET['usine_id'] ?? null;
$date_debut = $_GET['date_debut'] ?? '';
$date_fin = $_GET['date_fin'] ?? '';
$search_agent = $_GET['search_agent'] ?? '';
$search_usine = $_GET['search_usine'] ?? '';
$numero_ticket = $_GET['numero_ticket'] ?? '';

// Récupérer les tickets en attente avec les filtres
$tickets = getTicketsAttente($conn, $agent_id, $usine_id, $date_debut, $date_fin, $numero_ticket);

// Filtrer les tickets si un terme de recherche est présent
if (!empty($search_agent) || !empty($search_usine)) {
    $tickets = array_filter($tickets, function($ticket) use ($search_agent, $search_usine) {
        $match = true;
        if (!empty($search_agent)) {
            $match = $match && stripos($ticket['agent_nom_complet'], $search_agent) !== false;
        }
        if (!empty($search_usine)) {
            $match = $match && stripos($ticket['nom_usine'], $search_usine) !== false;
        }
        return $match;
    });
}

// Calculer la pagination
$total_tickets = count($tickets);
$total_pages = ceil($total_tickets / $limit);
$page = max(1, min($page, $total_pages));
$offset = ($page - 1) * $limit;

// Extraire les tickets pour la page courante
$tickets_list = array_slice($tickets, $offset, $limit);

// Récupérer les listes pour l'autocomplétion
$agents = getAgents($conn);
$usines = getUsines($conn);
?>

<!-- Barre de recherche en haut -->
<div class="search-container mb-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <form id="filterForm" method="GET">
                <div class="row">
                    <!-- Numéro de ticket -->
                    <div class="col-md-3 mb-3">
                        <input type="text" 
                               class="form-control" 
                               name="numero_ticket" 
                               id="numero_ticket"
                               placeholder="Numéro de ticket" 
                               value="<?= htmlspecialchars($numero_ticket) ?>">
                    </div>

                    <!-- Recherche par agent -->
                    <div class="col-md-3 mb-3">
                        <select class="form-control" name="agent_id" id="agent_select">
                            <option value="">Sélectionner un agent</option>
                            <?php foreach($agents as $agent): ?>
                                <option value="<?= $agent['id_agent'] ?>" <?= ($agent_id == $agent['id_agent']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($agent['nom_complet_agent']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Recherche par usine -->
                    <div class="col-md-3 mb-3">
                        <select class="form-control" name="usine_id" id="usine_select">
                            <option value="">Sélectionner une usine</option>
                            <?php foreach($usines as $usine): ?>
                                <option value="<?= $usine['id_usine'] ?>" <?= ($usine_id == $usine['id_usine']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($usine['nom_usine']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Date de début -->
                    <div class="col-md-3 mb-3">
                        <input type="date" 
                               class="form-control" 
                               name="date_debut" 
                               id="date_debut"
                               placeholder="Date de début" 
                               value="<?= htmlspecialchars($date_debut) ?>">
                    </div>

                    <!-- Date de fin -->
                    <div class="col-md-3 mb-3">
                        <input type="date" 
                               class="form-control" 
                               name="date_fin" 
                               id="date_fin"
                               placeholder="Date de fin" 
                               value="<?= htmlspecialchars($date_fin) ?>">
                    </div>

                    <!-- Boutons -->
                    <div class="col-12 text-center">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-search"></i> Rechercher
                        </button>
                        <a href="tickets_attente.php" class="btn btn-outline-danger">
                            <i class="fa fa-times"></i> Réinitialiser les filtres
                        </a>
                    </div>
                </div>
            </form>
            
            <!-- Filtres actifs -->
            <?php if($agent_id || $usine_id || $date_debut || $date_fin || $numero_ticket): ?>
            <div class="active-filters mt-3">
                <div class="d-flex align-items-center flex-wrap">
                    <strong class="text-muted mr-2">Filtres actifs :</strong>
                    <?php if($numero_ticket): ?>
                        <span class="badge badge-info mr-2 p-2">
                            <i class="fa fa-ticket"></i>
                            Ticket N°: <?= htmlspecialchars($numero_ticket) ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['numero_ticket' => null])) ?>" class="text-white ml-2">
                                <i class="fa fa-times"></i>
                            </a>
                        </span>
                    <?php endif; ?>
                    <?php if($agent_id): ?>
                        <?php 
                        $agent_name = '';
                        foreach($agents as $agent) {
                            if($agent['id_agent'] == $agent_id) {
                                $agent_name = $agent['nom_complet_agent'];
                                break;
                            }
                        }
                        ?>
                        <span class="badge badge-info mr-2 p-2">
                            <i class="fa fa-user"></i> 
                            Agent: <?= htmlspecialchars($agent_name) ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['agent_id' => null])) ?>" class="text-white ml-2">
                                <i class="fa fa-times"></i>
                            </a>
                        </span>
                    <?php endif; ?>
                    <?php if($usine_id): ?>
                        <?php 
                        $usine_name = '';
                        foreach($usines as $usine) {
                            if($usine['id_usine'] == $usine_id) {
                                $usine_name = $usine['nom_usine'];
                                break;
                            }
                        }
                        ?>
                        <span class="badge badge-info mr-2 p-2">
                            <i class="fa fa-building"></i>
                            Usine: <?= htmlspecialchars($usine_name) ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['usine_id' => null])) ?>" class="text-white ml-2">
                                <i class="fa fa-times"></i>
                            </a>
                        </span>
                    <?php endif; ?>
                    <?php if($date_debut): ?>
                        <span class="badge badge-info mr-2 p-2">
                            <i class="fa fa-calendar"></i>
                            Depuis: <?= htmlspecialchars($date_debut) ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['date_debut' => null])) ?>" class="text-white ml-2">
                                <i class="fa fa-times"></i>
                            </a>
                        </span>
                    <?php endif; ?>
                    <?php if($date_fin): ?>
                        <span class="badge badge-info mr-2 p-2">
                            <i class="fa fa-calendar"></i>
                            Jusqu'au: <?= htmlspecialchars($date_fin) ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['date_fin' => null])) ?>" class="text-white ml-2">
                                <i class="fa fa-times"></i>
                            </a>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function appliquerFiltres() {
    const agent_id = document.getElementById('agent_select').value;
    const usine_id = document.getElementById('usine_select').value;
    const date_debut = document.getElementById('date_debut').value;
    const date_fin = document.getElementById('date_fin').value;
    const numero_ticket = document.getElementById('numero_ticket').value;
    
    let params = new URLSearchParams(window.location.search);
    
    if (numero_ticket) params.set('numero_ticket', numero_ticket);
    else params.delete('numero_ticket');
    
    if (agent_id) params.set('agent_id', agent_id);
    else params.delete('agent_id');
    
    if (usine_id) params.set('usine_id', usine_id);
    else params.delete('usine_id');
    
    if (date_debut) params.set('date_debut', date_debut);
    else params.delete('date_debut');
    
    if (date_fin) params.set('date_fin', date_fin);
    else params.delete('date_fin');
    
    window.location.href = '?' + params.toString();
}

// Initialiser les sélecteurs de date
document.addEventListener('DOMContentLoaded', function() {
    const date_debut = document.getElementById('date_debut');
    const date_fin = document.getElementById('date_fin');
    
    // Mettre à jour la date de fin minimale lorsque la date de début change
    date_debut.addEventListener('change', function() {
        date_fin.min = this.value;
    });
    
    // Mettre à jour la date de début maximale lorsque la date de fin change
    date_fin.addEventListener('change', function() {
        date_debut.max = this.value;
    });
});
</script>

<!-- Ajout du style pour l'autocomplétion -->
<style>
.ui-autocomplete {
    max-height: 200px;
    overflow-y: auto;
    overflow-x: hidden;
    z-index: 1000;
    background-color: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 5px 0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.ui-menu-item {
    padding: 8px 15px;
    cursor: pointer;
    list-style: none;
}

.ui-menu-item:hover {
    background-color: #f8f9fa;
}

.search-container {
    background-color: #f8f9fa;
    padding: 20px;
    border-radius: 5px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.input-group .form-control {
    height: 45px;
    font-size: 16px;
    border-radius: 4px;
}

.input-group .btn {
    padding: 0 20px;
}

.active-filters {
    background-color: #fff;
    padding: 10px 15px;
    border-radius: 4px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.badge {
    font-size: 0.9rem;
    padding: 8px 12px;
    background-color: #17a2b8;
    border: none;
}

.badge a {
    text-decoration: none;
}

.badge a:hover {
    opacity: 0.8;
}

.badge i {
    margin-right: 5px;
}

.btn-outline-danger {
    border-radius: 20px;
    padding: 5px 15px;
}

.input-group .form-control {
    height: 45px;
    font-size: 16px;
    border-radius: 4px;
}

.input-group-append .btn {
    border-top-right-radius: 4px;
    border-bottom-right-radius: 4px;
}

.spacing {
    margin-right: 10px; 
    margin-bottom: 20px;
}
</style>

<!-- Ajout de jQuery UI pour l'autocomplétion -->
<link rel="stylesheet" href="../../plugins/jquery-ui/jquery-ui.min.css">
<script src="../../plugins/jquery-ui/jquery-ui.min.js"></script>

<script>
$(document).ready(function() {
    // Préparer les données des agents pour l'autocomplétion
    var agents = <?= json_encode(array_map(function($agent) {
        return [
            'value' => $agent['id_agent'],
            'label' => $agent['nom_complet_agent']
        ];
    }, $agents)) ?>;

    // Préparer les données des usines pour l'autocomplétion
    var usines = <?= json_encode(array_map(function($usine) {
        return [
            'value' => $usine['id_usine'],
            'label' => $usine['nom_usine']
        ];
    }, $usines)) ?>;

    // Autocomplétion pour les agents
    $("#agent_search").autocomplete({
        source: function(request, response) {
            var term = request.term.toLowerCase();
            var matches = agents.filter(function(agent) {
                return agent.label.toLowerCase().indexOf(term) !== -1;
            });
            response(matches);
        },
        select: function(event, ui) {
            window.location.href = 'tickets_attente.php?' + $.param({
                ...getUrlParams(),
                'agent_id': ui.item.value,
                'search_agent': ui.item.label
            });
            return false;
        },
        minLength: 1
    });

    // Autocomplétion pour les usines
    $("#usine_search").autocomplete({
        source: function(request, response) {
            var term = request.term.toLowerCase();
            var matches = usines.filter(function(usine) {
                return usine.label.toLowerCase().indexOf(term) !== -1;
            });
            response(matches);
        },
        select: function(event, ui) {
            window.location.href = 'tickets_attente.php?' + $.param({
                ...getUrlParams(),
                'usine_id': ui.item.value,
                'search_usine': ui.item.label
            });
            return false;
        },
        minLength: 1
    });

    // Fonction utilitaire pour obtenir les paramètres d'URL actuels
    function getUrlParams() {
        var params = {};
        window.location.search.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(str, key, value) {
            params[key] = value;
        });
        return params;
    }
});
</script>

  <style>
  .pagination-container {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 20px;
}

.pagination-link {
    padding: 8px;
    text-decoration: none;
    color: white;
    background-color: #007bff; 
    border: 1px solid #007bff;
    border-radius: 4px; 
    margin-right: 4px;
}

.items-per-page-form {
    margin-left: 20px;
}

label {
    margin-right: 5px;
}

.items-per-page-select {
    padding: 6px;
    border-radius: 4px; 
}

.submit-button {
    padding: 6px 10px;
    background-color: #007bff;
    color: #fff;
    border: none;
    border-radius: 4px; 
    cursor: pointer;
}
 .custom-icon {
            color: green;
            font-size: 24px;
            margin-right: 8px;
 }
 .spacing {
    margin-right: 10px; 
    margin-bottom: 20px;
}
</style>

  <style>
        @media only screen and (max-width: 767px) {
            
            th {
                display: none; 
            }
            tbody tr {
                display: block;
                margin-bottom: 20px;
                border: 1px solid #ccc;
                padding: 10px;
            }
            tbody tr td::before {

                font-weight: bold;
                margin-right: 5px;
            }
        }
        .margin-right-15 {
        margin-right: 15px;
       }
        .block-container {
      background-color:  #d7dbdd ;
      padding: 20px;
      border-radius: 5px;
      width: 100%;
      margin-bottom: 20px;
    }
    </style>


<div class="row">

<div class="block-container">
    <div class="d-flex justify-content-between align-items-center">
        <h3><i class="fa fa-stop text-success"></i> Liste des Tickets attente</h3>
        <div class="text-muted">
            Total: <?php echo $total_tickets; ?> ticket(s) en attente
        </div>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalUsineTickets">
              <i class="fa fa-edit"></i> Validation en masse    
            </button>
    </div>
</div>
</div>



 <!-- <button type="button" class="btn btn-primary spacing" data-toggle="modal" data-target="#add-commande">
    Enregistrer une commande
  </button>


    <button type="button" class="btn btn-outline-secondary spacing" data-toggle="modal" data-target="#recherche-commande1">
        <i class="fas fa-print custom-icon"></i>
    </button>


  <a class="btn btn-outline-secondary" href="commandes_print.php"><i class="fa fa-print" style="font-size:24px;color:green"></i></a>


     Utilisation du formulaire Bootstrap avec ms-auto pour aligner à droite
<form action="page_recherche.php" method="GET" class="d-flex ml-auto">
    <input class="form-control me-2" type="search" name="recherche" style="width: 400px;" placeholder="Recherche..." aria-label="Search">
    <button class="btn btn-outline-primary spacing" style="margin-left: 15px;" type="submit">Rechercher</button>
</form>

-->




<div class="table-responsive">
    <!-- Loader -->
    <div id="loader" class="text-center p-3">
        <img src="../dist/img/loading.gif" alt="Chargement..." />
    </div>
    <!-- Table qui sera initialement cachée -->
    <table id="example1" class="table table-bordered table-striped" style="display: none;">

 <!-- <table style="max-height: 90vh !important; overflow-y: scroll !important" id="example1" class="table table-bordered table-striped">-->
    <thead>
      <tr>
        
        <th>Date ticket</th>
        <th>Numero Ticket</th>
        <th>usine</th>
        <th>Chargé de mission</th>
        <th>Vehicule</th>
        <th>Poids</th>
        <th>Ticket crée par</th>
        <th>Date Ajout</th>
        <th>Prix Unitaire</th>
        <th>Date validation</th>
        <th>Montant</th>
        <th>Date Paie</th>
        <th>Validation de tickets</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($tickets_list)) : ?>
        <?php foreach ($tickets_list as $ticket) : ?>
          <tr>
          
            <td><?= date('d/m/Y', strtotime($ticket['date_ticket'])) ?></td>
            <td><a href="#" data-toggle="modal" data-target="#ticketModal<?= $ticket['id_ticket'] ?>"><?= $ticket['numero_ticket'] ?></a></td>
            <td><a href="javascript:void(0)" onclick="showUsineTickets(<?= $ticket['id_usine'] ?>, '<?= addslashes($ticket['nom_usine']) ?>')"><?= $ticket['nom_usine'] ?></a></td>
            <td><?= $ticket['agent_nom_complet'] ?></td>
            <td><?= $ticket['matricule_vehicule'] ?></td>
            <td><?= $ticket['poids'] ?></td>

            <td><?= $ticket['utilisateur_nom_complet'] ?></td>
            <td><?= date('d/m/Y', strtotime($ticket['created_at'])) ?></td>

           <td>
              <?php if ($ticket['prix_unitaire'] === null || $ticket['prix_unitaire'] == 0.00): ?>
                  <!-- Affichage d'un bouton rouge désactivé avec message -->
                  <button class="btn btn-danger btn-block" disabled>
                      En Attente de validation
                  </button>
              <?php else: ?>
                  <!-- Affichage du prix unitaire dans un bouton noir -->
                  <button class="btn btn-dark btn-block" disabled>
                      <?= $ticket['prix_unitaire'] ?>
                  </button>
              <?php endif; ?>
          </td>




         <td>
              <?php if ($ticket['date_validation_boss'] === null): ?>
          <button class="btn btn-warning btn-block" disabled>
              En cours
          </button>
      <?php else: ?>
          <?= date('d/m/Y', strtotime($ticket['date_validation_boss'])) ?>
          <?php endif; ?>
         </td>


        <td>
                    <?php if ($ticket['montant_paie'] === null): ?>
            <button class="btn btn-primary btn-block" disabled>
                En attente de PU
            </button>
        <?php else: ?>
        <button class="btn btn-info btn-block" disabled>
            <?= $ticket['montant_paie'] ?>
            <?php endif; ?>
            </button>
          </td>


              <td>
                <?php if ($ticket['date_paie'] === null): ?>
            <button class="btn btn-dark btn-block" disabled>
                Paie non encore effectuée
            </button>
        <?php else: ?>
            <?= date('d/m/Y', strtotime($ticket['date_paie'])) ?>
            <?php endif; ?>
          </td>
          
  
          <td class="actions">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#valider_un_ticket<?= $ticket['id_ticket'] ?>">
              <i class="fa fa-edit"></i> Valider un ticket
            </button>
          </td>
          <div class="modal" id="valider_un_ticket<?= $ticket['id_ticket'] ?>">

          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-body">
                <form action="valider_un_tickets.php" method="post">
                  <input type="hidden" name="id_ticket" value="<?= $ticket['id_ticket'] ?>">
                  <input type="hidden" name="current_url" value="<?= $_SERVER['REQUEST_URI'] ?>">
                  <div class="form-group">
                    <label>Ajouter le prix unitaire</label>
                  </div>
                  <div class="form-group">
                    <input type="number" 
                           class="form-control" 
                           name="prix_unitaire" 
                           value="<?= $ticket['prix_unitaire'] ?>" 
                           <?= ($ticket['prix_unitaire'] > 0) ? 'readonly' : '' ?> 
                           min="0.01" 
                           step="0.01" 
                           required>
                  </div>
                  <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Valider</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>

<script>
function submitValidation(event, ticketId) {
    event.preventDefault();
    const form = document.getElementById('form-validation-' + ticketId);
    const prix_unitaire = form.querySelector('[name="prix_unitaire"]').value;
    const id_ticket = form.querySelector('[name="id_ticket"]').value;

    if (!prix_unitaire || prix_unitaire <= 0) {
        alert('Veuillez entrer un prix unitaire valide');
        return false;
    }

    $.ajax({
        url: 'valider_tickets.php',
        method: 'POST',
        data: {
            ticket_id: id_ticket,
            prix_unitaire: prix_unitaire
        },
        success: function(response) {
            try {
                const data = typeof response === 'string' ? JSON.parse(response) : response;
                if (data.success) {
                    // Fermer le modal
                    $(`#valider_ticket${ticketId}`).modal('hide');
                    // Recharger la page
                    window.location.reload();
                } else {
                    alert(data.message || 'Erreur lors de la validation du ticket');
                }
            } catch (e) {
                console.error('Erreur de parsing:', e);
                alert('Erreur lors du traitement de la réponse');
            }
        },
        error: function(xhr, status, error) {
            console.error('Erreur:', error);
            console.error('Response:', xhr.responseText);
            alert('Erreur lors de la validation du ticket');
        }
    });

    return false;
}

// Pour la validation multiple
function validerTicketsSelectionnes() {
    const selectedTickets = [];
    $('.ticket-checkbox:checked').each(function() {
        selectedTickets.push($(this).val());
    });

    if (selectedTickets.length === 0) {
        alert('Veuillez sélectionner au moins un ticket à valider');
        return;
    }

    if (confirm('Voulez-vous vraiment valider les tickets sélectionnés ?')) {
        $.ajax({
            url: 'valider_tickets.php',
            method: 'POST',
            data: { 
                ticket_ids: selectedTickets,
                is_mass_validation: true
            },
            success: function(response) {
                try {
                    const data = typeof response === 'string' ? JSON.parse(response) : response;
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'Erreur lors de la validation des tickets');
                    }
                } catch (e) {
                    console.error('Erreur de parsing:', e);
                    alert('Erreur lors du traitement de la réponse');
                }
            },
            error: function(xhr, status, error) {
                console.error('Erreur:', error);
                console.error('Response:', xhr.responseText);  // Pour voir la réponse brute
                alert('Erreur lors de la validation des tickets: ' + error);
            }
        });
    }
}
</script>

      <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="13" class="text-center">Pas de tickets en attente de validation</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

</div>

  <div class="pagination-container bg-secondary d-flex justify-content-center w-100 text-white p-3">
    <?php if($page > 1): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="btn btn-primary"><</a>
    <?php endif; ?>
    
    <span class="mx-2"><?= $page . '/' . $total_pages ?></span>
    
    <?php if($page < $total_pages): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="btn btn-primary">></a>
    <?php endif; ?>
    
    <form action="" method="get" class="items-per-page-form ml-3">
        <?php
        // Conserver les paramètres de filtrage actuels
        foreach (['agent_id', 'usine_id', 'search_agent', 'search_usine', 'numero_ticket'] as $param) {
            if (isset($_GET[$param])) {
                echo '<input type="hidden" name="' . $param . '" value="' . htmlspecialchars($_GET[$param]) . '">';
            }
        }
        ?>
        <label for="limit">Afficher :</label>
        <select name="limit" id="limit" class="items-per-page-select" onchange="this.form.submit()">
            <option value="15" <?= $limit == 15 ? 'selected' : '' ?>>15</option>
            <option value="25" <?= $limit == 25 ? 'selected' : '' ?>>25</option>
            <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50</option>
            <option value="100" <?= $limit == 100 ? 'selected' : '' ?>>100</option>
        </select>
    </form>
  </div>





<!-- Modal de recherche par agent -->
<div class="modal fade" id="searchByAgentModal" tabindex="-1" role="dialog" aria-labelledby="searchByAgentModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="searchByAgentModalLabel">Filtrer par agent</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="get">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="agent_id">Sélectionner un agent</label>
                        <select class="form-control" name="agent_id" id="agent_id" required>
                            <option value="">Choisir un agent</option>
                            <?php foreach ($agents as $agent): ?>
                                <option value="<?= $agent['id_agent'] ?>" <?= ($agent_id == $agent['id_agent'] ? 'selected' : '') ?>>
                                    <?= htmlspecialchars($agent['nom_agent'] . ' ' . $agent['prenom_agent']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn btn-primary">Filtrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Recherche par tickets-->
<div class="modal fade" id="search_ticket">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-search mr-2"></i>Rechercher des tickets
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="d-flex flex-column">
                    <button type="button" class="btn btn-primary btn-block mb-3" data-toggle="modal" data-target="#searchByAgentModal" data-dismiss="modal">
                        <i class="fas fa-user-tie mr-2"></i>Recherche par chargé de Mission
                    </button>
                    
                    <button type="button" class="btn btn-primary btn-block mb-3" data-toggle="modal" data-target="#searchByUsineModal" data-dismiss="modal">
                        <i class="fas fa-industry mr-2"></i>Recherche par Usine
                    </button>
                    
                    <button type="button" class="btn btn-primary btn-block mb-3" data-toggle="modal" data-target="#searchByDateModal" data-dismiss="modal">
                        <i class="fas fa-calendar-alt mr-2"></i>Recherche par Date
                    </button>
                    
                    <button type="button" class="btn btn-primary btn-block mb-3" data-toggle="modal" data-target="#searchByVehiculeModal" data-dismiss="modal">
                        <i class="fas fa-truck mr-2"></i>Recherche par Véhicule
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Recherche par Agent -->
<div class="modal fade" id="searchByAgentModal" tabindex="-1" role="dialog" aria-labelledby="searchByAgentModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="searchByAgentModalLabel">
                    <i class="fas fa-user-tie mr-2"></i>Recherche par chargé de Mission
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="searchByAgentForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="agent_id">Sélectionner un chargé de Mission</label>
                        <select class="form-control" name="agent_id" id="agent_id" required>
                            <option value="">Choisir un chargé de Mission</option>
                            <?php foreach ($agents as $agent): ?>
                                <option value="<?= $agent['id_agent'] ?>">
                                    <?= $agent['nom_agent'] . ' ' . $agent['prenom_agent'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Rechercher</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Recherche par Usine -->
<div class="modal fade" id="searchByUsineModal" tabindex="-1" role="dialog" aria-labelledby="searchByUsineModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="searchByUsineModalLabel">
                    <i class="fas fa-industry mr-2"></i>Recherche par Usine
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="searchByUsineForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="usine">Sélectionner une Usine</label>
                        <select class="form-control" name="usine" id="usine" required>
                            <option value="">Choisir une usine</option>
                            <?php foreach ($usines as $usine): ?>
                                <option value="<?= $usine['id_usine'] ?>">
                                    <?= $usine['nom_usine'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Rechercher</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Recherche par Date -->
<div class="modal fade" id="searchByDateModal" tabindex="-1" role="dialog" aria-labelledby="searchByDateModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="searchByDateModalLabel">
                    <i class="fas fa-calendar-alt mr-2"></i>Recherche par Date
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="searchByDateForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="date_creation">Sélectionner une Date</label>
                        <input type="date" class="form-control" id="date_creation" name="date_creation" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Rechercher</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Recherche par Véhicule -->
<div class="modal fade" id="searchByVehiculeModal" tabindex="-1" role="dialog" aria-labelledby="searchByVehiculeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="searchByVehiculeModalLabel">
                    <i class="fas fa-truck mr-2"></i>Recherche par Véhicule
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="searchByVehiculeForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="chauffeur">Sélectionner un Véhicule</label>
                        <select class="form-control" name="chauffeur" id="chauffeur" required>
                            <option value="">Choisir un véhicule</option>
                            <?php foreach ($vehicules as $vehicule): ?>
                                <option value="<?= $vehicule['vehicules_id'] ?>">
                                    <?= $vehicule['matricule_vehicule'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Rechercher</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Gestionnaire pour le formulaire de recherche par usine
document.getElementById('searchByUsineForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const usineId = document.getElementById('usine').value;
    if (usineId) {
        window.location.href = 'tickets.php?usine=' + usineId;
    }
});

// Gestionnaire pour le formulaire de recherche par date
document.getElementById('searchByDateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const date = document.getElementById('date_creation').value;
    if (date) {
        window.location.href = 'tickets.php?date_creation=' + date;
    }
});

// Gestionnaire pour le formulaire de recherche par véhicule
document.getElementById('searchByVehiculeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const vehiculeId = document.getElementById('chauffeur').value;
    if (vehiculeId) {
        window.location.href = 'tickets.php?chauffeur=' + vehiculeId;
    }
});
</script>

<?php foreach ($tickets as $ticket) : ?>
  <div class="modal fade" id="ticketModal<?= $ticket['id_ticket'] ?>" tabindex="-1" role="dialog" aria-labelledby="ticketModalLabel<?= $ticket['id_ticket'] ?>" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="ticketModalLabel<?= $ticket['id_ticket'] ?>">
            <i class="fas fa-ticket-alt mr-2"></i>Détails du Ticket #<?= $ticket['numero_ticket'] ?>
          </h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="row mb-4">
            <div class="col-md-6">
              <div class="info-group">
                <label class="text-muted">Date du ticket:</label>
                <p class="font-weight-bold"><?= date('d/m/Y', strtotime($ticket['date_ticket'])) ?></p>
              </div>
              <div class="info-group">
                <label class="text-muted">Usine:</label>
                <p class="font-weight-bold"><?= $ticket['nom_usine'] ?></p>
              </div>
              <div class="info-group">
                <label class="text-muted">Agent:</label>
                <p class="font-weight-bold"><?= $ticket['agent_nom_complet'] ?></p>
              </div>
              <div class="info-group">
                <label class="text-muted">Véhicule:</label>
                <p class="font-weight-bold"><?= $ticket['matricule_vehicule'] ?></p>
              </div>
              <div class="info-group">
                <label class="text-muted">Poids ticket:</label>
                <p class="font-weight-bold"><?= $ticket['poids'] ?> kg</p>
              </div>
            </div>
            <div class="col-md-6">
              <div class="info-group">
                <label class="text-muted">Prix unitaire:</label>
                <p class="font-weight-bold"><?= number_format($ticket['prix_unitaire'], 2, ',', ' ') ?> FCFA</p>
              </div>
              <div class="info-group">
                <label class="text-muted">Montant à payer:</label>
                <p class="font-weight-bold text-primary"><?= number_format($ticket['montant_paie'], 2, ',', ' ') ?> FCFA</p>
              </div>
              <div class="info-group">
                <label class="text-muted">Montant payé:</label>
                <p class="font-weight-bold text-success"><?= number_format($ticket['montant_payer'] ?? 0, 2, ',', ' ') ?> FCFA</p>
              </div>
              <div class="info-group">
                <label class="text-muted">Reste à payer:</label>
                <p class="font-weight-bold <?= ($ticket['montant_reste'] == 0) ? 'text-success' : 'text-danger' ?>">
                  <?= number_format($ticket['montant_reste'] ?? $ticket['montant_paie'], 2, ',', ' ') ?> FCFA
                </p>
              </div>
            </div>
          </div>
          <div class="border-top pt-3">
            <div class="info-group">
              <label class="text-muted">Créé par:</label>
              <p class="font-weight-bold"><?= $ticket['utilisateur_nom_complet'] ?></p>
            </div>
            <div class="info-group">
              <label class="text-muted">Date de création:</label>
              <p class="font-weight-bold"><?= date('d/m/Y', strtotime($ticket['created_at'])) ?></p>
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
        </div>
      </div>
    </div>
  </div>

  <style>
  .info-group {
    margin-bottom: 15px;
  }
  .info-group label {
    display: block;
    font-size: 0.9em;
    margin-bottom: 2px;
  }
  .info-group p {
    margin-bottom: 0;
  }
  .modal-header.bg-primary {
    background-color: #007bff !important;
  }

  .modal-header .close.text-white {
    color: #fff;
    opacity: 1;
  }

  .modal-header .close.text-white:hover {
    opacity: 0.75;
  }
  </style>
<?php endforeach; ?>

</body>

</html>

<script>
function getUrlParams() {
    const params = new URLSearchParams(window.location.search);
    const urlParams = {};
    
    // Récupérer tous les paramètres actuels
    for(const [key, value] of params.entries()) {
        if(value) urlParams[key] = value;
    }
    
    return urlParams;
}

function submitValidation(event, ticketId) {
    event.preventDefault();
    const form = document.getElementById('form-validation-' + ticketId);
    const formData = new FormData(form);

    $.ajax({
        url: 'valider_tickets.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                // Construire l'URL de redirection avec les paramètres actuels
                const params = new URLSearchParams(urlParams);
                window.location.href = 'tickets_attente.php?' + params.toString();
            } else {
                alert(response.message || 'Erreur lors de la validation du ticket');
            }
        },
        error: function(xhr, status, error) {
            console.error('Erreur:', error);
            console.error('Response:', xhr.responseText);
            alert('Erreur lors de la validation du ticket');
        }
    });

    return false;
}

// Pour la validation multiple
function validerTicketsSelectionnes() {
    const selectedTickets = [];
    $('.ticket-checkbox:checked').each(function() {
        selectedTickets.push($(this).val());
    });

    console.log('=== Validation des tickets ===');
    console.log('Tickets sélectionnés:', selectedTickets);
    console.log('Nombre total:', selectedTickets.length);

    if (selectedTickets.length === 0) {
        alert('Veuillez sélectionner au moins un ticket');
        return;
    }

    if (confirm('Voulez-vous vraiment valider les ' + selectedTickets.length + ' ticket(s) sélectionné(s) ?')) {
        console.log('=== Envoi de la requête ===');
        console.log('URL:', 'valider_tickets.php');
        console.log('Données:', { ticket_ids: selectedTickets });

        $.ajax({
            url: 'valider_tickets.php',
            method: 'POST',
            data: {
                ticket_ids: selectedTickets
            },
            success: function(response) {
                console.log('=== Réponse reçue ===');
                console.log('Réponse brute:', response);
                
                try {
                    const data = JSON.parse(response);
                    console.log('Données parsées:', data);
                    
                    if (data.success) {
                        alert('Les tickets ont été validés avec succès');
                        $('#modalResultatsRecherche').modal('hide');
                        location.reload();
                    } else {
                        alert(data.message || 'Erreur lors de la validation');
                    }
                } catch (e) {
                    console.error('=== Erreur de parsing ===');
                    console.error('Type:', e.name);
                    console.error('Message:', e.message);
                    console.error('Stack:', e.stack);
                    alert('Erreur lors du traitement de la réponse');
                }
            },
            error: function(xhr, status, error) {
                console.error('=== Erreur AJAX ===');
                console.error('Status:', status);
                console.error('Erreur:', error);
                console.error('Réponse:', xhr.responseText);
                alert('Erreur lors de la validation des tickets');
            }
        });
    }
}
</script>
<script>
function validerTicketsSelectionnes() {
    // Pour la validation en masse uniquement
    const selectedTickets = [];
    $('.ticket-checkbox:checked').each(function() {
        selectedTickets.push($(this).val());
    });

    if (selectedTickets.length === 0) {
        alert('Veuillez sélectionner au moins un ticket à valider');
        return;
    }

    $.ajax({
        url: 'valider_tickets.php',
        method: 'POST',
        data: { 
            ticket_ids: selectedTickets,
            is_mass_validation: true
        },
        success: function(response) {
            try {
                const data = typeof response === 'string' ? JSON.parse(response) : response;
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Erreur lors de la validation des tickets');
                }
            } catch (e) {
                console.error('Erreur:', e);
                alert('Erreur lors du traitement de la réponse');
            }
        },
        error: function(xhr, status, error) {
            console.error('Erreur:', error);
            console.error('Response:', xhr.responseText);  // Pour voir la réponse brute
            alert('Erreur lors de la validation des tickets: ' + error);
        }
    });
}

function submitValidation(event, ticketId) {
    event.preventDefault();
    const form = document.getElementById('form-validation-' + ticketId);
    const prix_unitaire = form.querySelector('[name="prix_unitaire"]').value;

    if (!prix_unitaire || prix_unitaire <= 0) {
        alert('Veuillez entrer un prix unitaire valide');
        return false;
    }

    // Validation d'un seul ticket
    $.ajax({
        url: 'valider_tickets.php',
        method: 'POST',
        data: {
            ticket_id: ticketId,
            prix_unitaire: prix_unitaire,
            is_mass_validation: false
        },
        success: function(response) {
            try {
                const data = typeof response === 'string' ? JSON.parse(response) : response;
                if (data.success) {
                    $(`#valider_ticket${ticketId}`).modal('hide');
                    window.location.reload();
                } else {
                    alert(data.message || 'Erreur lors de la validation du ticket');
                }
            } catch (e) {
                console.error('Erreur:', e);
                alert('Erreur lors du traitement de la réponse');
            }
        },
        error: function(xhr, error) {
            console.error('Erreur:', error);
            alert('Erreur lors de la validation du ticket');
        }
    });

    return false;
}
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation de tous les modals
    $('.modal').modal({
        keyboard: false,
        backdrop: 'static',
        show: false
    });

    // Gestionnaire spécifique pour le modal d'ajout
    $('#add-ticket').on('show.bs.modal', function (e) {
        console.log('Modal add-ticket en cours d\'ouverture');
    });
});
</script>
<script src="../../plugins/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="../../plugins/jquery-ui/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<!-- <script>
  $.widget.bridge('uibutton', $.ui.button)
</script>-->
<!-- Bootstrap 4 -->
<script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- ChartJS -->
<script src="../../plugins/chart.js/Chart.min.js"></script>
<!-- Sparkline -->
<script src="../../plugins/sparklines/sparkline.js"></script>
<!-- JQVMap -->
<script src="../../plugins/jqvmap/jquery.vmap.min.js"></script>
<script src="../../plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
<!-- jQuery Knob Chart -->
<script src="../../plugins/jquery-knob/jquery.knob.min.js"></script>
<!-- daterangepicker -->
<script src="../../plugins/moment/moment.min.js"></script>
<script src="../../plugins/daterangepicker/daterangepicker.js"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="../../plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- Summernote -->
<script src="../../plugins/summernote/summernote-bs4.min.js"></script>
<!-- overlayScrollbars -->
<script src="../../plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- AdminLTE App -->
<script src="../../dist/js/adminlte.js"></script>
<?php

if (isset($_SESSION['popup']) && $_SESSION['popup'] ==  true) {
  ?>
    <script>
      var audio = new Audio("../inc/sons/notification.mp3");
      audio.volume = 1.0; // Assurez-vous que le volume n'est pas à zéro
      audio.play().then(() => {
        // Lecture réussie
        var Toast = Swal.mixin({
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000
        });
  
        Toast.fire({
          icon: 'success',
          title: 'Action effectuée avec succès.'
        });
      }).catch((error) => {
        console.error('Erreur de lecture audio :', error);
      });
    </script>
  <?php
    $_SESSION['popup'] = false;
  }
  ?>



<!------- Delete Pop--->
<?php

if (isset($_SESSION['delete_pop']) && $_SESSION['delete_pop'] ==  true) {
?>
  <script>
    var Toast = Swal.mixin({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 3000
    });

    Toast.fire({
      icon: 'error',
      title: 'Action échouée.'
    })
  </script>

<?php
  $_SESSION['delete_pop'] = false;
}
?>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<!--<script src="dist/js/pages/dashboard.js"></script>-->
<script>
function showSearchModal(modalId) {
  // Hide all modals
  document.querySelectorAll('.modal').forEach(modal => {
    $(modal).modal('hide');
  });

  // Show the selected modal
  $('#' + modalId).modal('show');
}
</script>

<!-- Modal Validation en Masse -->
<div class="modal fade" id="modalUsineTickets" tabindex="-1" role="dialog" aria-labelledby="modalUsineTicketsLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalUsineTicketsLabel">Validation en Masse des Tickets</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formValidationMasse">
                  <!--  <form action="valider_tickets_masse.php" method="POST">-->
                    <div class="row">
                        <!-- Sélection des agents -->
                        <div class="col-md-6 mb-3">
                            <label for="agents" class="font-weight-bold">Agents</label>
                            <select class="form-control select2-agents" id="agents" name="agents">
                                <?php
                                $agents = getAgents($conn);
                                foreach($agents as $agent) {
                                    echo '<option value="'.$agent['id_agent'].'">'.$agent['nom_agent'].' '.$agent['prenom_agent'].'</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <!-- Sélection des usines -->
                        <div class="col-md-6 mb-3">
                            <label for="usines" class="font-weight-bold">Usines</label>
                            <select class="form-control select2-usines" id="usines" name="usines">
                                <?php
                                $usines = getUsines($conn);
                                foreach($usines as $usine) {
                                    echo '<option value="'.$usine['id_usine'].'">'.$usine['nom_usine'].'</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <!-- Date début -->
                        <div class="col-md-6 mb-3">
                            <label for="date_debut" class="font-weight-bold">Date début</label>
                            <input type="date" class="form-control" id="fdate_debut" name="date_debut" required>
                        </div>
                        <!-- Date fin -->
                        <div class="col-md-6 mb-3">
                            <label for="date_fin" class="font-weight-bold">Date fin</label>
                            <input type="date" class="form-control" id="fdate_fin" name="date_fin" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary" onclick="validerEnMasse()">Rechercher les tickets</button>
            </div>
        </div>
    </div>
</div>

<!-- Script pour l'initialisation des select2 et la validation en masse -->
<script>
$(document).ready(function() {
    // Initialisation des select2 pour les agents
    $('.select2-agents').select2({
        placeholder: 'Sélectionner des agents',
        language: 'fr',
        width: '100%',
        dropdownParent: $('#modalUsineTickets'),
        allowClear: true
    });

    // Initialisation des select2 pour les usines
    $('.select2-usines').select2({
        placeholder: 'Sélectionner des usines',
        language: 'fr',
        width: '100%',
        dropdownParent: $('#modalUsineTickets'),
        allowClear: true
    });

    // Reset form on modal close
    $('#modalUsineTickets').on('hidden.bs.modal', function () {
        $('#formValidationMasse')[0].reset();
        $('.select2-agents, .select2-usines').val(null).trigger('change');
    });
});

function validerEnMasse() {
    const agent_id = $('#agents').val();
    const usine_id = $('#usines').val();
    const date_debut = $('#fdate_debut').val();
    const date_fin = $('#fdate_fin').val();

    // const date_debut = document.getElementById('fdate_debut').value;
    // const date_fin = document.getElementById('fdate_fin').value;

    // Afficher les valeurs sélectionnées
    console.log('=== Valeurs sélectionnées ===');
    console.log('Agent ID:', agent_id);
    console.log('Agent Nom:', $('#agents option:selected').text());
    console.log('Usine ID:', usine_id);
    console.log('Usine Nom:', $('#usines option:selected').text());
    console.log('Date début:', date_debut);
    console.log('Date fin:', date_fin);
    console.log('========================');

    // Validation des champs
    if (!agent_id || !usine_id || !date_debut || !date_fin) {
        alert('Veuillez remplir tous les champs');
        return;
    }

    // Rechercher les tickets
    $.ajax({
        url: 'rechercher_tickets.php',
        method: 'POST',
        data: {
            agent_id: agent_id,
            usine_id: usine_id,
            date_debut: date_debut,
            date_fin: date_fin
        },
        success: function(response) {
            try {
                console.log('=== Réponse du serveur ===');
                
                console.log('Données reçues:', response);
                const data = response;
                console.log('=== Réponse du serveur ===');
                console.log('Données reçues:', data);
                console.log('Nombre de tickets:', data.tickets ? data.tickets.length : 0);
                console.log('========================');

                if (data.success) {
                    // Remplir le tableau avec les résultats
                    let html = '';
                    data.tickets.forEach(ticket => {
                        html += `
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" class="ticket-checkbox" value="${ticket.id_ticket}">
                                </td>
                                <td>${ticket.numero_ticket}</td>
                                <td>${ticket.date_ticket}</td>
                                <td>${ticket.nom_agent} ${ticket.prenom_agent}</td>
                                <td>${ticket.nom_usine}</td>
                                <td>${ticket.prix_unitaire}</td>
                                <td>${ticket.montant_paie}</td>
                               
                            </tr>
                        `;
                    });
                    $('#resultsTableBody').html(html);
                    
                    // Fermer le modal de recherche et ouvrir celui des résultats
                    $('#modalUsineTickets').modal('hide');
                    $('#modalResultatsRecherche').modal('show');
                } else {
                    alert('Aucun ticket trouvé pour ces critères');
                }
            } catch (e) {
                console.error('=== Erreur ===');
                console.error('Type:', e.name);
                console.error('Message:', e.message);
                console.error('Stack:', e.stack);
                console.error('========================');
                alert('Erreur lors du traitement des résultats');
            }
        },
        error: function(xhr, status, error) {
            console.error('=== Erreur AJAX ===');
            console.error('Status:', status);
            console.error('Erreur:', error);
            console.error('Réponse:', xhr.responseText);
            console.error('========================');
            alert('Erreur lors de la recherche des tickets');
        }
    });
}
</script>

<!-- Modal Résultats de Recherche -->
<div class="modal fade" id="modalResultatsRecherche" tabindex="-1" role="dialog" aria-labelledby="modalResultatsRechercheLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalResultatsRechercheLabel">Résultats de la Recherche</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="50px">
                                    <input type="checkbox" id="checkAll" class="check-all">
                                </th>
                                <th>N° Ticket</th>
                                <th>Date</th>
                                <th>Agent</th>
                                <th>Usine</th>
                                <th>Prix unitaire</th>
                                <th>Montant</th>
                            </tr>
                        </thead>
                        <tbody id="resultsTableBody">
                            <!-- Les résultats seront insérés ici -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <div>
                    <span class="text-muted" id="selectedCount">0 ticket(s) sélectionné(s)</span>
                </div>
                <div>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                    <button type="button" class="btn btn-success" onclick="validerTicketsSelectionnes()">
                        <i class="fa fa-check"></i> Valider la sélection
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Afficher le loader
    document.getElementById('loader').style.display = 'block';
    
    // Cacher le loader et afficher la table après un court délai
    setTimeout(function() {
        document.getElementById('loader').style.display = 'none';
        document.getElementById('example1').style.display = 'table';
    }, 1000); // 1 seconde de délai
    
    // Initialisation des autres fonctionnalités
});
</script>

<style>
/* Styles pour Select2 */
.select2-container--default .select2-selection--single {
    height: 38px;
    border: 1px solid #ced4da;
    border-radius: 4px;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 38px;
    color: #495057;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px;
}

.select2-dropdown {
    border: 1px solid #ced4da;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Styles pour les modals */
.modal-xl {
    max-width: 90%;
}

.modal-header.bg-primary {
    background-color: #007bff !important;
}

.modal-header .close.text-white {
    color: #fff;
    opacity: 1;
}

.modal-header .close.text-white:hover {
    opacity: 0.75;
}

/* Styles pour les tableaux */
.table-responsive {
    max-height: 60vh;
    overflow-y: auto;
}

/* Styles pour les boutons */
.btn-success {
    background-color: #28a745;
    border-color: #28a745;
}

.btn-success:hover {
    background-color: #218838;
    border-color: #1e7e34;
}

/* Styles pour les checkboxes */
.check-all {
    margin: 0;
    padding: 0;
}
</style>

<style>
/* Styles existants */
.search-fieldset {
    border: 1px solid #ddd;
    border-radius: 8px;
    background-color: rgb(189, 195, 199);
    margin-bottom: 20px;
    position: relative;
    padding: 25px 15px 15px;
}

.search-legend {
    font-size: 1.2rem;
    font-weight: 600;
    color: #495057;
    width: auto;
    padding: 0 10px;
    margin-bottom: 0;
    background-color: #f8f9fa;
    border: 1px solid #ddd;
    border-radius: 4px;
    position: absolute;
    top: -15px;
    left: 15px;
}

/* Styles pour le formulaire et les entrées */
.form-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
}

.input-group {
    border-radius: 8px;
    overflow: hidden;
}

.input-group-text {
    background-color: #e9ecef;
    border-color: #ced4da;
}

.input-group-prepend .input-group-text {
    border-top-left-radius: 8px;
    border-bottom-left-radius: 8px;
    border-right: none;
    background-color: #f8f9fa;
}

.input-group .form-control {
    border-top-right-radius: 8px !important;
    border-bottom-right-radius: 8px !important;
    border-left: none;
}

/* Styles pour le loader */
#loader {
    display: none;
    position: relative;
    min-height: 200px;
    width: 100%;
    background: rgba(255, 255, 255, 0.8);
}

#loader img {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

/* Styles pour le bouton de recherche */
.btn-primary {
    position: relative;
    transition: all 0.3s ease;
}

.btn-primary:active {
    transform: scale(0.95);
}

.btn-primary:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.btn-lg {
    padding: 0.5rem 2rem;
}

/* Styles pour le conteneur de bloc */
.block-container {
    background-color: #d7dbdd;
    padding: 20px;
    border-radius: 5px;
    width: 100%;
    margin-bottom: 20px;
}

/* Styles pour la table */
.table-responsive {
    margin-top: 20px;
}

.table {
    background-color: white;
}

/* Styles pour les filtres actifs */
.active-filters {
    margin-top: 10px;
}

.badge {
    font-size: 0.9rem;
    padding: 8px 12px;
    margin-right: 8px;
    margin-bottom: 8px;
    border-radius: 20px;
}

.badge i {
    margin-left: 5px;
    cursor: pointer;
}

/* Styles pour les modals */
.modal-content {
    border-radius: 8px;
}

.modal-header {
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
}
</style>
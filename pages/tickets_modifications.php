<?php
include('header.php');
require_once('../inc/functions/connexion.php');
require_once('../inc/functions/requete/requete_tickets.php');
require_once('../inc/functions/requete/requete_usines.php');
require_once('../inc/functions/requete/requete_chef_equipes.php');
require_once('../inc/functions/requete/requete_vehicules.php');
require_once('../inc/functions/requete/requete_agents.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$id_user = $_SESSION['user_id'];

// Récupération des données pour les listes déroulantes
$usines = getUsines($conn);
$chefs_equipes = getChefEquipes($conn);
$vehicules = getVehicules($conn);
$agents = getAgents($conn);

// Paramètres de filtrage avec vérification
$agent_id = isset($_GET['agent_id']) && !empty($_GET['agent_id']) ? (int)$_GET['agent_id'] : null;
$usine_id = isset($_GET['usine_id']) && !empty($_GET['usine_id']) ? (int)$_GET['usine_id'] : null;
$date_debut = isset($_GET['date_debut']) ? trim($_GET['date_debut']) : '';
$date_fin = isset($_GET['date_fin']) ? trim($_GET['date_fin']) : '';
$numero_ticket = isset($_GET['numero_ticket']) ? trim($_GET['numero_ticket']) : '';

// Validation des dates
if (!empty($date_debut) && !strtotime($date_debut)) {
    $date_debut = '';
}
if (!empty($date_fin) && !strtotime($date_fin)) {
    $date_fin = '';
}

$limit = isset($_GET['limit']) ? max(1, min(50, (int)$_GET['limit'])) : 15;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// Récupérer les données avec vérification d'erreurs
try {
    // Préparation des filtres
    $filters = [];
    if (!empty($agent_id)) {
        $filters['agent'] = $agent_id;
    }
    if (!empty($usine_id)) {
        $filters['usine'] = $usine_id;
    }
    if (!empty($date_debut)) {
        $filters['date_debut'] = $date_debut;
    }
    if (!empty($date_fin)) {
        $filters['date_fin'] = $date_fin;
    }

    if (!empty($numero_ticket)) {
        // Si un numéro de ticket est spécifié, on ne récupère que ce ticket
        $tickets = searchTickets($conn, null, null, null, null, $numero_ticket);
        $tickets_list = $tickets; // Pas besoin de pagination pour une recherche spécifique
        $total_pages = 1;
    } else {
        // Sinon on récupère tous les tickets selon les filtres
        $tickets = getTickets($conn, $filters);
        
        if (!empty($tickets)) {
            $total_tickets = count($tickets);
            $total_pages = ceil($total_tickets / $limit);
            $page = max(1, min($page, $total_pages));
            $offset = ($page - 1) * $limit;
            $tickets_list = array_slice($tickets, $offset, $limit);
        } else {
            $tickets_list = [];
            $total_pages = 1;
        }
    }

    // Vérification et initialisation des tableaux
    $tickets = is_array($tickets) ? $tickets : [];
    $usines = is_array($usines) ? $usines : [];
    $chefs_equipes = is_array($chefs_equipes) ? $chefs_equipes : [];
    $vehicules = is_array($vehicules) ? $vehicules : [];
    $agents = is_array($agents) ? $agents : [];

} catch (Exception $e) {
    error_log("Erreur lors de la récupération des données: " . $e->getMessage());
    $error_message = "Une erreur est survenue lors de la récupération des données.";
    $tickets = [];
    $usines = [];
    $chefs_equipes = [];
    $vehicules = [];
    $agents = [];
}

// Pagination sécurisée
$total_tickets = count($tickets);
$total_pages = max(1, ceil($total_tickets / $limit));
$page = min($page, $total_pages);
$offset = ($page - 1) * $limit;

$tickets_list = !empty($tickets) ? array_slice($tickets, $offset, $limit) : [];

// Préserver les paramètres de filtrage pour la pagination
$filter_params = [];
if (!empty($agent_id)) $filter_params['agent_id'] = $agent_id;
if (!empty($usine_id)) $filter_params['usine_id'] = $usine_id;
if (!empty($date_debut)) $filter_params['date_debut'] = $date_debut;
if (!empty($date_fin)) $filter_params['date_fin'] = $date_fin;
if (!empty($numero_ticket)) $filter_params['numero_ticket'] = $numero_ticket;
if ($limit !== 15) $filter_params['limit'] = $limit;

?>

<!-- Main row -->
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
    <!-- Formulaire de filtrage -->
    <div class="col-12 mb-4">
        <form method="GET" class="bg-light p-3 rounded">
            <div class="row">
                <div class="col-md-2">
                    <label>Numéro ticket</label>
                    <input type="text" class="form-control" name="numero_ticket" placeholder="Numéro ticket" value="<?= htmlspecialchars($numero_ticket) ?>">
                </div>
                <div class="col-md-2">
                    <label>Agent</label>
                    <select class="form-control select2-agent" name="agent_id">
                        <option value="">Tous les agents</option>
                        <?php foreach($agents as $agent): ?>
                            <?php if(isset($agent['id_agent'], $agent['nom_complet_agent'])): ?>
                                <option value="<?= htmlspecialchars($agent['id_agent']) ?>" <?= ($agent_id == $agent['id_agent']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($agent['nom_complet_agent']) ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Usine</label>
                    <select class="form-control select2-usine" name="usine_id" style="width: 100%;">
                        <option value="">Toutes les usines</option>
                        <?php foreach($usines as $usine): ?>
                            <?php if(isset($usine['id_usine'], $usine['nom_usine'])): ?>
                                <option value="<?= htmlspecialchars($usine['id_usine']) ?>" <?= ($usine_id == $usine['id_usine']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($usine['nom_usine']) ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Date début</label>
                    <input type="date" class="form-control" name="date_debut" value="<?= htmlspecialchars($date_debut) ?>">
                </div>
                <div class="col-md-2">
                    <label>Date fin</label>
                    <input type="date" class="form-control" name="date_fin" value="<?= htmlspecialchars($date_fin) ?>">
                </div>
                <div class="col-md-2">
                    <label>&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-search"></i> Filtrer
                        </button>
                        <a href="tickets_modifications.php" class="btn btn-secondary">
                            <i class="fa fa-times"></i> Réinitialiser
                        </a>
                    </div>
                </div>
            </div>
            <?php if (isset($_GET['limit'])): ?>
                <input type="hidden" name="limit" value="<?= (int)$_GET['limit'] ?>">
            <?php endif; ?>
        </form>
    </div>

    <div class="table-responsive">
        <table id="example1" class="table table-bordered table-striped" style="min-height: 30vh;">
            <thead>
              <tr>
                
                <th>Date ticket</th>
                <th>Date création</th>
                <th>Numero Ticket</th>
                <th>usine</th>
                <th>Chargé de mission</th>
                <th>Vehicule</th>
                <th>Poids</th>
                <th>Prix Unitaire</th>
                <th>Montant à payer</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($tickets_list as $ticket) : ?>
                <tr>
                  
                  <td><?= date('d/m/Y', strtotime($ticket['date_ticket'])) ?></td>
                  <td><?= date('d/m/Y', strtotime($ticket['created_at'])) ?></td>
                  <td><?= $ticket['numero_ticket'] ?></td>
                  <td><?= $ticket['nom_usine'] ?></td>
                  <td><?= $ticket['nom_complet_agent'] ?></td>
                  <td><?= $ticket['matricule_vehicule'] ?></td>
                  <td><?= $ticket['poids'] ?></td>

                  <td><?= $ticket['prix_unitaire'] ?></td>
                  <td><?= $ticket['montant_paie'] ?></td>
                  <td>
                    <div class="btn-group">
                      <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-menu"></i> Modifications
                      </button>
                      <div class="dropdown-menu" aria-labelledby="actionMenu<?= $ticket['id_ticket'] ?>">
                        <a class="dropdown-item <?= $ticket['date_paie'] !== null ? 'disabled' : '' ?>" href="#" data-toggle="modal" data-target="#editModalNumeroTicket<?= $ticket['id_ticket'] ?>">
                            <i class="fas fa-hashtag"></i> Changer N° Ticket
                        </a>
                        <a class="dropdown-item <?= $ticket['date_paie'] !== null ? 'disabled' : '' ?>" href="#" data-toggle="modal" data-target="#editModalUsine<?= $ticket['id_ticket'] ?>">
                            <i class="fas fa-industry"></i> Changer Usine
                        </a>
                        <a class="dropdown-item <?= $ticket['date_paie'] !== null ? 'disabled' : '' ?>" href="#" data-toggle="modal" data-target="#editModalChefEquipe<?= $ticket['id_ticket'] ?>">
                            <i class="fas fa-user"></i> Changer Chef Mission
                        </a>
                        <a class="dropdown-item <?= $ticket['date_paie'] !== null ? 'disabled' : '' ?>" href="#" data-toggle="modal" data-target="#editModalVehicule<?= $ticket['id_ticket'] ?>">
                            <i class="fas fa-truck"></i> Changer Véhicule
                        </a>
                        <a class="dropdown-item <?= $ticket['date_paie'] !== null ? 'disabled' : '' ?>" href="#" data-toggle="modal" data-target="#editModalPrixUnitaire<?= $ticket['id_ticket'] ?>">
                            <i class="fas fa-money-bill"></i> Changer Prix Unitaire
                        </a>
                        <a class="dropdown-item <?= $ticket['date_paie'] !== null ? 'disabled' : '' ?>" href="#" data-toggle="modal" data-target="#editModalDateCreation<?= $ticket['id_ticket'] ?>">
                            <i class="fas fa-calendar-alt"></i> Changer Date Création
                        </a>
                      </div>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
        </table>

    </div>

    <div class="pagination-container bg-secondary d-flex justify-content-center w-100 text-white p-3">
        <?php if($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>&<?= http_build_query($filter_params) ?>" class="btn btn-primary"><i class="fas fa-chevron-left"></i></a>
        <?php endif; ?>
        
        <span class="mx-3"><?= $page . '/' . $total_pages ?></span>
        
        <?php if($page < $total_pages): ?>
            <a href="?page=<?= $page + 1 ?>&<?= http_build_query($filter_params) ?>" class="btn btn-primary"><i class="fas fa-chevron-right"></i></a>
        <?php endif; ?>
        
        <form action="" method="get" class="items-per-page-form ml-3">
            <label for="limit" class="mr-2">Afficher :</label>
            <select name="limit" id="limit" class="form-control-sm" onchange="this.form.submit()">
                <?php foreach([15, 25, 50] as $val): ?>
                    <option value="<?= $val ?>" <?= $limit == $val ? 'selected' : '' ?>><?= $val ?></option>
                <?php endforeach; ?>
            </select>
            <?php 
            // Préserver les paramètres de filtrage lors du changement de limite
            foreach($filter_params as $key => $value):
                if($key !== 'limit' && $key !== 'page'):
            ?>
                <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($value) ?>">
            <?php 
                endif;
            endforeach;
            ?>
        </form>
    </div>

    <?php if(empty($tickets_list)): ?>
        <div class="alert alert-info text-center mt-4">
            <i class="fas fa-info-circle"></i> Aucun ticket ne correspond aux critères de recherche.
        </div>
    <?php endif; ?>

    <?php if(isset($error_message)): ?>
        <div class="alert alert-danger text-center mt-4">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_message) ?>
        </div>
    <?php endif; ?>

    <!-- Modales pour chaque ticket -->
    <?php foreach ($tickets_list as $ticket) : ?>
      <!-- Modal pour modifier le numéro de ticket -->
      <div class="modal fade" id="editModalNumeroTicket<?= $ticket['id_ticket'] ?>">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Modifier le numéro de ticket</h5>
              <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
              <form action="traitement_tickets.php" method="post">
                <input type="hidden" name="id_ticket" value="<?= $ticket['id_ticket'] ?>">
                <div class="form-group">
                  <label>Nouveau numéro de ticket</label>
                  <input type="text" name="numero_ticket" class="form-control" value="<?= $ticket['numero_ticket'] ?>" required>
                </div>
                <div class="modal-footer">
                  <button type="submit" class="btn btn-success" name="updateNumeroTicket">
                    <i class="fas fa-save"></i> Mettre à jour
                  </button>
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Annuler
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal pour modifier l'usine -->
      <div class="modal fade" id="editModalUsine<?= $ticket['id_ticket'] ?>">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Modifier l'usine</h5>
              <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
              <form action="traitement_tickets.php" method="post">
                <input type="hidden" name="id_ticket" value="<?= $ticket['id_ticket'] ?>">
                <div class="form-group">
                  <label for="usine<?= $ticket['id_ticket'] ?>" class="font-weight-bold mb-2">Sélectionner la nouvelle usine</label>
                  <select name="usine" id="usine<?= $ticket['id_ticket'] ?>" class="form-control select2-usine" style="width: 100%;">
                    <option value="">Sélectionner une usine</option>
                    <?php foreach ($usines as $usine) : ?>
                      <option value="<?= htmlspecialchars($usine['id_usine']) ?>" <?= $usine['id_usine'] == $ticket['id_usine'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($usine['nom_usine']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="modal-footer">
                  <button type="submit" class="btn btn-success" name="updateUsine">
                    <i class="fas fa-save"></i> Mettre à jour
                  </button>
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Annuler
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal pour modifier le chef de mission -->
      <div class="modal fade" id="editModalChefEquipe<?= $ticket['id_ticket'] ?>">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Modifier chargé de mission</h5>
              <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
              <form action="traitement_tickets.php" method="post">
                <input type="hidden" name="id_ticket" value="<?= $ticket['id_ticket'] ?>">
                <div class="form-group">
                  <label for="chef_equipe<?= $ticket['id_ticket'] ?>" class="font-weight-bold mb-2">Sélectionner le nouveau chargé de mission</label>
                  <select name="chef_equipe" id="chef_equipe<?= $ticket['id_ticket'] ?>" class="form-control select2-chef-equipe" style="width: 100%;">
                    <option value="">Sélectionner un chargé de mission</option>
                    <?php foreach ($agents as $agent) : ?>
                      <option value="<?= htmlspecialchars($agent['id_agent']) ?>" <?= $agent['id_agent'] == $ticket['id_agent'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($agent['nom_complet_agent']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="modal-footer">
                  <button type="submit" class="btn btn-success" name="updateChefEquipe">
                    <i class="fas fa-save"></i> Mettre à jour
                  </button>
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Annuler
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal pour modifier le véhicule -->
      <div class="modal fade" id="editModalVehicule<?= $ticket['id_ticket'] ?>">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Modifier le véhicule</h5>
              <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
              <form action="traitement_tickets.php" method="post">
                <input type="hidden" name="id_ticket" value="<?= $ticket['id_ticket'] ?>">
                <div class="form-group">
                  <label for="vehicule<?= $ticket['id_ticket'] ?>" class="font-weight-bold mb-2">Sélectionner le nouveau véhicule</label>
                  <select name="vehicule" id="vehicule<?= $ticket['id_ticket'] ?>" class="form-control select2-vehicule" style="width: 100%;">
                    <option value="">Sélectionner un véhicule</option>
                    <?php foreach ($vehicules as $vehicule) : ?>
                      <option value="<?= htmlspecialchars($vehicule['vehicules_id']) ?>" <?= $vehicule['vehicules_id'] == $ticket['vehicule_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($vehicule['matricule_vehicule']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="modal-footer">
                  <button type="submit" class="btn btn-success" name="updateVehicule">
                    <i class="fas fa-save"></i> Mettre à jour
                  </button>
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Annuler
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal pour modifier le prix unitaire -->
      <div class="modal fade" id="editModalPrixUnitaire<?= $ticket['id_ticket'] ?>">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Modifier le prix unitaire</h5>
              <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
              <form action="traitement_tickets.php" method="post">
                <input type="hidden" name="id_ticket" value="<?= $ticket['id_ticket'] ?>">
                <input type="hidden" name="action" value="update_prix_unitaire">
                <div class="form-group">
                  <label for="prix_unitaire<?= $ticket['id_ticket'] ?>">Prix unitaire</label>
                  <div class="input-group">
                    <input type="number" 
                           class="form-control" 
                           id="prix_unitaire<?= $ticket['id_ticket'] ?>" 
                           value="<?= $ticket['prix_unitaire'] ?>" 
                           name="prix_unitaire"
                           min="1"
                           step="1"
                           required
                           data-poids="<?= $ticket['poids'] ?>"
                           data-montant-deja-paye="<?= $ticket['montant_payer'] ?>"
                           oninput="updateTotal(this, <?= $ticket['poids'] ?>)">
                    <div class="input-group-append">
                      <span class="input-group-text">FCFA</span>
                    </div>
                  </div>
                  <small class="form-text text-muted" id="total<?= $ticket['id_ticket'] ?>">
                    Le montant à payer sera automatiquement recalculé (Prix unitaire × <?= $ticket['poids'] ?> kg = <?= number_format($ticket['prix_unitaire'] * $ticket['poids'], 0, ',', ' ') ?> FCFA)
                  </small>
                  <?php if ($ticket['montant_payer'] > 0): ?>
                    <small class="form-text text-warning">
                      <i class="fas fa-exclamation-triangle"></i>
                      Attention : Ce ticket a déjà reçu <?= number_format($ticket['montant_payer'], 0, ',', ' ') ?> FCFA de paiement
                    </small>
                  <?php endif; ?>
                </div>
                <div class="modal-footer">
                  <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Mettre à jour
                  </button>
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Annuler
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal pour modifier la date de création -->
      <div class="modal fade" id="editModalDateCreation<?= $ticket['id_ticket'] ?>">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Modifier la date de création</h5>
              <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
              <form action="update_date_creation.php?id=<?= $ticket['id_ticket'] ?>" method="post">
                <input type="hidden" name="id_ticket" value="<?= $ticket['id_ticket'] ?>">
                <div class="form-group">
                  <label for="date_creation_<?= $ticket['id_ticket'] ?>">Date de création</label>
                  <div class="input-group">
                    <input type="date" 
                           class="form-control" 
                           id="date_creation_<?= $ticket['id_ticket'] ?>"
                           value="<?= date('Y-m-d', strtotime($ticket['created_at'])) ?>" 
                           name="date_creation" 
                           required>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Mettre à jour
                  </button>
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Annuler
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>

    <div class="modal fade" id="add-ticket">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title">Enregistrer un ticket</h4>
            </div>
            <div class="modal-body">
              <form class="forms-sample" method="post" action="traitement_tickets.php">
                <div class="card-body">
                <div class="form-group">
                    <label for="exampleInputEmail1">Date ticket</label>
                    <input type="date" class="form-control" id="exampleInputEmail1" placeholder="date ticket" name="date_ticket">
                  </div> 
                  <div class="form-group">
                    <label for="exampleInputEmail1">Numéro du Ticket</label>
                    <input type="text" class="form-control" id="exampleInputEmail1" placeholder="Numero du ticket" name="numero_ticket">
                  </div>
                   <div class="form-group">
                      <label>Selection Usine</label>
                      <select id="select" name="usine" class="form-control select2-usine">
                          <?php
                          // Vérifier si des usines existent
                          if (!empty($usines)) {

                              foreach ($usines as $usine) {
                                  echo '<option value="' . htmlspecialchars($usine['id_usine']) . '">' . htmlspecialchars($usine['nom_usine']) . '</option>';
                              }
                          } else {
                              echo '<option value="">Aucune usine disponible</option>';
                          }
                          ?>
                      </select>
                  </div>

                  <div class="form-group">
                      <label>Chargé de Mission</label>
                      <select id="select" name="id_agent" class="form-control select2-agent">
                          <?php
                          // Vérifier si des usines existent
                          if (!empty($agents)) {
                              foreach ($agents as $agent) {
                                  echo '<option value="' . htmlspecialchars($agent['id_agent']) . '">' . htmlspecialchars($agent['nom_complet_agent']) . '</option>';
                              }
                          } else {
                              echo '<option value="">Aucune chef eéuipe disponible</option>';
                          }
                          ?>
                      </select>
                  </div>

                  <div class="form-group">
                      <label>Selection véhicules</label>
                      <select id="select" name="vehicule" class="form-control select2-vehicule">
                          <?php
                          // Vérifier si des usines existent
                          if (!empty($vehicules)) {
                              foreach ($vehicules as $vehicule) {
                                  echo '<option value="' . htmlspecialchars($vehicule['vehicules_id']) . '">' . htmlspecialchars($vehicule['matricule_vehicule']) . '</option>';
                              }
                          } else {
                              echo '<option value="">Aucun véhicule disponible</option>';
                          }
                          ?>
                      </select>
                  </div>

                  <div class="form-group">
                    <label for="exampleInputPassword1">Poids</label>
                    <input type="text" class="form-control" id="exampleInputPassword1" placeholder="Poids" name="poids">
                  </div>

                  <button type="submit" class="btn btn-primary mr-2" name="saveCommande">Enregister</button>
                  <button class="btn btn-light">Annuler</button>
                </div>
              </form>
            </div>
          </div>
          <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>

    <div class="modal fade" id="print-bordereau">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title">Impression bordereau</h4>
            </div>
            <div class="modal-body">
              <form class="forms-sample" method="post" action="print_bordereau.php" target="_blank">
                <div class="card-body">
                  <div class="form-group">
                      <label>Chargé de Mission</label>
                      <select id="select" name="id_agent" class="form-control select2-agent">
                          <?php
                          // Vérifier si des usines existent
                          if (!empty($agents)) {
                              foreach ($agents as $agent) {
                                  echo '<option value="' . htmlspecialchars($agent['id_agent']) . '">' . htmlspecialchars($agent['nom_complet_agent']) . '</option>';
                              }
                          } else {
                              echo '<option value="">Aucune chef eéuipe disponible</option>';
                          }
                          ?>
                      </select>
                  </div>
                  <div class="form-group">
                    <label for="exampleInputPassword1">Date de debut</label>
                    <input type="date" class="form-control" id="exampleInputPassword1" placeholder="Poids" name="date_debut">
                  </div>
                  <div class="form-group">
                    <label for="exampleInputPassword1">Date Fin</label>
                    <input type="date" class="form-control" id="exampleInputPassword1" placeholder="Poids" name="date_fin">
                  </div>

                  <button type="submit" class="btn btn-primary mr-2" name="saveCommande">Imprimer</button>
                  <button class="btn btn-light">Annuler</button>
                </div>
              </form>
            </div>
          </div>
          <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>

    <!-- Recherche par Communes -->



  


    <!-- /.row (main row) -->
</div><!-- /.container-fluid -->
<!-- /.content -->
</div>
<!-- /.content-wrapper -->
<!-- <footer class="main-footer">
    <strong>Copyright &copy; 2014-2021 <a href="https://adminlte.io">AdminLTE.io</a>.</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
      <b>Version</b> 3.2.0
    </div>
  </footer>-->
<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-dark">
  <!-- Control sidebar content goes here -->
</aside>
<!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<!-- jQuery -->
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

$(document).ready(function() {
    $('#confirmDeleteModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Lien qui a déclenché la modale
        var ticketId = button.data('id'); // Récupère l'ID du ticket
        var modal = $(this);
        var deleteUrl = 'tickets_delete.php?id=' + ticketId;
        modal.find('#confirmDeleteBtn').attr('href', deleteUrl);
    });
});

</script>

<script>
function updateTotal(input, poids) {
    const prix = parseFloat(input.value) || 0;
    const total = prix * poids;
    const montantDejaPaye = parseFloat(input.dataset.montantDejaPaye) || 0;
    const ticketId = input.id.replace('prix_unitaire', '');
    const submitBtn = input.closest('form').querySelector('button[type="submit"]');
    
    document.getElementById('total' + ticketId).innerHTML = 
        `Le montant à payer sera automatiquement recalculé (Prix unitaire × ${poids} kg = ${total.toLocaleString('fr-FR')} FCFA)`;
    
    // Vérifier si le nouveau montant est valide
    if (total < montantDejaPaye) {
        input.setCustomValidity(`Le nouveau montant (${total.toLocaleString('fr-FR')} FCFA) ne peut pas être inférieur au montant déjà payé (${montantDejaPaye.toLocaleString('fr-FR')} FCFA)`);
        submitBtn.disabled = true;
    } else {
        input.setCustomValidity('');
        submitBtn.disabled = false;
    }
}

// Initialiser le calcul au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('input[id^="prix_unitaire"]');
    inputs.forEach(input => {
        const poids = parseFloat(input.dataset.poids);
        if (poids) updateTotal(input, poids);
    });
});
</script>

<script>
$(document).ready(function() {
    // Initialisation de Select2 pour les usines
    $('.select2-usine').select2({
        theme: 'bootstrap4',
        placeholder: 'Sélectionner une usine',
        allowClear: true,
        width: '100%',
        language: {
            noResults: function() {
                return "Aucune usine trouvée";
            },
            searching: function() {
                return "Recherche...";
            }
        }
    });

    // Initialisation de Select2 pour les chargés de mission
    $('.select2-agent').select2({
        theme: 'bootstrap4',
        placeholder: 'Sélectionner un chargé de mission',
        allowClear: true,
        width: '100%',
        language: {
            noResults: function() {
                return "Aucun chargé de mission trouvé";
            },
            searching: function() {
                return "Recherche...";
            }
        }
    });

    // Initialisation de Select2 pour les véhicules
    $('.select2-vehicule').select2({
        theme: 'bootstrap4',
        placeholder: 'Sélectionner un véhicule',
        allowClear: true,
        width: '100%',
        language: {
            noResults: function() {
                return "Aucun véhicule trouvé";
            },
            searching: function() {
                return "Recherche...";
            }
        }
    });

    // Réinitialiser Select2 quand la modale est fermée
    $('.modal').on('hidden.bs.modal', function() {
        $(this).find('select').val('').trigger('change');
    });
});
</script>
</body>

</html>

<style>
      .dropdown-item {
        padding: 0.5rem 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: background-color 0.2s;
      }
      
      .dropdown-item:hover {
        background-color: #f8f9fa;
      }
      
      .dropdown-item i {
        width: 20px;
      }
      
      .dropdown-item:disabled {
        color: #6c757d;
        opacity: 0.65;
        cursor: not-allowed;
      }
      
      .btn-primary {
        background-color: #007bff;
        border-color: #007bff;
      }
      
      .btn-primary:hover {
        background-color: #0069d9;
        border-color: #0062cc;
      }
      
      .modal-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
      }
      
      .modal-footer {
        background-color: #f8f9fa;
        border-top: 1px solid #dee2e6;
      }

      .dropdown-menu {
        min-width: 200px;
      }
    </style>
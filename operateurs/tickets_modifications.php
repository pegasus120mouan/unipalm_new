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

    // Récupération des données avec les filtres
    $tickets = getTickets($conn, $filters);
    $usines = getUsines($conn);
    $chefs_equipes = getChefEquipes($conn);
    $vehicules = getVehicules($conn);
    $agents = getAgents($conn);

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

// Appliquer le filtre de numéro de ticket si nécessaire
if (!empty($numero_ticket)) {
    $tickets = array_filter($tickets, function($ticket) use ($numero_ticket) {
        return isset($ticket['numero_ticket']) && 
               stripos($ticket['numero_ticket'], $numero_ticket) !== false;
    });
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
                    <select class="form-control" name="agent_id">
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
                    <select class="form-control" name="usine_id">
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
        <table id="example1" class="table table-bordered table-striped">
            <thead>
              <tr>
                
                <th>Date ticket</th>
                <th>Numero Ticket</th>
                <th>usine</th>
                <th>Chargé de mission</th>
                <th>Vehicule</th>
                <th>Poids</th>
                <th>Prix Unitaire</th>
                <th>Montant à payer</th>
                <th>Changer Usine</th>
                <th>Changer Chef Mission</th>
                <th>Changer Vehicule</th>
                <th>Changer Prix U.</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($tickets_list as $ticket) : ?>
                <tr>
                  
                  <td><?= date('d/m/Y', strtotime($ticket['date_ticket'])) ?></td>
                  <td><?= $ticket['numero_ticket'] ?></td>
                  <td><?= $ticket['nom_usine'] ?></td>
                  <td><?= $ticket['nom_complet_agent'] ?></td>
                  <td><?= $ticket['matricule_vehicule'] ?></td>
                  <td><?= $ticket['poids'] ?></td>

                  <td><?= $ticket['prix_unitaire'] ?></td>
                  <td><?= $ticket['montant_paie'] ?></td>
                  <td>
                    <button 
                        class="btn btn-dark btn-block" 
                        data-toggle="modal" 
                        data-target="#editModalUsine<?= $ticket['id_ticket'] ?>" 
                        <?= $ticket['date_paie'] !== null ? 'disabled' : '' ?>>
                        Changer Usine
                    </button>
                  </td>
                  <!-- Modal pour modifier l'usine -->
                  <div class="modal" id="editModalUsine<?= $ticket['id_ticket'] ?>">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <div class="modal-body">
                          <form action="traitement_tickets.php" method="post">
                            <input type="hidden" name="id_ticket" value="<?= $ticket['id_ticket'] ?>">
                            <div class="form-group">
                              <label>Selection la nouvelle usine</label>
                              <select id="select" name="usine" class="form-control">
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
                            <button type="submit" class="btn btn-success mr-2" name="saveCommande">Mise à jour</button>
                            <button class="btn btn-light">Annuler</button>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!-- Fin Modal pour modifier l'usine -->
                  <td>
                      <button 
                          class="btn btn-info btn-block" 
                          data-toggle="modal" 
                          data-target="#editModalChefEquipe<?= $ticket['id_ticket'] ?>" 
                          <?= $ticket['date_paie'] !== null ? 'disabled' : '' ?>>
                          Changer Chef Mission
                      </button>
                    </td>
                    <!-- Modal pour modifier le chef de mission -->
                    <div class="modal" id="editModalChefEquipe<?= $ticket['id_ticket'] ?>">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <div class="modal-body">
                            <form action="traitement_tickets.php" method="post">
                              <input type="hidden" name="id_ticket" value="<?= $ticket['id_ticket'] ?>">
                              <div class="form-group">
                                <label>Selection le nouveau chef de mission</label>
                                <select id="select" name="chef_equipe" class="form-control">
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
                              <button type="submit" class="btn btn-success mr-2" name="saveCommande">Mise à jour</button>
                              <button class="btn btn-light">Annuler</button>
                            </form>
                          </div>
                        </div>
                      </div>
                    </div>
                    <!-- Fin Modal pour modifier le chef de mission -->
                    <td>
                        <button 
                            class="btn btn-secondary btn-block" 
                            data-toggle="modal" 
                            data-target="#editModalVehicule<?= $ticket['id_ticket'] ?>" 
                            <?= $ticket['date_paie'] !== null ? 'disabled' : '' ?>>
                            Changer Vehicule
                        </button>
                    </td>
                    <!-- Modal pour modifier le vehicule -->
                    <div class="modal" id="editModalVehicule<?= $ticket['id_ticket'] ?>">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <div class="modal-body">
                            <form action="traitement_tickets.php" method="post">
                              <input type="hidden" name="id_ticket" value="<?= $ticket['id_ticket'] ?>">
                              <div class="form-group">
                                <label>Selection le nouveau vehicule</label>
                                <select id="select" name="vehicule" class="form-control">
                                    <?php
                                    if (!empty($vehicules)) {
                                        foreach ($vehicules as $vehicule) {
                                            echo '<option value="' . htmlspecialchars($vehicule['vehicules_id']) . '"' . 
                                                 ($vehicule['vehicules_id'] == $ticket['vehicules_id'] ? ' selected' : '') . '>' . 
                                                 htmlspecialchars($vehicule['matricule_vehicule']) . '</option>';
                                        }
                                    } else {
                                        echo '<option value="">Aucun véhicule disponible</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                              <button type="submit" class="btn btn-success mr-2">Mise à jour</button>
                              <button type="button" class="btn btn-light" data-dismiss="modal">Annuler</button>
                            </form>
                          </div>
                        </div>
                      </div>
                    </div>

                    <td>
                        <button 
                            class="btn btn-success btn-block" 
                            data-toggle="modal" 
                            data-target="#editModalPrixUnitaire<?= $ticket['id_ticket'] ?>">
                            Changer le prix unitaire
                        </button>
                    </td>
                    <!-- Modal pour modifier le vehicule -->
                    <div class="modal" id="editModalPrixUnitaire<?= $ticket['id_ticket'] ?>">
                      <div class="modal-dialog">
                        <div class="modal-content">
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
                    <!-- Lien pour déclencher la modale -->
                    <!--a href="#" class="trash" data-toggle="modal" data-target="#confirmDeleteModal" data-id="<?= $ticket['id_ticket'] ?>">
                        <i class="fas fa-trash fa-xs" style="font-size:24px;color:red"></i>
                    </a-->

                    <!-- Modale de confirmation -->
                    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="confirmDeleteModalLabel">Confirmer la suppression</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    Êtes-vous sûr de vouloir supprimer ce ticket ?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                    <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Supprimer</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    </td>

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
                      <select id="select" name="usine" class="form-control">
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
                      <select id="select" name="id_agent" class="form-control">
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
                      <select id="select" name="vehicule" class="form-control">
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
                      <select id="select" name="id_agent" class="form-control">
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

</body>

</html>
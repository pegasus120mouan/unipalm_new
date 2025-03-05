<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_tickets.php';
require_once '../inc/functions/requete/requete_usines.php';
require_once '../inc/functions/requete/requete_chef_equipes.php';
require_once '../inc/functions/requete/requete_vehicules.php';
require_once '../inc/functions/requete/requete_agents.php';
include('header.php');

$id_user=$_SESSION['user_id'];

$limit = $_GET['limit'] ?? 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Récupérer les paramètres de filtrage
$agent_id = $_GET['agent_id'] ?? null;
$usine_id = $_GET['usine_id'] ?? null;
$date_debut = $_GET['date_debut'] ?? '';
$date_fin = $_GET['date_fin'] ?? '';
$search_agent = $_GET['search_agent'] ?? '';
$search_usine = $_GET['search_usine'] ?? '';
$statut = $_GET['statut'] ?? ''; // Nouveau paramètre pour le statut

// Récupérer les données
$tickets = getTicketsPayes($conn, $agent_id, $usine_id, $date_debut, $date_fin);
$usines = getUsines($conn);
$chefs_equipes = getChefEquipes($conn);
$vehicules = getVehicules($conn);
$agents = getAgents($conn);

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

// Filtrer par statut si sélectionné
if (!empty($statut)) {
    $tickets = array_filter($tickets, function($ticket) use ($statut) {
        $montant_reste = isset($ticket['montant_reste']) ? (float)$ticket['montant_reste'] : 0;
        
        if ($statut === 'solde') {
            return $montant_reste <= 0;
        } else if ($statut === 'en_cours') {
            return $montant_reste > 0;
        }
        return true;
    });
}

// Calculer la pagination
$total_tickets = count($tickets);
$total_pages = ceil($total_tickets / $limit);
$page = max(1, min($page, $total_pages));
$offset = ($page - 1) * $limit;

// Extraire les tickets pour la page courante
$tickets_list = array_slice($tickets, $offset, $limit);
?>

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
    <div class="col-12">
        <h4 class="mb-4">Liste des tickets Soldés ou en cours de paiement</h4>
        <div class="text-muted mb-3">
            Total: <?php echo count($tickets); ?> ticket(s)
        </div>
    </div>
</div>

<!-- Barre de recherche en haut -->
<div class="search-container mb-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <form id="filterForm" method="GET">
                <div class="row">
                    <!-- Statut du ticket -->
                    <div class="col-md-3 mb-3">
                        <select class="form-control" name="statut" id="statut_select">
                            <option value="">Tous les statuts</option>
                            <option value="solde" <?= ($statut === 'solde') ? 'selected' : '' ?>>Soldés</option>
                            <option value="en_cours" <?= ($statut === 'en_cours') ? 'selected' : '' ?>>En cours de paiement</option>
                        </select>
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
                        <a href="tickets_payes.php" class="btn btn-outline-danger">
                            <i class="fa fa-times"></i> Réinitialiser les filtres
                        </a>
                    </div>
                </div>
            </form>
            
            <!-- Filtres actifs -->
            <?php if($agent_id || $usine_id || $date_debut || $date_fin || $statut): ?>
            <div class="active-filters mt-3">
                <div class="d-flex align-items-center flex-wrap">
                    <strong class="text-muted mr-2">Filtres actifs :</strong>
                    <?php if($statut): ?>
                        <span class="badge badge-info mr-2 p-2">
                            <i class="fa fa-filter"></i>
                            Statut: <?= $statut === 'solde' ? 'Soldés' : 'En cours de paiement' ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['statut' => null])) ?>" class="text-white ml-2">
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

<div class="table-responsive">
    <table id="example1" class="table table-bordered table-striped">

 <!-- <table style="max-height: 90vh !important; overflow-y: scroll !important" id="example1" class="table table-bordered table-striped">-->
    <thead>
      <tr>
        
        <th>Date ticket</th>
        <th>Numero Ticket</th>
        <th>usine</th>
        <th>Chargé de Mission</th>
        <th>Vehicule</th>
        <th>Poids</th>
        <th>Ticket crée par</th>
        <th>Prix Unitaire</th>
        <th>Date validation</th>
        <th>Montant</th>
        <th>Date Paie</th>
      <!--  <th>Actions</th>-->
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($tickets_list)) : ?>
        <?php foreach ($tickets_list as $ticket) : ?>
          <tr>
            
            <td><?= date('d/m/Y', strtotime($ticket['date_ticket'])) ?></td>
            <td><?= $ticket['numero_ticket'] ?></td>
            <td><?= $ticket['nom_usine'] ?></td>
            <td><?= $ticket['agent_nom_complet'] ?></td>
            <td><?= $ticket['matricule_vehicule'] ?></td>
            <td><?= $ticket['poids'] ?></td>

            <td><?= $ticket['utilisateur_nom_complet'] ?></td></td>

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
          
  
      <!--    <td class="actions">
            <a class="edit" data-toggle="modal" data-target="#editModalTicket<?= $ticket['id_ticket'] ?>">
            <i class="fas fa-pen fa-xs" style="font-size:24px;color:blue"></i>
            </a>
            <a href="delete_commandes.php?id=<?= $ticket['id_ticket'] ?>" class="trash"><i class="fas fa-trash fa-xs" style="font-size:24px;color:red"></i></a>
          </td>-->

          <div class="modal fade" id="editModalTicket<?= $ticket['id_ticket'] ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Modification Ticket <?= $ticket['id_ticket'] ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Formulaire de modification du ticket -->
                <form action="commandes_update.php?id=<?= $ticket['id_ticket'] ?>" method="post">
                <div class="form-group">
                        <label for="prix_unitaire">Numéro du ticket</label>
                        <input type="text" class="form-control" id="numero_ticket" name="numero_ticket" value="<?= $ticket['numero_ticket'] ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="prix_unitaire">Prix Unitaire</label>
                        <input type="number" class="form-control" id="prix_unitaire" name="prix_unitaire" value="<?= $ticket['prix_unitaire'] ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="date_validation_boss">Date de Validation</label>
                        <input type="date" class="form-control" id="date_validation_boss" name="date_validation_boss" value="<?= $ticket['date_validation_boss'] ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Sauvegarder les modifications</button>
                </form>
            </div>
        </div>
    </div>
</div>

          

         <div class="modal" id="valider_ticket<?= $ticket['id_ticket'] ?>">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-body">
                <form action="traitement_tickets.php" method="post">
                  <input type="hidden" name="id_ticket" value="<?= $ticket['id_ticket'] ?>">
                  <div class="form-group">
                    <label>Ajouter le prix unitaire</label>
                  </div>
                  <div class="form-group">
                <input type="text" class="form-control" id="exampleInputEmail1" placeholder="Prix unitaire" name="prix_unitaire">
              </div>
                  <button type="submit" class="btn btn-primary mr-2" name="saveCommande">Ajouter</button>
                  <button class="btn btn-light">Annuler</button>
                </form>
              </div>
            </div>
          </div>
        </div>


      <?php endforeach; ?>
    <?php else: ?>
        <tr>
          <td colspan="12" class="text-center">Pas de tickets payés pour le moment</td>
        </tr>
    <?php endif; ?>
    </tbody>
  </table>

</div>

  <div class="pagination-container bg-secondary d-flex justify-content-center w-100 text-white p-3">
    <?php if($page > 1): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="btn btn-primary"><</a>
    <?php endif; ?>
    
    <span class="mx-3"><?= $page . '/' . $total_pages ?></span>

    <?php if($page < $total_pages): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="btn btn-primary">></a>
    <?php endif; ?>
    
    <form action="" method="get" class="items-per-page-form">
        <?php
        // Preserve existing GET parameters
        foreach ($_GET as $key => $value) {
            if ($key !== 'limit') {
                echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
            }
        }
        ?>
        <label for="limit">Afficher :</label>
        <select name="limit" id="limit" class="items-per-page-select">
            <option value="5" <?= $limit == 5 ? 'selected' : '' ?>>5</option>
            <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10</option>
            <option value="15" <?= $limit == 15 ? 'selected' : '' ?>>15</option>
        </select>
        <button type="submit" class="submit-button">Valider</button>
    </form>
</div>

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
                  <label>Selection chef Equipe</label>
                  <select id="select" name="chef_equipe" class="form-control">
                      <?php
                      // Vérifier si des usines existent
                      if (!empty($chefs_equipes)) {
                          foreach ($chefs_equipes as $chefs_equipe) {
                              echo '<option value="' . htmlspecialchars($chefs_equipe['id_chef']) . '">' . htmlspecialchars($chefs_equipe['chef_nom_complet']) . '</option>';
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
                          echo '<option value="">Aucun vehicule disponible</option>';
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

<script>
function appliquerFiltres() {
    const agent_id = document.getElementById('agent_select').value;
    const usine_id = document.getElementById('usine_select').value;
    const date_debut = document.getElementById('date_debut').value;
    const date_fin = document.getElementById('date_fin').value;
    const statut = document.getElementById('statut_select').value;
    
    let params = new URLSearchParams(window.location.search);
    
    if (statut) params.set('statut', statut);
    else params.delete('statut');
    
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

$(document).ready(function() {
    // Initialiser les sélecteurs
    $('#agent_select, #usine_select, #statut_select').select2({
        placeholder: 'Sélectionner...',
        allowClear: true
    });
});
</script>
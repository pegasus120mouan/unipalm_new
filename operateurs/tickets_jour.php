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

// Récupérer les tickets du jour avec les filtres
$tickets = getTicketsJour($conn);

// Filtrer les tickets si un terme de recherche est présent
if (!empty($search_agent) || !empty($search_usine) || !empty($agent_id) || !empty($usine_id) || !empty($date_debut) || !empty($date_fin) || !empty($numero_ticket)) {
    $tickets = array_filter($tickets, function($ticket) use ($search_agent, $search_usine, $agent_id, $usine_id, $date_debut, $date_fin, $numero_ticket) {
        $match = true;
        
        if (!empty($search_agent)) {
            $match = $match && stripos($ticket['agent_nom_complet'], $search_agent) !== false;
        }
        if (!empty($search_usine)) {
            $match = $match && stripos($ticket['nom_usine'], $search_usine) !== false;
        }
        if (!empty($agent_id)) {
            $match = $match && $ticket['id_agent'] == $agent_id;
        }
        if (!empty($usine_id)) {
            $match = $match && $ticket['id_usine'] == $usine_id;
        }
        if (!empty($numero_ticket)) {
            $match = $match && stripos($ticket['numero_ticket'], $numero_ticket) !== false;
        }
        if (!empty($date_debut)) {
            $match = $match && strtotime($ticket['date_ticket']) >= strtotime($date_debut);
        }
        if (!empty($date_fin)) {
            $match = $match && strtotime($ticket['date_ticket']) <= strtotime($date_fin);
        }
        return $match;
    });
}

// Récupérer les données pour les filtres
$usines = getUsines($conn);
$agents = getAgents($conn);
$chefs_equipes = getChefEquipes($conn);
$vehicules = getVehicules($conn);

// Calculer la pagination
$total_tickets = count($tickets);
$total_pages = ceil($total_tickets / $limit);
$page = max(1, min($page, $total_pages));
$offset = ($page - 1) * $limit;

// Extraire les tickets pour la page courante
$tickets_list = array_slice($tickets, $offset, $limit);
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
                        <a href="tickets_jour.php" class="btn btn-outline-danger">
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

    <div class="block-container">
        <div class="d-flex justify-content-between align-items-center">
            <h3><i class="fa fa-calendar text-success"></i> Liste des Tickets du jours</h3>
            <div class="text-muted">
                Total: <?php echo $total_tickets; ?> ticket(s)
            </div>
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
       <!-- <th>Actions</th>-->
      <!--  <th>Validation Prix</th>-->
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

        <!--  <td>
            <button 
            type="button" 
            class="btn btn-success" 
            data-toggle="modal" 
            data-target="#valider_ticket<?= $ticket['id_ticket'] ?>" 
            <?= $ticket['prix_unitaire'] == 0.00 ? '' : 'disabled title="Le prix est déjà validé"' ?>>
            Valider un ticket
           </button>

        </td>-->
          


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
          <td colspan="12" class="text-center">Pas de tickets ajoutés pour le moment</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

</div>

  <div class="pagination-container bg-secondary d-flex justify-content-center w-100 text-white p-3">
    <?php if($page > 1 ): ?>
        <a href="?page=<?= $page - 1 ?>" class="btn btn-primary"><</a>
    <?php endif; ?>
    <span><?= $page . '/' . $total_pages ?></span>

    <?php if($page < $total_pages): ?>
        <a href="?page=<?= $page + 1 ?>" class="btn btn-primary">></a>
    <?php endif; ?>
    <form action="" method="get" class="items-per-page-form">
        <label for="limit">Afficher :</label>
        <select name="limit" id="limit" class="items-per-page-select">
            <option value="5" <?php if ($limit == 5) { echo 'selected'; } ?> >5</option>
            <option value="10" <?php if ($limit == 10) { echo 'selected'; } ?>>10</option>
            <option value="15" <?php if ($limit == 15) { echo 'selected'; } ?>>15</option>
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
<?php
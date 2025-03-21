<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_tickets.php';
require_once '../inc/functions/requete/requete_usines.php';
require_once '../inc/functions/requete/requete_chef_equipes.php';
require_once '../inc/functions/requete/requete_vehicules.php';
require_once '../inc/functions/requete/requete_agents.php';

include('header.php');

$id_user = $_SESSION['user_id'];

$limit = $_GET['limit'] ?? 15; // Nombre d'éléments par page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Page actuelle

// Récupérer les paramètres de filtrage
$usine_id = $_GET['usine_id'] ?? null;
$date_debut = $_GET['date_debut'] ?? '';
$date_fin = $_GET['date_fin'] ?? '';
$prix_min = $_GET['prix_min'] ?? '';
$prix_max = $_GET['prix_max'] ?? '';

// Requête SQL pour compter le nombre total d'éléments
$sql_count = "SELECT COUNT(*) as total FROM prix_unitaires
              INNER JOIN usines ON prix_unitaires.id_usine = usines.id_usine
              WHERE 1=1";

// Requête SQL pour récupérer les prix unitaires avec filtres
$sql = "SELECT
    usines.nom_usine,
    usines.id_usine,
    prix_unitaires.prix,
    prix_unitaires.date_debut,
    prix_unitaires.date_fin,
    prix_unitaires.id
FROM
    prix_unitaires
INNER JOIN
    usines ON prix_unitaires.id_usine = usines.id_usine
WHERE 1=1";

// Ajouter les conditions de filtrage aux deux requêtes
if ($usine_id) {
    $condition = " AND prix_unitaires.id_usine = :usine_id";
    $sql .= $condition;
    $sql_count .= $condition;
}
if ($date_debut) {
    $condition = " AND prix_unitaires.date_debut >= :date_debut";
    $sql .= $condition;
    $sql_count .= $condition;
}
if ($date_fin) {
    $condition = " AND prix_unitaires.date_fin <= :date_fin";
    $sql .= $condition;
    $sql_count .= $condition;
}
if ($prix_min) {
    $condition = " AND prix_unitaires.prix >= :prix_min";
    $sql .= $condition;
    $sql_count .= $condition;
}
if ($prix_max) {
    $condition = " AND prix_unitaires.prix <= :prix_max";
    $sql .= $condition;
    $sql_count .= $condition;
}

// Ajouter l'ordre et la pagination à la requête principale
$sql .= " ORDER BY prix_unitaires.date_debut DESC LIMIT :offset, :limit";

// Préparer et exécuter la requête de comptage
$stmt_count = $conn->prepare($sql_count);
if ($usine_id) $stmt_count->bindParam(':usine_id', $usine_id);
if ($date_debut) $stmt_count->bindParam(':date_debut', $date_debut);
if ($date_fin) $stmt_count->bindParam(':date_fin', $date_fin);
if ($prix_min) $stmt_count->bindParam(':prix_min', $prix_min);
if ($prix_max) $stmt_count->bindParam(':prix_max', $prix_max);
$stmt_count->execute();
$total_items = $stmt_count->fetch(PDO::FETCH_ASSOC)['total'];

// Calculer la pagination
$total_pages = ceil($total_items / $limit);
$page = max(1, min($page, $total_pages));
$offset = ($page - 1) * $limit;

// Préparer et exécuter la requête principale
$stmt = $conn->prepare($sql);
if ($usine_id) $stmt->bindParam(':usine_id', $usine_id);
if ($date_debut) $stmt->bindParam(':date_debut', $date_debut);
if ($date_fin) $stmt->bindParam(':date_fin', $date_fin);
if ($prix_min) $stmt->bindParam(':prix_min', $prix_min);
if ($prix_max) $stmt->bindParam(':prix_max', $prix_max);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$prix_unitaires_list = $stmt->fetchAll();

$usines = getUsines($conn);
$chefs_equipes=getChefEquipes($conn);
$vehicules=getVehicules($conn);
$agents=getAgents($conn);

// Vérifiez si des tickets existent avant de procéder
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

    <div class="block-container">
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add-ticket">
      <i class="fa fa-edit"></i>Enregistrer un Prix Unitaire
    </button>

    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#print-bordereau">
      <i class="fa fa-print"></i> Imprimer la liste des prix unitaires
    </button>
</div>

<!-- Barre de recherche et filtres -->
<div class="search-container mb-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <form id="filterForm" method="GET">
                <div class="row">
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

                    <!-- Prix minimum -->
                    <div class="col-md-3 mb-3">
                        <input type="number" 
                               class="form-control" 
                               name="prix_min" 
                               id="prix_min"
                               placeholder="Prix minimum" 
                               value="<?= htmlspecialchars($prix_min) ?>">
                    </div>

                    <!-- Prix maximum -->
                    <div class="col-md-3 mb-3">
                        <input type="number" 
                               class="form-control" 
                               name="prix_max" 
                               id="prix_max"
                               placeholder="Prix maximum" 
                               value="<?= htmlspecialchars($prix_max) ?>">
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
                        <a href="prix_unitaires.php" class="btn btn-outline-danger">
                            <i class="fa fa-times"></i> Réinitialiser les filtres
                        </a>
                    </div>
                </div>
            </form>
            
            <!-- Filtres actifs -->
            <?php if($usine_id || $date_debut || $date_fin || $prix_min || $prix_max): ?>
            <div class="active-filters mt-3">
                <div class="d-flex align-items-center flex-wrap">
                    <strong class="text-muted mr-2">Filtres actifs :</strong>
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
                    <?php if($prix_min): ?>
                        <span class="badge badge-info mr-2 p-2">
                            <i class="fa fa-money"></i>
                            Prix min: <?= htmlspecialchars($prix_min) ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['prix_min' => null])) ?>" class="text-white ml-2">
                                <i class="fa fa-times"></i>
                            </a>
                        </span>
                    <?php endif; ?>
                    <?php if($prix_max): ?>
                        <span class="badge badge-info mr-2 p-2">
                            <i class="fa fa-money"></i>
                            Prix max: <?= htmlspecialchars($prix_max) ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['prix_max' => null])) ?>" class="text-white ml-2">
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
        
        <th>Nom Usine</th>
        <th>Prix Unitaire</th>
        <th>Date Début</th>
        <th>Date Fin</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($prix_unitaires_list as $prix) : ?>
        <tr>
          <td><?= htmlspecialchars($prix['nom_usine']) ?></td>
          <td><?= htmlspecialchars($prix['prix']) ?></td>
          <td><?= date('d/m/Y', strtotime($prix['date_debut'])) ?></td>
          <td><?= $prix['date_fin'] ? date('d/m/Y', strtotime($prix['date_fin'])) : 'En cours' ?></td>
          <td class="actions">
            <a href="#" class="edit" data-toggle="modal" data-target="#editModal<?= $prix['id'] ?>">
              <i class="fas fa-pen fa-xs" style="font-size:24px;color:blue"></i>
            </a>
            <a href="#" class="trash" data-toggle="modal" data-target="#deleteModal<?= $prix['id'] ?>">
              <i class="fas fa-trash fa-xs" style="font-size:24px;color:red"></i>
            </a>
          </td>
        </tr>

        <!-- Modal Modification -->
        <div class="modal fade" id="editModal<?= $prix['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?= $prix['id'] ?>" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel<?= $prix['id'] ?>">Modifier le Prix Unitaire</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <form action="traitement_prix_unitaires.php" method="post">
                  <input type="hidden" name="id" value="<?= $prix['id'] ?>">
                  
                  <div class="form-group">
                    <label>Sélection Usine</label>
                    <select name="id_usine" class="form-control" required>
                      <?php foreach ($usines as $usine) : ?>
                        <option value="<?= $usine['id_usine'] ?>" <?= $usine['id_usine'] == $prix['id_usine'] ? 'selected' : '' ?>>
                          <?= htmlspecialchars($usine['nom_usine']) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <div class="form-group">
                    <label>Prix Unitaire</label>
                    <input type="number" step="0.01" class="form-control" name="prix" value="<?= $prix['prix'] ?>" required>
                  </div>

                  <div class="form-group">
                    <label>Date de début</label>
                    <input type="date" class="form-control" name="date_debut" value="<?= $prix['date_debut'] ?>" required>
                  </div>

                  <div class="form-group">
                    <label>Date de fin</label>
                    <input type="date" class="form-control" name="date_fin" value="<?= $prix['date_fin'] ?>">
                    <small class="form-text text-muted">Laissez vide si le prix unitaire est toujours en cours</small>
                  </div>

                  <button type="submit" class="btn btn-primary" name="updatePrixUnitaire">Enregistrer les modifications</button>
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                </form>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal Suppression -->
        <div class="modal fade" id="deleteModal<?= $prix['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel<?= $prix['id'] ?>" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel<?= $prix['id'] ?>">Confirmer la suppression</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer ce prix unitaire ?</p>
                <p>Usine: <?= htmlspecialchars($prix['nom_usine']) ?></p>
                <p>Prix: <?= htmlspecialchars($prix['prix']) ?></p>
              </div>
              <div class="modal-footer">
                <form action="traitement_prix_unitaires.php" method="post">
                  <input type="hidden" name="id" value="<?= $prix['id'] ?>">
                  <button type="submit" class="btn btn-danger" name="deletePrixUnitaire">Supprimer</button>
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </tbody>
    </table>
</div>

  <div class="pagination-container bg-secondary d-flex justify-content-center w-100 text-white p-3">
    <?php if($page > 1): ?>
        <a href="?page=<?= $page - 1 ?><?= $usine_id ? '&usine_id='.$usine_id : '' ?><?= $date_debut ? '&date_debut='.$date_debut : '' ?><?= $date_fin ? '&date_fin='.$date_fin : '' ?><?= $prix_min ? '&prix_min='.$prix_min : '' ?><?= $prix_max ? '&prix_max='.$prix_max : '' ?><?= isset($_GET['limit']) ? '&limit='.$_GET['limit'] : '' ?>" class="btn btn-primary"><</a>
    <?php endif; ?>
    
    <span class="mx-2"><?= $page . '/' . $total_pages ?></span>

    <?php if($page < $total_pages): ?>
        <a href="?page=<?= $page + 1 ?><?= $usine_id ? '&usine_id='.$usine_id : '' ?><?= $date_debut ? '&date_debut='.$date_debut : '' ?><?= $date_fin ? '&date_fin='.$date_fin : '' ?><?= $prix_min ? '&prix_min='.$prix_min : '' ?><?= $prix_max ? '&prix_max='.$prix_max : '' ?><?= isset($_GET['limit']) ? '&limit='.$_GET['limit'] : '' ?>" class="btn btn-primary">></a>
    <?php endif; ?>
    
    <form action="" method="get" class="items-per-page-form ml-3">
        <?php if($usine_id): ?>
            <input type="hidden" name="usine_id" value="<?= htmlspecialchars($usine_id) ?>">
        <?php endif; ?>
        <?php if($date_debut): ?>
            <input type="hidden" name="date_debut" value="<?= htmlspecialchars($date_debut) ?>">
        <?php endif; ?>
        <?php if($date_fin): ?>
            <input type="hidden" name="date_fin" value="<?= htmlspecialchars($date_fin) ?>">
        <?php endif; ?>
        <?php if($prix_min): ?>
            <input type="hidden" name="prix_min" value="<?= htmlspecialchars($prix_min) ?>">
        <?php endif; ?>
        <?php if($prix_max): ?>
            <input type="hidden" name="prix_max" value="<?= htmlspecialchars($prix_max) ?>">
        <?php endif; ?>
        <label for="limit">Afficher :</label>
        <select name="limit" id="limit" class="items-per-page-select">
            <option value="5" <?= $limit == 5 ? 'selected' : '' ?>>5</option>
            <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10</option>
            <option value="15" <?= $limit == 15 ? 'selected' : '' ?>>15</option>
        </select>
        <button type="submit" class="submit-button">Valider</button>
    </form>
  </div>



  <div class="modal fade" id="add-ticket" tabindex="-1" role="dialog" aria-labelledby="addTicketModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="addTicketModalLabel">Enregistrer un Prix Unitaire</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form class="forms-sample" method="post" action="traitement_prix_unitaires.php">
            <div class="card-body">
              <div class="form-group">
                  <label>Sélection Usine</label>
                  <select id="select" name="id_usine" class="form-control" required>
                      <?php
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
                <label>Prix Unitaire</label>
                <input type="number" step="0.01" class="form-control" placeholder="Prix unitaire" name="prix" required>
              </div>

              <div class="form-group">
                <label>Date de début</label>
                <input type="date" class="form-control" name="date_debut" required>
              </div>

              <div class="form-group">
                <label>Date de fin</label>
                <input type="date" class="form-control" name="date_fin">
                <small class="form-text text-muted">Laissez vide si le prix unitaire est toujours en cours</small>
              </div>

              <button type="submit" class="btn btn-primary mr-2" name="savePrixUnitaire">Enregistrer</button>
              <button type="button" class="btn btn-light" data-dismiss="modal">Annuler</button>
            </div>
          </form>
        </div>
      </div>
    </div>
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
              <button type="button" class="btn btn-light" data-dismiss="modal">Annuler</button>
            </div>
          </form>
        </div>
      </div>
      <!-- /.modal-content -->
    </div>


    <!-- /.modal-dialog -->
  </div>

<!-- Recherche par tickets-->
<div class="modal fade" id="search_ticket">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-search mr-2"></i>Rechercher un ticket
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

                    <button type="button" class="btn btn-primary btn-block mb-3" data-toggle="modal" data-target="#searchByBetweendateModal" data-dismiss="modal">
                        <i class="fas fa-calendar-alt mr-2"></i>Recherche entre 2 dates
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
                                    <?= $agent['nom_complet_agent'] ?>
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

<div class="modal fade" id="searchByBetweendateModal" tabindex="-1" role="dialog" aria-labelledby="searchByDateModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="searchByBetweendateModalLabel">
                    <i class="fas fa-calendar-alt mr-2"></i>Recherche entre 2 dates
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="searchByBetweendateForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="date_debut">Sélectionner date Début</label>
                        <input type="date" class="form-control" id="date_debut" name="date_debut" placeholder="date debut" required>
                    </div>
                    <div class="form-group">
                        <label for="date_fin">Sélectionner date de Fin</label>
                        <input type="date" class="form-control" id="date_fin" name="date_fin" placeholder="date fin" required>
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

// Gestionnaire pour le formulaire de recherche entre deux dates
document.getElementById('searchByBetweendateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const date_debut = document.getElementById('date_debut').value;
    const date_fin = document.getElementById('date_fin').value;
    if (date_debut && date_fin) {
        window.location.href = 'tickets.php?date_debut=' + date_debut + '&date_fin=' + date_fin;
    }
});
</script>

<?php foreach ($tickets_list as $ticket) : ?>
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
  .modal-header .close {
    padding: 1rem;
    margin: -1rem -1rem -1rem auto;
  }
  </style>
<?php endforeach; ?>

</body>

</html>

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
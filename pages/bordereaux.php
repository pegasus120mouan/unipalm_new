<?php
//session_start();
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_tickets.php';
require_once '../inc/functions/requete/requete_bordereaux.php';
require_once '../inc/functions/requete/requete_usines.php';
require_once '../inc/functions/requete/requete_chef_equipes.php';
require_once '../inc/functions/requete/requete_vehicules.php';
require_once '../inc/functions/requete/requete_agents.php';

// Traitement du formulaire avant tout affichage HTML
if (isset($_POST['saveBordereau'])) {
    $id_agent = $_POST['id_agent'];
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];

   // echo $id_agent;
    //echo $date_debut;
   // echo $date_fin;

    $result = saveBordereau($conn, $id_agent, $date_debut, $date_fin);
    if ($result['success']) {
        $_SESSION['success'] = $result['message'];
    } else {
        $_SESSION['error'] = $result['message'];
    }
    header('Location: bordereaux.php');
    exit();
}

// Traitement de la suppression du bordereau
if (isset($_POST['delete_bordereau'])) {
    $id_bordereau = $_POST['id_bordereau'];
    
    try {
        // Vérifier si le bordereau existe
        $check_sql = "SELECT id_bordereau FROM bordereau WHERE id_bordereau = :id_bordereau";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bindParam(':id_bordereau', $id_bordereau);
        $check_stmt->execute();
        
        if ($check_stmt->rowCount() > 0) {
            // Supprimer le bordereau
            $delete_sql = "DELETE FROM bordereau WHERE id_bordereau = :id_bordereau";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bindParam(':id_bordereau', $id_bordereau);
            $delete_stmt->execute();
            
            $_SESSION['success'] = "Bordereau supprimé avec succès";
        } else {
            $_SESSION['error'] = "Bordereau introuvable";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la suppression du bordereau: " . $e->getMessage();
    }
    
    header('Location: bordereaux.php');
    exit();
}

include('header.php');

//$_SESSION['user_id'] = $user['id'];
 $id_user=$_SESSION['user_id'];
 //echo $id_user;

////$stmt = $conn->prepare("SELECT * FROM users");
//$stmt->execute();
//$users = $stmt->fetchAll();
//foreach($users as $user)

$limit = $_GET['limit'] ?? 15; // Nombre de tickets par page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Page actuelle

// Récupérer les paramètres de recherche
$search_usine = $_GET['usine'] ?? null;
$search_date = $_GET['date_creation'] ?? null;
$search_chauffeur = $_GET['chauffeur'] ?? null;
$search_agent = $_GET['agent'] ?? null;
$search_date_debut = $_GET['date_debut'] ?? null;
$search_date_fin = $_GET['date_fin'] ?? null;

// Récupérer les données (functions)
/*if ($search_usine || $search_date || $search_chauffeur || $search_agent) {
    $tickets = searchTickets($conn, $search_usine, $search_date, $search_chauffeur, $search_agent);
} else {
    $tickets = getTickets($conn);
}*/

// Vérifiez si des tickets existent avant de procéder
/*if (!empty($tickets)) {
    $total_tickets = count($tickets);
    $total_pages = ceil($total_tickets / $limit);
    $page = max(1, min($page, $total_pages));
    $offset = ($page - 1) * $limit;
    $tickets_list = array_slice($tickets, $offset, $limit);
} else {
    $tickets_list = [];
    $total_pages = 1;
}*/

$usines = getUsines($conn);
$chefs_equipes=getChefEquipes($conn);
$vehicules=getVehicules($conn);
$agents=getAgents($conn);

// Récupération des bordereaux avec pagination et filtres
$result = getBordereaux($conn, $page, $limit, [
    'usine' => $search_usine,
    'date' => $search_date,
    'chauffeur' => $search_chauffeur,
    'agent' => $search_agent,
    'date_debut' => $search_date_debut,
    'date_fin' => $search_date_fin
]);

$bordereaux = $result['data'];
$total_pages = $result['total_pages'];
$current_page = $page;



// Vérifiez si des tickets existent avant de procéder
//if (!empty($tickets)) {
//    $ticket_pages = array_chunk($tickets, $limit); // Divise les tickets en pages
//    $tickets_list = $ticket_pages[$page - 1] ?? []; // Tickets pour la page actuelle
//} else {
//    $tickets_list = []; // Aucun ticket à afficher
//}


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

<link rel="stylesheet" href="../../plugins/jquery-ui/jquery-ui.min.css">
<style>
.ui-autocomplete {
    max-height: 200px;
    overflow-y: auto;
    overflow-x: hidden;
    z-index: 9999;
}
.ui-menu-item {
    padding: 5px 10px;
    cursor: pointer;
}
.ui-menu-item:hover {
    background-color: #f8f9fa;
}
</style>

<div class="row">
    <?php if (isset($_SESSION['warning'])): ?>
        <div class="col-12">
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <?= $_SESSION['warning'] ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
        <?php unset($_SESSION['warning']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['popup'])): ?>
        <div class="col-12">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Ticket enregistré avec succès
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
        <?php unset($_SESSION['popup']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['delete_pop'])): ?>
        <div class="col-12">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                Une erreur s'est produite
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
        <?php unset($_SESSION['delete_pop']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="col-12">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $_SESSION['success'] ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="col-12">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $_SESSION['error'] ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="block-container">
    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#print-bordereau">
      <i class="fa fa-print"></i> Générer un bordereau
    </button>

    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#search_ticket">
      <i class="fa fa-search"></i> Rechercher un ticket
    </button>

    <button type="button" class="btn btn-dark" onclick="window.location.href='export_tickets.php'">
              <i class="fa fa-print"></i> Exporter la liste les tickets
             </button>

    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add-bordereau">
        <i class="fa fa-plus"></i> Nouveau bordereau
    </button>


</div>

<div class="block-container">
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="input-group">
                <input type="text" id="search_agent" class="form-control" placeholder="Rechercher un bordereau en saisissant le nom de l'agent...">
                <input type="hidden" id="selected_agent_id" name="agent_id">
            </div>
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
    <table id="example1" class="table table-bordered table-striped">

 <!-- <table style="max-height: 90vh !important; overflow-y: scroll !important" id="example1" class="table table-bordered table-striped">-->
    <thead>
      <tr>
            <th>Date de génération</th>
            <th>Numéro</th>
            <th>Nombre de ticket</th>
            <th>Date Début</th>
            <th>Date Fin</th>
            <th>Poids Total</th>
            <th>Montant Total</th>
            <th>Montant Payé</th>
            <th>Reste à Payer</th>
            <th>Statut</th>
            <th>Agent</th> 
            <th>Actions</th>
            <th>Statut Validation</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($bordereaux as $bordereau) : ?>
        <tr>
          <td><?= date('d/m/Y', strtotime($bordereau['date_creation_bordereau'])) ?></td>
          <td>
            <a href="view_bordereau.php?numero=<?= urlencode($bordereau['numero_bordereau']) ?>" class="text-primary">
                <?= $bordereau['numero_bordereau'] ?>
            </a>
          </td>
          <td>
            <button type="button" class="btn btn-link" data-toggle="modal" data-target="#ticketsModal<?= $bordereau['id_bordereau'] ?>">
              <?= $bordereau['nombre_tickets'] ?>
            </button>
          </td>
          <td><?= $bordereau['date_debut'] ? date('d/m/Y', strtotime($bordereau['date_debut'])) : '-' ?></td>
          <td><?= $bordereau['date_fin'] ? date('d/m/Y', strtotime($bordereau['date_fin'])) : '-' ?></td>
          <td><?= number_format($bordereau['poids_total'], 2, ',', ' ') ?> kg</td>
          <td><?= number_format($bordereau['montant_total'], 0, ',', ' ') ?> FCFA</td>
          <td><?= number_format($bordereau['montant_payer'] ?? 0, 0, ',', ' ') ?> FCFA</td>
          <td><?= number_format($bordereau['montant_reste'] ?? $bordereau['montant_total'], 0, ',', ' ') ?> FCFA</td>
          <td>
            <span class="badge badge-<?= $bordereau['statut_bordereau'] === 'soldé' ? 'success' : 'warning' ?>">
              <?= ucfirst($bordereau['statut_bordereau']) ?>
            </span>
          </td>
          <td><?= $bordereau['nom_complet_agent'] ?></td>
          <td>
            <a href="print_bordereau.php?id=<?= $bordereau['id_bordereau'] ?>" class="btn btn-sm btn-success" target="_blank">
              <i class="fas fa-print"></i>
            </a>
            <?php if ($bordereau['date_validation_boss'] === null): ?>
              <button class="btn btn-sm btn-primary validate-btn" data-id="<?= $bordereau['id_bordereau'] ?>">
                <i class="fas fa-check"></i> Valider
              </button>
              <form method="post" action="" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce bordereau ?');">
                <input type="hidden" name="id_bordereau" value="<?= $bordereau['id_bordereau'] ?>">
                <button type="submit" name="delete_bordereau" class="btn btn-sm btn-danger">
                  <i class="fas fa-trash"></i>
                </button>
              </form>
            <?php endif; ?>
          </td>
          <td>
            <?php if ($bordereau['date_validation_boss'] === null): ?>
              <button class="btn btn-sm btn-secondary" disabled>
                <i class="fas fa-check"></i> Non Validé
              </button>
            <?php else: ?>
              <button class="btn btn-sm btn-secondary" disabled>
                <i class="fas fa-check"></i> Validé le <?= date('d/m/Y', strtotime($bordereau['date_validation_boss'])) ?>
              </button>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

</div>

  <div class="pagination-container bg-secondary d-flex justify-content-center w-100 text-white p-3">
    <?php if($page > 1): ?>
        <a href="?page=<?= $page - 1 ?><?= isset($_GET['usine']) ? '&usine='.$_GET['usine'] : '' ?><?= isset($_GET['date_creation']) ? '&date_creation='.$_GET['date_creation'] : '' ?><?= isset($_GET['chauffeur']) ? '&chauffeur='.$_GET['chauffeur'] : '' ?><?= isset($_GET['agent_id']) ? '&agent_id='.$_GET['agent_id'] : '' ?>" class="btn btn-primary"><</a>
    <?php endif; ?>
    
    <?php
    // Afficher les numéros de page
    $start = max(1, $page - 2);
    $end = min($total_pages, $page + 2);
    
    // Afficher la première page si on n'y est pas
    if ($start > 1) {
        echo '<a href="?page=1' . 
            (isset($_GET['usine']) ? '&usine='.$_GET['usine'] : '') . 
            (isset($_GET['date_creation']) ? '&date_creation='.$_GET['date_creation'] : '') . 
            (isset($_GET['chauffeur']) ? '&chauffeur='.$_GET['chauffeur'] : '') . 
            (isset($_GET['agent_id']) ? '&agent_id='.$_GET['agent_id'] : '') . 
            '" class="btn btn-primary">1</a>';
        if ($start > 2) {
            echo '<span class="px-2 text-white">...</span>';
        }
    }
    
    // Afficher les pages autour de la page courante
    for ($i = $start; $i <= $end; $i++) {
        if ($i == $page) {
            echo '<span class="btn btn-secondary active">' . $i . '</span>';
        } else {
            echo '<a href="?page=' . $i . 
                (isset($_GET['usine']) ? '&usine='.$_GET['usine'] : '') . 
                (isset($_GET['date_creation']) ? '&date_creation='.$_GET['date_creation'] : '') . 
                (isset($_GET['chauffeur']) ? '&chauffeur='.$_GET['chauffeur'] : '') . 
                (isset($_GET['agent_id']) ? '&agent_id='.$_GET['agent_id'] : '') . 
                '" class="btn btn-primary">' . $i . '</a>';
        }
    }
    
    // Afficher la dernière page si on n'y est pas
    if ($end < $total_pages) {
        if ($end < $total_pages - 1) {
            echo '<span class="px-2 text-white">...</span>';
        }
        echo '<a href="?page=' . $total_pages . 
            (isset($_GET['usine']) ? '&usine='.$_GET['usine'] : '') . 
            (isset($_GET['date_creation']) ? '&date_creation='.$_GET['date_creation'] : '') . 
            (isset($_GET['chauffeur']) ? '&chauffeur='.$_GET['chauffeur'] : '') . 
            (isset($_GET['agent_id']) ? '&agent_id='.$_GET['agent_id'] : '') . 
            '" class="btn btn-primary">' . $total_pages . '</a>';
    }
    ?>
    
    <?php if($page < $total_pages): ?>
        <a href="?page=<?= $page + 1 ?><?= isset($_GET['usine']) ? '&usine='.$_GET['usine'] : '' ?><?= isset($_GET['date_creation']) ? '&date_creation='.$_GET['date_creation'] : '' ?><?= isset($_GET['chauffeur']) ? '&chauffeur='.$_GET['chauffeur'] : '' ?><?= isset($_GET['agent_id']) ? '&agent_id='.$_GET['agent_id'] : '' ?>" class="btn btn-primary">></a>
    <?php endif; ?>
  </div>
  <form action="" method="get" class="items-per-page-form ml-3">
        <?php if(isset($_GET['usine'])): ?>
            <input type="hidden" name="usine" value="<?= htmlspecialchars($_GET['usine']) ?>">
        <?php endif; ?>
        <?php if(isset($_GET['date_creation'])): ?>
            <input type="hidden" name="date_creation" value="<?= htmlspecialchars($_GET['date_creation']) ?>">
        <?php endif; ?>
        <?php if(isset($_GET['chauffeur'])): ?>
            <input type="hidden" name="chauffeur" value="<?= htmlspecialchars($_GET['chauffeur']) ?>">
        <?php endif; ?>
        <?php if(isset($_GET['agent_id'])): ?>
            <input type="hidden" name="agent_id" value="<?= htmlspecialchars($_GET['agent_id']) ?>">
        <?php endif; ?>
        <label for="limit">Afficher :</label>
        <select name="limit" id="limit" class="items-per-page-select">
            <option value="5" <?= $limit == 5 ? 'selected' : '' ?>>5</option>
            <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10</option>
            <option value="15" <?= $limit == 15 ? 'selected' : '' ?>>15</option>
        </select>
        <button type="submit" class="submit-button">Valider</button>
    </form>

  <div class="modal fade" id="add-ticket" tabindex="-1" role="dialog" aria-labelledby="addTicketModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="addTicketModalLabel">Enregistrer un ticket</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
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
                          echo '<option value="">Aucune chef equipe disponible</option>';
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

  <!-- Modal d'ajout de bordereau -->
<div class="modal fade" id="add-bordereau" tabindex="-1" role="dialog" aria-labelledby="addBordereauLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="addBordereauLabel">Nouveau bordereau</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="post" action="save_bordereau.php">
                    <div class="form-group">
                        <label for="id_agent">Chargé de Mission</label>
                        <select name="id_agent" id="id_agent" class="form-control" required>
                            <option value="">Sélectionner un chargé de mission</option>
                            <?php foreach ($agents as $agent): ?>
                                <option value="<?= htmlspecialchars($agent['id_agent']) ?>">
                                    <?= htmlspecialchars($agent['nom_complet_agent']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="date_debut">Date de début</label>
                        <input type="date" class="form-control" id="date_debut" name="date_debut" required>
                    </div>
                    <div class="form-group">
                        <label for="date_fin">Date de fin</label>
                        <input type="date" class="form-control" id="date_fin" name="date_fin" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary" name="saveBordereau">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
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

<?php foreach ($bordereaux as $bordereau) : ?>
<!-- Modal pour la sélection des tickets -->
<div class="modal fade" id="ticketsModal<?= $bordereau['id_bordereau'] ?>" tabindex="-1" role="dialog" aria-labelledby="ticketsModalLabel<?= $bordereau['id_bordereau'] ?>" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ticketsModalLabel<?= $bordereau['id_bordereau'] ?>">Tickets du bordereau <?= $bordereau['numero_bordereau'] ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <?php 
        $tickets = getTicketsByBordereau($conn, $bordereau['id_bordereau']);
        if (!empty($tickets)) : 
        ?>
        <form id="ticketsForm<?= $bordereau['id_bordereau'] ?>" action="associer_tickets.php" method="post">
          <input type="hidden" name="bordereau" value="<?= $bordereau['numero_bordereau'] ?>">
          <div class="table-responsive">
            <table class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th style="width: 40px">
                    <input type="checkbox" id="select-all-<?= $bordereau['id_bordereau'] ?>" class="select-all">
                  </th>
                  <th>Date</th>
                  <th>N° Ticket</th>
                  <th>Usine</th>
                  <th>Véhicule</th>
                  <th>Poids (T)</th>
                  <th>Prix Unit.</th>
                  <th>Montant</th>
                  <th>Statut</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($tickets as $ticket) : ?>
                  <tr>
                    <td>
                      <input type="checkbox" name="tickets[]" value="<?= $ticket['id_ticket'] ?>" <?= isset($ticket['numero_bordereau']) && $ticket['numero_bordereau'] === $bordereau['numero_bordereau'] ? 'checked disabled' : '' ?>>
                    </td>
                    <td><?= date('d/m/Y', strtotime($ticket['date_ticket'])) ?></td>
                    <td><?= $ticket['numero_ticket'] ?></td>
                    <td><?= $ticket['nom_usine'] ?></td>
                    <td><?= $ticket['matricule_vehicule'] ?></td>
                    <td class="text-right"><?= number_format($ticket['poids'], 2, ',', ' ') ?></td>
                    <td class="text-right"><?= number_format($ticket['prix_unitaire'], 0, ',', ' ') ?></td>
                    <td class="text-right"><?= number_format($ticket['montant_total'], 0, ',', ' ') ?></td>
                    <td>
                      <?php if (isset($ticket['numero_bordereau']) && $ticket['numero_bordereau'] == $bordereau['numero_bordereau']) : ?>
                        <span class="badge badge-success">Associé</span>
                      <?php elseif (isset($ticket['numero_bordereau'])) : ?>
                        <span class="badge badge-warning">Autre bordereau</span>
                      <?php else : ?>
                        <span class="badge badge-info">Disponible</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
              <tfoot>
                <tr>
                  <th colspan="5" class="text-right">Total</th>
                  <th class="text-right"><?= number_format(array_sum(array_column($tickets, 'poids')), 2, ',', ' ') ?></th>
                  <th></th>
                  <th class="text-right"><?= number_format(array_sum(array_column($tickets, 'montant_total')), 0, ',', ' ') ?></th>
                  <th></th>
                </tr>
              </tfoot>
            </table>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
            <button type="submit" class="btn btn-primary">Associer les tickets</button>
          </div>
        </form>
        <?php else : ?>
          <p class="text-center">Aucun ticket disponible pour cette période.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php endforeach; ?>

<!-- Script pour la gestion des tickets -->
<script>
$(document).ready(function() {
    // Initialisation des modals Bootstrap
    $('.modal').modal({
        show: false
    });

    $("#search_agent").autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "get_agents.php",
                dataType: "json",
                data: {
                    term: request.term
                },
                success: function(data) {
                    response($.map(data, function(item) {
                        return {
                            label: item.nom_complet_agent,
                            value: item.nom_complet_agent,
                            id: item.id_agent
                        };
                    }));
                }
            });
        },
        minLength: 2,
        select: function(event, ui) {
            $("#selected_agent_id").val(ui.item.id);
            // Recharger la page avec le filtre
            window.location.href = 'bordereaux.php?agent=' + ui.item.id;
        }
    });
});
</script>
<?php foreach ($bordereaux as $bordereau) : ?>
  <div class="modal fade" id="bordereauModal<?= $bordereau['id_bordereau'] ?>" tabindex="-1" role="dialog" aria-labelledby="bordereauModalLabel<?= $bordereau['id_bordereau'] ?>" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="bordereauModalLabel<?= $bordereau['id_bordereau'] ?>">
            <i class="fas fa-ticket-alt mr-2"></i>Détails du Bordereau #<?= $bordereau['numero_bordereau'] ?>
          </h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="row mb-4">
            <div class="col-md-6">
              <div class="info-group">
                <label class="text-muted">Date du bordereau:</label>
                <p class="font-weight-bold"><?= date('d/m/Y', strtotime($bordereau['date_creation_bordereau'])) ?></p>
              </div>
              <div class="info-group">
                <label class="text-muted">Usine:</label>
                <p class="font-weight-bold"><?= $bordereau['nom_usine'] ?></p>
              </div>
              <div class="info-group">
                <label class="text-muted">Agent:</label>
                <p class="font-weight-bold"><?= $bordereau['nom_complet_agent'] ?></p>
              </div>
              <div class="info-group">
                <label class="text-muted">Poids total:</label>
                <p class="font-weight-bold"><?= number_format($bordereau['poids_total'], 2, ',', ' ') ?> kg</p>
              </div>
            </div>
            <div class="col-md-6">
              <div class="info-group">
                <label class="text-muted">Montant total:</label>
                <p class="font-weight-bold text-primary"><?= number_format($bordereau['montant_total'], 0, ',', ' ') ?> FCFA</p>
              </div>
            </div>
          </div>
          <div class="border-top pt-3">
            <div class="info-group">
              <label class="text-muted">Créé par:</label>
              <p class="font-weight-bold"><?= $bordereau['utilisateur_nom_complet'] ?></p>
            </div>
            <div class="info-group">
              <label class="text-muted">Date de création:</label>
              <p class="font-weight-bold"><?= date('d/m/Y', strtotime($bordereau['created_at'])) ?></p>
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
          <form action="print_bordereau.php" method="get" target="_blank" class="d-inline">
            <input type="hidden" name="id" value="<?= $bordereau['id_bordereau'] ?>">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-print"></i> Imprimer
            </button>
          </form>
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

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius: 15px;">
            <div class="modal-body text-center p-4">
                <div class="mb-4">
                    <div style="width: 70px; height: 70px; background-color: #4CAF50; border-radius: 50%; display: inline-flex; justify-content: center; align-items: center; margin-bottom: 20px;">
                        <i class="fas fa-check" style="font-size: 35px; color: white;"></i>
                    </div>
                    <h4 class="mb-3" style="font-weight: 600;">SUCCESS</h4>
                    <?php if (isset($_SESSION['message'])): ?>
                        <p class="mb-4"><?= $_SESSION['message'] ?></p>
                        <?php unset($_SESSION['message']); ?>
                    <?php else: ?>
                        <p class="mb-4">Ticket ajouté avec succès!</p>
                        <p style="color: #666;">Le prix unitaire pour cette période est : <strong><?= isset($_SESSION['prix_unitaire']) ? number_format($_SESSION['prix_unitaire'], 2, ',', ' ') : '0,00' ?> FCFA</strong></p>
                    <?php endif; ?>
                </div>
                <button type="button" class="btn btn-success px-4 py-2" data-dismiss="modal" style="min-width: 120px; border-radius: 25px;">CONTINUE</button>
            </div>
        </div>
    </div>
</div>

<!-- Warning Modal -->
<div class="modal fade" id="warningModal" tabindex="-1" role="dialog" aria-labelledby="warningModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius: 15px;">
            <div class="modal-body text-center p-4">
                <div class="mb-4">
                    <div style="width: 70px; height: 70px; background-color: #FFC107; border-radius: 50%; display: inline-flex; justify-content: center; align-items: center; margin-bottom: 20px;">
                        <i class="fas fa-exclamation" style="font-size: 35px; color: white;"></i>
                    </div>
                    <h4 class="mb-3" style="font-weight: 600;">ATTENTION</h4>
                    <p style="color: #666;"><?= isset($_SESSION['warning']) ? $_SESSION['warning'] : '' ?></p>
                </div>
                <button type="button" class="btn btn-warning px-4 py-2" data-dismiss="modal" style="min-width: 120px; border-radius: 25px;">CONTINUE</button>
            </div>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="errorModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius: 15px;">
            <div class="modal-body text-center p-4">
                <div class="mb-4">
                    <div style="width: 70px; height: 70px; background-color: #dc3545; border-radius: 50%; display: inline-flex; justify-content: center; align-items: center; margin-bottom: 20px;">
                        <i class="fas fa-times" style="font-size: 35px; color: white;"></i>
                    </div>
                    <h4 class="mb-3" style="font-weight: 600;">ERROR</h4>
                    <p style="color: #666;">Une erreur s'est produite lors de l'opération.</p>
                </div>
                <button type="button" class="btn btn-danger px-4 py-2" data-dismiss="modal" style="min-width: 120px; border-radius: 25px;">AGAIN</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation de tous les modals
    $('.modal').modal({
        keyboard: false,
        backdrop: 'static',
        show: false
    });

    // Gestion de la suppression
    $('.trash').click(function(e) {
        e.preventDefault();
        var ticketId = $(this).data('id');
        $('#confirmDeleteBtn').attr('href', 'traitement_tickets.php?action=delete&id=' + ticketId);
    });
});
</script>

<?php if (isset($_SESSION['success_modal'])): ?>
<script>
    $(document).ready(function() {
        $('#successModal').modal('show');
        var audio = new Audio("../inc/sons/notification.mp3");
        audio.volume = 1.0;
        audio.play().catch((error) => {
            console.error('Erreur de lecture audio :', error);
        });
    });
</script>
<?php 
    unset($_SESSION['success_modal']);
    unset($_SESSION['prix_unitaire']);
endif; ?>

<?php if (isset($_SESSION['warning'])): ?>
<script>
    $(document).ready(function() {
        $('#warningModal').modal('show');
    });
</script>
<?php 
    unset($_SESSION['warning']);
endif; ?>

<?php if (isset($_SESSION['delete_pop'])): ?>
<script>
    $(document).ready(function() {
        $('#errorModal').modal('show');
    });
</script>
<?php 
    unset($_SESSION['delete_pop']);
endif; ?>
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
<script>
$(document).ready(function() {
    // Initialisation des modals Bootstrap
    $('.modal').modal({
        show: false
    });
});
</script>
<script>
    // Fonction pour rediriger vers associer_tickets.php avec les tickets sélectionnés
    function associerTickets(numero_bordereau) {
        var selectedTickets = [];
        $('input[name="tickets[]"]:checked').each(function() {
            selectedTickets.push($(this).val());
        });
        
        if (selectedTickets.length > 0) {
            var url = 'associer_tickets.php?bordereau=' + encodeURIComponent(numero_bordereau);
            url += '&tickets[]=' + selectedTickets.join('&tickets[]=');
            window.location.href = url;
        } else {
            alert('Veuillez sélectionner au moins un ticket.');
        }
    }
</script>
<script>
$(document).ready(function() {
    // Gestion de la validation des bordereaux
    $('.validate-btn').on('click', function() {
        const btn = $(this);
        const id = btn.data('id');
        
        if (confirm('Êtes-vous sûr de vouloir valider ce bordereau ?')) {
            $.ajax({
                url: 'valider_bordereau.php',
                type: 'POST',
                data: {
                    id_bordereau: id
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        window.location.reload();
                    } else {
                        alert(response.message);
                    }
                },
                error: function() {
                    alert('Erreur lors de la communication avec le serveur');
                }
            });
        }
    });
});
</script>
<script>
  // Gestion de la sélection de tous les tickets pour chaque modal
  document.addEventListener('DOMContentLoaded', function() {
    // Pour chaque checkbox "select-all"
    document.querySelectorAll('.select-all').forEach(function(checkbox) {
      checkbox.addEventListener('change', function() {
        // Trouver le formulaire parent
        const form = this.closest('form');
        // Sélectionner toutes les checkboxes non désactivées dans ce formulaire
        const checkboxes = form.querySelectorAll('input[type="checkbox"]:not([disabled])');
        // Appliquer l'état de la checkbox "select-all" à toutes les autres
        checkboxes.forEach(function(cb) {
          if (cb !== checkbox) { // Ne pas modifier la checkbox "select-all" elle-même
            cb.checked = checkbox.checked;
          }
        });
      });
    });
  });
</script>

<!-- Error Modal -->
<div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="errorModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title text-white" id="errorModalLabel">Erreur</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="errorMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<script>
// Gestion des erreurs et succès via URL
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const error = urlParams.get('error');
    const success = urlParams.get('success');

    if (error) {
        let message = '';
        switch(error) {
            case 'missing_data':
                message = 'Veuillez sélectionner au moins un ticket.';
                break;
            case 'update_failed':
                message = 'Une erreur est survenue lors de la mise à jour des tickets.';
                break;
            default:
                message = 'Une erreur est survenue.';
        }
        $('#errorMessage').text(message);
        $('#errorModal').modal('show');
    }

    if (success === 'tickets_associated') {
        $('#successMessage').text('Les tickets ont été associés avec succès.');
        $('#successModal').modal('show');
    }
});

// Validation du formulaire avant soumission
document.querySelectorAll('form[id^="ticketsForm"]').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        const checkboxes = form.querySelectorAll('input[type="checkbox"]:checked:not(.select-all)');
        if (checkboxes.length === 0) {
            e.preventDefault();
            $('#errorMessage').text('Veuillez sélectionner au moins un ticket.');
            $('#errorModal').modal('show');
        }
    });
});
</script>
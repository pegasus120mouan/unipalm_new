<?php
//session_start();
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_tickets.php';
require_once '../inc/functions/requete/requete_bordereaux.php';
require_once '../inc/functions/requete/requete_usines.php';
require_once '../inc/functions/requete/requete_chef_equipes.php';
require_once '../inc/functions/requete/requete_vehicules.php';
require_once '../inc/functions/requete/requete_agents.php';

if(isset($_GET['action']) && $_GET['action'] == 'delete') {
    $id_bordereau = $_GET['id'];
    $numero_bordereau = $_GET['numero_bordereau'];  
    deleteBordereau($conn, $id_bordereau, $numero_bordereau);
    header('Location: bordereaux.php');
    exit();
}

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
<div id="loader" class="text-center p-3">
        <img src="../dist/img/loading.gif" alt="Chargement..." />
    </div>
    <table id="example1" class="table table-bordered table-striped" style="display: none;">

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
            <th>Validation</th>
            <th>Actions</th>
            <th>Statut Validation</th>
            <th>Associer les tickets</th>
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
            <span class="badge badge-primary">
              <?= $bordereau['nombre_tickets'] ?>
            </span>
          </td>
          <td><?= $bordereau['date_debut'] ? date('d/m/Y', strtotime($bordereau['date_debut'])) : '-' ?></td>
          <td><?= $bordereau['date_fin'] ? date('d/m/Y', strtotime($bordereau['date_fin'])) : '-' ?></td>
          <td><?= number_format($bordereau  ['poids_total'], 2, ',', ' ') ?> kg</td>
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
    <form method="POST" action="validate_bordereau.php" style="display: inline;">
        <input type="hidden" name="id_bordereau" value="<?= $bordereau['id_bordereau'] ?>">
        <input type="hidden" name="action" value="validate">
        <button type="submit" class="btn btn-sm btn-primary"
            <?php if ($bordereau['date_validation_boss'] !== null): ?>
                disabled
            <?php endif; ?>
        >
            <i class="fas fa-check"></i> Valider le bordereau
        </button>
    </form>
   </td>
          <td>
         
            <a href="?action=delete&id=<?= $bordereau['id_bordereau'] ?>&numero_bordereau=<?= $bordereau['numero_bordereau'] ?>" class="btn btn-sm btn-danger">
                <i class="fas fa-trash"></i>
            </a>
            <a href="print_visualisation_bordereau.php?id=<?= $bordereau['id_bordereau'] ?>" class="btn btn-sm btn-success" target="_blank">
              <i class="fas fa-print"></i>
            </a>
            <?php if ($bordereau['date_validation_boss'] === null): ?>
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
          <td>
            <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#ticketsAssociationBordereau<?= $bordereau['id_bordereau'] ?>">
              <i class="fas fa-menu"></i> Associer les tickets au bordereau
            </button>
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
          <form class="forms-sample" method="post" action="print_visualisation_bordereau.php" target="_blank">
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
    <!-- Modal pour l'association des tickets -->
    <div class="modal fade" id="ticketsAssociationBordereau<?= $bordereau['id_bordereau'] ?>" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Association des tickets au bordereau <?= $bordereau['numero_bordereau'] ?></h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-2 text-muted">Informations du bordereau</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <th>ID Agent :</th>
                                            <td><?= $bordereau['id_agent'] ?></td>
                                            <th>N° Bordereau :</th>
                                            <td><?= $bordereau['numero_bordereau'] ?></td>
                                            <th>Date début :</th>
                                            <td><?= date('d/m/Y', strtotime($bordereau['date_debut'])) ?></td>
                                            <th>Date fin :</th>
                                            <td><?= date('d/m/Y', strtotime($bordereau['date_fin'])) ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            
                            <?php 
                            $tickets = getTicketsAssociation(
                                $conn, 
                                $bordereau['id_agent'],
                                $bordereau['date_debut'],
                                $bordereau['date_fin']
                            );
                            if (!empty($tickets)) : 
                                $has_available_tickets = false;
                                foreach ($tickets as $ticket) {
                                    if (empty($ticket['numero_bordereau'])) {
                                        $has_available_tickets = true;
                                        break;
                                    }
                                }
                            ?>
                            <form id="associationForm<?= $bordereau['id_bordereau'] ?>" action="associer_tickets.php" method="post">
                                <input type="hidden" name="id_agent" value="<?= $bordereau['id_agent'] ?>">
                                <input type="hidden" name="numero_bordereau" value="<?= $bordereau['numero_bordereau'] ?>">
                                <input type="hidden" name="date_debut" value="<?= $bordereau['date_debut'] ?>">
                                <input type="hidden" name="date_fin" value="<?= $bordereau['date_fin'] ?>">
                                
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th style="width: 40px">
                                                    <?php if ($has_available_tickets): ?>
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input select-all" id="selectAll<?= $bordereau['id_bordereau'] ?>">
                                                        <label class="custom-control-label" for="selectAll<?= $bordereau['id_bordereau'] ?>"></label>
                                                    </div>
                                                    <?php endif; ?>
                                                </th>
                                                <th>Date Réception</th>
                                                <th>Date Ticket</th>
                                                <th>Véhicule</th>
                                                <th>N° Ticket</th>
                                                <th>Poids (kg)</th>
                                                <th>Prix unitaire</th>
                                                <th>Montant total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $total_poids = 0;
                                            $total_montant_total = 0;
                                            foreach ($tickets as $ticket) : 
                                                $total_poids += $ticket['poids'];
                                                $total_montant_total += $ticket['montant_total'];
                                                $is_associated = !empty($ticket['numero_bordereau']);
                                            ?>
                                            <tr <?= $is_associated ? 'class="text-muted bg-light"' : '' ?>>
                                                <td>
                                                    <?php if (!$is_associated): ?>
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input ticket-checkbox" 
                                                               id="ticket<?= $ticket['id_ticket'] ?>" 
                                                               name="tickets[]" 
                                                               value="<?= $ticket['id_ticket'] ?>">
                                                        <label class="custom-control-label" for="ticket<?= $ticket['id_ticket'] ?>"></label>
                                                    </div>
                                                    <?php else: ?>
                                                    <i class="fas fa-link text-muted" title="Déjà associé au bordereau <?= htmlspecialchars($ticket['numero_bordereau']) ?>"></i>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= date('d/m/y', strtotime($ticket['date_reception'])) ?></td>
                                                <td><?= date('d/m/y', strtotime($ticket['date_ticket'])) ?></td>
                                                <td><?= $ticket['vehicule'] ?></td>
                                                <td><?= $ticket['numero_ticket'] ?></td>
                                                <td class="text-right"><?= number_format($ticket['poids'], 0, ',', ' ') ?></td>
                                                <td class="text-right"><?= number_format($ticket['prix_unitaire'], 2, ',', ' ') ?></td>
                                                <td class="text-right"><?= number_format($ticket['montant_total'], 2, ',', ' ') ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <tr class="font-weight-bold">
                                                <td colspan="5" class="text-right">TOTAL GÉNÉRAL (<?= count($tickets) ?> tickets)</td>
                                                <td class="text-right"><?= number_format($total_poids, 0, ',', ' ') ?></td>
                                                <td colspan="2" class="text-right"><?= number_format($total_montant_total, 2, ',', ' ') ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                                    <?php if ($has_available_tickets): ?>
                                    <button type="submit" class="btn btn-primary" id="submitAssociation<?= $bordereau['id_bordereau'] ?>">
                                        <i class="fas fa-link"></i> Associer les tickets sélectionnés
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </form>
                            <?php else : ?>
                                <div class="alert alert-info">
                                    Aucun ticket disponible pour cette période et cet agent.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>


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

<!-- Script pour la gestion des tickets -->
<script>
function validateBordereau(bordereauId) {
    console.log('Validation du bordereau:', bordereauId);
    
    $.ajax({
        url: 'validate_bordereau.php',
        method: 'POST',
        data: { 
            id_bordereau: bordereauId,
            action: 'validate'
        },
        dataType: 'json',
        success: function(response) {
            console.log('Réponse reçue:', response);
            if (response.success) {
                location.reload();
            } else {
                alert('Erreur lors de la validation du bordereau');
            }
        },
        error: function(xhr, status, error) {
            console.error('Erreur AJAX:', error);
            console.error('Status:', status);
            console.error('Réponse:', xhr.responseText);
            alert('Erreur lors de la validation du bordereau');
        }
    });
}

$(document).ready(function() {
    // Le reste de votre code JavaScript existant...
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Afficher le loader au démarrage
    document.getElementById('loader').style.display = 'block';
    document.getElementById('example1').style.display = 'none';
    
    // Cacher le loader et afficher la table après un court délai
    setTimeout(function() {
        document.getElementById('loader').style.display = 'none';
        document.getElementById('example1').style.display = 'table';
        
        // Initialiser DataTables après avoir affiché la table
        if($.fn.DataTable.isDataTable('#example1')) {
            $('#example1').DataTable().destroy();
        }
        $('#example1').DataTable({
            "responsive": true,
            "lengthChange": false,
            "autoWidth": false,
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/French.json"
            }
        });
    }, 1000);
    
    // Gestion des soumissions de formulaire
    $('form').on('submit', function() {
        document.getElementById('loader').style.display = 'block';
    });

    // Gestion des requêtes AJAX
    $(document).ajaxStart(function() {
        document.getElementById('loader').style.display = 'block';
    }).ajaxStop(function() {
        document.getElementById('loader').style.display = 'none';
    });
    
    // Gestion des modals
    $('.modal').on('show.bs.modal', function() {
        document.getElementById('loader').style.display = 'none';
    });
});
</script>
</body>
</html>
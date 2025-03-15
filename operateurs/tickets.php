<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_tickets.php';
require_once '../inc/functions/requete/requete_usines.php';
require_once '../inc/functions/requete/requete_chef_equipes.php';
require_once '../inc/functions/requete/requete_vehicules.php';
require_once '../inc/functions/requete/requete_agents.php';

// Initialisation des variables de pagination et de recherche
$limit = $_GET['limit'] ?? 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Récupérer les paramètres de recherche
$search_usine = $_GET['usine'] ?? null;
$search_date = $_GET['date_creation'] ?? null;
$search_chauffeur = $_GET['chauffeur'] ?? null;
$search_agent = $_GET['agent_id'] ?? null;

// Récupérer les données (functions)
if ($search_usine || $search_date || $search_chauffeur || $search_agent) {
    $tickets = searchTickets($conn, $search_usine, $search_date, $search_chauffeur, $search_agent);
} else {
    $tickets = getTickets($conn);
}

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

// Récupérer les données pour les listes déroulantes
$usines = getUsines($conn);
$chefs_equipes = getChefEquipes($conn);
$vehicules = getVehicules($conn);
$agents = getAgents($conn);

include('header.php');
?>

<!-- Message d'erreur/succès -->
<?php if (isset($_SESSION['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= $_SESSION['error'] ?>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= $_SESSION['success'] ?>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php unset($_SESSION['success']); ?>
<?php endif; ?>

<!-- Reste du code HTML -->

<style>
/* Styles communs pour les formulaires */
.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    font-weight: bold;
    margin-bottom: 0.5rem;
    display: block;
}

/* Styles pour les champs de saisie */
#input,
#input_agent,
#input_vehicule {
    padding: 0.375rem 0.75rem;
    font-size: 1rem;
    line-height: 1.5;
    color: #495057;
    background-color: #fff;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

#input:focus,
#input_agent:focus,
#input_vehicule:focus {
    color: #495057;
    background-color: #fff;
    border-color: #80bdff;
    outline: 0;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

#input::placeholder,
#input_agent::placeholder,
#input_vehicule::placeholder {
    color: #6c757d;
    opacity: 1;
}

/* Styles pour la liste déroulante */
.list {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    list-style: none;
    padding: 0;
    margin: 0;
    border: 1px solid #ddd;
    border-top: none;
    border-radius: 0 0 4px 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    z-index: 1000;
    max-height: 200px;
    overflow-y: auto;
    display: none;
}

.list li {
    padding: 8px 12px;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.list li:hover {
    background-color: #f8f9fa;
}

.list li strong {
    color: #007bff;
}

/* Styles pour le responsive */
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

/* Utilitaires */
.margin-right-15 {
    margin-right: 15px;
}

.block-container {
    background-color: #d7dbdd;
    padding: 20px;
    border-radius: 5px;
    width: 100%;
    margin-bottom: 20px;
}

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

    <div class="block-container">
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add-ticket">
      <i class="fa fa-edit"></i>Enregistrer un ticket
    </button>
    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#print-bordereau-agent">
      <i class="fa fa-print"></i> Imprimer Bordereau
    </button>
    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#print-bordereau">
      <i class="fas fa-file-pdf"></i> Imprimer ticket par usine
    </button>
    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#search_ticket">
      <i class="fa fa-search"></i> Rechercher un ticket
    </button>

    <button type="button" class="btn btn-dark" onclick="window.location.href='export_tickets.php'">
              <i class="fa fa-print"></i> Exporter tous  les tickets
    </button>

    <button type="button" class="btn btn-outline-dark" data-toggle="modal" data-target="#exportDateModal">
              <i class="fas fa-file-excel"></i> Exporter  les tickets sur une période
    </button>
 
</div>

  <div class="table-responsive">
    <table id="example1" class="table table-bordered table-striped">

 <!-- <table style="max-height: 90vh !important; overflow-y: scroll !important" id="example1" class="table table-bordered table-striped">-->
    <thead>
      <tr>
        
        <th>Date ticket</th>
        <th>Numero Ticket</th>
        <th>usine</th>
        <th>Chargé de mission</th>
        <th>Vehicule</th>
        <th>Poids</th>
        <th>Ticket créé par</th>
        <th>Date Ajout</th>
        <th>Prix Unitaire</th>
        <th>Date validation</th>
        <th>Montant</th>
        <th>Date Paie</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($tickets_list as $ticket) : ?>
        <tr>
          
          <td><?= isset($ticket['date_ticket']) ? date('d/m/Y', strtotime($ticket['date_ticket'])) : '-' ?></td>
          <td><a href="#" data-toggle="modal" data-target="#ticketModal<?= $ticket['id_ticket'] ?>"><?= isset($ticket['numero_ticket']) ? $ticket['numero_ticket'] : '-' ?></a></td>
          <td><?= isset($ticket['nom_usine']) ? $ticket['nom_usine'] : '-' ?></td>
          <td><?= isset($ticket['nom_complet_agent']) ? $ticket['nom_complet_agent'] : '-' ?></td>
          <td><?= isset($ticket['matricule_vehicule']) ? $ticket['matricule_vehicule'] : '-' ?></td>
          <td><?= isset($ticket['poids']) ? $ticket['poids'] : '-' ?></td>

          <td><?= isset($ticket['utilisateur_nom_complet']) ? $ticket['utilisateur_nom_complet'] : '-' ?></td>
          <td><?= isset($ticket['created_at']) ? date('d/m/Y', strtotime($ticket['created_at'])) : '-' ?></td>

         <td>
            <?php if (!isset($ticket['prix_unitaire']) || $ticket['prix_unitaire'] === null || $ticket['prix_unitaire'] == 0.00): ?>
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
            <?php if (!isset($ticket['date_validation_boss']) || $ticket['date_validation_boss'] === null): ?>
        <button class="btn btn-warning btn-block" disabled>
            En cours
        </button>
    <?php else: ?>
        <?= date('d/m/Y', strtotime($ticket['date_validation_boss'])) ?>
        <?php endif; ?>
       </td>


    <td>
                <?php if (!isset($ticket['montant_paie']) || $ticket['montant_paie'] === null): ?>
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
                <?php if (!isset($ticket['date_paie']) || $ticket['date_paie'] === null): ?>
            <button class="btn btn-secondary btn-block" disabled>
                Non payé
            </button>
        <?php else: ?>
            <?= date('d/m/Y', strtotime($ticket['date_paie'])) ?>
            <?php endif; ?>
          </td>
          
  
          <td class="actions">
         <?php if (!isset($ticket['date_paie']) || $ticket['date_paie'] === null): ?>
            <a class="edit" data-toggle="modal" data-target="#editModalTicket<?= $ticket['id_ticket'] ?>">
            <i class="fas fa-pen fa-xs" style="font-size:24px;color:blue"></i>
            </a>
            <a href="#" class="trash" data-toggle="modal" data-target="#confirmDeleteModal" data-id="<?= $ticket['id_ticket'] ?>">
                <i class="fas fa-trash fa-xs" style="font-size:24px;color:red"></i>
            </a>
            <?php else: ?>
            <i class="fas fa-pen fa-xs" style="font-size:24px;color:gray" title="Ticket déjà payé"></i>
            <i class="fas fa-trash fa-xs" style="font-size:24px;color:gray" title="Ticket déjà payé"></i>
            <?php endif; ?>
          </td>
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
                <form action="tickets_update.php?id=<?= $ticket['id_ticket'] ?>" method="post">
                <div class="form-group">
                <label for="exampleInputEmail1">Date ticket</label>
                <input type="date" class="form-control" id="exampleInputEmail1" placeholder="date ticket" name="date_ticket" value="<?= isset($ticket['date_ticket']) ? $ticket['date_ticket'] : '' ?>"> 
              </div> 
                <div class="form-group">
                        <label for="prix_unitaire">Numéro du ticket</label>
                        <input type="text" class="form-control" id="numero_ticket" name="numero_ticket" value="<?= isset($ticket['numero_ticket']) ? $ticket['numero_ticket'] : '' ?>" required>
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
    </tbody>
  </table>

</div>

  <div class="pagination-container bg-secondary d-flex justify-content-center w-100 text-white p-3">
    <?php if($page > 1): ?>
        <a href="?page=<?= $page - 1 ?><?= isset($_GET['usine']) ? '&usine='.$_GET['usine'] : '' ?><?= isset($_GET['date_creation']) ? '&date_creation='.$_GET['date_creation'] : '' ?><?= isset($_GET['chauffeur']) ? '&chauffeur='.$_GET['chauffeur'] : '' ?><?= isset($_GET['agent_id']) ? '&agent_id='.$_GET['agent_id'] : '' ?>" class="btn btn-primary"><</a>
    <?php endif; ?>
    
    <span class="mx-2"><?= $page . '/' . $total_pages ?></span>

    <?php if($page < $total_pages): ?>
        <a href="?page=<?= $page + 1 ?><?= isset($_GET['usine']) ? '&usine='.$_GET['usine'] : '' ?><?= isset($_GET['date_creation']) ? '&date_creation='.$_GET['date_creation'] : '' ?><?= isset($_GET['chauffeur']) ? '&chauffeur='.$_GET['chauffeur'] : '' ?><?= isset($_GET['agent_id']) ? '&agent_id='.$_GET['agent_id'] : '' ?>" class="btn btn-primary">></a>
    <?php endif; ?>
    
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
</div>

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
                <label for="input" class="font-weight-bold mb-2">Sélection Usine</label>
                <div class="position-relative">
                    <input type="text" class="form-control" id="input" placeholder="Sélectionner une usine" autocomplete="off">
                    <input type="hidden" name="id_usine" id="usine_id">
                    <ul class="list shadow-sm"></ul>
                </div>
              </div>

              <div class="form-group">
                <label for="input" class="font-weight-bold mb-2">Sélectionner un chargé de mission</label>
                <div class="position-relative">
                    <input type="text" class="form-control" id="input_agent" placeholder="Sélectionner un chargé de mission" autocomplete="off">
                    <input type="hidden" name="id_agent" id="agent_id">
                    <ul class="list shadow-sm"></ul>
                </div>
              </div>

              <div class="form-group">
                <label for="input" class="font-weight-bold mb-2">Sélection véhicule</label>
                <div class="position-relative">
                    <input type="text" class="form-control" id="input_vehicule" placeholder="Sélectionner un véhicule" autocomplete="off">
                    <input type="hidden" name="vehicule_id" id="vehicule_id">
                    <ul class="list shadow-sm"></ul>
                </div>
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
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title">
            <i class="fas fa-file-pdf"></i> Impression des tickets par usine
          </h5>
          <button type="button" class="close text-white" data-dismiss="modal">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form action="print_tickets_usine.php" method="POST" target="_blank">
          <div class="modal-body">
            <div class="form-group">
              <label for="id_usine">Sélectionner une usine</label>
              <select class="form-control" name="id_usine" id="id_usine" required>
                <option value="">Choisir une usine</option>
                <?php foreach($usines as $usine): ?>
                  <option value="<?= $usine['id_usine'] ?>"><?= htmlspecialchars($usine['nom_usine']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label for="date_debut">Date début</label>
              <input type="date" class="form-control" name="date_debut" id="date_debut" required>
            </div>
            <div class="form-group">
              <label for="date_fin">Date fin</label>
              <input type="date" class="form-control" name="date_fin" id="date_fin" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
            <button type="submit" class="btn btn-success">
              <i class="fas fa-file-pdf"></i> Générer PDF
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal pour impression bordereau par agent -->
<div class="modal fade" id="print-bordereau-agent">
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
                                if (!empty($agents)) {
                                    foreach ($agents as $agent) {
                                        echo '<option value="' . $agent['id_agent'] . '">' . $agent['nom_complet_agent'] . '</option>';
                                    }
                                } else {
                                    echo '<option value="">Aucune chef équipe disponible</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="date_debut">Date de debut</label>
                            <input type="date" class="form-control" id="date_debut" name="date_debut" required>
                        </div>
                        <div class="form-group">
                            <label for="date_fin">Date Fin</label>
                            <input type="date" class="form-control" id="date_fin" name="date_fin" required>
                        </div>

                        <button type="submit" class="btn btn-primary mr-2" name="saveCommande">Imprimer</button>
                        <button type="button" class="btn btn-light" data-dismiss="modal">Annuler</button>
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

<!-- Modal Recherche par Date -->
<div class="modal fade" id="exportDateModal" tabindex="-1" role="dialog" aria-labelledby="searchByDateModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="searchByDateModalLabel">
                    <i class="fas fa-calendar-alt mr-2"></i>Exporter Tickets sur une période
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="exportDateForm" method="get" action="export_tickets_periode.php" target="_blank">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="date_debut" class="font-weight-bold mb-2">Date de début</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                            </div>
                            <input type="date" class="form-control custom-input" id="date_debut" name="date_debut" required 
                                   style="padding: 0.5rem; border: 1px solid #ced4da; border-radius: 0 0.25rem 0.25rem 0;">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="date_fin" class="font-weight-bold mb-2">Date fin</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                            </div>
                            <input type="date" class="form-control custom-input" id="date_fin" name="date_fin" required
                                   style="padding: 0.5rem; border: 1px solid #ced4da; border-radius: 0 0.25rem 0.25rem 0;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-2"></i>Annuler
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-file-export mr-2"></i>Exporter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Recherche par Date -->
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
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation des modals
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

    // Configuration pour la sélection d'usine
    setupAutoComplete({
        inputId: 'input',
        hiddenInputId: 'usine_id',
        listSelector: '#usine-list',
        apiUrl: '../inc/functions/requete/api_requete_usines.php',
        nameField: 'nom_usine',
        idField: 'id_usine'
    });

    // Configuration pour la sélection d'agent
    setupAutoComplete({
        inputId: 'input_agent',
        hiddenInputId: 'agent_id',
        listSelector: '#agent-list',
        apiUrl: '../inc/functions/requete/api_requete_agents.php',
        nameField: 'nom_complet_agent',
        idField: 'id_agent'
    });

    // Configuration pour la sélection de véhicule
    setupAutoComplete({
        inputId: 'input_vehicule',
        hiddenInputId: 'vehicule_id',
        listSelector: '#vehicule-list',
        apiUrl: '../inc/functions/requete/api_requete_vehicules.php',
        nameField: 'matricule_vehicule',
        idField: 'vehicules_id'
    });

    function setupAutoComplete(config) {
        const input = document.getElementById(config.inputId);
        const hiddenInput = document.getElementById(config.hiddenInputId);
        const list = input.parentElement.querySelector('.list');
        let data = [];

        // Récupération des données
        fetch(config.apiUrl)
            .then(response => response.json())
            .then(result => {
                if (result.success && result.data.length > 0) {
                    data = result.data;
                } else {
                    console.error('Aucune donnée trouvée');
                }
            })
            .catch(error => console.error('Erreur:', error));

        function showSuggestions() {
            const inputValue = input.value.toLowerCase();
            list.innerHTML = '';
            list.style.display = 'none';

            if (!inputValue) {
                hiddenInput.value = '';
                return;
            }

            const matchingItems = data.filter(item => 
                item[config.nameField].toLowerCase().includes(inputValue)
            );

            if (matchingItems.length > 0) {
                list.style.display = 'block';
                matchingItems.forEach(item => {
                    const li = document.createElement('li');
                    const name = item[config.nameField];
                    const index = name.toLowerCase().indexOf(inputValue);
                    const avant = name.substring(0, index);
                    const match = name.substring(index, index + inputValue.length);
                    const apres = name.substring(index + inputValue.length);

                    li.innerHTML = avant + '<strong>' + match + '</strong>' + apres;
                    
                    li.addEventListener('click', () => {
                        input.value = name;
                        hiddenInput.value = item[config.idField];
                        list.style.display = 'none';
                    });

                    list.appendChild(li);
                });
            }
        }

        input.addEventListener('input', showSuggestions);
        input.addEventListener('focus', showSuggestions);

        // Fermer la liste si on clique ailleurs
        document.addEventListener('click', (e) => {
            if (e.target !== input) {
                list.style.display = 'none';
            }
        });

        // Empêcher la fermeture lors du clic sur la liste
        list.addEventListener('click', (e) => {
            e.stopPropagation();
        });

        // Réinitialiser à la fermeture du modal
        $('#add-ticket').on('hidden.bs.modal', function () {
            input.value = '';
            hiddenInput.value = '';
            list.style.display = 'none';
        });
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des notifications
    <?php if (isset($_SESSION['success_modal'])): ?>
        $('#successModal').modal('show');
        var audio = new Audio("../inc/sons/notification.mp3");
        audio.volume = 1.0;
        audio.play().catch((error) => {
            console.error('Erreur de lecture audio :', error);
        });
        <?php 
        unset($_SESSION['success_modal']);
        unset($_SESSION['prix_unitaire']);
    endif; ?>

    <?php if (isset($_SESSION['warning'])): ?>
        $('#warningModal').modal('show');
        <?php unset($_SESSION['warning']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['delete_pop'])): ?>
        $('#errorModal').modal('show');
        <?php unset($_SESSION['delete_pop']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['popup']) && $_SESSION['popup'] == true): ?>
        var audio = new Audio("../inc/sons/notification.mp3");
        audio.volume = 1.0;
        audio.play().then(() => {
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
        <?php $_SESSION['popup'] = false; ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['delete_pop']) && $_SESSION['delete_pop'] == true): ?>
        var Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });

        Toast.fire({
            icon: 'error',
            title: 'Action échouée.'
        });
        <?php $_SESSION['delete_pop'] = false; ?>
    <?php endif; ?>
});
</script>

<!-- Scripts -->
<script src="../../plugins/jquery/jquery.min.js"></script>
<script src="../../plugins/jquery-ui/jquery-ui.min.js"></script>
<script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../../plugins/chart.js/Chart.min.js"></script>
<script src="../../plugins/sparklines/sparkline.js"></script>
<script src="../../plugins/jqvmap/jquery.vmap.min.js"></script>
<script src="../../plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
<script src="../../plugins/jquery-knob/jquery.knob.min.js"></script>
<script src="../../plugins/moment/moment.min.js"></script>
<script src="../../plugins/daterangepicker/daterangepicker.js"></script>
<script src="../../plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<script src="../../plugins/summernote/summernote-bs4.min.js"></script>
<script src="../../plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<script src="../../dist/js/adminlte.js"></script>
</body>
</html>
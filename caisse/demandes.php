<?php
require_once '../inc/functions/connexion.php';
include('header.php');

// Paramètres de pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Paramètres de filtrage
$statut = isset($_GET['statut']) ? $_GET['statut'] : 'all';
$date_debut = isset($_GET['date_debut']) ? $_GET['date_debut'] : '';
$date_fin = isset($_GET['date_fin']) ? $_GET['date_fin'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Construction de la requête SQL
$where_conditions = [];
$params = [];

if ($statut !== 'all') {
    $where_conditions[] = "d.statut = ?";
    $params[] = $statut;
}

if ($date_debut) {
    $where_conditions[] = "DATE(d.date_demande) >= ?";
    $params[] = $date_debut;
}

if ($date_fin) {
    $where_conditions[] = "DATE(d.date_demande) <= ?";
    $params[] = $date_fin;
}

if ($search) {
    $where_conditions[] = "(d.numero_demande LIKE ? OR d.motif LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
}

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Requête pour le nombre total de demandes
$count_query = "SELECT COUNT(*) as total FROM demande_sortie d $where_clause";
$stmt = $conn->prepare($count_query);
$stmt->execute($params);
$total_rows = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_rows / $limit);

// Requête pour les demandes avec les noms des utilisateurs
$query = "SELECT d.*, 
                 u1.nom as approbateur_nom, u1.prenoms as approbateur_prenoms,
                 u2.nom as payeur_nom, u2.prenoms as payeur_prenoms
          FROM demande_sortie d 
          LEFT JOIN utilisateurs u1 ON d.approuve_par = u1.id
          LEFT JOIN utilisateurs u2 ON d.paye_par = u2.id
          $where_clause 
          ORDER BY d.date_demande DESC 
          LIMIT $limit OFFSET $offset";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fonction pour générer un numéro de demande unique
function genererNumeroDemande($conn) {
    $date = date('Ymd');
    $sql = "SELECT COUNT(*) as count FROM demande_sortie WHERE DATE(created_at) = CURDATE()";
    $stmt = $conn->query($sql);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $count = $result['count'] + 1;
    return 'DEM-' . $date . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
}

// Fonctions utilitaires
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'en_attente':
            return 'warning';
        case 'approuve':
            return 'success';
        case 'rejete':
            return 'danger';
        case 'paye':
            return 'info';
        default:
            return 'secondary';
    }
}

function getStatusLabel($status) {
    switch ($status) {
        case 'en_attente':
            return 'En attente';
        case 'approuve':
            return 'Approuvé';
        case 'rejete':
            return 'Rejeté';
        case 'paye':
            return 'Payé';
        default:
            return $status;
    }
}
?>

<!-- Modal pour ticket en doublon -->
<div class="modal fade" id="ticketExistModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i> Attention !
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-times-circle text-danger fa-4x mb-3"></i>
                <h4 class="text-danger">Numéro de ticket en double</h4>
                <p class="mb-0">Le ticket numéro <strong id="duplicateTicketNumber"></strong> existe déjà.</p>
                <p>Veuillez utiliser un autre numéro.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

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

<script>
$(document).ready(function() {
    // Vérification lors de la saisie
    $('input[name="numero_ticket"]').on('change', function() {
        var numero_ticket = $(this).val().trim();
        if (numero_ticket) {
            $.ajax({
                url: 'check_ticket.php',
                method: 'POST',
                data: { numero_ticket: numero_ticket },
                dataType: 'json',
                success: function(response) {
                    if (response.exists) {
                        $('#duplicateTicketNumber').text(numero_ticket);
                        $('#ticketExistModal').modal('show');
                        $('input[name="numero_ticket"]').val('');
                    }
                }
            });
        }
    });

    // Focus sur le champ après fermeture du modal
    $('#ticketExistModal').on('hidden.bs.modal', function() {
        $('input[name="numero_ticket"]').focus();
    });
});
</script>

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
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add-demande">
      <i class="fa fa-edit"></i>Enregistrer une demande
    </button>

    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#print-bordereau">
      <i class="fa fa-print"></i> Imprimer un bordereau
    </button>

    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#search_ticket">
      <i class="fa fa-search"></i> Rechercher un ticket
    </button>

    <button type="button" class="btn btn-dark" onclick="window.location.href='export_tickets.php'">
              <i class="fa fa-print"></i> Exporter la liste les tickets
             </button>
</div>



  <div class="table-responsive">
  <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>N° Demande</th>
                                            <th>Montant</th>
                                            <th>Motif</th>
                                            <th>Statut</th>
                                            <th>Date Approbation</th>
                                            <th>Approuvé par</th>
                                            <th>Date Paiement</th>
                                            <th>Payé par</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($demandes as $demande): ?>
                                            <tr>
                                                <td><?= date('d/m/Y H:i', strtotime($demande['date_demande'])) ?></td>
                                                <td><?= htmlspecialchars($demande['numero_demande']) ?></td>
                                                <td><?= number_format($demande['montant'], 0, ',', ' ') ?> FCFA</td>
                                                <td><?= htmlspecialchars($demande['motif']) ?></td>
                                                <td>
                                                    <span class="badge badge-<?= getStatusBadgeClass($demande['statut']) ?>">
                                                        <?= getStatusLabel($demande['statut']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if (empty($demande['date_approbation'])): ?>
                                                        <button class="btn btn-danger btn-block" disabled>
                                                            En attente de validation
                                                        </button>
                                                    <?php else: ?>
                                                        <button class="btn btn-dark btn-block" disabled>
                                                            <?= date('d/m/Y H:i', strtotime($demande['date_approbation'])) ?>
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    if (!empty($demande['approbateur_nom'])) {
                                                        echo htmlspecialchars($demande['approbateur_nom'] . ' ' . $demande['approbateur_prenoms']);
                                                    } else {
                                                        echo '-';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php if (empty($demande['date_paiement'])): ?>
                                                        <button class="btn btn-danger btn-block" disabled>
                                                            En attente de paiement
                                                        </button>
                                                    <?php else: ?>
                                                        <button class="btn btn-dark btn-block" disabled>
                                                            <?= date('d/m/Y H:i', strtotime($demande['date_paiement'])) ?>
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    if (!empty($demande['payeur_nom'])) {
                                                        echo htmlspecialchars($demande['payeur_nom'] . ' ' . $demande['payeur_prenoms']);
                                                    } else {
                                                        echo '-';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php if ($demande['statut'] === 'en_attente'): ?>
                                                        <div class="btn-group">
                                                            <button type="button" class="btn btn-info btn-sm" onclick="editDemande(<?= $demande['id_demande'] ?>)" title="Modifier">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-danger btn-sm" onclick="deleteDemande(<?= $demande['id_demande'] ?>)" title="Supprimer">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
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

  <div class="modal fade" id="add-demande" tabindex="-1" role="dialog" aria-labelledby="addDemandeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="addDemandeModalLabel">Enregistrer une demande</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form class="forms-sample" method="post" action="traitement_demande.php">
            <div class="card-body">
              <div class="form-group">
                <label for="montant">Montant</label>
                <div class="input-group">
                  <input type="number" step="0.01" min="0" class="form-control" id="montant" name="montant" placeholder="Montant demandé" required>
                  <div class="input-group-append">
                    <span class="input-group-text">FCFA</span>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label for="motif">Motif de la sortie</label>
                <textarea class="form-control" id="motif" name="motif" rows="3" placeholder="Décrivez le motif de votre demande de sortie" required></textarea>
              </div>
              <input type="hidden" name="statut" value="en_attente">

              <button type="submit" class="btn btn-primary mr-2" name="saveDemande">Enregistrer</button>
              <button type="button" class="btn btn-light" data-dismiss="modal">Annuler</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
    $(document).ready(function() {
        // Mettre à jour la date automatiquement quand le modal s'ouvre
        // $('#add-demande').on('show.bs.modal', function () {
        //     var now = new Date();
        //     var year = now.getFullYear();
        //     var month = String(now.getMonth() + 1).padStart(2, '0');
        //     var day = String(now.getDate()).padStart(2, '0');
        //     var hours = String(now.getHours()).padStart(2, '0');
        //     var minutes = String(now.getMinutes()).padStart(2, '0');
            
        //     var datetime = `${year}-${month}-${day}T${hours}:${minutes}`;
        //     $('#date_demande').val(datetime);
        // });
    });
  </script>

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

<!-- Modal Modifier -->
<div class="modal fade" id="edit-demande" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Modifier la demande</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="modifier_demande.php" method="post">
                <div class="modal-body">
                    <input type="hidden" id="edit_id_demande" name="id_demande">
                    <div class="form-group">
                        <label for="edit_montant">Montant</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="edit_montant" name="montant" required min="0">
                            <div class="input-group-append">
                                <span class="input-group-text">FCFA</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_motif">Motif</label>
                        <textarea class="form-control" id="edit_motif" name="motif" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" name="update_demande">Enregistrer</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Supprimer -->
<div class="modal fade" id="delete-demande" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Confirmer la suppression</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer cette demande ?</p>
                <form method="post" action="supprimer_demande.php">
                    <input type="hidden" id="delete_id_demande" name="id_demande">
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger">Supprimer</button>
                        <button type="button" class="btn btn-light" data-dismiss="modal">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Debug
    console.log('Script chargé');
    
    // Fonction pour éditer une demande
    window.editDemande = function(id) {
        // Récupérer les données de la ligne
        var row = $('button[onclick="editDemande(' + id + ')"]').closest('tr');
        var montant = row.find('td:eq(2)').text().replace(' FCFA', '').replace(/\s/g, '');
        var motif = row.find('td:eq(3)').text();
        
        // Remplir le formulaire
        $('#edit_id_demande').val(id);
        $('#edit_montant').val(montant);
        $('#edit_motif').val(motif);
        
        // Afficher le modal
        $('#edit-demande').modal('show');
    };
    
    // Fonction pour supprimer une demande
    window.deleteDemande = function(id) {
        $('#delete_id_demande').val(id);
        $('#delete-demande').modal('show');
    };
});

// Vérifier que jQuery et Bootstrap sont chargés
console.log('jQuery version:', typeof $ !== 'undefined' ? $.fn.jquery : 'non chargé');
console.log('Bootstrap modal:', typeof $.fn.modal !== 'undefined' ? 'chargé' : 'non chargé');
</script>

<!-- Modal pour ticket existant -->
<div class="modal fade" id="ticketExistModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i> Ticket déjà existant
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Le ticket numéro <strong id="existingTicketNumber"></strong> existe déjà dans la base de données.</p>
                <p>Veuillez utiliser un autre numéro de ticket.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<?php foreach ($demandes as $demande) : ?>
  <div class="modal fade" id="demandeModal<?= $demande['id_demande'] ?>" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-info text-white">
          <h5 class="modal-title" id="demandeModalLabel<?= $demande['id_demande'] ?>">
            <i class="fas fa-file-alt mr-2"></i>Détails de la demande #<?= $demande['numero_demande'] ?>
          </h5>
          <button type="button" class="close text-white" data-dismiss="modal">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-12">
              <table class="table table-bordered">
                <tr>
                  <th style="width: 150px;">Date demande</th>
                  <td><?= date('d/m/Y H:i', strtotime($demande['date_demande'])) ?></td>
                </tr>
                <tr>
                  <th>Motif</th>
                  <td><?= nl2br(htmlspecialchars($demande['motif'])) ?></td>
                </tr>
                <tr>
                  <th>Montant</th>
                  <td><?= number_format($demande['montant'], 0, ',', ' ') ?> FCFA</td>
                </tr>
                <tr>
                  <th>Statut</th>
                  <td>
                    <span class="badge badge-<?= getStatusBadgeClass($demande['statut']) ?>">
                      <?= getStatusLabel($demande['statut']) ?>
                    </span>
                  </td>
                </tr>
                <?php if ($demande['approuve_par']): ?>
                <tr>
                  <th>Approuvé par</th>
                  <td><?= htmlspecialchars($demande['approbateur_nom'] . ' ' . $demande['approbateur_prenoms']) ?><br>
                      Le <?= date('d/m/Y H:i', strtotime($demande['date_approbation'])) ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($demande['paye_par']): ?>
                <tr>
                  <th>Payé par</th>
                  <td><?= htmlspecialchars($demande['payeur_nom'] . ' ' . $demande['payeur_prenoms']) ?><br>
                      Le <?= date('d/m/Y H:i', strtotime($demande['date_paiement'])) ?></td>
                </tr>
                <?php endif; ?>
              </table>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
        </div>
      </div>
    </div>
  </div>
<?php endforeach; ?>





<script src="../../plugins/jquery/jquery.min.js"></script>
<script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../../dist/js/adminlte.min.js"></script>

<script>
$(document).ready(function() {
    console.log('Document ready');
    
    // Fonction pour éditer une demande
    window.editDemande = function(id) {
        console.log('Edit clicked:', id);
        
        // Récupérer les données de la ligne
        var row = $('button[onclick="editDemande(' + id + ')"]').closest('tr');
        var montant = row.find('td:eq(2)').text().replace(' FCFA', '').replace(/\s/g, '');
        var motif = row.find('td:eq(3)').text();
        
        console.log('Data:', { montant, motif });
        
        // Remplir le formulaire
        $('#edit_id_demande').val(id);
        $('#edit_montant').val(montant);
        $('#edit_motif').val(motif);
        
        // Afficher le modal
        $('#edit-demande').modal('show');
    };
    
    // Fonction pour supprimer une demande
    window.deleteDemande = function(id) {
        console.log('Delete clicked:', id);
        $('#delete_id_demande').val(id);
        $('#delete-demande').modal('show');
    };
});
</script>
</body>
</html>
<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_agents.php';
include('header.php');

// Récupérer la liste des agents
$agents = getAgents($conn);

// Paramètres de pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Paramètres de filtrage
$type = isset($_GET['type']) ? $_GET['type'] : 'all';
$date_debut = isset($_GET['date_debut']) ? $_GET['date_debut'] : '';
$date_fin = isset($_GET['date_fin']) ? $_GET['date_fin'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$agent_id = isset($_GET['agent_id']) ? $_GET['agent_id'] : '';

// Construction de la requête SQL
$where_conditions = [];
$params = [];

if ($type !== 'all') {
    $where_conditions[] = "r.type_document = ?";
    $params[] = $type;
}

if ($date_debut) {
    $where_conditions[] = "DATE(r.date_creation) >= ?";
    $params[] = $date_debut;
}

if ($date_fin) {
    $where_conditions[] = "DATE(r.date_creation) <= ?";
    $params[] = $date_fin;
}

if ($search) {
    $where_conditions[] = "(r.numero_recu LIKE ? OR r.numero_document LIKE ? OR r.nom_agent LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if ($agent_id) {
    $where_conditions[] = "r.id_agent = ?";
    $params[] = $agent_id;
}

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Requête pour le nombre total de reçus
$count_query = "SELECT COUNT(*) as total FROM recus_paiements r $where_clause";
$stmt = $conn->prepare($count_query);
$stmt->execute($params);
$total_rows = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_rows / $limit);

// Requête pour les reçus avec LIMIT et OFFSET directement dans la requête
$query = "
    SELECT r.* 
    FROM recus_paiements r 
    $where_clause 
    ORDER BY r.date_creation DESC 
    LIMIT $limit OFFSET $offset
";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$recus = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    /*
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
    */
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
        <form method="get" class="form-inline">
            <div class="form-group mx-2">
                <select name="type" class="form-control">
                    <option value="all" <?= $type === 'all' ? 'selected' : '' ?>>Tous les types</option>
                    <option value="ticket" <?= $type === 'ticket' ? 'selected' : '' ?>>Tickets</option>
                    <option value="bordereau" <?= $type === 'bordereau' ? 'selected' : '' ?>>Bordereaux</option>
                </select>
            </div>
            <div class="form-group mx-2">
                <select name="agent_id" class="form-control">
                    <option value="">Tous les agents</option>
                    <?php foreach ($agents as $agent): ?>
                        <option value="<?= $agent['id_agent'] ?>" <?= $agent_id == $agent['id_agent'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($agent['nom_complet_agent']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group mx-2">
                <input type="date" name="date_debut" class="form-control" value="<?= htmlspecialchars($date_debut) ?>" placeholder="Date début">
            </div>
            <div class="form-group mx-2">
                <input type="date" name="date_fin" class="form-control" value="<?= htmlspecialchars($date_fin) ?>" placeholder="Date fin">
            </div>
            <div class="form-group mx-2">
                <input type="text" name="search" class="form-control" value="<?= htmlspecialchars($search) ?>" placeholder="Rechercher...">
            </div>
            <button type="submit" class="btn btn-primary">Filtrer</button>
        </form>
    </div>

  <div class="table-responsive">
  <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>N° Reçu</th>
                                        <th>Type</th>
                                        <th>N° Document</th>
                                        <th>Agent</th>
                                        <th>Montant Payé</th>
                                        <th>Reste à Payer</th>
                                        <th>Caissier</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($recus)) : ?>
                                        <?php foreach ($recus as $recu) : ?>
                                            <tr>
                                                <td><?= date('d/m/Y H:i', strtotime($recu['date_creation'])) ?></td>
                                                <td><?= htmlspecialchars($recu['numero_recu']) ?></td>
                                                <td><?= ucfirst(htmlspecialchars($recu['type_document'])) ?></td>
                                                <td><?= htmlspecialchars($recu['numero_document']) ?></td>
                                                <td>
                                                    <?= htmlspecialchars($recu['nom_agent']) ?>
                                                    <?php if ($recu['contact_agent']) : ?>
                                                        <br><small><?= htmlspecialchars($recu['contact_agent']) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= number_format($recu['montant_paye'], 0, ',', ' ') ?> FCFA</td>
                                                <td><?= number_format($recu['reste_a_payer'], 0, ',', ' ') ?> FCFA</td>
                                                <td><?= htmlspecialchars($recu['nom_caissier']) ?></td>
                                                <td>
    <?php if ($recu['type_document'] === 'ticket') : ?>
        <a href="recu_paiement_pdf.php?id_ticket=<?= htmlspecialchars($recu['id_document']) ?>&reimprimer=1" 
           class="btn btn-info btn-sm" target="_blank">
            <i class="fas fa-print"></i> Imprimer
        </a>
    <?php else : ?>
        <a href="recu_paiement_pdf.php?id_bordereau=<?= htmlspecialchars($recu['id_document']) ?>&reimprimer=1" 
           class="btn btn-info btn-sm" target="_blank">
            <i class="fas fa-print"></i> Imprimer
        </a>
    <?php endif; ?>

    <!-- Bouton de suppression -->
    <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" 
        data-target="#supprimer_paiement_<?= htmlspecialchars($recu['id_recu']) ?>">
        <i class="fas fa-trash"></i> Supprimer le paiement
    </button>
</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <tr>
                                            <td colspan="9" class="text-center">Aucun reçu trouvé</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>

</div>
</body>

</html>

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
<script>
// Afficher le modal si le ticket existe
<?php if (isset($_SESSION['ticket_error']) && $_SESSION['ticket_error']): ?>
    $(document).ready(function() {
        $('#existingTicketNumber').text('<?= $_SESSION['numero_ticket'] ?>');
        $('#ticketExistModal').modal('show');
    });
    <?php 
    unset($_SESSION['ticket_error']);
    unset($_SESSION['numero_ticket']);
    ?>
<?php endif; ?>

// Validation du formulaire

/*
$(document).ready(function() {
    $('form').on('submit', function(e) {
        var numeroTicket = $('#numero_ticket').val();
        
        // Vérification AJAX du numéro de ticket
        $.ajax({
            url: 'check_ticket.php',
            method: 'POST',
            data: { numero_ticket: numeroTicket },
            success: function(response) {
                if (response.exists) {
                    e.preventDefault();
                    $('#existingTicketNumber').text(numeroTicket);
                    $('#ticketExistModal').modal('show');
                }
            }
        });
    });
});
*/
</script>

<!-- Modal pour ticket existant -->
<div class="modal fade" id="ticketExistModal" tabindex="-1" role="dialog" aria-labelledby="ticketExistModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="ticketExistModalLabel">
                    <i class="fas fa-exclamation-triangle"></i> Ticket déjà existant
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
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

<!-- Modals de suppression -->
<?php foreach ($recus as $recu) : ?>
    <div class="modal fade" id="supprimer_paiement_<?= htmlspecialchars($recu['id_recu']) ?>">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h4 class="modal-title">Confirmer l'annulation</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir annuler ce paiement ?</p>
                    <p><strong>N° Reçu :</strong> <?= htmlspecialchars($recu['numero_recu']) ?></p>
                    <p><strong>Montant du paiement :</strong> <?= number_format($recu['montant_paye'], 0, ',', ' ') ?> FCFA</p>
                    <p>Cette action va :</p>
                    <ul>
                        <li>Supprimer le reçu de paiement</li>
                        <li>Créer une transaction d'annulation</li>
                        <li>Mettre à jour le montant payé du <?= htmlspecialchars($recu['type_document']) ?></li>
                    </ul>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> Cette action est irréversible !
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fas fa-times"></i> Fermer
                    </button>
                    <form action="delete_recus_paiement.php" method="POST" style="display: inline;">
                        <input type="hidden" name="id_recu" value="<?= htmlspecialchars($recu['id_recu']) ?>">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-check"></i> Confirmer l'annulation
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_tickets.php';
require_once '../inc/functions/requete/requete_usines.php';
require_once '../inc/functions/requete/requete_chef_equipes.php';
require_once '../inc/functions/requete/requete_vehicules.php';
require_once '../inc/functions/requete/requete_agents.php';
//require_once '../inc/functions/requete/requetes_selection_boutique.php';
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

// Récupérer les données (functions)
$tickets = getTickets($conn); 
$usines = getMontantUsines($conn);
$chefs_equipes=getChefEquipes($conn);
$vehicules=getVehicules($conn);
$agents=getAgents($conn);

// Récupérer les montants totaux pour chaque usine
$montants_usines = [];
foreach ($usines as $usine) {
    $sql = "SELECT COALESCE(SUM(montant_paie), 0) as total_montant FROM tickets WHERE id_usine = :id_usine";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id_usine' => $usine['id_usine']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $montants_usines[$usine['id_usine']] = $result['total_montant'];
}

// Calculer le montant total
$montant_total = 0;
foreach ($usines as $usine) {
    $montant_total += floatval($montants_usines[$usine['id_usine']]);
}

// Calculer le montant total payé
$sql_paye = "SELECT SUM(montant_paye) as total_paye FROM usines";
$stmt = $conn->prepare($sql_paye);
$stmt->execute();
$result_paye = $stmt->fetch(PDO::FETCH_ASSOC);
$montant_total_paye = $result_paye['total_paye'] ?? 0;

// Calculer le montant total restant
$sql_restant = "SELECT SUM(montant_restant) as total_restant FROM usines";
$stmt = $conn->prepare($sql_restant);
$stmt->execute();
$result_restant = $stmt->fetch(PDO::FETCH_ASSOC);
$montant_total_restant = $result_restant['total_restant'] ?? 0;

$montant_total_restant = $montant_total - $montant_total_paye;



// Vérifiez si des tickets existent avant de procéder
if (!empty($usines)) {
    $usine_pages = array_chunk($usines, $limit); // Divise les tickets en pages
    $usines_list = $usine_pages[$page - 1] ?? []; // Tickets pour la page actuelle
} else {
    $usines_list = []; // Aucun ticket à afficher
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

<style>
.btn-primary.btn-lg {
    font-weight: bold;
    padding: 12px 24px;
    font-size: 1.1rem;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.btn-primary.btn-lg:disabled {
    background-color: #007bff;
    border-color: #007bff;
    opacity: 1;
    cursor: default;
}
.btn-primary.btn-lg i {
    font-size: 1.2rem;
    margin-right: 8px;
}
.block-container {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
    align-items: center;
}
</style>


<div class="row">

    <div class="block-container">
    <button type="button" class="btn btn-primary btn-lg" disabled>
        <i class="fas fa-coins mr-2"></i><?= number_format($montant_total, 0, ',', ' ') ?> FCFA
    </button>

    <button type="button" class="btn btn-success btn-lg" disabled>
        <i class="fas fa-check-circle mr-2"></i><?= number_format($montant_total_paye, 0, ',', ' ') ?> FCFA
    </button>

    <button type="button" class="btn btn-warning btn-lg" disabled>
        <i class="fas fa-clock mr-2"></i><?= number_format($montant_total_restant, 0, ',', ' ') ?> FCFA
    </button>

    <button type="button" class="btn btn-danger btn-lg" data-toggle="modal" data-target="#printPaiementsModal">
        <i class="fas fa-print mr-2"></i>Imprimer la liste des paiements par Usines
    </button>
</div>

<!-- Modal d'impression des paiements -->
<div class="modal fade" id="printPaiementsModal" tabindex="-1" role="dialog" aria-labelledby="printPaiementsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="printPaiementsModalLabel">
                    <i class="fas fa-print mr-2"></i>Impression des paiements par usine
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="usines_paiement_liste.php" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="id_usine">Sélectionner une usine</label>
                        <select name="id_usine" id="id_usine" class="form-control select2" style="width: 100%" required>
                            <option value="">Sélectionner une usine</option>
                            <?php foreach ($usines as $usine): ?>
                                <option value="<?= $usine['id_usine'] ?>"><?= htmlspecialchars($usine['nom_usine']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="date_debut">Date début</label>
                        <input type="date" class="form-control" id="date_debut" name="date_debut" required>
                    </div>
                    <div class="form-group">
                        <label for="date_fin">Date fin</label>
                        <input type="date" class="form-control" id="date_fin" name="date_fin" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-print mr-2"></i>Imprimer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        placeholder: 'Sélectionner une usine',
        allowClear: true
    });

    // Set default dates
    var today = new Date();
    var firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    
    $('#date_debut').val(firstDayOfMonth.toISOString().split('T')[0]);
    $('#date_fin').val(today.toISOString().split('T')[0]);

    // Date validation
    $('#date_fin').change(function() {
        var dateDebut = $('#date_debut').val();
        var dateFin = $(this).val();
        
        if(dateDebut && dateFin && dateDebut > dateFin) {
            alert('La date de fin doit être supérieure à la date de début');
            $(this).val('');
        }
    });

    $('#date_debut').change(function() {
        var dateDebut = $(this).val();
        var dateFin = $('#date_fin').val();
        
        if(dateDebut && dateFin && dateDebut > dateFin) {
            alert('La date de début doit être inférieure à la date de fin');
            $(this).val('');
        }
    });
});
</script>

<h2>Liste des usines</h2>
<div class="table-responsive">
    <table id="example1" class="table table-bordered table-striped">

 <!-- <table style="max-height: 90vh !important; overflow-y: scroll !important" id="example1" class="table table-bordered table-striped">-->
    <thead>
      <tr>
        
        <th>Nom usine</th>
        <th>Total montant</th>
        <th>Montant payé</th>
        <th>Reste à payer</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($usines_list as $usine) : ?>
        <tr>
          <td>
            <a href="details_usine.php?id=<?= $usine['id_usine'] ?>" class="btn btn-light btn-block text-left shadow-sm">
              <i class="fas fa-industry text-primary mr-2"></i>
              <?= $usine['nom_usine'] ?>
            </a>
          </td>
          <td class="text-center">
            <div class="d-flex justify-content-center align-items-center">
              <div class="bg-light rounded-lg p-3 border">
                <div class="text-muted small mb-1">
                  <i class="fas fa-money-bill text-success mr-1"></i>Total
                </div>
                <div class="h5 mb-0 text-success">
                  <?= number_format($montants_usines[$usine['id_usine']], 0, ',', ' ') ?> FCFA
                </div>
              </div>
            </div>
          </td>
          <td class="text-center">
            <div class="d-flex justify-content-center align-items-center">
              <div class="bg-light rounded-lg p-3 border">
                <div class="text-muted small mb-1">
                  <i class="fas fa-money-bill text-success mr-1"></i>Montant payé
                </div>  
                <div class="h5 mb-0 text-success">
                  <?= number_format($usine['montant_paye'], 0, ',', ' ') ?> FCFA
                </div>
              </div>
            </div>
          </td>
          <td class="text-center">
            <div class="d-flex justify-content-center align-items-center">
              <div class="bg-light rounded-lg p-3 border">
                <div class="text-muted small mb-1">
                  <i class="fas fa-money-bill text-success mr-1"></i>Reste à payer
                </div>
                <div class="h5 mb-0 text-success">
                  <?= number_format($usine['montant_restant'], 0, ',', ' ') ?> FCFA
                </div>
              </div>
            </div>
          </td>
          <td class="actions">
            <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#paiementModal<?= $usine['id_usine'] ?>">
              <i class="fas fa-money-bill-wave"></i> Effectuer un paiement
            </button>
          </td>
        </tr>

        <!-- Modal Paiement pour chaque usine -->
        <div class="modal fade" id="paiementModal<?= $usine['id_usine'] ?>" tabindex="-1" role="dialog" aria-labelledby="paiementModalLabel<?= $usine['id_usine'] ?>" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="paiementModalLabel<?= $usine['id_usine'] ?>">
                  Effectuer un paiement - <?= htmlspecialchars($usine['nom_usine']) ?>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <form action="traitement_paiement.php" method="POST">
                <div class="modal-body">
                  <input type="hidden" name="id_usine" value="<?= $usine['id_usine'] ?>">
                  
                  <div class="form-group">
                    <label for="montant<?= $usine['id_usine'] ?>">Montant du paiement (FCFA)</label>
                    <input type="number" class="form-control" id="montant<?= $usine['id_usine'] ?>" name="montant" required>
                  </div>
                  
                  <div class="form-group">
                    <label for="date_paiement<?= $usine['id_usine'] ?>">Date du paiement</label>
                    <input type="date" class="form-control" id="date_paiement<?= $usine['id_usine'] ?>" name="date_paiement" required>
                  </div>

                  <div class="form-group">
                    <label for="mode_paiement<?= $usine['id_usine'] ?>">Mode de paiement</label>
                    <select class="form-control" id="mode_paiement<?= $usine['id_usine'] ?>" name="mode_paiement" required>
                      <option value="">Sélectionner un mode de paiement</option>
                      <option value="Espèces">Espèces</option>
                      <option value="Chèque">Chèque</option>
                      <option value="Virement">Virement bancaire</option>
                      <option value="Mobile Money">Mobile Money</option>
                    </select>
                  </div>

                  <div class="form-group">
                    <label for="reference<?= $usine['id_usine'] ?>">Référence du paiement</label>
                    <input type="text" class="form-control" id="reference<?= $usine['id_usine'] ?>" name="reference" placeholder="N° chèque, N° transaction...">
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                  <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Enregistrer le paiement
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </tbody>
  </table>

</div>

  <div class="pagination-container bg-secondary d-flex justify-content-center w-100 text-white p-3">
    <?php if($page > 1 ): ?>
        <a href="?page=<?= $page - 1 ?>" class="btn btn-primary"><</a>
    <?php endif; ?>

    <span><?= $page . '/' . count($usine_pages) ?></span>

    <?php if($page < count($usine_pages)): ?>
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
</script>

</body>

</html>
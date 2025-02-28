<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_tickets.php';
require_once '../inc/functions/requete/requete_usines.php';
require_once '../inc/functions/requete/requete_chef_equipes.php';
require_once '../inc/functions/requete/requete_vehicules.php';
require_once '../inc/functions/requete/requete_agents.php';
//require_once '../inc/functions/requete/requetes_selection_boutique.php';
include('header_operateurs.php');

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
$usines = getUsines($conn);
$chefs_equipes=getChefEquipes($conn);
$vehicules=getVehicules($conn);
$agents=getAgents($conn);


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


<div class="row">

    <div class="block-container">
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add-ticket">
      <i class="fa fa-edit"></i>Enregistrer une usine
    </button>

    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#add-point">
      <i class="fa fa-print"></i> Imprimer la liste des usines
    </button>

    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#search-commande">
      <i class="fa fa-search"></i> Recherche une usine
    </button>

    <button type="button" class="btn btn-dark" onclick="window.location.href='export_commandes.php'">
              <i class="fa fa-print"></i> Exporter la liste des usines
             </button>
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



<h2>Liste des usines</h2>
<div class="table-responsive">
    <table id="example1" class="table table-bordered table-striped">

 <!-- <table style="max-height: 90vh !important; overflow-y: scroll !important" id="example1" class="table table-bordered table-striped">-->
    <thead>
      <tr>
        
        <th>Nom usine</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($usines_list as $usine) : ?>
        <tr>
          
          <td><?= $usine['nom_usine'] ?></td>
    
          
  
          <td class="actions">
            <a class="edit" data-toggle="modal" data-target="#editModalTicket<?= $ticket['id_ticket'] ?>">
            <i class="fas fa-pen fa-xs" style="font-size:24px;color:blue"></i>
            </a>
            <a href="delete_commandes.php?id=<?= $ticket['id_ticket'] ?>" class="trash"><i class="fas fa-trash fa-xs" style="font-size:24px;color:red"></i></a>
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



  <div class="modal fade" id="add-ticket">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Enregistrer un agent</h4>
        </div>
        <div class="modal-body">
        
          <form class="forms-sample" method="post" action="traitement_agents.php">
            <div class="card-body">
              <div class="form-group">
                <label for="exampleInputEmail1">Nom</label>
                <input type="text" class="form-control" id="exampleInputEmail1" placeholder="Nom" name="nom">
              </div>

              <div class="form-group">
                <label for="exampleInputEmail1">Prenoms</label>
                <input type="text" class="form-control" id="exampleInputEmail1" placeholder="Prenoms" name="prenoms">
              </div>

              <div class="form-group">
                <label for="exampleInputEmail1">Contact</label>
                <input type="text" class="form-control" id="exampleInputEmail1" placeholder="Contact" name="contact">
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

<!-- jQuery -->
<script src="../../plugins/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="../../plugins/jquery-ui/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<!-- <script>
  $.widget.bridge('uibutton', $.ui.button)
</script>-->
<!-- Bootstrap 4 -->
<script src="../../plugins/sweetalert2/sweetalert2.min.js"></script>

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
<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_imprevus.php';
include('header.php');

$rows = $getLivreurs->fetchAll(PDO::FETCH_ASSOC);

$getSommeImprevuQuery = "SELECT SUM(montant) AS somme_totale_imprevus
FROM imprevu";
$getSommeImprevuQueryStmt = $conn->query($getSommeImprevuQuery);
$somme_imprevu = $getSommeImprevuQueryStmt->fetch(PDO::FETCH_ASSOC);


//$livreurs = $getStatut->fetchAll(PDO::FETCH_ASSOC);

////$stmt = $conn->prepare("SELECT * FROM users");
//$stmt->execute();
//$users = $stmt->fetchAll();
//foreach($users as $user)

$limit = $_GET['limit'] ?? 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$imprevu_pages = array_chunk($imprevus, $limit );
//$commandes_list = $commande_pages[$_GET['page'] ?? ] ;
$imprevus_list = $imprevu_pages[$page - 1] ?? [];

//var_dump($commandes_list);


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
    background-color: #007bff; /* Bleu */
    border: 1px solid #007bff;
    border-radius: 4px; /* Ajout de la bordure arrondie */
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
    border-radius: 4px; /* Ajout de la bordure arrondie */
}

.submit-button {
    padding: 6px 10px;
    background-color: #007bff;
    color: #fff;
    border: none;
    border-radius: 4px; /* Ajout de la bordure arrondie */
    cursor: pointer;
}
</style>

  <div class="row">
          <div class="col-md-12 col-sm-6 col-12">
            <div class="info-box bg-dark">
            <span class="info-box-icon" style="font-size: 48px;">
                <i class="fas fa-hand-holding-usd">
                </i></span>
              
              <div class="info-box-content">
                <span style="text-align: center; font-size: 20px;" class="info-box-text">Total imprevu</span>

                <div class="progress">
                  <div class="progress-bar" style="width: 100%"></div>
                </div>
                <span class="progress-description">
                <h1 style="text-align: center; font-size: 70px;"><strong><?php echo $somme_imprevu['somme_totale_imprevus']; ?></strong></h1>
                </span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
  </div>
<div class="row">
  <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add-imprevu">
    Enregistrer un imprevu
  </button>
    <!-- Utilisation du formulaire Bootstrap avec ms-auto pour aligner à droite -->
    <form action="page_recherche.php" method="GET" class="d-flex ml-auto">
    <input class="form-control me-2" type="search" name="recherche" style="width: 400px;" placeholder="Recherche..." aria-label="Search">
    <button class="btn btn-outline-primary" type="submit">Rechercher</button>
</form>
  <table style="max-height: 90vh !important; overflow-y: scroll !important" id="example1" class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>Montant</th>
        <th>Motifs</th>
        <th>Générer par</th>
        <th>Date contraction</th>
        <th>Actions</th>
      <!--  <th>Effectuer un paiement</th>-->
      </tr>
    </thead>
    <tbody>
      <?php foreach ($imprevus_list as $imprevu) : ?>
        <tr>
          
          <td><?= $imprevu['montant'] ?></td>
          <td><?= $imprevu['motif'] ?></td>
          <td><?= $imprevu['nom_livreur'] ?></td>
          <td><?= $imprevu['date_contraction'] ?></td>

          <td class="actions">
            <a href="imprevus_update.php?id=<?= $imprevu['id'] ?>" class="edit"><i class="fas fa-pen fa-xs" style="font-size:24px;color:blue"></i></a>
            <a href="delete_imprevus.php?id=<?= $imprevu['id'] ?>" class="trash"><i class="fas fa-trash fa-xs" style="font-size:24px;color:red"></i></a>
          </td>


         <td>
         <a href="versement_detaille.php?id=<?= $imprevu['id'] ?>">
          <button type="button" class="btn btn-warning">
              Détails
          </button>
         </a>          
         </td>
      <?php endforeach; ?>
    </tbody>
  </table>
  <div class="pagination-container bg-secondary d-flex justify-content-center w-100 text-white p-3">
    <?php if($page > 1 ): ?>
        <a href="?page=<?= $page - 1 ?>" class="btn btn-primary"><</a>
    <?php endif; ?>

    <span><?= $page . '/' . count($imprevu_pages) ?></span>

    <?php if($page < count($imprevu_pages)): ?>
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



  <div class="modal fade" id="add-imprevu">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Enregistrer un imprévu</h4>
        </div>
        <div class="modal-body">
          <form class="forms-sample" method="post" action="save_imprevu.php">
            <div class="card-body">
              <div class="form-group">
                <label for="exampleInputEmail1">Montant</label>
                <input type="text" class="form-control" id="exampleInputEmail1" placeholder="Montant" name="montant">
              </div>
              <div class="form-group">
                <label for="exampleInputEmail1">Motif</label>
                <input type="text" class="form-control" id="exampleInputEmail1" placeholder="Motifs" name="motif">
              </div>
              <div class="form-group">
                    <label>Générer par</label>
                    <select name="livreur_id" class="form-control">
                      <?php
                      foreach ($rows as $row) {
                        echo '<option value="' . $row['id'] . '">' . $row['livreur_name'] . '</option>';
                      }
                      ?></select>

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

</div>

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
</body>

</html>
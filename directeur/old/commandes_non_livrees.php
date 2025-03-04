<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_commandes.php';
include('header.php');

$rows = $getLivreurs->fetchAll(PDO::FETCH_ASSOC);

$livreurs = $getStatut->fetchAll(PDO::FETCH_ASSOC);

////$stmt = $conn->prepare("SELECT * FROM users");
//$stmt->execute();
//$users = $stmt->fetchAll();
//foreach($users as $user)

?>
<!-- Main row -->
<div class="row">
  <h1><span class="badge bg-secondary">Liste des colis non livrés</span></h1>
  <table id="example1" class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>Communes</th>
        <th>Coût Global</th>
        <th>Livraison</th>
        <th>Côut réel</th>
        <th>Client</th>
        <th>Livreur</th>
        <th>Statut</th>
        <th>Date de la commande</th>
        <th>Changer Statut livraison</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($commandes_non_livrees as $commandes_non_livree) : ?>
      <tr>
        <td><?= $commandes_non_livree['commande_communes'] ?></td>
        <td><?= $commandes_non_livree['commande_cout_global'] ?></td>
        <td><?= $commandes_non_livree['commande_cout_livraison'] ?></td>
        <td><?= $commandes_non_livree['commande_cout_reel'] ?></td>
        <td><?= $commandes_non_livree['nom_boutique'] ?></td>
        <?php if ($commandes_non_livree['fullname']) : ?>
        <td><?= $commandes_non_livree["fullname"] ?></td>
        <?php else : ?>
        <td class="badge badge-warning badge-lg">Pas de livreur attribué</td>
        <?php endif; ?>


        <td>
          <?php if ($commandes_non_livree['commande_statut'] !== null) : ?>
          <?php if ($commandes_non_livree['commande_statut'] == 'Non Livré') : ?>
          <span class="badge badge-danger badge-lg"><?= $commandes_non_livree['commande_statut'] ?></span>
          <?php else : ?>
          <span class="badge badge-success badge-lg"><?= $commandes_non_livree['commande_statut'] ?></span>
          <?php endif; ?>
          <?php else : ?>
          <span class="badge badge-success badge-lg">Pas de point</span>
          <?php endif; ?>
        </td>
        <td><?= $commandes_non_livree['date_commande'] ?></td>
        <td>
          <?php if ($commandes_non_livree['commande_statut'] == 'Livré') : ?>
          <button class="btn btn-info" disabled>Changer le statut</button>
          <?php else : ?>
          <button class="btn btn-warning" data-toggle="modal"
            data-target="#update_statut-<?= $commandes_non_livree['commande_id'] ?>">Changer le statut</button>
          <?php endif; ?>
        </td>
      </tr>
      <div class="modal" id="update-<?= $commandes_non_livree['commande_id'] ?>">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-body">
              <form action="traitement_commande_livreurs_update.php" method="post">
                <input type="hidden" name="commande_id" value="<?= $commandes_non_livree['commande_id'] ?>">
                <div class="form-group">
                  <label>Livreur</label>
                  <select name="livreur_id" class="form-control">
                    <?php
                      foreach ($rows as $row) {
                        echo '<option value="' . $row['id'] . '">' . $row['livreur_name'] . '</option>';
                      }
                      ?></select>

                </div>
                <button type="submit" class="btn btn-primary mr-2" name="saveCommande">Attribuer</button>
                <button class="btn btn-light">Annuler</button>
              </form>
            </div>
          </div>
        </div>
      </div>



      <div class="modal" id="update_statut-<?= $commandes_non_livree['commande_id'] ?>">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-body">
              <form action="traitement_commande_statut_update.php" method="post">
                <input type="hidden" name="commande_id" value="<?= $commandes_non_livree['commande_id'] ?>">
                <div class="form-group">
                  <label>Changer le statut de la commande</label>
                  <select name="statut" class="form-control">
                    <?php
                      foreach ($livreurs as $livreur) {
                        echo '<option value="' . $livreur['statut'] . '">' . $livreur['statut'] . '</option>';
                      }
                      ?></select>

                </div>
                <button type="submit" class="btn btn-primary mr-2" name="saveCommande">Changer le statut</button>
                <button class="btn btn-light">Annuler</button>
              </form>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </tbody>
  </table>
  <div class="modal fade" id="add-commande">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Enregistrer une commande</h4>
        </div>
        <div class="modal-body">
          <form class="forms-sample" method="post" action="save_commande.php">
            <div class="card-body">
              <div class="form-group">
                <label for="exampleInputEmail1">Communes</label>
                <input type="text" class="form-control" id="exampleInputEmail1" placeholder="Commune destination"
                  name="communes">
              </div>
              <div class="form-group">
                <label for="exampleInputPassword1">Côut Global</label>
                <input type="text" class="form-control" id="exampleInputPassword1" placeholder="Coût global Colis"
                  name="cout_global">
              </div>
              <div class="form-group">
                <label>Côut Livraison</label>
                <?php
                echo  '<select id="select" name="livraison" class="form-control">';
                while ($coutLivraison = $cout_livraison->fetch(PDO::FETCH_ASSOC)) {
                  echo '<option value="' . $coutLivraison['cout_livraison'] . '">' . $coutLivraison['cout_livraison'] . '</option>';
                }
                echo '</select>'
                ?>
              </div>
              <div class="form-group">
                <label>Clients</label>
                <?php
                echo  '<select id="select" name="client_id" class="form-control">';
                while ($row = $getClientsStmt->fetch(PDO::FETCH_ASSOC)) {
                  echo '<option value="' . $row['id'] . '">' . $row['nom_boutique'] . '</option>';
                }
                echo '</select>'
                ?>
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
var Toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 3000
});

Toast.fire({
  icon: 'success',
  title: 'Action effectuée avec succès.'
})
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
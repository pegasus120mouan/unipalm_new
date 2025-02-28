<?php
include('header_clients.php');
require_once '../inc/functions/requete/clients/requete_commandes_clients.php';
//$id_user = $_SESSION['user_id'];

//$rows = $getLivreurs->fetchAll(PDO::FETCH_ASSOC);

//$livreurs = $getStatut->fetchAll(PDO::FETCH_ASSOC);

////$stmt = $conn->prepare("SELECT * FROM users");
//$stmt->execute();
//$users = $stmt->fetchAll();
//foreach($users as $user)


?>

<!-- Main row -->
<div class="row">
  <h1><span class="badge bg-dark">Liste des colis non livrés</span></h1>
  <table id="example1" class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>Communes</th>
        <th>Coût Global</th>
        <th>Livraison</th>
        <th>Côut réel</th>
        <th>Partenaires</th>
        <th>Livreur</th>
        <th>Statut</th>
        <th>Date de la commande</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($commandes_non_livrees as $commandes_non_livree) : ?>
        <tr>
          <td><?= $commandes_non_livree['communes'] ?></td>
          <td><?= $commandes_non_livree['cout_global'] ?></td>
          <td><?= $commandes_non_livree['cout_livraison'] ?></td>
          <td><?= $commandes_non_livree['cout_reel'] ?></td>
          <td><?= $commandes_non_livree['nom_boutique'] ?></td>
          <td><?= $commandes_non_livree['fullname'] ?></td>

          <td>
            <?php if ($commandes_non_livree['statut'] !== null) : ?>
              <?php if ($commandes_non_livree['statut'] == 'Non Livré') : ?>
                <span class="badge badge-danger badge-lg"><?= $commandes_non_livree['statut'] ?></span>
              <?php else : ?>
                <span class="badge badge-success badge-lg"><?= $commandes_non_livree['statut'] ?></span>
              <?php endif; ?>
            <?php else : ?>
              <span class="badge badge-success badge-lg">Pas de point</span>
            <?php endif; ?>
          </td>
          <td><?= $commandes_non_livree['date_commande'] ?></td>
        </tr>

      <?php endforeach; ?>
    </tbody>
  </table>
</div>
</div>
</div>
<aside class="control-sidebar control-sidebar-dark">
</aside>
</div>


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
<!------- Delete Pop--->
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<!--<script src="dist/js/pages/dashboard.js"></script>-->
</body>

</html>
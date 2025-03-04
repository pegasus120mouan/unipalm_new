<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_cout_livraison.php';
include('header.php');

$aujourdhui = date("d-m-Y");

?>

<h2>Impression des côuts de livraison</h2>
<div class="col-lg-3 col-6">
  <!-- small box -->



 <!-- liste_commune
  $liste_commune = $conn->query("SELECT commune_id,nom_commune from communes");-->

  <form action="traitement_coutlivraison_print.php" method="POST">
    <div class="form-group row">
      <label for="client" class="col-4 col-form-label">Select</label>
      <div class="form-group">
        <select name="communes" class="form-control">
          <?php
          while ($selection = $liste_commune->fetch()) {
            echo '<option value="' . $selection['commune_id'] . '">' . $selection['nom_commune'] . '</option>';
          }
          ?></select>

      </div>
    </div>
    <div class="form-group row">
      <div class="offset-4 col-8">
        <button type="submit" class="btn btn-warning btn-rounded btn-fw">Imprimer</button>
      </div>

    </div>
  </form>



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

</body>

</html>
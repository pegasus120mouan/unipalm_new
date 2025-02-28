<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requetes_selection_boutique.php';
include('header.php');

$aujourdhui = date("d-m-Y");

?>

<h2>Impression des points <?php echo $aujourdhui; ?></h2>
<div class="col-lg-3 col-6">
  <!-- small box -->






  <form action="traitement_commandes_print.php" method="POST">
    <div class="form-group row">
      <label for="client" class="col-4 col-form-label">Select</label>
      <div class="form-group">
        <select name="client" class="form-control">
          <?php
          while ($selection = $stmt_select_boutique->fetch()) {
            echo '<option value="' . $selection['nom_boutique'] . '">' . $selection['nom_boutique'] . '</option>';
          }
          ?></select>

      </div>
    </div>
    <div class="form-group row">
      <label for="date" class="col-4 col-form-label">Date</label>
      <div class="col-8">
        <div class="input-group">
          <div class="input-group-prepend">
            <div class="input-group-text">
              <i class="fa fa-calendar"></i>
            </div>
          </div>
          <input id="date" name="date" type="date" class="form-control">
        </div>
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
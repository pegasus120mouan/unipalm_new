<?php
require_once '../inc/functions/connexion.php';
//require_once '../inc/functions/requete/requete_commandes.php'; 
include('header_livreurs.php');

$id_user=$_SESSION['user_id'];;
//$stmt->execute();
//$point_livreurs = $stmt->fetchAll();

$sql_depense = "SELECT points_livreurs.id AS point_livreur_id,depense,
date_commande, 
utilisateurs.id AS utilisateur_id,
CONCAT(utilisateurs.nom,' ', utilisateurs.prenoms) as livreur_nom
FROM points_livreurs JOIN  utilisateurs 
ON points_livreurs.utilisateur_id=utilisateurs.id
WHERE utilisateurs.id=:id_user ORDER BY date_commande DESC";

$req_livreur_depense = $conn->prepare($sql_depense);
$req_livreur_depense->bindParam(':id_user', $id_user, PDO::PARAM_INT);
$req_livreur_depense->execute();
$point_livreurs = $req_livreur_depense->fetchAll();



//$point_livreurs = $stmt->fetchAll();

$sql_livreur="SELECT id, CONCAT(nom, ' ', prenoms) AS nom_prenoms 
FROM livreurs where livreurs.id=:id_user";

$livreurs_selection= $conn->prepare($sql_livreur);
$livreurs_selection->bindParam(':id_user', $id_user, PDO::PARAM_INT);
$livreurs_selection->execute();



//$livreurs_selection = $conn->query("SELECT id, CONCAT(nom, ' ', prenoms) AS nom_prenoms FROM livreurs");




?>
<!-- Main row -->
<div class="row">
  <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add-point">
    Enregistrer un point
  </button>
  <!--  <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modal-success">
                  Launch Success Modal
                </button>-->
</div>


<table id="example1" class="table table-bordered table-striped">
  <thead>
    <tr>
      <th>Nom livreur</th>
      <th>Depenses</th>
      <th>Date</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($point_livreurs as $point_livreur) : ?>
    <tr>
      <td><?= $point_livreur['livreur_nom'] ?></td>
      <td><?= $point_livreur['depense'] ?></td>
      <td><?= $point_livreur['date_commande'] ?></td>
      <td class="actions">
        <a href="point_livraison_update.php?id=<?= $point_livreur['point_livreur_id'] ?>" class="edit"><i
            class="fas fa-pen fa-xs" style="font-size:24px;color:blue"></i></a>
        <a href="point_livraison_delete.php?id=<?= $point_livreur['point_livreur_id'] ?>" class="trash"><i
            class="fas fa-trash fa-xs" style="font-size:24px;color:red"></i></a>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<div class="modal fade" id="add-point">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Enregistrer une commande</h4>
      </div>
      <div class="modal-body">
        <form class="forms-sample" method="post" action="save_pointlivraison.php">
          <div class="form-group">
            <label>Prenom Livreur</label>
            <?php
            echo  '<select id="select" name="livreur_id" class="form-control">';
            while ($rowLivreur = $livreurs_selection->fetch(PDO::FETCH_ASSOC)) {
              echo '<option value="' . $rowLivreur['id'] . '">' . $rowLivreur['nom_prenoms'] . '</option>';
            }
            echo '</select>'
            ?>
          </div>

          <div class="form-group">
            <label for="exampleInputPassword1">Dépenses du jour</label>
            <input type="text" class="form-control" id="exampleInputPassword1" placeholder="Dépenses du jour"
              name="depenses">
          </div>


          <button type="submit" class="btn btn-primary mr-2" name="savePLivraison">Enregister</button>
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

<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<!--<script src="dist/js/pages/dashboard.js"></script>-->
</body>

</html>
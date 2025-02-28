<?php
require_once '../inc/functions/connexion.php';
include('header.php');

$recherche=$_GET['recherche'];




$stmt = $conn->prepare("SELECT utilisateurs.id as utilisateur_id,
       utilisateurs.nom as utilisateur_nom,
       utilisateurs.prenoms as utilisateur_prenoms,
       utilisateurs.contact as utilisateur_contact,
       utilisateurs.login as utilisateur_login,
       utilisateurs.avatar as utilisateur_avatar,
       utilisateurs.role as utilisateur_role,
       boutiques.nom as boutique_nom
FROM utilisateurs
JOIN boutiques ON utilisateurs.boutique_id = boutiques.id
WHERE utilisateurs.nom LIKE :clients");

$rechercheAvecPourcentage = '%' . $recherche . '%';
$stmt->bindParam(':clients', $rechercheAvecPourcentage, PDO::PARAM_STR);
$stmt->execute();
$clients = $stmt->fetchAll();


























 
?>


<!-- Main row -->
<div class="row">
  <table id="example1" class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>Photo</th>
        <th>Nom</th>
        <th>Prenom</th>
        <th>Contact</th>
        <th>Login utilisateur</th>
        <th>Nom Entreprise</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($clients as $client): ?>
      <tr>
        <td>
          <a href="client_profile.php?id=<?=$client['utilisateur_id']?>" class="edit"><img
              src="../dossiers_images/<?php echo $client['utilisateur_avatar']; ?>" alt="Logo" width="50"
              height="50"> </a>
        </td>
        <td><?=$client['utilisateur_nom']?></td>
        <td><?=$client['utilisateur_prenoms']?></td>
        <td><?=$client['utilisateur_contact']?></td>
        <td><?=$client['utilisateur_login']?></td>

        <td><?=$client['boutique_nom']?></td>


        <td class="actions">
          <a href="client_update.php?id=<?=$client['utilisateur_id']?>" class="edit"><i class="fas fa-pen fa-xs"
              style="font-size:24px;color:blue"></i></a>
          <a href="client_delete.php?id=<?=$client['utilisateur_id']?>" class="trash"><i class="fas fa-trash fa-xs"
              style="font-size:24px;color:red"></i></a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

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

if(isset($_SESSION['popup']) && $_SESSION['popup'] ==  true) {
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

if(isset($_SESSION['delete_pop']) && $_SESSION['delete_pop'] ==  true) {
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
  title: 'Commande non inserée.'
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
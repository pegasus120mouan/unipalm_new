<?php

require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_admin.php'; 
include('header.php');

//$stmt = $conn->prepare("SELECT * FROM utilisateurs");
//$stmt->execute();

$admins = $liste_admins->fetchAll();
//foreach($users as $user)
?>




        <!-- Main row -->
        <div class="row">

        
                <button type="button" class="btn btn-primary" data-toggle="modal" 
                data-target="#add-client" style="margin-bottom: 15px; margin-left: 15px;">
                  Enregistrer un administrateur
                </button>   

                <h1>Liste des administrateurs</h1>

                 <form action="page_recherche_admin.php" method="GET" class="d-flex ml-auto">
      <input class="form-control me-2" type="search" name="recherche" style="width: 400px;" placeholder="Recherche..." aria-label="Search">
      <button class="btn btn-outline-primary" type="submit" style="margin-bottom: 15px; margin-left: 15px;">Rechercher</button>
 </form>

                <table id="example1" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                    <th>Nom</th>
                    <th>Prenoms</th>
                    <th>Contact</th>
                    <th>Login</th>
                    <th>Avatar</th>
                    <th>Actions</th>
                  </tr>
                  </thead>
                  <tbody>
                  <?php foreach ($admins as $admin): ?>
                  <tr>
                
                <td><?=$admin['nom']?></td>
                <td><?=$admin['prenoms']?></td>
                <td><?=$admin['contact']?></td>
                <td><?=$admin['login']?></td>
                <td>
                <a href="admin_users_profile.php?id=<?=$admin['id']?>" class="edit"><img src="../dossiers_images/<?php echo $admin['avatar']; ?>" alt="Logo" width="50" height="50"> </a>
                 </td>
                    <td class="actions">
                        <a href="admin_update.php?id=<?=$admin['id']?>" class="edit"><i class="fas fa-pen fa-xs" style="font-size:24px;color:blue"></i></a>
                        <a href="admin_users_delete.php?id=<?=$admin['id']?>" class="trash"><i class="fas fa-trash fa-xs" style="font-size:24px;color:red"></i></a>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                  </tbody>
                </table>
         

                

          <div class="modal fade" id="add-client">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title">Enregistrer un livreur</h4>
            </div>
            <div class="modal-body">
            <form class="forms-sample" method="post" action="save_livreur.php">
                <div class="card-body">
                  <div class="form-group">
                    <label for="exampleInputEmail1">Nom</label>
                    <input type="text" class="form-control" id="exampleInputEmail1" placeholder="Nom" name="nom">
                  </div>
                  <div class="form-group">
                    <label for="exampleInputEmail3">Prenom</label>
                    <input type="text" class="form-control" id="exampleInputEmail3"
                     placeholder="Prenom" name="prenoms">
                      </div>
                  <div class="form-group">
                                                <label for="exampleInputCity1">Contact</label>
                                                <input type="text" class="form-control" id="exampleInputCity1"
                                                    placeholder="Contact" name="contact">
                                            </div>
                                            <div class="form-group">
                                                <label for="exampleInputCity1">Login</label>
                                                <input type="text" class="form-control" id="exampleInputCity1"
                                                    placeholder="Login" name="login">
                 </div>


                
                    <div class="form-group">
                                                <label for="exampleInputPassword4">Password</label>
                                                <input type="password" class="form-control" id="exampleInputPassword4"
                                                    placeholder="Password" name="password">
                  </div>
                                            <div class="form-group">
                                                <label for="exampleInputCity1">Confirmation Password</label>
                                                <input type="password" class="form-control" id="exampleInputCity1"
                                                    placeholder="Confirmation Password" name="retype_password">
                                            </div>
                                            
                                            <button type="submit" class="btn btn-primary mr-2" name="signup">Enregister</button>
                                            <button class="btn btn-light">Annuler</button>

<!-- Gestion Partenaires--->
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
        title: 'Utilisateur crée.'
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
        title: 'Utilisateur non crée.'
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

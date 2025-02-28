<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_utilisateur.php'; 
include('header.php');

$stmt = $conn->prepare("SELECT * FROM utilisateurs");
$stmt->execute();
$utilisateurs = $stmt->fetchAll();
//foreach($users as $user)
?>




        <!-- Main row -->
        <div class="row">
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add-client">
                  Enregistrer un utilisateur
                </button>   

                <table id="example1" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                    <th>id</th>
                    <th>Nom</th>
                    <th>Prenoms</th>
                    <th>Email</th>
                    <th>Contact</th>
                    <th>Username</th>
                    <th>Avatar</th>
                    <th>Actions</th>
                  </tr>
                  </thead>
                  <tbody>
                  <?php foreach ($utilisateurs as $utilisateur): ?>
                  <tr>
                  <td><?=$utilisateur['utilisateur_id']?></td>
                
                <td><?=$utilisateur['nom']?></td>
                <td><?=$utilisateur['prenom']?></td>
                <td><?=$utilisateur['email']?></td>
                <td><?=$utilisateur['contact']?></td>
                <td><?=$utilisateur['login']?></td>
                <td>
                <a href="client_profile.php?id=<?=$utilisateur['id']?>" class="edit"><img src="../pages/dossier_avatars/<?php echo $user['avatar']; ?>" alt="Logo" width="50" height="50"> </a>
                 </td>
                    <td class="actions">
                        <a href="utilisateurs_update.php?id=<?=$utilisateur['utilisateur_id']?>" class="edit"><i class="fas fa-pen fa-xs" style="font-size:24px;color:blue"></i></a>
                        <a href="utilisateurs_delete.php?id=<?=$utilisateur['utilisateur_id']?>" class="trash"><i class="fas fa-trash fa-xs" style="font-size:24px;color:red"></i></a>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                  </tbody>
                </table>
         

                

          <div class="modal fade" id="add-client">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title">Enregistrer un utilisateur</h4>
            </div>
            <div class="modal-body">
            <form class="forms-sample" method="post" action="save_utilisateurs.php">
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
                                                <label for="exampleInputPassword4">Email</label>
                                                <input type="email" class="form-control" id="exampleInputPassword4"
                                                    placeholder="Email" name="email">
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

<!-- Gestion Partenaires--->
<div class="form-group">
                                                <label for="exampleInputCity1">Nom Boutique</label>
                                                <input type="text" class="form-control" id="exampleInputCity1"
                                                    placeholder="Nom Boutique" name="nom_boutique">
                                            </div>


                                            <div class="form-group">
                                                <label for="exampleInputCity1">Contact Boutique</label>
                                                <input type="text" class="form-control" id="exampleInputCity1"
                                                    placeholder="Contact boutique" name="contact_boutique">
                                            </div>


                                            <label for="exampleInputCity1">Localisation Boutique</label>
                                                <input type="text" class="form-control" id="exampleInputCity1"
                                                    placeholder="Localisation Boutique" name="localisation_boutique">
                                            </div>


                                            <div class="form-group">
                        <label>Type de Partenaire</label>
                         <select id="select" name="type_partenaire" class="form-control">
                         <option value="Entreprise">Entreprise</option>
                         <option value="Particulier">Particulier</option>
                        </select>
                        
                    </div>

                                            <button type="submit" class="btn btn-primary mr-2" name="signup">Enregister</button>
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

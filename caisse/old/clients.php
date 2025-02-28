<?php
require_once '../inc/functions/connexion.php';
include('header.php');

$stmt = $conn->prepare("SELECT utilisateurs.id as utilisateur_id,
       utilisateurs.nom as utilisateur_nom,
       utilisateurs.prenoms as utilisateur_prenoms,
       utilisateurs.contact as utilisateur_contact,
       utilisateurs.login as utilisateur_login,
       utilisateurs.avatar as utilisateur_avatar,
       utilisateurs.role as utilisateur_role,
       utilisateurs.statut_compte as statut_compte,
       boutiques.nom as boutique_nom
FROM utilisateurs
JOIN boutiques ON utilisateurs.boutique_id = boutiques.id
WHERE utilisateurs.role = 'clients'");
$stmt->execute();
$clients = $stmt->fetchAll();
//foreach($users as $user)
?>



<!-- Main row -->
<div class="row">
  <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add-client" style="margin-bottom: 15px;">
    Enregistrer un client
  </button>

 <form action="page_recherche_client.php" method="GET" class="d-flex ml-auto">
      <input class="form-control me-2" type="search" name="recherche" style="width: 400px;" placeholder="Recherche..." aria-label="Search">
    <button class="btn btn-outline-primary" type="submit" style="margin-bottom: 15px; margin-left: 15px;">Rechercher</button>
 </form>



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
        <th>Statut compte </th>
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
          <td>
    <?php if ($client['statut_compte'] == 0): ?>
        <button class="btn btn-dark btn-block" disabled>
            <?= $client['boutique_nom'] ?>
        </button>
    <?php else: ?>
        <a class="btn btn-dark btn-block" href="commandes_clients.php?id=<?= $client['utilisateur_id'] ?>">
            <?= $client['boutique_nom'] ?>
        </a>
    <?php endif; ?>
</td>


        <td class="actions">
          <a href="client_update.php?id=<?=$client['utilisateur_id']?>" class="edit"><i class="fas fa-pen fa-xs"
              style="font-size:24px;color:blue"></i></a>
          <a href="client_delete.php?id=<?=$client['utilisateur_id']?>" class="trash"><i class="fas fa-trash fa-xs"
              style="font-size:24px;color:red"></i></a>
        </td>
           <td>
                        <form method="post" action="liste_livreurs.php">
                            <input type="hidden" name="user_id" value="<?=$client['id']?>">
                            <input type="hidden" name="statut_compte" value="<?=($client['statut_compte'] == 1) ? 0 : 1 ?>">
                            <input type="checkbox" name="statut_compte" data-toggle="toggle" data-on="Actif" data-off="Inactif" data-onstyle="success" data-offstyle="danger" <?=($client['statut_compte'] == 1) ? 'checked' : ''?> onchange="submitForm(this)">
                        </form>
                    </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <div class="modal fade" id="add-client">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Enregistrer un client</h4>
        </div>
        <div class="modal-body">
          <form class="forms-sample" method="post" action="save_client.php">
            <div class="card-body">
              <div class="form-group">
                <label for="exampleInputEmail1">Nom boutique </label>
                <input type="text" class="form-control" id="exampleInputEmail1" placeholder="Nom Boutique"
                  name="boutique_nom">
              </div>
              <div class="form-group">
                <label for="exampleInputEmail1">Nom </label>
                <input type="text" class="form-control" id="exampleInputEmail1" placeholder="Nom" name="nom">
              </div>
              <div class="form-group">
                <label for="exampleInputEmail3">Prenoms </label>
                <input type="text" class="form-control" id="exampleInputEmail3" placeholder="Prenoms" name="prenoms">
              </div>
              <div class="form-group">
                <label for="exampleInputPassword4">Contact</label>
                <input type="text" class="form-control" id="exampleInputPassword4" placeholder="Contact" name="contact">
              </div>
              <div class="form-group">
                <label for="exampleInputCity1">Login</label>
                <input type="text" class="form-control" id="exampleInputCity1" placeholder="Login" name="login">
              </div>
              <div class="form-group">
                <label for="exampleInputCity1">Mot de passe</label>
                <input type="password" class="form-control" id="exampleInputCity1" placeholder="Mot de passe"
                  name="password">
              </div>
              <div class="form-group">
                <label for="exampleInputCity1">Verification mot de passe</label>
                <input type="password" class="form-control" id="exampleInputCity1"
                  placeholder="Verification mot de passe" name="retype_password">
              </div>
              <div class="form-group">
                <label for="select" class="col-3 col-form-label">Rôle</label>
                <div class="col-9">
                  <select id="select" name="role" class="form-control" id="exampleInputCity1">
                    <option value="clients">clients</option>
                  </select>
                </div>
              </div>
              <button type="submit" class="btn btn-primary mr-2" name="saveClient">Enregister</button>
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
<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
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
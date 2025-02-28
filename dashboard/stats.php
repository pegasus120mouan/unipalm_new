<?php
//require_once '../inc/functions/connexion.php';

include('header_clients.php');
require_once '../inc/functions/requete/clients/requete_commandes_clients.php';



$id_user = $_SESSION['user_id'];
$nom_user = $_SESSION['nom'];
$prenoms_user = $_SESSION['prenoms'];
$role_user = $_SESSION['user_role'];
//$login_user=$_SESSION['user_login'];

//echo $id_user;
$requete = $conn->prepare("SELECT
commandes.id as commande_id,
utilisateur_id, livreur_id, communes, cout_global,
cout_livraison, cout_reel, statut, date_commande, clients.id as id_client,
clients.nom as client_nom, prenoms, contact, login, avatar, boutique_id, boutiques.nom as boutique_nom
FROM `commandes`  
join (select * from utilisateurs where role = 'clients')  as clients on clients.id=commandes.utilisateur_id
join boutiques on clients.boutique_id=boutiques.id having utilisateur_id=:id_user order by date_commande DESC LIMIT 15");

// Liaison de la variable avec le paramètre de la requête
$requete->bindParam(':id_user', $id_user, PDO::PARAM_INT);
$requete->execute();
$commandes = $requete->fetchAll();

?>




<!-- Main row -->
<div class="row">
  <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add-commande">
    Enregistrer une commande
  </button>

  <a class="btn btn-outline-secondary" href="clients_commandes_print.php"><i class="fa fa-print"
      style="font-size:24px;color:green"></i></a>
  <!--<a href="commandes_update.php"><i class="fa fa-print" style="font-size:24px;color:green">Imprimer point du jour</i></a>
                <button type="button"  class="btn btn-primary"><i class="fa fa-print"></i> Imprimer point du jour</button>-->
  <table id="example1" class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>Nom Boutique</th>
>
        <th>Date de la commande</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($commandes as $commande) : ?>
      <tr>
        <td><?= $commande['communes'] ?></td>
        <td><?= $commande['cout_global'] ?></td>
        <td><?= $commande['cout_livraison'] ?></td>
        <td><?= $commande['cout_reel'] ?></td>
        <td><?= $commande['boutique_nom'] ?></td>


        <td>
          <?php if ($commande['statut'] !== null) : ?>
          <?php if ($commande['statut'] == 'Non Livré') : ?>
          <span class="badge badge-danger badge-lg"><?= $commande['statut'] ?></span>
          <?php else : ?>
          <span class="badge badge-success badge-lg"><?= $commande['statut'] ?></span>
          <?php endif; ?>
          <?php else : ?>
          <span class="badge badge-success badge-lg">Pas de point</span>
          <?php endif; ?>
        </td>




        <td><?= $commande['date_commande'] ?></td>

        <td class="actions">
          <a href="update_commande_client.php?id=<?= $commande['commande_id'] ?>" class="edit"><i
              class="fas fa-pen fa-xs" style="font-size:24px;color:blue"></i></a>
          <a href="delete_commande_client.php?id=<?= $commande['commande_id'] ?>" class="trash"><i
              class="fas fa-trash fa-xs" style="font-size:24px;color:red"></i></a>
        </td>
        <td>
      </tr>
      <div class="modal" id="update-<?= $commande['commande_id'] ?>">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-body">
              <form action="traitement_commande_livreurs_update.php" method="post">
                <input type="hidden" name="commande_id" value="<?= $commande['id'] ?>">
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



      <div class="modal" id="update_statut-<?= $commande['commande_id'] ?>">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-body">
              <form action="traitement_commande_statut_update.php" method="post">
                <input type="hidden" name="commande_id" value="<?= $commande['commande_id'] ?>">

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
          <form class="forms-sample" method="post" action="enregistrement/save_commande_client.php">
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
<script src="../plugins/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="../plugins/jquery-ui/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<!-- <script>
  $.widget.bridge('uibutton', $.ui.button)
</script>-->
<!-- Bootstrap 4 -->
<script src="../plugins/sweetalert2/sweetalert2.min.js"></script>

<script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- ChartJS -->
<script src="../plugins/chart.js/Chart.min.js"></script>
<!-- Sparkline -->
<script src="../plugins/sparklines/sparkline.js"></script>
<!-- JQVMap -->
<script src="../plugins/jqvmap/jquery.vmap.min.js"></script>
<script src="../plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
<!-- jQuery Knob Chart -->
<script src="../plugins/jquery-knob/jquery.knob.min.js"></script>
<!-- daterangepicker -->
<script src="../plugins/moment/moment.min.js"></script>
<script src="../plugins/daterangepicker/daterangepicker.js"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="../plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- Summernote -->
<script src="../plugins/summernote/summernote-bs4.min.js"></script>
<!-- overlayScrollbars -->
<script src="../plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- AdminLTE App -->
<script src="../dist/js/adminlte.js"></script>
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
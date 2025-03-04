<?php
//require_once '../inc/functions/connexion.php';

include('header.php');
require_once '../inc/functions/requete/clients/requete_commandes_clients.php';



$id_boutique=$_GET['id'];
//$login_user=$_SESSION['user_login'];

//$getStatut = $conn->query("SELECT statut FROM statut_livraison");
//$livreurs = $getStatut->fetchAll(PDO::FETCH_ASSOC);


$rows = $getLivreurs->fetchAll(PDO::FETCH_ASSOC);

$livreurs = $getStatut->fetchAll(PDO::FETCH_ASSOC);




//echo $id_user;
$stmt = $stmt = $conn->prepare(
    "SELECT 
    commandes.id AS commande_id, 
    commandes.communes AS communes, 
    commandes.cout_global AS cout_global, 
    commandes.cout_livraison AS cout_livraison, 
    commandes.cout_reel AS cout_reel, 
    commandes.statut AS commande_statut, 
    commandes.date_commande, 
    utilisateurs.nom AS nom_utilisateur, 
    utilisateurs.prenoms AS prenoms_utilisateur,
    boutiques.nom AS boutique_nom, 
    CONCAT(livreur.nom, ' ', livreur.prenoms) AS nom_livreur, 
    utilisateurs.role
FROM commandes
JOIN utilisateurs ON commandes.utilisateur_id = utilisateurs.id
JOIN boutiques ON utilisateurs.boutique_id = boutiques.id
LEFT JOIN utilisateurs AS livreur ON commandes.livreur_id = livreur.id
WHERE utilisateur_id = :id_user
    AND DATE(commandes.date_commande) = CURDATE()
ORDER BY commandes.date_commande DESC 
LIMIT 15"
);


$stmt->bindParam(':id_user', $id_boutique, PDO::PARAM_INT);
$stmt->execute();
$commandes = $stmt->fetchAll();


?>




<!-- Main row -->
<div class="row">

<div class="card-header">
    <h2><u>Liste des colis de :</u> <strong><?php echo $commandes[0]['boutique_nom']; ?></strong>
</h2>
 </div>

  <table id="example1" class="table table-bordered table-striped">
    <thead>
      <tr>
      <th>Communes</th>
        <th>Coût Global</th>
        <th>Livraison</th>
        <th>Côut réel</th>
        <th>Boutique</th>
        <th>Livreur</th>
        <th>Statut</th>
        <th>Date de la commande</th>
        <th>Actions</th>
        <th>Attribuer un livreur</th>
        <th>Changer Statut livraison</th>
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
        <td><?= $commande['nom_livreur'] ?></td>


        <td>
          <?php if ($commande['commande_statut'] !== null) : ?>
          <?php if ($commande['commande_statut'] == 'Non Livré') : ?>
          <span class="badge badge-danger badge-lg"><?= $commande['commande_statut'] ?></span>
          <?php else : ?>
          <span class="badge badge-success badge-lg"><?= $commande['commande_statut'] ?></span>
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
            <?php if ($commande['nom_livreur']) : ?>
              <button class="btn btn-secondary" disabled>Attribuer un livreur</button>
            <?php else : ?>
              <button class="btn btn-info" data-toggle="modal" data-target="#update-<?= $commande['commande_id'] ?>">Attribuer
                un livreur</button>
            <?php endif; ?>
          </td>

          <td>
            <?php if ($commande['commande_statut'] == 'Livré') : ?>
              <button class="btn btn-info" disabled>Changer le statut</button>
            <?php else : ?>
              <button class="btn btn-warning" data-toggle="modal" data-target="#update_statut-<?= $commande['commande_id'] ?>">Changer le statut</button>
            <?php endif; ?>
          </td>
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
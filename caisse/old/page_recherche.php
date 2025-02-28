<?php
//require_once '../inc/functions/connexion.php';

include('header.php');
require_once '../inc/functions/requete/clients/requete_commandes_clients.php';

$recherche = $_GET['recherche'] ?? '';
$limit = $_GET['limit'] ?? 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$rows = $getLivreurs->fetchAll(PDO::FETCH_ASSOC);
$livreurs = $getStatut->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare(
    "SELECT 
    commandes.id AS commande_id, 
    commandes.communes AS commande_communes, 
    commandes.cout_global AS commande_cout_global, 
    commandes.cout_livraison AS commande_cout_livraison, 
    commandes.cout_reel AS commande_cout_reel, 
    commandes.statut AS commande_statut, 
    commandes.date_commande, 
    utilisateurs.nom AS nom_utilisateur, 
    utilisateurs.prenoms AS prenoms_utilisateur,
    boutiques.nom AS nom_boutique, 
    livreur.nom AS nom_livreur, 
    livreur.prenoms AS prenoms_livreur, 
    CONCAT(livreur.nom, ' ', livreur.prenoms) AS fullname,
    utilisateurs.role
FROM commandes
JOIN utilisateurs ON commandes.utilisateur_id = utilisateurs.id
JOIN boutiques ON utilisateurs.boutique_id = boutiques.id
LEFT JOIN utilisateurs AS livreur ON commandes.livreur_id = livreur.id 
WHERE commandes.communes LIKE :communes
ORDER BY commandes.date_commande DESC"
);

$rechercheAvecPourcentage = '%' . $recherche . '%';
$stmt->bindParam(':communes', $rechercheAvecPourcentage, PDO::PARAM_STR);
$stmt->execute();
$commandes = $stmt->fetchAll();

$commande_pages = array_chunk($commandes, $limit);
$commandes_list = $commande_pages[$page - 1] ?? [];
?> 

<style>
  /* Your CSS styles */
</style>

<!-- Main row -->
<div class="row">
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
      <?php foreach ($commandes_list as $commande) : ?>
      <tr>
        <td><?= htmlspecialchars($commande['commande_communes']) ?></td>
        <td><?= htmlspecialchars($commande['commande_cout_global']) ?></td>
        <td><?= htmlspecialchars($commande['commande_cout_livraison']) ?></td>
        <td><?= htmlspecialchars($commande['commande_cout_reel']) ?></td>
        <td><?= htmlspecialchars($commande['nom_boutique']) ?></td>
        <td><?= htmlspecialchars($commande['fullname']) ?></td>
        <td>
          <?php if ($commande['commande_statut'] !== null) : ?>
          <?php if ($commande['commande_statut'] == 'Non Livré') : ?>
          <span class="badge badge-danger badge-lg"><?= htmlspecialchars($commande['commande_statut']) ?></span>
          <?php else : ?>
          <span class="badge badge-success badge-lg"><?= htmlspecialchars($commande['commande_statut']) ?></span>
          <?php endif; ?>
          <?php else : ?>
          <span class="badge badge-success badge-lg">Pas de point</span>
          <?php endif; ?>
        </td>
        <td><?= htmlspecialchars($commande['date_commande']) ?></td>
        <td class="actions">
          <a href="commandes_update_recherche.php?id=<?= $commande['commande_id'] ?>&recherche=<?= urlencode($recherche) ?>&page=<?= $page ?>&limit=<?= $limit ?>" class="edit"><i class="fas fa-pen fa-xs" style="font-size:24px;color:blue"></i></a>
          <a href="delete_commandes.php?id=<?= $commande['commande_id'] ?>&recherche=<?= urlencode($recherche) ?>&page=<?= $page ?>&limit=<?= $limit ?>" class="trash"><i class="fas fa-trash fa-xs" style="font-size:24px;color:red"></i></a>
        </td>
        <td>
          <?php if ($commande['nom_livreur']) : ?>
            <button class="btn btn-secondary" disabled>Attribuer un livreur</button>
          <?php else : ?>
            <button class="btn btn-info" data-toggle="modal" data-target="#update-<?= $commande['commande_id'] ?>">Attribuer un livreur</button>
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
                <input type="hidden" name="commande_id" value="<?= $commande['commande_id'] ?>">
                <div class="form-group">
                  <label>Livreur</label>
                  <select name="livreur_id" class="form-control">
                    <?php
                      foreach ($rows as $row) {
                        echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['livreur_name']) . '</option>';
                      }
                    ?>
                  </select>
                </div>
                <button type="submit" class="btn btn-primary mr-2" name="saveCommande">Attribuer</button>
                <button type="button" class="btn btn-light" data-dismiss="modal">Annuler</button>
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
                        echo '<option value="' . htmlspecialchars($livreur['statut']) . '">' . htmlspecialchars($livreur['statut']) . '</option>';
                      }
                    ?>
                  </select>
                </div>
                <button type="submit" class="btn btn-primary mr-2" name="saveCommande">Changer le statut</button>
                <button type="button" class="btn btn-light" data-dismiss="modal">Annuler</button>
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
                <input type="text" class="form-control" id="exampleInputEmail1" placeholder="Commune destination" name="communes">
              </div>
              <div class="form-group">
                <label for="exampleInputPassword1">Côut Global</label>
                <input type="text" class="form-control" id="exampleInputPassword1" placeholder="Coût global Colis" name="cout_global">
              </div>
              <div class="form-group">
                <label>Côut Livraison</label>
                <?php
                echo '<select id="select" name="livraison" class="form-control">';
                while ($coutLivraison = $cout_livraison->fetch(PDO::FETCH_ASSOC)) {
                  echo '<option value="' . htmlspecialchars($coutLivraison['cout_livraison']) . '">' . htmlspecialchars($coutLivraison['cout_livraison']) . '</option>';
                }
                echo '</select>';
                ?>
              </div>
              <button type="submit" class="btn btn-primary mr-2" name="saveCommande">Enregistrer</button>
              <button type="button" class="btn btn-light" data-dismiss="modal">Annuler</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- /.row (main row) -->
</div><!-- /.container-fluid -->
<!-- /.content -->
</div>

<div class="pagination-container bg-secondary d-flex justify-content-center w-100 text-white p-3">
    <?php if ($page > 1): ?>
        <a href="?recherche=<?= urlencode($recherche) ?>&page=<?= $page - 1 ?>&limit=<?= $limit ?>" class="btn btn-primary">&lt;</a>
    <?php endif; ?>

    <span><?= $page . '/' . count($commande_pages) ?></span>

    <?php if ($page < count($commande_pages)): ?>
        <a href="?recherche=<?= urlencode($recherche) ?>&page=<?= $page + 1 ?>&limit=<?= $limit ?>" class="btn btn-primary">&gt;</a>
    <?php endif; ?>

    <form action="" method="get" class="items-per-page-form ml-3">
        <input type="hidden" name="recherche" value="<?= htmlspecialchars($recherche) ?>">
        <input type="hidden" name="page" value="<?= $page ?>">
        <label for="limit">Afficher :</label>
        <select name="limit" id="limit" class="items-per-page-select" onchange="this.form.submit()">
            <option value="5" <?= $limit == 5 ? 'selected' : '' ?>>5</option>
            <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10</option>
            <option value="15" <?= $limit == 15 ? 'selected' : '' ?>>15</option>
        </select>
        <button type="submit" class="submit-button">Valider</button>
    </form>
</div>

<!-- Footer -->
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
if (isset($_SESSION['popup']) && $_SESSION['popup'] == true) {
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
if (isset($_SESSION['delete_pop']) && $_SESSION['delete_pop'] == true) {
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

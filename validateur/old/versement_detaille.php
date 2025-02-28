<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_cout_livraison.php';
include('header.php');

$id_dette = $_GET['id'];

$requete = $conn->prepare("
    SELECT v.*, d.montant_actuel AS montant_dette
    FROM versements v
    INNER JOIN dette d ON v.dette_id = d.id
    WHERE v.dette_id = :id_dette
");

$requete->bindParam(':id_dette', $id_dette, PDO::PARAM_INT);
$requete->execute();
$versements_listes = $requete->fetchAll();
?>

<!-- Main row -->
<div class="row">
  <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add-versement">
    Enregistrer un versement
  </button>

  <a class="btn btn-outline-secondary" href="coutlivraison_print.php">
    <i class="fa fa-print" style="font-size:24px;color:green"></i>
  </a>

  <form action="page_recherche.php" method="GET" class="d-flex ml-auto">
    <input class="form-control me-2" type="search" name="recherche" style="width: 400px;" placeholder="Recherche..." aria-label="Search">
    <button class="btn btn-outline-primary" type="submit">Rechercher</button>
  </form>

  <table id="example1" class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>Montant de la dette</th>
        <th>Versement</th>
        <th>Date versement</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($versements_listes as $versements_liste) : ?>
      <tr>
        <td><?= htmlspecialchars($versements_liste['montant_dette']) ?></td>
        <td><?= htmlspecialchars($versements_liste['montant_versement']) ?></td>
        <td><?= htmlspecialchars($versements_liste['date_versement']) ?></td> 
        <td class="actions">
          <a href="versements_update.php?id=<?= $versements_liste['id'] ?>&id_dette=<?= $id_dette ?>" class="edit">
            <i class="fas fa-pen fa-xs" style="font-size:24px;color:blue"></i>
          </a>
          <a href="delete_versements.php?id=<?= $versements_liste['id'] ?>&id_dette=<?= $id_dette ?>" class="trash">
            <i class="fas fa-trash fa-xs" style="font-size:24px;color:red"></i>
          </a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div class="modal fade" id="add-versement">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Enregistrer un versement</h4>
        </div>
        <div class="modal-body">
          <form class="forms-sample" method="post" action="save_versement.php?id=<?= $id_dette?>">
            <div class="card-body">
              <div class="form-group">
                <label for="montant_versement">Montant Versement</label>
                <input type="text" class="form-control" id="montant_versement" placeholder="Montant à verser" name="montant_versement">
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

<!-- Footer and scripts -->
<script src="../../plugins/jquery/jquery.min.js"></script>
<script src="../../plugins/jquery-ui/jquery-ui.min.js"></script>
<script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../../plugins/sweetalert2/sweetalert2.min.js"></script>
<script src="../../plugins/chart.js/Chart.min.js"></script>
<script src="../../plugins/sparklines/sparkline.js"></script>
<script src="../../plugins/jqvmap/jquery.vmap.min.js"></script>
<script src="../../plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
<script src="../../plugins/jquery-knob/jquery.knob.min.js"></script>
<script src="../../plugins/moment/moment.min.js"></script>
<script src="../../plugins/daterangepicker/daterangepicker.js"></script>
<script src="../../plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<script src="../../plugins/summernote/summernote-bs4.min.js"></script>
<script src="../../plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<script src="../../dist/js/adminlte.js"></script>

<?php if (isset($_SESSION['popup']) && $_SESSION['popup']) : ?>
  <script>
    var audio = new Audio("../inc/sons/notification.mp3");
    audio.volume = 1.0;
    audio.play().then(() => {
      Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
      }).fire({
        icon: 'success',
        title: 'Action effectuée avec succès.'
      });
    }).catch((error) => {
      console.error('Erreur de lecture audio :', error);
    });
    <?php $_SESSION['popup'] = false; ?>
  </script>
<?php endif; ?>

<?php if (isset($_SESSION['delete_pop']) && $_SESSION['delete_pop']) : ?>
  <script>
    Swal.mixin({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 3000
    }).fire({
      icon: 'error',
      title: 'Action échouée.'
    });
    <?php $_SESSION['delete_pop'] = false; ?>
  </script>
<?php endif; ?>

</body>
</html>

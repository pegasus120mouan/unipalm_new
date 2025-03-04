<?php
require_once '../inc/functions/connexion.php';
include('header.php');

$stmt = $conn->prepare("SELECT id, description, nom_pictures, banner_app FROM banner WHERE banner_app = 'admin'");
$stmt->execute();
$banners_admins = $stmt->fetchAll();
?>

<style>
.mb-10 {
  margin-bottom: 15px;
}
</style>
<!-- Main row -->
<div class="row">
 <button type="button" class="btn btn-primary mb-10" data-toggle="modal" data-target="#add-client">
  Enregistrer un banner
</button>

  <table id="example1" class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>Banner</th>
        <th>Description</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($banners_admins as $banners_admin) : ?>
      <tr>
        <td>
          <a href="banner_profile.php?id=<?=$banners_admin['id']?>" class="edit">
            <img src="../dossiers_banners/<?= htmlspecialchars($banners_admin['nom_pictures']) ?>" alt="Logo" width="150" height="50">
          </a>
        </td>
        <td><?= htmlspecialchars($banners_admin['description']) ?></td>
        <td class="actions">
          <button class="btn btn-warning edit-btn" data-toggle="modal" data-target="#edit-banner"
                  data-id="<?= $banners_admin['id'] ?>" data-description="<?= htmlspecialchars($banners_admin['description']) ?>">
            <i class="fas fa-pen fa-xs" style="font-size:24px"></i>
          </button>
          <button class="btn btn-danger delete-btn" data-id="<?= $banners_admin['id'] ?>">
            <i class="fas fa-trash fa-xs" style="font-size:24px"></i>
          </button>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- Modal for adding banner -->
  <div class="modal fade" id="add-client">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Enregistrer un banner</h4>
        </div>
        <div class="modal-body">
          <form method="post" action="save_banner.php">
            <div class="form-group">
              <label for="addDescription">Description</label>
              <input type="text" class="form-control" id="addDescription" placeholder="Description" name="description">
            </div>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal for editing banner -->
  <div class="modal fade" id="edit-banner">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Modifier le banner</h4>
        </div>
        <div class="modal-body">
          <form method="post" action="traitement/traitement_update_banner.php">
            <input type="hidden" id="editId" name="id">
            <div class="form-group">
              <label for="editDescription">Description</label>
              <input type="text" class="form-control" id="editDescription" name="description">
            </div>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          </form>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- jQuery -->
<script src="../../plugins/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="../../plugins/jquery-ui/jquery-ui.min.js"></script>
<!-- Bootstrap 4 -->
<script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 -->
<script src="../../plugins/sweetalert2/sweetalert2.min.js"></script>
<!-- AdminLTE App -->
<script src="../../dist/js/adminlte.js"></script>

<script>
$(document).ready(function(){
  // Handle edit button click
  $('.edit-btn').on('click', function() {
    var id = $(this).data('id');
    var description = $(this).data('description');

    $('#editId').val(id);
    $('#editDescription').val(description);
  });

  // Handle delete button click
  $('.delete-btn').on('click', function() {
    var id = $(this).data('id');
    Swal.fire({
      title: 'Êtes-vous sûr?',
      text: "Vous ne pourrez pas revenir en arrière!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Oui, supprimer!'
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = 'banner_delete.php?id=' + id;
      }
    })
  });

  // Handle success popup
  <?php if (isset($_SESSION['popup']) && $_SESSION['popup'] == true) : ?>
  Swal.fire({
    icon: 'success',
    title: 'Action effectuée avec succès.',
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000
  });
  <?php $_SESSION['popup'] = false; endif; ?>

  // Handle delete popup
  <?php if (isset($_SESSION['delete_pop']) && $_SESSION['delete_pop'] == true) : ?>
  Swal.fire({
    icon: 'error',
    title: 'Banner non inserée.',
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000
  });
  <?php $_SESSION['delete_pop'] = false; endif; ?>
});
</script>
</body>
</html>

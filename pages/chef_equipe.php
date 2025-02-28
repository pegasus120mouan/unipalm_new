<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_chef_equipes.php';
include('header.php');
$chefs = getChefEquipesFull($conn); 
?>

<style>
        .block-container {
      background-color:  #d7dbdd ;
      padding: 20px;
      border-radius: 5px;
      width: 100%;
      margin-bottom: 20px;
    }
</style>

        <!-- Main row -->
        <div class="row">

            <div class="block-container">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add-chef">
              <i class="fa fa-edit"></i>Enregistrer un chef d'equipe
            </button>

            <button type="button" class="btn btn-danger" onclick="window.location.href='impression_chefs.php'">
              <i class="fa fa-print"></i> Imprimer la liste des chefs équipes
             </button>
        </div>



  <table id="example1" class="table table-bordered table-striped">
    <thead>
                  <tr>
                    <th>Nom</th>
                    <th>Prenoms</th>
                    <th>Actions</th>
                  </tr>
     </thead>
                  <tbody>
                  <?php foreach ($chefs as $chef): ?>
                  <tr>
                
                <td><?=$chef['nom']?></td>

                <td><?=$chef['prenoms']?></td>
                    <td class="actions">
                        <a href="#" class="edit" data-toggle="modal" data-target="#modifier<?= $chef['id_chef'] ?>">
                        <i class="fas fa-pen fa-xs" style="font-size:24px;color:blue"></i>
                    </a>                        
                        <a href="#" onclick="confirmDelete(<?= $chef['id_chef'] ?>)" class="trash">
                          <i class="fas fa-trash fa-xs" style="font-size:24px;color:red"></i>
                        </a>
                      </td>
 <div class="modal fade" id="modifier<?= $chef['id_chef'] ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="editModalLabel">Modifier un chef d'équipe <?= $chef['nom'] ?> <?= $chef['prenoms'] ?></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
         </div>

         <div class="modal-body">
            <form id="edit-chef-form" method="post" action="traitement_chefs_equipe.php">
               <input type="hidden" name="id_chef" value="<?= $chef['id_chef'] ?>">

               <div class="mb-3">
                  <label for="edit-nom" class="form-label">Nom</label>
                  <input type="text" class="form-control" id="edit-nom" name="nom" value="<?= htmlspecialchars($chef['nom']) ?>" required>
               </div>

               <div class="mb-3">
                  <label for="edit-prenoms" class="form-label">Prénoms</label>
                  <input type="text" class="form-control" id="edit-prenoms" name="prenoms" value="<?= htmlspecialchars($chef['prenoms']) ?>" required>
               </div>

               <div class="modal-footer">
                  <button type="submit" class="btn btn-success btn-lg">Mise à jour</button>
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
               </div>
            </form>
         </div>
      </div>
   </div>
</div>


</div>

     
                     </tr>
                  <?php endforeach; ?>
                  </tbody>
</table>
         

                

      <div class="modal fade" id="add-chef">
            <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title">Enregistrer un chef d'equipe</h4>
            </div>
            <div class="modal-body">
            <form class="forms-sample" method="post" action="traitement_chefs_equipe.php">
                <div class="card-body">
                  <div class="form-group">
                    <label for="exampleInputEmail1">Nom</label>
                    <input type="text" class="form-control" id="exampleInputEmail1" placeholder="Nom" name="nom">
                  </div>
                  <div class="form-group">
                    <label for="exampleInputEmail3">Prenoms</label>
                    <input type="text" class="form-control" id="exampleInputEmail3"
                     placeholder="Prenom" name="prenoms">
                </div>

                                            
               <button type="submit" class="btn btn-primary mr-2" name="signup">Enregister</button>
                          <button class="btn btn-light">Annuler</button>
            </form>

<!-- Gestion Partenaires--->
<!-- ./wrapper -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
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
<!-- JavaScript -->
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
  <script>
    function confirmDelete(id) {
    Swal.fire({
        title: 'Êtes-vous sûr ?',
        text: "Voulez-vous vraiment supprimer cet utilisateur ?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'traitement_chefs_equipe.php?action=delete&id=' + id;
        }
    });
}
</script>
</body>
</html>

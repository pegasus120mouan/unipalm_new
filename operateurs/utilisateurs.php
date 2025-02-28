<?php

require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_utilisateurs.php';
include('header_operateurs.php');

//$stmt = $conn->prepare("SELECT * FROM utilisateurs");
//$stmt->execute();

//$utilisateurs = $liste_livreur->fetchAll();
//$statuts_comptes= $getStatut_compte->fetchAll(PDO::FETCH_ASSOC);

$utilisateurs = getUtilisateurs($conn); 

//foreach($users as $user)
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
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add-client">
              <i class="fa fa-edit"></i>Enregistrer un utilisateur
            </button>

            <button type="button" class="btn btn-danger" onclick="window.location.href='export_livreurs.php'">
              <i class="fa fa-print"></i> Exporter la liste des utilisateurs
             </button>
        </div>



  <table id="example1" class="table table-bordered table-striped">
    <thead>
                  <tr>
                    <th>Nom</th>
                    <th>Prenoms</th>
                    <th>Contact</th>
                    <th>Rôle</th>
                    <th>Login</th>
                    <th>Avatar</th>
                    
                    <th>Actions</th>
                    <th>Statut compte </th>
                  </tr>
     </thead>
                  <tbody>
                  <?php foreach ($utilisateurs as $utilisateur): ?>
                  <tr>
                
                <td><?=$utilisateur['nom']?></td>

                <td><?=$utilisateur['prenoms']?></td>

                <td><?=$utilisateur['contact']?></td>
                <td><?=$utilisateur['role']?></td>

   <td>
    <?php if ($utilisateur['statut_compte'] == 0): ?>
        <button class="btn btn-dark btn-block" disabled>
            <?= $utilisateur['login'] ?>
        </button>
    <?php else: ?>
        <a class="btn btn-dark btn-block" href="commandes_livreurs.php?id=<?= $utilisateur['id'] ?>">
            <?= $utilisateur['login'] ?>
        </a>
    <?php endif; ?>
</td>
                <td>
                <a href="utilisateurs_profile.php?id=<?=$utilisateur['id']?>" class="edit"><img src="../dossiers_images/<?php echo $utilisateur['avatar']; ?>" alt="Logo" width="50" height="50"> </a>
                 </td>
                    <td class="actions">
                        <a href="livreurs_update.php?id=<?=$utilisateur['id']?>" class="edit"><i class="fas fa-pen fa-xs" style="font-size:24px;color:blue"></i></a>
                        <a href="livreurs_delete.php?id=<?=$utilisateur['id']?>" class="trash"><i class="fas fa-trash fa-xs" style="font-size:24px;color:red"></i></a>
                </td>
                

                
                  
   <td>
                        <form method="post" action="liste_livreurs.php">
                            <input type="hidden" name="user_id" value="<?=$utilisateur['id']?>">
                            <input type="hidden" name="statut_compte" value="<?=($utilisateur['statut_compte'] == 1) ? 0 : 1 ?>">
                            <input type="checkbox" name="statut_compte" data-toggle="toggle" data-on="Actif" data-off="Inactif" data-onstyle="success" data-offstyle="danger" <?=($utilisateur['statut_compte'] == 1) ? 'checked' : ''?> onchange="submitForm(this)">
                        </form>
  </td>
     
 </tr>
         <div class="modal" id="update_compte-<?= $utilisateur['id'] ?>">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-body">
                <form action="traitement_etat_compte_livreur.php" method="post">
                  <input type="hidden" name="id" value="<?= $utilisateur['id'] ?>">
                  <div class="form-group">
                    <label>Activer/Désactiver le compte</label>
                    <select name="valeur" class="form-control">
                      <?php
                      foreach ($statuts_comptes as $statuts_compte) {
                        echo '<option value="' . $statuts_compte['valeur'] . '">' . $statuts_compte['etat'] . '</option>';
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
         

                

      <div class="modal fade" id="add-client">
            <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title">Enregistrer un utilisateur</h4>
            </div>
            <div class="modal-body">
            <form class="forms-sample" method="post" action="traitement_utilisateurs.php">
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
                  <div class="form-group">
                        <label for="exampleInputCity1">Contact</label>
                            <input type="text" class="form-control" id="exampleInputCity1" placeholder="Contact" name="contact">
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

                                              <div class="form-group">
                  <label>Selection rôle</label>
                  <select id="select" name="role" class="form-control">
                    <option value="admin">Administrateur</option>
                    <option value="operateur">Opérateur</option>
                    <option value="validateur">Validateur</option>
                    
                  </select>
              </div>
                                            
                                            <button type="submit" class="btn btn-primary mr-2" name="signup">Enregister</button>
                                            <button class="btn btn-light">Annuler</button>

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
</body>
</html>

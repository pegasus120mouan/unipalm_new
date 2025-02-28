<?php

require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_utilisateurs.php'; 
include('header.php');

//$stmt = $conn->prepare("SELECT * FROM utilisateurs");
//$stmt->execute();

$utilisateurs = $liste_utilisateurs->fetchAll();
$statuts_comptes= $getStatut_compte->fetchAll(PDO::FETCH_ASSOC);

//foreach($users as $user)
?>




        <!-- Main row -->
        
        <div class="row">

        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add-client">
                  Enregistrer un livreur
                </button>   

  <table id="example1" class="table table-bordered table-striped">
    <thead>
                  <tr>
                    <th>Nom</th>
                    <th>Prenoms</th>
                    <th>Contact</th>
                    <th>Login</th>
                    <th>Role</th>
                    <th>Boutique</th>
                    <th>Type d'articles</th>
                    <th>Logo boutique</th>
                    <th>Avatar</th>
                    <th>Actions</th>
                    <th>Statut compte </th>
                    <th>Gestion Compte</th>
                  </tr>
     </thead>
                  <tbody>
                  <?php foreach ($utilisateurs as $utilisateur): ?>
                  <tr>
                
                <td><?=$utilisateur['nom']?></td>

                <td><?=$utilisateur['prenoms']?></td>

                <td><?=$utilisateur['contact']?></td>

                <td><?=$utilisateur['login']?></td>
                <td class="<?=($utilisateur['role'] == 'livreur') ? 'bg-gray' : (($utilisateur['role'] == 'clients') ? 'bg-blue' : 'bg-red')?>">
                      <?=$utilisateur['role']?>
                </td>
                <td><?=$utilisateur['nom_boutique']?></td>
                <td><?=$utilisateur['type_articles']?></td>
                  <td>
                  <?php if ($utilisateur['logo_boutique'] !== null): ?>
    <a href="boutique_profile.php?id=<?php echo $utilisateur['boutique_id']; ?>" class="edit">
        <img src="../dossiers_images/<?php echo $utilisateur['logo_boutique']; ?>" alt="Logo" width="50" height="50">
    </a>
<?php endif; ?>

                  </td>

                <td>
                <a href="utilisateurs_profile.php?id=<?=$utilisateur['id']?>" class="edit"><img src="../dossiers_images/<?php echo $utilisateur['avatar']; ?>" alt="Logo" width="50" height="50"> </a>
                 </td>
                    <td class="actions">
                        <a href="gestion_access_update.php?id=<?=$utilisateur['id']?>" class="edit"><i class="fas fa-pen fa-xs" style="font-size:24px;color:blue"></i></a>
                        <a href="gestion_access_delete.php?id=<?=$utilisateur['id']?>" class="trash"><i class="fas fa-trash fa-xs" style="font-size:24px;color:red"></i></a>
                </td>
                  
   <td>
                        <form method="post" action="#">
                            <input type="hidden" name="user_id" value="<?=$utilisateur['id']?>">
                            <input type="hidden" name="statut_compte" value="<?=($utilisateur['statut_compte'] == 1) ? 0 : 1 ?>">
                            <input type="checkbox" name="statut_compte" data-toggle="toggle" data-on="Actif" data-off="Inactif" data-onstyle="success" data-offstyle="danger" <?=($utilisateur['statut_compte'] == 1) ? 'checked' : ''?> onchange="submitForm(this)">
                        </form>
                    </td>
<td>
  <button class="btn btn-warning" data-toggle="modal" data-target="#update_gestion-<?= $utilisateur['id'] ?>">
    <i class="fas fa-key"></i> <!-- Utilisation de l'icône de clé de Font Awesome -->
</button>
</td>
     
 </tr>
         <div class="modal" id="update_gestion-<?= $utilisateur['id'] ?>">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-body">
                <form action="traitement_gestion_compte_livreur.php" method="post">
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-RsAP5Cgq5FTwAxyy9WdZJe43NzyC9pIPkRHUovMr6Dlbc4sCvUA7hFJjciwl3GdW" crossorigin="anonymous">

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

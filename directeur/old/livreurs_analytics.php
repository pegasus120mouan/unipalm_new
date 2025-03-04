<?php
require_once '../inc/functions/connexion.php';
include('header.php');

$stmt = $conn->prepare("SELECT
u.id AS id_livreur,
u.nom AS nom_livreur,
u.prenoms AS prenoms_livreur,
u.avatar AS livreurs_avatar,
CONCAT(u.nom, ' ', u.prenoms) AS nom_prenoms,
MONTHNAME(MIN(c.date_commande)) AS nom_mois,
SUM(c.cout_global) AS somme_montant,
SUM(c.cout_livraison) AS somme_montant_livraison,
SUM(c.cout_reel) AS somme_montant_reel,
SUM(c.statut = 'livré') AS livraisons_livre,
SUM(c.statut = 'non livré') AS livraisons_non_livre,
(
    SELECT SUM(depense)
    FROM points_livreurs d
    WHERE d.utilisateur_id = u.id
      AND MONTH(d.date_commande) = MONTH(CURDATE()) - 1
      AND YEAR(d.date_commande) = YEAR(CURDATE())
) AS somme_depenses,
(
    SELECT COUNT(id)
    FROM commandes
    WHERE MONTH(date_commande) = MONTH(CURDATE()) - 1
    AND YEAR(date_commande) = YEAR(CURDATE())
) AS nombre_colis_total,
(
    SELECT SUM(gain_jour)
    FROM points_livreurs d
    WHERE d.utilisateur_id = u.id
      AND MONTH(d.date_commande) = MONTH(CURDATE()) - 1
      AND YEAR(d.date_commande) = YEAR(CURDATE())
) AS somme_gain
FROM utilisateurs u
JOIN commandes c ON u.id = c.livreur_id
WHERE MONTH(c.date_commande) = MONTH(CURDATE()) - 1
AND YEAR(c.date_commande) = YEAR(CURDATE())
GROUP BY u.id, u.nom, u.prenoms
ORDER BY somme_montant DESC");


$stmt->execute();
$statistiques_livreurs = $stmt->fetchAll();


?>




<!-- Main row -->
<div class="row">
 <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#vue_stats">
    Statistiques par mois par livreur
  </button>
  <table id="example1" class="table table-bordered table-striped">
  <thead>
      <tr>

       <th>Avatar</th>
        <th>Nom livreur</th>
        <th>Mois</th>
        <th>Montant Livraisons</th>
        <th>Somme depenses</th>
        <th>Gain</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($statistiques_livreurs as $statistiques_livreur) : ?>
        <tr>
        <td>
          <a href="client_profile.php?id=<?=$statistiques_livreur['id_livreur']?>" class="edit"><img
              src="../dossiers_images/<?php echo $statistiques_livreur['livreurs_avatar']; ?>" alt="Logo" width="50"
              height="50"> </a>
        </td>
          
        <td><?=$statistiques_livreur['nom_prenoms']?></td>
        <td><?= ucfirst(strftime('%B', strtotime($statistiques_livreur['nom_mois']))) ?></td>
        <td style="background-color: yellow">
           <strong> <?=$statistiques_livreur['somme_montant_livraison']?></strong>
        </td>
        <td style="background-color: red">
            <?=$statistiques_livreur['somme_depenses']?>
        </td>

        <td style="background-color: green">
            <?=$statistiques_livreur['somme_gain']?>
        </td>
        




          <td class="actions">
          <button class="btn btn-info" data-toggle="modal" data-target="#update-<?= $statistiques_livreur['id_livreur'] ?>">
          <i class="fas fa-eye"></i>
            </button>          
          </td>
          <td>
        </tr>
        <div class="modal" id="update-<?= $statistiques_livreur['id_livreur'] ?>">
          <div class="modal-dialog modal-xl">

            <div class="modal-content">
              <div class="modal-body">
              <h3>Statistiques des livraisons</h3>
              <p><i><u>Nom du livreur:</u></i>  <strong><?php echo $statistiques_livreur['nom_prenoms']; ?></strong>
              <p><i><u>Mois:</u></i>  <strong><?php echo ucfirst(strftime('%B', strtotime($statistiques_livreur['nom_mois']))); ?></strong></p>


              <section class="content">
        <div class="container-fluid">
          <!-- Small boxes (Stat box) -->
          <div class="row">
            <div class="col-lg-3 col-6">
              <!-- small box -->
              <div class="small-box bg-info">
                <div class="inner">
                  <h3><?php echo $statistiques_livreur['somme_montant'];   ?>
                  <span class="right badge badge-dark">CFA</span>
                </h3>
                <p>MontanDépensest Global</p>
                </div>
              </div>
            </div>
            
            <!-- ./col -->
            <div class="col-lg-3 col-6">
              <!-- small box -->
              <div class="small-box bg-success">
                <div class="inner">
                <h3><?php echo $statistiques_livreur['nombre_colis_total'];   ?>
               </h3>
               <p>Nbre de colis reçus</p>
                </div>
              </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-6">
              <!-- small box -->
              <div class="small-box bg-warning">
                <div class="inner">
                <h3><?php echo $statistiques_livreur['somme_montant_livraison'];   ?>
                </h3>
                <p>Somme globale livraison</p>

                </div>
                 </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-6">
              <!-- small box -->
              <div class="small-box bg-danger">
                <div class="inner">
                <h3><?php echo $statistiques_livreur['somme_depenses'];   ?>
                </h3>
                <p>Dépenses</p>
                </div>
              </div>
            </div>
            <!-- ./col -->
          </div>
          <!-- /.row -->













                  </div>
                            <div class="row">
            <div class="col-lg-3 col-6">
              <!-- small box -->
              <div class="small-box bg-info">
                <div class="inner">
                  <h3><?php echo $statistiques_livreur['somme_montant_reel'];   ?>
                  <span class="right badge badge-dark">CFA</span>
                </h3>
                <p>Montant remis aux cients</p>
                </div>
              </div>
            </div>
            
            <!-- ./col -->
            <div class="col-lg-3 col-6">
              <!-- small box -->
              <div class="small-box bg-success">
                <div class="inner">
                <h3><?php echo $statistiques_livreur['livraisons_livre'];   ?>
               </h3>
               <p>Nbre de colis livré</p>
                </div>
              </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-6">
              <!-- small box -->
              <div class="small-box bg-warning">
                <div class="inner">
                <h3><?php echo $statistiques_livreur['livraisons_non_livre'];   ?>
                </h3>
                <p>Nbre de colis non livré</p>

                </div>
                 </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-6">
              <!-- small box -->
              <div class="small-box bg-danger">
                <div class="inner">
                <h3><?php echo $statistiques_livreur['somme_gain'];   ?>
                </h3>
                <p>Gain du mois</p>
                </div>
              </div>
            </div>
            <!-- ./col -->
          </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </tbody>
  </table>


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

if (isset($_SESSION['popup']) && $_SESSION['popup'] ==  true) {
  ?>
    <script>
      var audio = new Audio("../inc/sons/notification.mp3");
      audio.volume = 1.0; // Assurez-vous que le volume n'est pas à zéro
      audio.play().then(() => {
        // Lecture réussie
        var Toast = Swal.mixin({
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000
        });
  
        Toast.fire({
          icon: 'success',
          title: 'Action effectuée avec succès.'
        });
      }).catch((error) => {
        console.error('Erreur de lecture audio :', error);
      });
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
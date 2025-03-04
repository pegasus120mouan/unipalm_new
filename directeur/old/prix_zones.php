<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_cout_livraison.php';
include('header.php');

//$rows = $getLivreurs->fetchAll(PDO::FETCH_ASSOC);

//$livreurs = $getStatut->fetchAll(PDO::FETCH_ASSOC);

//$cout_livraisons= $getZones->fetchAll(PDO::FETCH_ASSOC);

////$stmt = $conn->prepare("SELECT * FROM users");
//$stmt->execute();
//$users = $stmt->fetchAll();
//foreach($users as $user)

$id_zones= $_GET['id'];


$requete = $conn->prepare("SELECT 
communes.commune_id AS commune_id,
communes.nom_commune AS nom_commune, 
prix.montant AS prix_livraison, 
zones.nom_zone AS nom_zone
FROM communes
JOIN communes_zones ON communes.commune_id = communes_zones.commune_id
JOIN prix ON communes.commune_id = prix.commune_id AND communes_zones.zone_id = prix.zone_id
JOIN zones ON communes_zones.zone_id = zones.zone_id
WHERE communes_zones.zone_id = :id_zones");

$requete->bindParam(':id_zones', $id_zones, PDO::PARAM_INT);
$requete->execute();
$communes_listes = $requete->fetchAll();



/*$limit = $_GET['limit'] ?? 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$coutlivraison_pages = array_chunk($cout_livraisons, $limit );
//$commandes_list = $commande_pages[$_GET['page'] ?? ] ;
$coutlivraison_list = $coutlivraison_pages[$page - 1] ?? [];

//var_dump($commandes_list);*/


?>




<!-- Main row -->
<div class="row">
  <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add-prix">
    Enregistrer un prix
  </button>

  <a class="btn btn-outline-secondary" href="coutlivraison_print.php"><i class="fa fa-print" style="font-size:24px;color:green"></i></a>


    <!-- Utilisation du formulaire Bootstrap avec ms-auto pour aligner à droite -->
    <form action="page_recherche.php" method="GET" class="d-flex ml-auto">
    <input class="form-control me-2" type="search" name="recherche" style="width: 400px;" placeholder="Recherche..." aria-label="Search">
    <button class="btn btn-outline-primary" type="submit">Rechercher</button>
</form>






<table id="example1" class="table table-bordered table-striped">
    <thead>
      <tr>
       <!-- <th>ID</th>-->
       <th>Commune ID</th>
        <th>Commune de récuperation</th>
        <th>Commune de destination</th>
        <th>Coût</th>
        <th>Actions</th>
        <th>Changer le prix de la livraison</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($communes_listes as $communes_liste) : ?>
      <tr>
      <td><?= $communes_liste['commune_id'] ?></td>
        <td><?= $communes_liste['nom_zone'] ?></td>
        <td><?= $communes_liste['nom_commune'] ?></td>
        <td><?= $communes_liste['prix_livraison'] ?></td> 
          
        <td class="actions">
          <a href="update_commandes_livreurs.php?id=<?= $communes_liste['commune_id'] ?>" class="edit"><i
              class="fas fa-pen fa-xs" style="font-size:24px;color:blue"></i></a>
          <a href="delete_commandes_livreurs.php?id=<?= $communes_liste['commune_id'] ?>" class="trash"><i
              class="fas fa-trash fa-xs" style="font-size:24px;color:red"></i></a>
        </td>

        <td>
          <button class="btn btn-secondary" data-toggle="modal"
            data-target="#update_prix-<?= $communes_liste['commune_id'] ?>">Changer le prix</button>
        </td>

      </tr>
          </div>
        </div>
      </div>
      <div class="modal" id="update_prix-<?= $communes_liste['commune_id'] ?>">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-body">
              <form action="traitement/traitement_commande_livreur_update.php" method="post">
                <input type="hidden" name="commande_id" value="<?= $communes_liste['commune_id'] ?>">
                <div class="form-group">
                  <label>Changer le coût de la livraison</label>
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



  <div class="modal fade" id="add-prix">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Enregistrer un prix</h4>
        </div>
        <div class="modal-body">
          <form class="forms-sample" method="post" action="save_prix.php?id=<?= $id_zones ?>">
            <div class="card-body">
            <div class="form-group">
                <label>Commune de destination</label>
                <?php
                echo  '<select id="select" name="liste_commune" class="form-control">';
                while ($listeCommune = $liste_commune->fetch(PDO::FETCH_ASSOC)) {
                  echo '<option value="' . $listeCommune['commune_id'] . '">' . $listeCommune['nom_commune'] . '</option>';
                }
                echo '</select>'
                ?>
              </div>

              <div class="form-group">
                <?php
                echo  '<select id="select" name="cout_livraison" class="form-control">';
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
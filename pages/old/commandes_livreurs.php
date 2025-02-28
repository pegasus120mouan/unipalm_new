<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/livreurs/requete_commandes_livreurs.php';
include('header.php');

$aujourdhui = date("d-m-Y");

$id_user = $_GET['id'];


$requete = $conn->prepare("SELECT 
	commandes.id as commande_id,
  commandes.communes as commande_communes,
  commandes.cout_global as commande_cout_global, 
  commandes.cout_livraison as commande_cout_livraison,
  commandes.cout_reel as commande_cout_reel,
  commandes.statut as commande_statut,
  commandes.date_commande as date_commande,
  concat(livreurs.nom, ' ', livreurs.prenoms) as nom_livreur,
  clients.nom as nom_client,
  boutiques.nom as nom_boutique
from commandes
join livreurs on livreurs.id=commandes.livreur_id
join clients on clients.id=commandes.utilisateur_id
join boutiques on boutiques.id=clients.boutique_id
WHERE livreurs.id=:id_user order by date_commande DESC");
// Liaison de la variable avec le paramètre de la requête
$requete->bindParam(':id_user', $id_user, PDO::PARAM_INT);
$requete->execute();
$commande_livreurs = $requete->fetchAll();

// Selection Livreur

$sql = "SELECT utilisateurs.id as utilisateur_id, 
 concat(utilisateurs.nom,' ', utilisateurs.prenoms) as nom_utilisateurs,
 utilisateurs.contact as utilisateur_contact,
 utilisateurs.avatar as utilisateur_avatar
 FROM utilisateurs 
 WHERE role = 'livreur' and utilisateurs.id = :id_user";

$requete = $conn->prepare($sql);
$requete->bindParam(':id_user', $id_user, PDO::PARAM_INT);
$requete->execute();




$rows = $getLivreurs->fetchAll(PDO::FETCH_ASSOC);

$livreurs = $getStatut->fetchAll(PDO::FETCH_ASSOC);

////$stmt = $conn->prepare("SELECT * FROM users");
//$stmt->execute();
//$users = $stmt->fetchAll();
//foreach($users as $user)

$limit = $_GET['limit'] ?? 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$commande_livreurs_pages = array_chunk($commande_livreurs, $limit );
//$commandes_list = $commande_pages[$_GET['page'] ?? ] ;
$commandes_livreurs_list = $commande_livreurs_pages[$page - 1] ?? [];

//var_dump($commandes_list);

?>




<!-- Main row -->
<style>
  .pagination-container {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 20px;
}

.pagination-link {
    padding: 8px;
    text-decoration: none;
    color: white;
    background-color: #007bff; /* Bleu */
    border: 1px solid #007bff;
    border-radius: 4px; /* Ajout de la bordure arrondie */
    margin-right: 4px;
}

.items-per-page-form {
    margin-left: 20px;
}

label {
    margin-right: 5px;
}

.items-per-page-select {
    padding: 6px;
    border-radius: 4px; /* Ajout de la bordure arrondie */
}

.submit-button {
    padding: 6px 10px;
    background-color: #007bff;
    color: #fff;
    border: none;
    border-radius: 4px; /* Ajout de la bordure arrondie */
    cursor: pointer;
}
   .block-container {
      background-color:  #d7dbdd ;
      padding: 20px;
      border-radius: 5px;
      width: 100%;
      margin-bottom: 20px;
    }
</style>


<div class="row">
    <div class="block-container">
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add-commande">
      <i class="fa fa-edit"></i>Enregistrer une commande
    </button>

    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#print-commande">
      <i class="fa fa-print"></i> Imprimer un point
    </button>

    
    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modal-livreur-<?= $id_user ?>">
      <i class="fa fa-history"></i> Dépot à effectuer entre date
    </button>

    <a href="vuegeneral_hier.php?id=<?= $id_user ?>" class="btn btn-warning ml-auto">
     <i class="fa fa-calendar-minus-o"></i>Point d'hier
    </a>
  </div>

  <!--<a href="commandes_update.php"><i class="fa fa-print" style="font-size:24px;color:green">Imprimer point du jour</i></a>
                <button type="button"  class="btn btn-primary"><i class="fa fa-print"></i> Imprimer point du jour</button>-->
  <table id="example1" class="table table-bordered table-striped">
        <thead>
        <tr>
        <!-- <th>ID</th>-->
            <th>Communes</th>
            <th>Coût Global</th>
            <th>Livraison</th>
            <th>Côut réel</th>
            <th>Client</th>
            <th>Livreur</th>
            <th>Statut</th>
            <th>Date de la commande</th>
            <th>Actions</th>
            <th>Changer le livreur</th>
            <th>Changer Statut livraison</th>
        </tr>
        </thead>
        
        <tbody>
            <?php foreach ($commandes_livreurs_list as $commande_livreur) : ?>
            <tr>
             <!--   <td><?= $commande_livreur['commande_id'] ?></td>-->
                <td><?= $commande_livreur['commande_communes'] ?></td>
                <td><?= $commande_livreur['commande_cout_global'] ?></td>
                <td><?= $commande_livreur['commande_cout_livraison'] ?></td>
                <td><?= $commande_livreur['commande_cout_reel'] ?></td>
                <td><?= $commande_livreur['nom_boutique'] ?></td>
                <?php if ($commande_livreur['nom_livreur']) : ?>
                <td><?= $commande_livreur["nom_livreur"] ?></td>
                <?php else : ?>
                <td class="text-muted">Pas de livreur attribué</td>
                <?php endif; ?>


                <td>
                    <?php if ($commande_livreur['commande_statut'] !== null): ?>
                    <?php if ($commande_livreur['commande_statut'] == 'Non Livré'): ?>
                            <span class="badge badge-danger badge-lg"><?= $commande_livreur['commande_statut'] ?></span>
                    <?php else: ?>
                            <span class="badge badge-success badge-lg"><?= $commande_livreur['commande_statut'] ?></span>
                    <?php endif; ?>
                    <?php else: ?>
                        <span class="badge badge-success badge-lg">Pas de point</span>
                    <?php endif; ?>
               </td>
                <td><?= $commande_livreur['date_commande'] ?></td>
                <td class="actions">
                    <a href="update_commandes_par_livreurs.php?id=<?= $commande_livreur['commande_id'] ?>&id_user=<?= $id_user ?>" class="edit">
                        <i class="fas fa-pen fa-xs" style="font-size:24px;color:blue"></i> 
                    </a>
                    <a href="traitement_delete_commandes_par_livreurs.php?id=<?= $commande_livreur['commande_id'] ?>&id_user=<?= $id_user ?>" class="trash">
                        <i class="fas fa-pen fa-trash" style="font-size:24px;color:red"></i> 
                    </a>
                </td>
                <td>
                    <?php if ($commande_livreur['nom_livreur']) : ?>
                    <button class="btn btn-info" data-toggle="modal"
                        data-target="#change-<?= $commande_livreur['commande_id'] ?>">Changer
                        le livreur</button>
                    <?php else : ?>
                    <button class="btn btn-info" data-toggle="modal"
                        data-target="#update-<?= $commande_livreur['commande_id'] ?>">Attribuer
                        un livreur</button>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($commande_livreur['commande_statut'] == 'Livré') : ?>
                    <button class="btn btn-info" disabled>Changer le statut</button>
                    <?php else : ?>
                    <button class="btn btn-secondary" data-toggle="modal"
                        data-target="#update_statut-<?= $commande_livreur['commande_id'] ?>">Changer le statut</button>
                    <?php endif; ?>
                </td>
            </tr>
            <div class="modal" id="update-<?= $commande_livreur['commande_id'] ?>">
                <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                    <form action="traitement_commande_livreurs_update.php" method="post">
                            <input type="hidden" name="commande_id" value="<?= $commande_livreur['commande_id'] ?>">
                            <div class="form-group">
                                <label>Livreur</label>
                                <select name="livreur_id" class="form-control">
                                    <?php
                                    foreach ($rows as $row) 
                                    {
                                        echo '<option value="' . $row['id'] . '">' . $row['livreur_name'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary mr-2" name="saveCommande">Attribuer</button>
                            <button class="btn btn-light">Annuler</button>
                    </form>
                    </div>
                </div>
                </div>
            </div>

            <div class="modal" id="change-<?= $commande_livreur['commande_id'] ?>">
                <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                    <form action="traitement_livreur_commande_update.php?id=<?= $id_user ?>" method="post">
                    <!-- <form action="traitement_livreur_commande_update.php" method="post">-->

                    
                    <!-- <form class="forms-sample" method="post" action="save_commande_livreur.php?id=<?= $id_user ?>">-->
                        <input type="hidden" name="commande_id" value="<?= $commande_livreur['commande_id'] ?>">
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
            <div class="modal" id="update_statut-<?= $commande_livreur['commande_id'] ?>">
                <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                    <form action="traitement_commande_statut_livreur_update.php?id=<?= $id_user ?>" method="post">
                        <input type="hidden" name="commande_id" value="<?= $commande_livreur['commande_id'] ?>">
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

<!-- Pagination links -->
<div class="pagination-container bg-secondary d-flex justify-content-center w-100 text-white p-3">
    <?php if($page > 1): ?>
        <a href="?id=<?= $id_user ?>&page=<?= $page - 1 ?>&limit=<?= $limit ?>" class="btn btn-primary">&lt;</a>
    <?php endif; ?>

    <span><?= $page . '/' . count($commande_livreurs_pages) ?></span>

    <?php if($page < count($commande_livreurs_pages)): ?>
        <a href="?id=<?= $id_user ?>&page=<?= $page + 1 ?>&limit=<?= $limit ?>" class="btn btn-primary">&gt;</a>
    <?php endif; ?>

    <form action="" method="get" class="items-per-page-form ml-3">
        <input type="hidden" name="id" value="<?= $id_user ?>">
        <label for="limit">Afficher :</label>
        <select name="limit" id="limit" class="items-per-page-select" onchange="this.form.submit()">
            <option value="5" <?= $limit == 5 ? 'selected' : '' ?>>5</option>
            <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10</option>
            <option value="15" <?= $limit == 15 ? 'selected' : '' ?>>15</option>
        </select>
        <button type="submit" class="submit-button">Valider</button>
    </form>
</div>
  <div class="modal fade" id="add-commande">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Enregistrer une commande</h4>
        </div>
        <div class="modal-body">
          <form class="forms-sample" method="post" action="save_commande_livreur.php?id=<?= $id_user ?>">
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
              <div class="form-group">
                <label>Clients</label>
                <?php
                echo  '<select id="select" name="client_id" class="form-control">';
                while ($row = $getClientsStmt->fetch(PDO::FETCH_ASSOC)) {
                  echo '<option value="' . $row['id'] . '">' . $row['nom_boutique'] . '</option>';
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


    <div class="modal fade" id="print-commande">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Imprimer un point <strong><?php echo $aujourdhui; ?></strong></h4>
        </div>
        <div class="modal-body">
          <form action="traitement_livreurs_commandes_print.php" method="POST">
            <div class="form-group">
                <label>Livreur</label>
                <?php
                echo  '<select id="select" name="livreur_id" class="form-control">';
                while ($selection = $requete->fetch()) {
                  echo '<option value="' . $selection['utilisateur_id'] . '">' . $selection['nom_utilisateurs'] . '</option>';
                }
                echo '</select>'
                ?>
            </div>
              <div class="form-group">
                <label for="exampleInputPassword1">Date</label>
                <input type="date" class="form-control" id="exampleInputPassword1" placeholder="Selectionner date"
                  name="date">
              </div>
              <button type="submit" class="btn btn-danger mr-2">Imprimer</button>
            </div>
          </form>
        </div>
      </div>
      <!-- /.modal-content -->
    </div>


    <!-- /.modal-dialog -->
  </div>


  <!-- Modal -->
<div class="modal fade" id="modal-livreur-<?= $id_user ?>" tabindex="-1" aria-labelledby="modalLivreurLabel-<?= $id_user ?>" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLivreurLabel">Voir un point</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="form-livreur-<?= $id_user ?>" action="traitement_depot_date.php" method="POST">
                    <input type="hidden" name="id_livreur" id="id_livreur-<?= $id_user ?>" value="<?= $id_user ?>">
                    <div class="form-group">
                        <label for="start-date">Date Début</label>
                        <input type="date" class="form-control" id="start-date" name="start_date" required>
                    </div>
                    <div class="form-group">
                        <label for="end-date">Date Fin</label>
                        <input type="date" class="form-control" id="end-date" name="end_date" required>
                    </div>
                    <button type="submit" class="btn btn-danger">Imprimer</button>
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
<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_engins.php';
include('header.php');

$rows = $getLivreurs->fetchAll(PDO::FETCH_ASSOC);
?>
<style>
.table-head-gray {
    background-color:  #616a6b  /* Couleur de fond gris clair */
    color: #333; /* Couleur du texte */
    font-weight: bold; /* Police en gras */
}
</style>
<div class="row">
  <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add-engin">
    Enregistrer un engin
  </button>

  <a class="btn btn-outline-secondary" href="commandes_print.php"><i class="fa fa-print" style="font-size:24px;color:green"></i></a>
  <table style="max-height: 90vh !important; overflow-y: scroll !important" id="example1" class="table table-bordered table-striped">
    <thead class="thead-dark">
    <th>Type Engin</th>
    <th>Année Fabrication</th>
    <th>Numero Chassis</th>
    <th>Plaque d'Immatriculation</th>
    <th>Marque</th>
    <th>Date d'ajout</th>
    <th>Statut</th>
    <th>Actions</th>
    <th>Attribuer à</th>
    <th>Changer le livreur</th>
    <th>Changer le statut</th>
    <th>Géolocalisation</th>
</thead>
    <tbody>
      <?php foreach ($engins as $engin) : ?>
        <tr>
          <td>
            <?php if ($engin['type_engin'] === 'Moto') : ?>
             <i class="fas fa-motorcycle"></i>
            <?php elseif ($engin['type_engin'] === 'Voiture') : ?>
             <i class="fas fa-car"></i>
            <?php endif; ?>
          </td>
          <td><?= $engin['annee_fabrication'] ?></td>
        <td>
    <?php if ($engin['numero_chassis'] !== null) : ?>
        <span class="numero-chassis" title="Marque: <?= $engin['marque'] ?>, Couleur: <?= $engin['couleur'] ?>">
          <a href="infos_engins.php?id=<?= $engin['engin_id'] ?>">
            <b><i><?= $engin['numero_chassis'] ?></i></b>
          </a>
        </span>
    <?php else : ?>
        <span class="badge badge-pill badge-danger">Numero Chassis manquant</span>
    <?php endif; ?>
</td>

          <td><?= $engin['plaque_immatriculation'] ?></td>
          <td><?= $engin['marque'] ?></td>  
          <td><?= $engin['date_ajout'] ?></td>
          <td>
            <?php if ($engin['statut'] === 'En Utilisation') : ?>
              <span class="badge badge-pill badge-success"><?= $engin['statut'] ?></span>
            <?php elseif ($engin['statut'] === 'Pas attribuée') : ?>
              <span class="badge badge-pill badge-warning"><?= $engin['statut'] ?></span>
            <?php else : ?>
              <span class="badge badge-pill badge-danger"><?= $engin['statut'] ?></span>
            <?php endif; ?>
          </td>
          <td class="actions">
            <a href="engins_update.php?id=<?= $engin['engin_id'] ?>" class="edit"><i class="fas fa-pen fa-xs" style="font-size:24px;color:blue"></i></a>
            <a href="delete_engins.php?id=<?= $engin['engin_id'] ?>" class="trash"><i class="fas fa-trash fa-xs" style="font-size:24px;color:red"></i></a>
          </td>
          <td>
                 <img src="../dossiers_images/<?php echo $engin['avatar']; ?>" alt="Avatar" width="50" height="50" title="<?= $engin['nom_livreur'] ?>">
                </td>
          <td>
              <button class="btn btn-warning" data-toggle="modal" data-target="#update_livreur-<?= $engin['engin_id'] ?>">Changer le livreur</button>
          </td>
          <td>
              <button class="btn btn-info" data-toggle="modal" data-target="#update_statut-<?= $engin['engin_id'] ?>">Changer le statut</button>
          </td>

          <td>
              <button class="btn btn-dark" data-toggle="modal" data-target="#add_position-<?= $engin['engin_id'] ?>">Ajouter Position</button>
          </td>
        </tr>
        
        <!-- Modal pour changer le statut -->
        <div class="modal" id="update_statut-<?= $engin['engin_id'] ?>">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-body">
                <form action="traitement_engin_statut_update.php" method="post">
                  <input type="hidden" name="engin_id" value="<?= $engin['engin_id'] ?>">
                  <div class="form-group">
                    <label>Statut</label>
                    <select name="statut" class="form-control">
                      <option value="En Utilisation">En Utilisation</option>
                      <option value="En Panne">En Panne</option>
                      <option value="Pas attribuée">Pas attribuée</option>
                    </select>
                  </div>
                  <button type="submit" class="btn btn-primary mr-2" name="saveCommande">Attribuer</button>
                  <button class="btn btn-light">Annuler</button>
                </form>
              </div>
            </div>
          </div>
        </div>


        <!-- Modal pour ajouter la position -->
<div class="modal" id="add_position-<?= $engin['engin_id'] ?>">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Ajouter une Position</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <form method="POST" action="geocalisation_enregistrement.php">
          <input type="hidden" name="id_engin" value="<?= $engin['engin_id'] ?>">
          <div class="form-group">
            <label>Selectionner le livreur</label>
            <select name="utilisateur_id" class="form-control" required>
              <?php foreach ($rows as $row) {
                echo '<option value="' . $row['id'] . '">' . $row['nom_livreur'] . '</option>';
              } ?>
            </select>
          </div>
          <button type="submit" class="btn btn-primary">Soumettre</button>
        </form>
      </div>
    </div>
  </div>
</div>



        <!-- Modal pour changer le livreur -->
        <div class="modal" id="update_livreur-<?= $engin['engin_id'] ?>">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-body">
                <form action="traitement_engin_livreurs_update.php" method="post">
                  <input type="hidden" name="engin_id" value="<?= $engin['engin_id'] ?>">
                  <div class="form-group">
                    <label>Livreur</label>
                    <select name="utilisateur_id" class="form-control">
                      <?php foreach ($rows as $row) {
                        echo '<option value="' . $row['id'] . '">' . $row['nom_livreur'] . '</option>';
                      } ?>
                    </select>
                  </div>
                  <button type="submit" class="btn btn-primary mr-2" name="saveCommande">Attribuer</button>
                  <button class="btn btn-light">Annuler</button>
                </form>
              </div>
            </div>
          </div>
        </div>
        
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- Modal pour ajouter un engin -->
  <div class="modal fade" id="add-engin">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Enregistrer un engin</h4>
        </div>
        <div class="modal-body">
          <form class="forms-sample" method="post" action="save_engins.php">
            <div class="card-body">
              <div class="form-group">
                <label for="exampleInputEmail1">Année de Fabrication</label>
                <input type="text" class="form-control" id="exampleInputEmail1" placeholder="Année de Fabrication" name="annee_fabrication">
              </div>
              <div class="form-group">
                <label for="exampleInputPassword1">Plaque d'immatriculation</label>
                <input type="text" class="form-control" id="exampleInputPassword1" placeholder="Plaque d'immatriculation" name="plaque_immatriculation">
              </div>
              <div class="form-group">
                <label for="exampleInputPassword1">Couleur</label>
                <input type="text" class="form-control" id="exampleInputPassword1" placeholder="Couleur" name="couleur">
              </div>
              <div class="form-group">
                <label for="exampleInputPassword1">Marque</label>
                <input type="text" class="form-control" id="exampleInputPassword1" placeholder="Marque" name="marque">
              </div>
              <div class="form-group">
                <label>Type d'engin</label>
                <?php
                echo  '<select id="select" name="type_engin" class="form-control">';
                while ($typeEngins = $type_engins->fetch(PDO::FETCH_ASSOC)) {
                  echo '<option value="' . $typeEngins['type'] . '">' . $typeEngins['type'] . '</option>';
                }
                echo '</select>';
                ?>
              </div>
              <div class="form-group">
                <label>Livreur</label>
                <select name="utilisateur_id" class="form-control">
                  <?php foreach ($rows as $row) {
                    echo '<option value="' . $row['id'] . '">' . $row['nom_livreur'] . '</option>';
                  } ?>
                </select>
              </div>
              <button type="submit" class="btn btn-primary mr-2" name="saveCommande">Enregister</button>
              <button class="btn btn-light">Annuler</button>
            </div>
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

<script>
    $(document).ready(function () {
        $('.trash').on('click', function (e) {
            e.preventDefault();
            var link = $(this).attr('href');
            Swal.fire({
                title: 'Êtes-vous sûr(e) de vouloir supprimer ce contrat ?',
                text: "Cette action est irréversible !",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Oui, supprimer !'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = link;
                }
            });
        });
    });

    <?php
    if (isset($_SESSION['popup']) && $_SESSION['popup'] == true) {
    ?>
        var audio = new Audio("../inc/sons/notification.mp3");
        audio.volume = 1.0; // Assurez-vous que le volume n'est pas à zéro
        audio.play().then(() => {
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
    <?php
        $_SESSION['popup'] = false;
    }
    ?>

    <?php
    if (isset($_SESSION['delete_pop']) && $_SESSION['delete_pop'] == true) {
    ?>
        var Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });

        Toast.fire({
            icon: 'error',
            title: 'Action échouée.'
        });
    <?php
        $_SESSION['delete_pop'] = false;
    }
    ?>
</script>

<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<!--<script src="dist/js/pages/dashboard.js"></script>-->
</body>
</html>

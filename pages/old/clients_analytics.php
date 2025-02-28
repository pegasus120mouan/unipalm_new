<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requetes_selection_boutique.php';

include('header.php');

// Définir la locale en français
setlocale(LC_TIME, 'fr_FR.UTF-8', 'fra');

$stmt = $conn->prepare("SELECT
    b.id AS id_boutique,
    b.nom AS nom_boutique,
    b.logo AS logo_boutique,
    MONTHNAME(MIN(c.date_commande)) AS nom_mois,
    SUM(c.cout_reel) AS somme_cout_reel,
    SUM(c.cout_livraison) AS somme_cout_livraison,
    COUNT(c.id) AS total_commandes,
    SUM(c.statut = 'livré') AS commandes_livre,
    SUM(c.statut = 'non livré') AS commandes_non_livre
FROM
    utilisateurs u
JOIN
    boutiques b ON u.boutique_id = b.id
JOIN
    commandes c ON u.id = c.utilisateur_id
WHERE
    MONTH(c.date_commande) = MONTH(CURDATE()) - 1
    AND YEAR(c.date_commande) = YEAR(CURDATE())
GROUP BY
    b.id, b.nom, YEAR(c.date_commande), MONTH(c.date_commande)
ORDER BY
    somme_cout_reel DESC");

$stmt->execute();
$statistiques_clients = $stmt->fetchAll();
?>

<!-- Main row -->
<style>
.spacing {
    margin-right: 10px; /* Ajustez cette valeur selon vos besoins */
}
</style>

<div class="row">
<button type="button" class="btn btn-secondary spacing" data-toggle="modal" data-target="#vue_stats_clients">
    Statistiques clients
  </button>

 <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#vue_stats">
    Statistiques par mois
  </button>
    <table id="example1" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Avatar</th>
                <th>Nom de la boutique</th>
                <th>Mois</th>
                <th>Montant transaction</th>
                <th>Montant Obtenu</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($statistiques_clients as $statistiques_client) : ?>
                <tr>
                    <td>
                        <a href="client_profile.php?id=<?=$statistiques_client['id_boutique']?>" class="edit"><img src="../dossiers_images/<?php echo $statistiques_client['logo_boutique']; ?>" alt="Logo" width="50" height="50"></a>
                    </td>
                    <td><?=$statistiques_client['nom_boutique']?></td>
                    <td><?= ucfirst(strftime('%B', strtotime($statistiques_client['nom_mois']))) ?></td>
                    <td style="background-color: green">
                        <strong><?=$statistiques_client['somme_cout_reel']?></strong>
                    </td>
                    <td><?=$statistiques_client['somme_cout_livraison']?></td>
                    <td class="actions">
                        <button class="btn btn-info" data-toggle="modal" data-target="#update-<?= $statistiques_client['id_boutique'] ?>">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>
                <div class="modal" id="update-<?= $statistiques_client['id_boutique'] ?>">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-body">
                                <h3>Statistiques des livraisons</h3>
                                <p><i><u>Nom du partenaire:</u></i> <strong><?php echo $statistiques_client['nom_boutique']; ?></strong></p>
                                <p><i><u>Mois:</u></i> <strong><?php echo ucfirst(strftime('%B', strtotime($statistiques_client['nom_mois']))); ?></strong></p>

                                <section class="content">
                                    <div class="container-fluid">
                                        <!-- Small boxes (Stat box) -->
                                        <div class="row">
                                            <div class="col-lg-3 col-6">
                                                <div class="small-box bg-info">
                                                    <div class="inner">
                                                        <h3><?php echo $statistiques_client['somme_cout_reel']; ?><span class="right badge badge-dark">CFA</span></h3>
                                                        <p>Montant Global</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-3 col-6">
                                                <div class="small-box bg-success">
                                                    <div class="inner">
                                                        <h3><?php echo $statistiques_client['total_commandes']; ?></h3>
                                                        <p>Nbre de colis reçu</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-3 col-6">
                                                <div class="small-box bg-warning">
                                                    <div class="inner">
                                                        <h3><?php echo $statistiques_client['commandes_livre']; ?></h3>
                                                        <p>Nbre de colis livré</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-3 col-6">
                                                <div class="small-box bg-danger">
                                                    <div class="inner">
                                                        <h3><?php echo $statistiques_client['commandes_non_livre']; ?></h3>
                                                        <p>Nbre de colis non livré</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="modal fade" id="vue_stats_clients">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <form action="traitement_depot_clients.php" method="POST">
    <div class="form-group row">
      <label for="client" class="col-4 col-form-label">Nom boutique</label>
      <div class="form-group">
        <select name="client" class="form-control">
          <?php
          while ($selection = $stmt_select_boutique->fetch()) {
            echo '<option value="' . $selection['nom_boutique'] . '">' . $selection['nom_boutique'] . '</option>';
          }
          ?></select>

      </div>
    </div>
    <div class="form-group row">
      <label for="date_debut" class="col-4 col-form-label">Date début</label>
      <div class="col-8">
        <div class="input-group">
          <div class="input-group-prepend">
            <div class="input-group-text">
              <i class="fa fa-calendar"></i>
            </div>
          </div>
          <input id="date" name="date_debut" type="date" class="form-control">
        </div>
      </div>
    </div>

    <div class="form-group row">
      <label for="date_fin" class="col-4 col-form-label">Date Fin</label>
      <div class="col-8">
        <div class="input-group">
          <div class="input-group-prepend">
            <div class="input-group-text">
              <i class="fa fa-calendar"></i>
            </div>
          </div>
          <input id="date" name="date_fin" type="date" class="form-control">
        </div>
      </div>
    </div>


    <div class="form-group row">
      <div class="offset-4 col-8">
        <button type="submit" class="btn btn-warning btn-rounded btn-fw">Imprimer</button>
      </div>

    </div>
  </form>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>



    <div class="modal fade" id="vue_stats">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <form class="forms-sample" method="post" action="statisques_dates.php">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Mois Début</label>
                                  <select name="month_start" class="form-control">
                                                <option value="01">Janvier</option>
                                                <option value="02">Février</option>
                                                <option value="03">Mars</option>
                                                <option value="04">Avril</option>
                                                <option value="05">Mai</option>
                                                <option value="06">Juin</option>
                                                <option value="07">Juillet</option>
                                                <option value="08">Aout</option>
                                                <option value="09">Septembre</option>
                                                <option value="10">Octobre</option>
                                                <option value="11">Novembre/option>
                                                <option value="12">Décembre</option>
                                  </select>
                            </div>
                            <div class="form-group">
                                <label for="exampleInputEmail1">Mois Fin</label>
                                  <select name="month_end" class="form-control">
                                                <option value="01">Janvier</option>
                                                <option value="02">Février</option>
                                                <option value="03">Mars</option>
                                                <option value="04">Avril</option>
                                                <option value="05">Mai</option>
                                                <option value="06">Juin</option>
                                                <option value="07">Juillet</option>
                                                <option value="08">Aout</option>
                                                <option value="09">Septembre</option>
                                                <option value="10">Octobre</option>
                                                <option value="11">Novembre/option>
                                                <option value="12">Decembre</option>
                                  </select>
                            </div>
                                  <div class="form-group">
                                    <select name="client" class="form-control">
                                      <?php
                                      while ($selection_mois = $stmt_select_boutique_mois->fetch()) {
                                        echo '<option value="' . $selection_mois['nom_boutique'] . '">' . $selection_mois['nom_boutique'] . '</option>';
                                      }
                                      ?></select>

                                  </div>
                            <input type="submit" class="btn btn-primary mr-2" value="Rechercher">
                        </div>
                    </form>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- /.row (main row) -->
</div><!-- /.container-fluid -->
<!-- /.content -->
</div>
<!-- /.content-wrapper -->
<aside class="control-sidebar control-sidebar-dark">
</aside>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="../../plugins/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="../../plugins/jquery-ui/jquery-ui.min.js"></script>
<!-- Bootstrap 4 -->
<script src="../../plugins/sweetalert2/sweetalert2.min.js"></script>
<script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
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
</body>
</html>

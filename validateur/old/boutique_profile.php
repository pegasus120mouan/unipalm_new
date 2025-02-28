<?php
//require_once '../inc/functions/connexion.php';
require_once '../inc/functions/connexion.php';
include('header.php');

// Récupération de l'ID de la commande depuis l'URL (par exemple, edit_commande.php?id=1)
$id_boutique = $_GET['id'];

// Requête pour récupérer les anciennes valeurs de la commande
/*$sql = "SELECT id, nom AS nom_boutique, 
logo AS logo_boutique, 
type_articles AS type_articles 
FROM boutiques
WHERE id = :id_boutique";
 $requete = $conn->prepare($sql);
 $requete->bindParam(':id_boutique', $id_boutique, PDO::PARAM_INT);
 $requete->execute();
 $vueBoutique= $requete->fetch(PDO::FETCH_ASSOC);*/


$sql = "SELECT utilisateurs.*, 
boutiques.nom AS nom_boutique,
boutiques.type_articles AS type_articles,
boutiques.logo AS logo_boutique
FROM utilisateurs
LEFT JOIN boutiques ON utilisateurs.boutique_id = boutiques.id
WHERE boutiques.id=:id_boutique";
 $requete = $conn->prepare($sql);
 $requete->bindParam(':id_boutique', $id_boutique, PDO::PARAM_INT);
 $requete->execute();
 $vueBoutique= $requete->fetch(PDO::FETCH_ASSOC);




?>
<!-- Content Header (Page header) -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Profile de <?php echo $vueBoutique['nom_boutique']; ?>
        </h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="#">Boutique</a></li>
          <li class="breadcrumb-item active"><?php echo $vueBoutique['nom_boutique']; ?></li>
        </ol>
      </div>
    </div>
  </div><!-- /.container-fluid -->
</section>

<!-- Main content -->
<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-3">

        <!-- Profile Image -->



        <div class="card card-primary card-outline">
          <div class="card-body box-profile">
            <div class="text-center">
              <img class="profile-user-img img-fluid img-circle" src="../dossiers_images/<?php echo $vueBoutique['logo_boutique']; ?> ">

            </div>

            <h3 class="profile-username text-center">
              <form action="traitement_boutiques_images.php" method="post" enctype="multipart/form-data">

                <input type="hidden" name="id" value="<?php echo $vueBoutique['boutique_id']; ?>">

                <input type="file" class="form-control" name="photo" accept="image/*">
                <input type="submit" class=" btn btn-info" value="Modifier mon image de profil">
              </form>

            </h3>


<?php $typeArticles = ($vueBoutique['type_articles'] !== null) ? $vueBoutique['type_articles'] : "Non défini"; ?>
<p class="text-center">
    <span class="badge badge-pill badge-danger"><?php echo $typeArticles; ?></span>
</p>

            <ul class="list-group list-group-unbordered mb-3">
              <li class="list-group-item">
                <b>Nom du gérant(e)</b> <a class="float-right"><?php echo $vueBoutique['nom']; ?></a>
              </li>
              <li class="list-group-item">
                <b>Prenoms du gérant(e)</b> <a class="float-right"><?php echo $vueBoutique['prenoms']; ?></a>
              </li>
              <li class="list-group-item">
                <b>Contact</b> <a class="float-right"><?php echo $vueBoutique['contact']; ?></a>
              </li>
            </ul>

            <a href="#" class="btn btn-primary btn-block"><b>Statistiques</b></a>
          </div>
          <!-- /.card-body -->
        </div>
        <!-- /.card -->

        <!-- About Me Box -->

        <!-- /.card -->
      </div>
      <!-- /.col -->
      <div class="col-md-9">
        <div class="card">
          <div class="card-header p-2">
            <ul class="nav nav-pills">
              <li class="nav-item"><a class="nav-link active" href="#activity" data-toggle="tab">Modifier nom boutique</a>
              </li>
              </li>
            </ul>
          </div><!-- /.card-header -->
          <div class="card-body">
            <div class="tab-content">
              <div class="active tab-pane" id="activity">
                <!-- Post -->
                <form class="form-horizontal" method="post" action="enregistrement/save_boutique_profile.php">



                  <div class="form-group row">
                  <input type="hidden" name="id" value="<?php echo $vueBoutique['boutique_id']; ?>">

                    <label for="inputName" class="col-sm-2 col-form-label">Nom de la boutique</label>
                    <div class="col-sm-10">
                      <input type="text" class="form-control" id="inputName" name="nom_boutique" value="<?php echo $vueBoutique['nom_boutique']; ?>">
                    </div>
                  </div>
                  <div class="form-group row">
                    <label for="inputEmail" class="col-sm-2 col-form-label">Type d'articles</label>
                    <div class="col-sm-10">
                     <input type="text" class="form-control" id="inputEmail" name="type_articles" value="<?php echo ($vueBoutique['type_articles'] !== null) ? $vueBoutique['type_articles'] : 'Non défini'; ?>">
                    </div>
                  </div>
                  <div class="form-group row">
                  </div>
                  <div class="form-group row">
                    <div class="offset-sm-2 col-sm-10">
                      <button type="submit" class="btn btn-info">Modifier info sur boutique</button>
                    </div>
                  </div>
                </form>
                <!-- /.post -->

                <!-- Post -->

                <!-- /.post -->

                <!-- Post -->

                <!-- /.post -->
              </div>
              <!-- /.tab-pane -->
              <div class="tab-pane" id="timeline">
                <!-- The timeline -->
                <div class="timeline timeline-inverse">
                  <!-- timeline time label -->
                  <div class="time-label">
                    <span class="bg-danger">
                      Information boutique
                    </span>
                  </div>
                  <!-- /.timeline-label -->
                  <!-- timeline item -->
                  <div>
                    <div class="timeline-item">
                      <form class="form-horizontal" method="post" action="enregistrement/save_update_profile.php">



                        <div class="form-group row">
                          <label for="inputName" class="col-sm-2 col-form-label">Nom boutique</label>
                          <div class="col-sm-10">
                            <input type="text" class="form-control" id="inputName" name="boutique_nom" value="<?php echo $vueClient['boutique_nom']; ?>">
                          </div>
                        </div>

                        <div class="form-group row">
                          <div class="offset-sm-2 col-sm-10">
                            <button type="submit" class="btn btn-info">Modifier mon profil</button>
                          </div>
                        </div>
                      </form>




                    </div>
                  </div>
                  <!-- END timeline item -->
                  <!-- timeline item -->

                  <!-- END timeline item -->
                  <!-- timeline item -->

                  <!-- END timeline item -->
                  <!-- timeline time label -->

                  <!-- /.timeline-label -->
                  <!-- timeline item -->

                  <!-- END timeline item -->

                </div>
              </div>
              <!-- /.tab-pane -->

              <div class="tab-pane" id="settings">
                <form class="form-horizontal" method="post" action="enregistrement/save_utilisateur_update_password.php">
                                    <input type="hidden" name="id" value="<?php echo $vueUtilisateur['id']; ?>">

                  <div class="form-group row">
                    <label for="inputName" class="col-sm-2 col-form-label">Ancien mot de passe</label>
                    <div class="col-sm-10">
                      <input type="password" class="form-control" id="inputName" name="old_password" placeholder="Entrez votre Ancien mot de passe">
                    </div>
                  </div>
                  <div class="form-group row">
                    <label for="inputEmail" class="col-sm-2 col-form-label">Nouveau mot de passe</label>
                    <div class="col-sm-10">
                      <input type="password" class="form-control" id="inputEmail" name="new_password" placeholder="Entrez votre nouveau mot de passe">
                    </div>
                  </div>
                  <div class="form-group row">
                    <label for="inputName2" class="col-sm-2 col-form-label">Confirmer mot de passe</label>
                    <div class="col-sm-10">
                      <input type="password" class="form-control" id="inputName2" name="check_password" placeholder="Confirmer votre nouveau mot de passe">
                    </div>
                  </div>
                  <div class="form-group row">
                    <div class="offset-sm-2 col-sm-10">
                      <button type="submit" class="btn btn-danger">Modifier votre mot de passe</button>
                    </div>
                  </div>
                </form>
              </div>
              <!-- /.tab-pane -->
            </div>
            <!-- /.tab-content -->
          </div><!-- /.card-body -->
        </div>
        <!-- /.card -->
      </div>
      <!-- /.col -->
    </div>
    <!-- /.row -->
  </div><!-- /.container-fluid -->
</section>
<!-- /.content -->
</div>
<!-- /.content-wrapper -->


<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-dark">
  <!-- Control sidebar content goes here -->
</aside>
<!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="../../plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="../../dist/js/adminlte.min.js"></script>
<!-- AdminLTE for demo purposes -->
<!--<script src="../../dist/js/demo.js"></script>-->
</body>

</html>
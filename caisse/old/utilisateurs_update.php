<!DOCTYPE html>
<html>
<head>
    <title>Édition du client</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../../plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Tempusdominus Bootstrap 4 -->
  <link rel="stylesheet" href="../../plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="../../plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- JQVMap -->
  <link rel="stylesheet" href="../../plugins/jqvmap/jqvmap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../../dist/css/adminlte.min.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="../../plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="../../plugins/daterangepicker/daterangepicker.css">
  <!-- summernote -->
  <link rel="stylesheet" href="../../plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">
  <link rel="stylesheet" href="../../plugins/summernote/summernote-bs4.min.css">
</head>
<body>
    
    <?php
    // Connexion à la base de données (à adapter avec vos informations)
    require_once '../inc/functions/connexion.php';
    include('header.php');

    // Récupération de l'ID de la commande depuis l'URL (par exemple, edit_commande.php?id=1)
    $id_utilisateur = $_GET['id'];

    // Requête pour récupérer les anciennes valeurs de la commande
    $sql = "SELECT * FROM utilisateurs WHERE utilisateur_id = :id_utilisateur";
    $requete = $conn->prepare($sql);
    $requete->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
    $requete->execute();
    $utilisateurs_modif = $requete->fetch(PDO::FETCH_ASSOC);
    ?>
  <form class="forms-sample" method="post" action="traitement_update_utilisateur.php">
<div class="form-group">
                                                <label for="exampleInputName1"></label>
                                                <input type="hidden" class="form-control" id="exampleInputName1"
                                                    placeholder="id" name="id" value="<?php echo $utilisateurs_modif['utilisateur_id']; ?>">
                                            </div>
                                            <div class="form-group">
                                                <label for="exampleInputName1">Nom </label>
                                                <input type="text" class="form-control" id="exampleInputName1"
                                                    placeholder="Nom " name="nom" value="<?php echo $utilisateurs_modif['nom']; ?>">
                                            </div>
                                            <div class="form-group">
                                                <label for="exampleInputEmail3">Prenom</label>
                                                <input type="text" class="form-control" id="exampleInputEmail3"
                                                    placeholder="Prenom" name="prenoms" value="<?php echo $utilisateurs_modif['prenom']; ?>">
                                            </div>
                                            <div class="form-group">
                                                <label for="exampleInputPassword4">Email</label>
                                                <input type="text" class="form-control" id="exampleInputPassword4"
                                                    placeholder="Email" name="email" value="<?php echo $utilisateurs_modif['email']; ?>">
                                            </div>
                                            <div class="form-group">
                                                <label for="exampleInputCity1">Contact</label>
                                                <input type="text" class="form-control" id="exampleInputCity1"
                                                    placeholder="Contact" name="contact" value="<?php echo $utilisateurs_modif['contact']; ?>">
                                            </div>
                                           
                                            
                                            
                
                                            <button type="submit" class="btn btn-success mr-2" name="updateClient">Enregister</button>
                                            <button class="btn btn-light">Annuler</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                </div>
                </form>
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
        title: 'Action effectuée avec succès.'
      })
</script>

<?php 
  $_SESSION['popup'] = false;
}
  ?>
</body>
</html>

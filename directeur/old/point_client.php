<?php
include('header.php');
 //require_once '../inc/functions/connexion.php';
require('../fpdf/fpdf.php');
require_once '../inc/functions/requete/requetes_selection_boutique.php'; 
//session_start(); 

if(isset($_POST['imprimerBtn'])) {
  $client = $_POST['client'];
  $date = $_POST['date'];
  $statut = $_POST['statut'];


/* Mes requetes */
  // Préparation de la requête SQL avec une variable
  $requete1 = $conn->prepare("
  SELECT communes, cout_reel, statut FROM commandes
  JOIN utilisateurs ON commandes.utilisateur_id = utilisateurs.id
  WHERE utilisateurs.nom = :nom_utilisateur AND commandes.date_commande = :date_commande"
);

  // Liaison de la variable avec le paramètre de la requête
  $requete1->bindParam(':client', $client, PDO::PARAM_STR);
  $requete1->bindParam(':date', $date, PDO::PARAM_STR);
  $requete1->bindParam(':statut', $statut, PDO::PARAM_STR);

  // Exécution de la requête
  $requete1->execute();

  // Récupération du résultat
  $resultat1 = $requete1->fetch(PDO::FETCH_ASSOC);

  if($resultat1)
  {
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(40, 10, "Informations de l'élève : " . $resultat1['communes']);
    $pdf->Ln(10);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(40, 10, "Prénom : " . $resultat1['cout_reel']);
    $pdf->Ln(10);
    $pdf->Cell(40, 10, "Âge : " . $resultat1['statut']);
    // Vous pouvez ajouter d'autres informations de l'élève ici

    // Générer le fichier PDF et le sauvegarder sur le serveur
    $pdf->Output('fiche_eleve.pdf', 'F'); 
  }


 }
?>

<h2><span class="badge bg-dark">Bilan Details</span></h2>
<div class="row">
 
<div class="col-lg-3 col-6">
            <!-- small box -->



<form action="client_print.php" method="POST">
  <div class="form-group row">
    <label for="client" class="col-4 col-form-label">Select</label> 
    <div class="form-group">
                            <select  name="client" class="form-control">
                            <?php
                                while ($selection = $stmt_select_boutique->fetch()) 
                              {
                              echo '<option value="' . $selection['nom_boutique'] . '">' . $selection['nom_boutique'] . '</option>';
                              }
                            ?></select>
</div>
  </div>
<div class="form-group row">
    <label for="date" class="col-4 col-form-label">Date</label> 
    <div class="col-8">
      <div class="input-group">
        <div class="input-group-prepend">
          <div class="input-group-text">
            <i class="fa fa-calendar"></i>
          </div>
        </div> 
        <input id="date" name="date" type="date" class="form-control">
      </div>
    </div>
  </div>
  <div class="form-group row">
    <div class="offset-4 col-8">
      <button type="submit" class="btn btn-warning btn-rounded btn-fw">Imprimer</button>
    </div>
    
  </div>
</form>


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

</body>

</html>
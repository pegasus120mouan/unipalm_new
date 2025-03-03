<?php
require_once '../inc/functions/connexion.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Récupérer les informations du chef d'équipe
    $stmt = $conn->prepare("SELECT * FROM chef_equipe WHERE id_chef = ?");
    $stmt->execute([$id]);
    $chef = $stmt->fetch();
    
    if (!$chef) {
        $_SESSION['popup'] = true;
        $_SESSION['message'] = "Chef d'équipe non trouvé !";
        $_SESSION['status'] = "error";
        header('Location: chef_equipe.php');
        exit;
    }
} else {
    header('Location: chef_equipe.php');
    exit;
}

include('header.php');
?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Modifier le chef d'équipe</h3>
        </div>
        <div class="card-body">
            <form action="traitement_chef_equipe_update.php" method="POST">
                <input type="hidden" name="id" value="<?= $chef['id_chef'] ?>">
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" name="nom" id="nom" class="form-control" value="<?= htmlspecialchars($chef['nom']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="prenoms">Prénoms</label>
                    <input type="text" name="prenoms" id="prenoms" class="form-control" value="<?= htmlspecialchars($chef['prenoms']) ?>" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                    <a href="chef_equipe.php" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

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

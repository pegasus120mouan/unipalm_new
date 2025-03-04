<?php
require_once '../inc/functions/connexion.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Récupérer les informations de l'utilisateur
    $stmt = $conn->prepare("SELECT * FROM utilisateurs WHERE id = ?");
    $stmt->execute([$id]);
    $utilisateur = $stmt->fetch();
    
    if (!$utilisateur) {
        $_SESSION['popup'] = true;
        $_SESSION['message'] = "Utilisateur non trouvé !";
        $_SESSION['status'] = "error";
        header('Location: utilisateurs.php');
        exit;
    }
} else {
    header('Location: utilisateurs.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $prenoms = $_POST['prenoms'];
    $contact = $_POST['contact'];
    $login = $_POST['login'];
    $role = $_POST['role'];
    
    try {
        $stmt = $conn->prepare("UPDATE utilisateurs SET nom = ?, prenoms = ?, contact = ?, login = ?, role = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$nom, $prenoms, $contact, $login, $role, $id]);
        
        $_SESSION['popup'] = true;
        $_SESSION['message'] = "L'utilisateur a été mis à jour avec succès !";
        $_SESSION['status'] = "success";
        header('Location: utilisateurs.php');
        exit;
        
    } catch(PDOException $e) {
        $_SESSION['popup'] = true;
        $_SESSION['message'] = "Erreur lors de la mise à jour : " . $e->getMessage();
        $_SESSION['status'] = "error";
    }
}

// Inclure le header après toutes les redirections potentielles
include('header.php');
?>

<div class="container mt-4">
    <h2>Modifier l'utilisateur</h2>
    <form action="" method="POST">
        <div class="form-group">
            <label for="nom">Nom</label>
            <input type="text" class="form-control" id="nom" name="nom" value="<?= htmlspecialchars($utilisateur['nom']) ?>" required>
        </div>
        
        <div class="form-group">
            <label for="prenoms">Prénoms</label>
            <input type="text" class="form-control" id="prenoms" name="prenoms" value="<?= htmlspecialchars($utilisateur['prenoms']) ?>" required>
        </div>
        
        <div class="form-group">
            <label for="contact">Contact</label>
            <input type="text" class="form-control" id="contact" name="contact" value="<?= htmlspecialchars($utilisateur['contact']) ?>" required>
        </div>
        
        <div class="form-group">
            <label>Login</label>
            <input type="text" name="login" class="form-control" value="<?= htmlspecialchars($utilisateur['login']) ?>" readonly style="background-color: #e9ecef;">
        </div>
        
        <div class="form-group">
            <label for="role">Rôle</label>
            <select class="form-control" id="role" name="role" required>
                <option value="admin" <?= $utilisateur['role'] == 'admin' ? 'selected' : '' ?>>Administrateur</option>
                <option value="operateur" <?= $utilisateur['role'] == 'operateur' ? 'selected' : '' ?>>Opérateur</option>
                <option value="validateur" <?= $utilisateur['role'] == 'validateur' ? 'selected' : '' ?>>Validateur</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Date de création: <?= date('d/m/Y', strtotime($utilisateur['created_at'])) ?></label>
        </div>
        
        <?php if ($utilisateur['updated_at']): ?>
        <div class="form-group">
            <label>Dernière modification: <?= date('d/m/Y', strtotime($utilisateur['updated_at'])) ?></label>
        </div>
        <?php endif; ?>
        
        <button type="submit" class="btn btn-primary">Mettre à jour</button>
        <a href="utilisateurs.php" class="btn btn-secondary">Annuler</a>
    </form>
</div>

<!-- ./wrapper -->
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
<script>
    $(document).ready(function() {
        // Pour l'ajout d'utilisateur
        $('#add-client form').on('submit', function() {
            $('#loadingModal').modal('show');
        });

        // Pour la suppression
        $('.trash').on('click', function(e) {
            e.preventDefault();
            var deleteUrl = $(this).attr('href');
            
            if(confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) {
                $('#loadingModal').modal('show');
                setTimeout(function() {
                    window.location.href = deleteUrl;
                }, 1000);
            }
        });
    });
</script>
<script>
  function showLoading() {
    $('#loadingModal').modal('show');
  }
</script>
<script>
function confirmDelete(id) {
    if(confirm('Voulez-vous vraiment supprimer cet utilisateur ?')) {
        window.location.href = 'delete_utilisateurs.php?id=' + id;
    }
    return false;
}
</script>
</body>
</html>

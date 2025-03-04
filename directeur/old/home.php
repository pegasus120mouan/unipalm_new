 
<?php
require_once '../inc/functions/connexion.php';
 include('header.php');
                      //  $a=1;
$stmt = $conn->prepare("SELECT * FROM users");
$stmt->execute();
$users = $stmt->fetchAll();
foreach($users as $user)
                        
?>
<div class="row">
	<a href="create.php" class="create-contact">Create Contact</a>
  <table id="example2" class="table table-bordered table-hover">
        <thead>
            <tr>
                <td>#</td>
                <td>Nom</td>
                <td>Prenoms</td>
                <td>Email</td>
                <td>Login</td>
                <td>Contacts</td>
                <td>Actions</td>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?=$user['id']?></td>
                <td><?=$user['nom']?></td>
                <td><?=$user['prenoms']?></td>
                <td><?=$user['email']?></td>
                <td><?=$user['login']?></td>
                <td><?=$user['contact']?></td>
                <td class="actions">
                    <a href="update.php?id=<?=$user['id']?>" class="edit"><i class="fas fa-pen fa-xs"></i></a>
                    <a href="delete.php?id=<?=$user['id']?>" class="trash"><i class="fas fa-trash fa-xs"></i></a>
        </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
	
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="plugins/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="plugins/jquery-ui/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<!--<script>
  $.widget.bridge('uibutton', $.ui.button)
</script>-->
<!-- Bootstrap 4 -->
<script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- ChartJS -->
<script src="../..plugins/chart.js/Chart.min.js"></script>
<!-- Sparkline -->
<script src="../..plugins/sparklines/sparkline.js"></script>
<!-- JQVMap -->
<script src="../..plugins/jqvmap/jquery.vmap.min.js"></script>
<script src="../..plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
<!-- jQuery Knob Chart -->
<script src="../..plugins/jquery-knob/jquery.knob.min.js"></script>
<!-- daterangepicker -->
<script src="../..plugins/moment/moment.min.js"></script>
<script src="../..plugins/daterangepicker/daterangepicker.js"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="../..plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- Summernote -->
<script src="../..plugins/summernote/summernote-bs4.min.js"></script>
<!-- overlayScrollbars -->
<script src="../..plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- AdminLTE App -->
<!-- AdminLTE for demo purposes -->
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
</body>
</html>

<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 01/07/2019
 * Time: 10:23
 */

require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_utilisateurs.php';
include('header.php');

$utilisateurs = [];
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $utilisateurs = searchUtilisateursByLogin($conn, $_GET['search']);
} else {
    $utilisateurs = getUtilisateurs($conn);
}
?>

<style>
    .block-container {
        background-color: #d7dbdd;
        padding: 20px;
        border-radius: 5px;
        width: 100%;
        margin-bottom: 20px;
    }

    /* Style pour l'écran de chargement */
    .loading-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.8);
        z-index: 9999;
    }

    .loading-content {
        position: absolute;
        top: 50%;
        left: 50%;    
        transform: translate(-50%, -50%);
        text-align: center;
    }     

    .loading-spinner {
        border: 5px solid #f3f3f3;
        border-radius: 50%;
        border-top: 5px solid #3498db;
        width: 50px;
        height: 50px;
        animation: spin 1s linear infinite;
        margin: 0 auto 20px;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    @media print {
        .no-print {
            display: none !important;
        }
        .content-wrapper {
            margin-left: 0 !important;
        }
        .main-header,
        .main-sidebar,
        .main-footer {
            display: none !important;
        }
        .actions, 
        .btn,
        form[method="post"] {
            display: none !important;
        }
        table {
            width: 100% !important;
        }
        .print-header {
            display: block !important;
            text-align: center;
            margin-bottom: 20px;
        }
        .print-header img {
            width: 150px;
            height: auto;
        }
        .print-header h1 {
            color: #008000;
            margin: 10px 0;
            font-size: 24px;
            font-weight: bold;
        }
        .print-header h2 {
            color: #90EE90;
            font-size: 18px;
            margin-bottom: 20px;
        }
    }
</style>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <h4 class="mt-2">Traitement en cours...</h4>
            </div>
        </div>
    </div>
</div>

<!-- En-tête pour l'impression -->
<div class="print-header d-none">
    <img src="../dossiers_images/logo.png" alt="UNIPALM COOP - CA">
    <h1>UNIPALM COOP - CA</h1>
    <h2>Société Coopérative Agricole Unie pour le Palmier</h2>
</div>

<!-- Content Wrapper. Contains page content -->
        <!-- Main row -->
        <div class="row">

            <div class="block-container">
                <button type="button" class="btn btn-primary no-print" data-toggle="modal" data-target="#add-client">
                    <i class="fa fa-edit"></i> Enregistrer un utilisateur
                </button>

                <button type="button" class="btn btn-danger" onclick="window.location.href='export_utilisateurs.php'">
                    <i class="fa fa-print"></i> Exporter la liste des utilisateurs
                </button>

                <button type="button" class="btn btn-info no-print" onclick="window.location.href='impression_utilisateurs.php'">
              <i class="fa fa-print"></i> Imprimer la liste des utilisateurs
             </button>
            </div>

        <div class="container-fluid">
            <!-- Barre de recherche -->
            <div class="row mb-3">
                <div class="col-md-6 offset-md-6">
                    <form action="" method="GET" class="form-inline justify-content-end">
                        <div class="input-group" style="max-width: 300px;">
                            <input type="text" name="search" class="form-control" placeholder="Rechercher par login..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Rechercher
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tableau des utilisateurs -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="text-center d-none d-print-block">Liste des Utilisateurs</h3>
                            <p class="text-center d-none d-print-block">Date d'impression: <?= date('d/m/Y H:i') ?></p>
                            <table id="example1" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th>Prenoms</th>
                                        <th>Contact</th>
                                        <th>Rôle</th>
                                        <th>Login</th>
                                        <th>Avatar</th>
                                        
                                        <th>Actions</th>
                                        <th>Statut compte </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($utilisateurs as $utilisateur): ?>
                                    <tr>
                                        
                                        <td><?=$utilisateur['nom']?></td>

                                        <td><?=$utilisateur['prenoms']?></td>

                                        <td><?=$utilisateur['contact']?></td>
                                        <td><?=$utilisateur['role']?></td>

                                        <td>
                                            <?php if ($utilisateur['statut_compte'] == 0): ?>
                                                <button class="btn btn-dark btn-block" disabled>
                                                    <?= $utilisateur['login'] ?>
                                                </button>
                                            <?php else: ?>
                                                <a class="btn btn-dark btn-block" href="commandes_livreurs.php?id=<?= $utilisateur['id'] ?>">
                                                    <?= $utilisateur['login'] ?>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="utilisateurs_profile.php?id=<?=$utilisateur['id']?>" class="edit"><img src="../dossiers_images/<?php echo $utilisateur['avatar']; ?>" alt="Logo" width="50" height="50"> </a>
                                        </td>
                                        <td class="actions">
                                            <a href="utilisateurs_update.php?id=<?=$utilisateur['id']?>" class="edit"><i class="fas fa-pen fa-xs" style="font-size:24px;color:blue"></i></a>
                                            <a href="#" onclick="confirmDelete(<?= $utilisateur['id'] ?>)" class="trash">
                                                <i class="fas fa-trash fa-xs" style="font-size:24px;color:red"></i>
                                            </a>
                                        

                                        <td>
                                            <form method="post" action="traitement_statut_compte.php">
                                                <input type="hidden" name="user_id" value="<?=$utilisateur['id']?>">
                                                <input type="hidden" name="statut_compte" value="<?=($utilisateur['statut_compte'] == 0) ? 1 : 0 ?>">
                                                <input type="checkbox" name="statut_compte_display" data-toggle="toggle" data-on="Actif" data-off="Inactif" data-onstyle="success" data-offstyle="danger" <?=($utilisateur['statut_compte'] == 1) ? 'checked' : ''?> onchange="submitForm(this)">
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="add-client">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Enregistrer un utilisateur</h4>
                    </div>
                    <div class="modal-body">
                        <form class="forms-sample" method="post" action="traitement_utilisateurs.php">
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Nom</label>
                                    <input type="text" class="form-control" id="exampleInputEmail1" placeholder="Nom" name="nom">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail3">Prenoms</label>
                                    <input type="text" class="form-control" id="exampleInputEmail3"
                                        placeholder="Prenom" name="prenoms">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputCity1">Contact</label>
                                    <input type="text" class="form-control" id="exampleInputCity1" placeholder="Contact" name="contact">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputCity1">Login</label>
                                    <input type="text" class="form-control" id="exampleInputCity1"
                                        placeholder="Login" name="login">
                                </div>

                                <div class="form-group">
                                    <label for="exampleInputPassword4">Password</label>
                                    <input type="password" class="form-control" id="exampleInputPassword4"
                                        placeholder="Password" name="password">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputCity1">Confirmation Password</label>
                                    <input type="password" class="form-control" id="exampleInputCity1"
                                        placeholder="Confirmation Password" name="retype_password">
                                </div>

                                <div class="form-group">
                                    <label>Selection rôle</label>
                                    <select id="select" name="role" class="form-control">
                                        <option value="admin">Administrateur</option>
                                        <option value="operateur">Opérateur</option>
                                        <option value="validateur">Validateur</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-primary mr-2" name="signup">Enregister</button>
                                <button class="btn btn-light">Annuler</button>
                            </div>
                        </form>
                    </div>
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
        <script src="../../plugins/sweetalert2/sweetalert2.min.js"></script>
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
          function showLoading() {
            $('#loadingModal').modal('show');
          }
        </script>
  <script>
    function confirmDelete(id) {
    Swal.fire({
        title: 'Êtes-vous sûr ?',
        text: "Voulez-vous vraiment supprimer cet utilisateur ?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'traitement_utilisateurs.php?action=delete&id=' + id;
        }
    });
}
</script>
  </script>
        
    </body>
</html>

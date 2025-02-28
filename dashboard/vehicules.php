<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="images/favicon.ico" type="image/ico" />

    <title>Dashboard</title>

    <!-- Bootstrap -->
    <link href="../vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="../vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <!-- NProgress -->
    <link href="../vendors/nprogress/nprogress.css" rel="stylesheet">
    <!-- iCheck -->
    <link href="../vendors/iCheck/skins/flat/green.css" rel="stylesheet">
    <!-- bootstrap-progressbar -->
    <link href="../vendors/bootstrap-progressbar/css/bootstrap-progressbar-3.3.4.min.css" rel="stylesheet">
    <!-- Custom Theme Style -->
    <link href="../build/css/custom.min.css" rel="stylesheet">
    <!-- PNotify -->
    <link href="../vendors/pnotify/dist/pnotify.css" rel="stylesheet">
    <link href="../vendors/pnotify/dist/pnotify.buttons.css" rel="stylesheet">
    <link href="../vendors/pnotify/dist/pnotify.nonblock.css" rel="stylesheet">
  </head>

  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
        <?php include 'nav.php'; ?>

        <!-- page content -->
        <div class="right_col" role="main">
          <?php include 'stats.php'; ?>
          
          <div class="row">
            <div class="col-md-12 col-sm-12 ">
              <div class="dashboard_graph">

                <div class="row x_title">
                  <div class="col-md-6">
                    <h3>Liste des vehicules</small></h3>
                  </div>
                  <div class="col-md-12 col-sm-12  ">
                <div class="x_panel">
                  <div class="x_title">
                    <div class="btn-group">
                      <button type="button" class="btn btn-primary" onclick="openAddModal()">Ajouter un véhicule</button>
                      <button type="button" class="btn btn-success">Exporter</button>
                      <button type="button" class="btn btn-info">Importer</button>
                    </div>
                    <ul class="nav navbar-right panel_toolbox">
                      <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                      </li>
                      <li><a class="close-link"><i class="fa fa-close"></i></a>
                      </li>
                    </ul>
                    <div class="clearfix"></div>
                  </div>

                  <div class="x_content">
                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                      <table class="table table-striped jambo_table bulk_action">
                        <thead style="position: sticky; top: 0; background: #2A3F54; z-index: 1;">
                          <tr class="headings">
                            <th style="color: white; font-weight: bold;">
                              <input type="checkbox" id="check-all" class="flat">
                            </th>
                            <th class="column-title" style="color: white; font-weight: bold;">Matricule</th>
                            <th class="column-title" style="color: white; font-weight: bold;">Date d'ajout</th>
                            <th class="column-title" style="color: white; font-weight: bold;">Dernière modification</th>
                            <th class="column-title" style="color: white; font-weight: bold;">Actions</th>
                            <th class="bulk-actions" colspan="5">
                              <a class="antoo" style="color:#fff; font-weight:500;">Bulk Actions ( <span class="action-cnt"> </span> ) <i class="fa fa-chevron-down"></i></a>
                            </th>
                          </tr>
                        </thead>

                        <tbody>
                          <?php
                            require_once 'requete/vehicule.php';
                            $vehicule = new Vehicule();
                            $vehicules = $vehicule->getAllVehicules();
                            
                            foreach($vehicules as $v) {
                              echo '<tr class="even pointer">';
                              echo '<td class="a-center "><input type="checkbox" class="flat" name="table_records"></td>';
                              echo '<td>'.htmlspecialchars($v['matricule_vehicule']).'</td>';
                              echo '<td>'.htmlspecialchars($v['created_at']).'</td>';
                              echo '<td>'.htmlspecialchars($v['updated_at']).'</td>';
                              echo '<td>';
                              echo '<button type="button" class="btn btn-info btn-sm" onclick="editVehicule('.$v['vehicules_id'].',\''.$v['matricule_vehicule'].'\')"><i class="fa fa-pencil"></i></button> ';
                              echo '<button type="button" class="btn btn-danger btn-sm" onclick="deleteVehicule('.$v['vehicules_id'].')"><i class="fa fa-trash"></i></button>';
                              echo '</td>';
                              echo '</tr>';
                            }
                          ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

        <!-- Modal Ajout Vehicule -->
        <div class="modal fade" id="addVehiculeModal" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h4 class="modal-title">Ajouter un véhicule</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">×</span>
                </button>
              </div>
              <div class="modal-body">
                <form id="addVehiculeForm">
                  <div class="form-group">
                    <label>Matricule</label>
                    <input type="text" class="form-control" id="matricule" name="matricule" required>
                  </div>
                </form>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary" onclick="saveVehicule()">Enregistrer</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal Edit Vehicule -->
        <div class="modal fade" id="editVehiculeModal" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h4 class="modal-title">Modifier un véhicule</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">×</span>
                </button>
              </div>
              <div class="modal-body">
                <form id="editVehiculeForm">
                  <input type="hidden" id="edit_vehicule_id">
                  <div class="form-group">
                    <label>Matricule</label>
                    <input type="text" class="form-control" id="edit_matricule" name="matricule" required>
                  </div>
                </form>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary" onclick="updateVehicule()">Mettre à jour</button>
              </div>
            </div>
          </div>
        </div>

    <!-- jQuery -->
    <script src="../vendors/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap -->
    <script src="../vendors/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <!-- FastClick -->
    <script src="../vendors/fastclick/lib/fastclick.js"></script>
    <!-- NProgress -->
    <script src="../vendors/nprogress/nprogress.js"></script>
    <!-- PNotify -->
    <script src="../vendors/pnotify/dist/pnotify.js"></script>
    <script src="../vendors/pnotify/dist/pnotify.buttons.js"></script>
    <script src="../vendors/pnotify/dist/pnotify.nonblock.js"></script>
    <!-- Custom Theme Scripts -->
    <script src="../build/js/custom.min.js"></script>

    <script>
        function openAddModal() {
            $('#addVehiculeForm')[0].reset();
            $('#addVehiculeModal').modal('show');
        }

        function saveVehicule() {
            var matricule = $('#matricule').val();
            
            $.ajax({
                url: 'requete/vehicule.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'add_vehicule',
                    matricule: matricule
                },
                success: function(response) {
                    if(response.success) {
                        new PNotify({
                            title: 'Succès',
                            text: 'Véhicule ajouté avec succès!',
                            type: 'success',
                            styling: 'bootstrap3'
                        });
                        $('#addVehiculeModal').modal('hide');
                        location.reload();
                    } else {
                        new PNotify({
                            title: 'Erreur',
                            text: response.error || 'Erreur lors de l\'ajout du véhicule',
                            type: 'error',
                            styling: 'bootstrap3'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    new PNotify({
                        title: 'Erreur',
                        text: 'Une erreur est survenue lors de la communication avec le serveur',
                        type: 'error',
                        styling: 'bootstrap3'
                    });
                }
            });
        }

        function editVehicule(id, matricule) {
            $('#edit_vehicule_id').val(id);
            $('#edit_matricule').val(matricule);
            $('#editVehiculeModal').modal('show');
        }

        function updateVehicule() {
            var id = $('#edit_vehicule_id').val();
            var matricule = $('#edit_matricule').val();
            
            $.ajax({
                url: 'requete/vehicule.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'update_vehicule',
                    id: id,
                    matricule: matricule
                },
                success: function(response) {
                    if(response.success) {
                        new PNotify({
                            title: 'Succès',
                            text: 'Véhicule modifié avec succès!',
                            type: 'success',
                            styling: 'bootstrap3'
                        });
                        $('#editVehiculeModal').modal('hide');
                        location.reload();
                    } else {
                        new PNotify({
                            title: 'Erreur',
                            text: response.error || 'Erreur lors de la modification du véhicule',
                            type: 'error',
                            styling: 'bootstrap3'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    new PNotify({
                        title: 'Erreur',
                        text: 'Une erreur est survenue lors de la communication avec le serveur',
                        type: 'error',
                        styling: 'bootstrap3'
                    });
                }
            });
        }

        function deleteVehicule(id) {
            if(confirm('Êtes-vous sûr de vouloir supprimer ce véhicule?')) {
                $.ajax({
                    url: 'requete/vehicule.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'delete_vehicule',
                        id: id
                    },
                    success: function(response) {
                        if(response.success) {
                            new PNotify({
                                title: 'Succès',
                                text: 'Véhicule supprimé avec succès!',
                                type: 'success',
                                styling: 'bootstrap3'
                            });
                            location.reload();
                        } else {
                            new PNotify({
                                title: 'Erreur',
                                text: response.error || 'Erreur lors de la suppression du véhicule',
                                type: 'error',
                                styling: 'bootstrap3'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        new PNotify({
                            title: 'Erreur',
                            text: 'Une erreur est survenue lors de la communication avec le serveur',
                            type: 'error',
                            styling: 'bootstrap3'
                        });
                    }
                });
            }
        }
    </script>
  </body>
</html>

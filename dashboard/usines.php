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
                    <h3>Liste des usines</small></h3>
                  </div>
                  <div class="col-md-12 col-sm-12  ">
                <div class="x_panel">
                  <div class="x_title">
                    <div class="btn-group">
                      <button type="button" class="btn btn-primary" onclick="openAddModal()">Ajouter une usine</button>
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
                            <th class="column-title" style="color: white; font-weight: bold;">Nom de l'usine</th>
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
                            require_once 'requete/usine.php';
                            $usine = new Usine();
                            $usines = $usine->getAllUsines();
                            
                            foreach($usines as $u) {
                              echo '<tr class="even pointer">';
                              echo '<td class="a-center "><input type="checkbox" class="flat" name="table_records"></td>';
                              echo '<td>' . htmlspecialchars($u['nom_usine']) . '</td>';
                              echo '<td>' . $u['created_at'] . '</td>';
                              echo '<td>' . $u['updated_by'] . '</td>';
                              echo '<td>';
                              echo '<button type="button" class="btn btn-primary btn-sm" onclick="openEditModal(' . $u['id_usine'] . ', \'' . htmlspecialchars($u['nom_usine']) . '\')"><i class="fa fa-pencil"></i></button>';
                              echo '<button type="button" class="btn btn-danger btn-sm" onclick="deleteUsine(' . $u['id_usine'] . ')"><i class="fa fa-trash"></i></button>';
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

        <!-- Add Modal -->
        <div class="modal fade" id="addUsineModal" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h4 class="modal-title">Ajouter une usine</h4>
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
              </div>
              <div class="modal-body">
                <form id="addUsineForm">
                  <div class="form-group">
                    <label>Nom de l'usine</label>
                    <input type="text" class="form-control" id="nom_usine" required>
                  </div>
                </form>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary" onclick="addUsine()">Ajouter</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Edit Modal -->
        <div class="modal fade" id="editUsineModal" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h4 class="modal-title">Modifier l'usine</h4>
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
              </div>
              <div class="modal-body">
                <form id="editUsineForm">
                  <input type="hidden" id="edit_usine_id">
                  <div class="form-group">
                    <label>Nom de l'usine</label>
                    <input type="text" class="form-control" id="edit_nom_usine" required>
                  </div>
                </form>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary" onclick="updateUsine()">Modifier</button>
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
    <!-- Chart.js -->
    <script src="../vendors/Chart.js/dist/Chart.min.js"></script>
    <!-- jQuery Sparklines -->
    <script src="../vendors/jquery-sparkline/dist/jquery.sparkline.min.js"></script>
    <!-- morris.js -->
    <script src="../vendors/raphael/raphael.min.js"></script>
    <script src="../vendors/morris.js/morris.min.js"></script>
    <!-- gauge.js -->
    <script src="../vendors/gauge.js/dist/gauge.min.js"></script>
    <!-- bootstrap-progressbar -->
    <script src="../vendors/bootstrap-progressbar/bootstrap-progressbar.min.js"></script>
    <!-- Skycons -->
    <script src="../vendors/skycons/skycons.js"></script>
    <!-- Flot -->
    <script src="../vendors/Flot/jquery.flot.js"></script>
    <script src="../vendors/Flot/jquery.flot.pie.js"></script>
    <script src="../vendors/Flot/jquery.flot.time.js"></script>
    <script src="../vendors/Flot/jquery.flot.stack.js"></script>
    <script src="../vendors/Flot/jquery.flot.resize.js"></script>
    <!-- Flot plugins -->
    <script src="../vendors/flot.orderbars/js/jquery.flot.orderBars.js"></script>
    <script src="../vendors/flot-spline/js/jquery.flot.spline.min.js"></script>
    <script src="../vendors/flot.curvedlines/curvedLines.js"></script>
    <!-- DateJS -->
    <script src="../vendors/DateJS/build/date.js"></script>
    <!-- bootstrap-daterangepicker -->
    <script src="../vendors/moment/min/moment.min.js"></script>
    <script src="../vendors/bootstrap-daterangepicker/daterangepicker.js"></script>
    <!-- PNotify -->
    <script src="../vendors/pnotify/dist/pnotify.js"></script>
    <script src="../vendors/pnotify/dist/pnotify.buttons.js"></script>
    <script src="../vendors/pnotify/dist/pnotify.nonblock.js"></script>
    <!-- Custom Theme Scripts -->
    <script src="../build/js/custom.min.js"></script>

    <script>
      function openAddModal() {
        $('#addUsineModal').modal('show');
      }

      function openEditModal(id, nom) {
        $('#edit_usine_id').val(id);
        $('#edit_nom_usine').val(nom);
        $('#editUsineModal').modal('show');
      }

      function addUsine() {
        var nom_usine = $('#nom_usine').val();
        
        $.ajax({
          url: 'requete/usine.php',
          type: 'POST',
          data: {
            action: 'add',
            nom_usine: nom_usine
          },
          success: function(response) {
            if(response.success) {
              new PNotify({
                title: 'Succès',
                text: 'Usine ajoutée avec succès!',
                type: 'success',
                styling: 'bootstrap3'
              });
              $('#addUsineModal').modal('hide');
              location.reload();
            } else {
              new PNotify({
                title: 'Erreur',
                text: 'Erreur lors de l\'ajout de l\'usine',
                type: 'error',
                styling: 'bootstrap3'
              });
            }
          }
        });
      }

      function updateUsine() {
        var id = $('#edit_usine_id').val();
        var nom_usine = $('#edit_nom_usine').val();
        
        $.ajax({
          url: 'requete/usine.php',
          type: 'POST',
          data: {
            action: 'update',
            id_usine: id,
            nom_usine: nom_usine
          },
          success: function(response) {
            if(response.success) {
              new PNotify({
                title: 'Succès',
                text: 'Usine modifiée avec succès!',
                type: 'success',
                styling: 'bootstrap3'
              });
              $('#editUsineModal').modal('hide');
              location.reload();
            } else {
              new PNotify({
                title: 'Erreur',
                text: 'Erreur lors de la modification de l\'usine',
                type: 'error',
                styling: 'bootstrap3'
              });
            }
          }
        });
      }

      function deleteUsine(id) {
        if(confirm('Êtes-vous sûr de vouloir supprimer cette usine?')) {
          $.ajax({
            url: 'requete/usine.php',
            type: 'POST',
            data: {
              action: 'delete',
              id_usine: id
            },
            success: function(response) {
              if(response.success) {
                new PNotify({
                  title: 'Succès',
                  text: 'Usine supprimée avec succès!',
                  type: 'success',
                  styling: 'bootstrap3'
                });
                location.reload();
              } else {
                new PNotify({
                  title: 'Erreur',
                  text: 'Erreur lors de la suppression de l\'usine',
                  type: 'error',
                  styling: 'bootstrap3'
                });
              }
            }
          });
        }
      }
    </script>
  </body>
</html>

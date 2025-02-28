<?php
session_start();
    require_once "../functions/agents.php";
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
    <!-- Datatables -->
    <link href="../vendors/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="../vendors/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
    <link href="../vendors/datatables.net-fixedheader-bs/css/fixedHeader.bootstrap.min.css" rel="stylesheet">
    <link href="../vendors/datatables.net-responsive-bs/css/responsive.bootstrap.min.css" rel="stylesheet">
    <link href="../vendors/datatables.net-scroller-bs/css/scroller.bootstrap.min.css" rel="stylesheet">
    
    <style>
    .dataTables_wrapper {
        padding: 20px;
    }
    .table-container {
        max-height: 500px;
        overflow-y: auto;
        position: relative;
    }
    .table {
        margin: 0 !important;
        border-collapse: separate;
        border-spacing: 0;
    }
    .table thead {
        position: sticky;
        top: 0;
        z-index: 2;
    }
    .table thead th {
        background: #2A3F54;
        color: white !important;
        font-size: 16px !important;
        font-weight: bold !important;
        border-bottom: 2px solid #1f2f3d !important;
        padding: 15px 8px !important;
        vertical-align: middle !important;
    }
    .table tbody td {
        font-size: 15px !important;
        padding: 12px 8px !important;
        background-color: #fff;
    }
    .table tbody tr:nth-child(even) td {
        background-color: #f9f9f9;
    }
    .table tbody tr:hover td {
        background-color: #f5f5f5;
    }
    .btn-sm {
        font-size: 14px !important;
        padding: 6px 12px !important;
    }
    .dataTables_filter input {
        height: 34px !important;
        padding: 6px 12px !important;
        font-size: 15px !important;
    }
    .dataTables_length select {
        height: 34px !important;
        font-size: 15px !important;
    }
    .dataTables_info, .dataTables_paginate {
        font-size: 15px !important;
        padding: 15px 0 !important;
    }
    /* Style pour les boutons d'action */
    .btn-primary {
        background-color: #2A3F54;
        border-color: #2A3F54;
    }
    .btn-danger {
        background-color: #E74C3C;
        border-color: #E74C3C;
    }
    </style>
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
                    <h3>Liste des agents</small></h3>
                  </div>
                  <div class="col-md-12 col-sm-12  ">
                <div class="x_panel">
                  <div class="x_title">
                    <div class="btn-group">
                      <button type="button" class="btn btn-primary" onclick="openAddModal()">Ajouter un agent</button>
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

                    

                    <div class="table-responsive table-container">
                      <table id="agentsTable" class="table table-striped jambo_table bulk_action">
                        <thead>
                          <tr class="headings">
                            <th>
                              <input type="checkbox" id="check-all" class="flat">
                            </th>
                            <th class="column-title">Nom</th>
                            <th class="column-title">Prénom</th>
                            <th class="column-title">Contact</th>
                            <th class="column-title">Nom chef</th>
                            <th class="column-title">Date d'ajout</th>
                            <th class="column-title">Crée par</th>
                            <th class="column-title">Actions</th>
                            <th class="bulk-actions" colspan="7">
                              <a class="antoo" style="color:#fff; font-weight:500;">Actions groupées ( <span class="action-cnt"> </span> ) <i class="fa fa-chevron-down"></i></a>
                            </th>
                          </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($agents as $agent): ?>
                <tr>
                    <td><?= htmlspecialchars($getAgent['id_agent']) ?></td>
                    <td><?= htmlspecialchars($agent['nom']) ?></td>
                    <td><?= htmlspecialchars($agent['prenom']) ?></td>
                    <td><?= htmlspecialchars($agent['contact']) ?></td>
                    <td><?= htmlspecialchars($agent['id_chef']) ?></td>
                    <td><?= htmlspecialchars($agent['date_ajout']) ?></td>
                </tr>
            <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
							
						
                  </div>
                </div>
              </div>

                </div>

          

                <div class="clearfix"></div>
              </div>
            </div>

          </div>
          <br />

        </div>
        <!-- /page content -->

        <!-- Modal Ajout Agent -->
        <div class="modal fade" id="addAgentModal" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h4 class="modal-title">Ajouter un Agent</h4>
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
              </div>
              <div class="modal-body">
                <form id="addAgentForm">
                  <div class="form-group">
                    <label>Nom *</label>
                    <input type="text" class="form-control" name="nom" required>
                  </div>
                  <div class="form-group">
                    <label>Prénom *</label>
                    <input type="text" class="form-control" name="prenom" required>
                  </div>
                  <div class="form-group">
                    <label>Contact *</label>
                    <input type="text" class="form-control" name="contact" required>
                  </div>
                  <div class="form-group">
                    <label>Nom du chef</label>
                    <select class="form-control" name="id_chef">
                      <option value="">Sélectionner un chef</option>
                      <?php
                      foreach($chefs as $chef) {
                          echo '<option value="'.htmlspecialchars($chef['id_chef']).'">'.htmlspecialchars($chef['nom_complet']).'</option>';
                      }
                      ?>
                    </select>
                  </div>
                </form>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary" onclick="saveAgent()">Enregistrer</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal Edit Agent -->
        <div class="modal fade" id="editAgentModal" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h4 class="modal-title">Modifier un Agent</h4>
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
              </div>
              <div class="modal-body">
                <form id="editAgentForm">
                  <input type="hidden" name="id_agent" id="edit_id_agent">
                  <div class="form-group">
                    <label>Nom *</label>
                    <input type="text" class="form-control" name="nom" id="edit_nom" required>
                  </div>
                  <div class="form-group">
                    <label>Prénom *</label>
                    <input type="text" class="form-control" name="prenom" id="edit_prenom" required>
                  </div>
                  <div class="form-group">
                    <label>Contact *</label>
                    <input type="text" class="form-control" name="contact" id="edit_contact" required>
                  </div>
                  <div class="form-group">
                    <label>Nom du chef</label>
                    <select class="form-control" name="id_chef" id="edit_id_chef">
                      <option value="">Sélectionner un chef</option>
                      <?php
                      foreach($chefs as $chef) {
                          echo '<option value="'.htmlspecialchars($chef['id_chef']).'">'.htmlspecialchars($chef['nom_complet']).'</option>';
                      }
                      ?>
                    </select>
                  </div>
                </form>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary" onclick="updateAgent()">Enregistrer</button>
              </div>
            </div>
          </div>
        </div>

        <script>
        // Ouvrir le modal d'ajout
        function openAddModal() {
            $('#addAgentModal').modal('show');
        }

        // Sauvegarder l'agent
        function saveAgent() {
            var formData = new FormData(document.getElementById('addAgentForm'));
            
            $.ajax({
                url: 'requete/ajax_handler.php',
                type: 'POST',
                data: {
                    action: 'add',
                    nom: formData.get('nom'),
                    prenom: formData.get('prenom'),
                    contact: formData.get('contact'),
                    id_chef: formData.get('id_chef'),
                    nom_chef: formData.get('nom_chef')
                },
                success: function(response) {
                    var result = JSON.parse(response);
                    if(result.success) {
                        $('#addAgentModal').modal('hide');
                        // Recharger la table
                        location.reload();
                        // Afficher un message de succès
                        new PNotify({
                            title: 'Succès',
                            text: 'Agent ajouté avec succès',
                            type: 'success',
                            styling: 'bootstrap3'
                        });
                    } else {
                        // Afficher un message d'erreur
                        new PNotify({
                            title: 'Erreur',
                            text: result.message || 'Erreur lors de l\'ajout de l\'agent',
                            type: 'error',
                            styling: 'bootstrap3'
                        });
                    }
                },
                error: function() {
                    new PNotify({
                        title: 'Erreur',
                        text: 'Erreur de connexion au serveur',
                        type: 'error',
                        styling: 'bootstrap3'
                    });
                }
            });
        }
        </script>

        <!-- footer content -->
        <footer>
          <div class="pull-right">
            Gentelella - Bootstrap Admin Template by <a href="https://colorlib.com">Colorlib</a>
          </div>
          <div class="clearfix"></div>
        </footer>
        <!-- /footer content -->
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
    <!-- gauge.js -->
    <script src="../vendors/gauge.js/dist/gauge.min.js"></script>
    <!-- bootstrap-progressbar -->
    <script src="../vendors/bootstrap-progressbar/bootstrap-progressbar.min.js"></script>
    <!-- iCheck -->
    <script src="../vendors/iCheck/icheck.min.js"></script>
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
    <!-- JQVMap -->
    <script src="../vendors/jqvmap/dist/jquery.vmap.js"></script>
    <script src="../vendors/jqvmap/dist/maps/jquery.vmap.world.js"></script>
    <script src="../vendors/jqvmap/examples/js/jquery.vmap.sampledata.js"></script>
    <!-- bootstrap-daterangepicker -->
    <script src="../vendors/moment/min/moment.min.js"></script>
    <script src="../vendors/bootstrap-daterangepicker/daterangepicker.js"></script>

    <!-- Custom Theme Scripts -->
    <script src="../build/js/custom.min.js"></script>

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
    
    <!-- Custom Scripts -->
    <script src="js/agents.js"></script>
    
    <!-- Datatables -->
    <script src="../vendors/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../vendors/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <script src="../vendors/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
    <script src="../vendors/datatables.net-buttons-bs/js/buttons.bootstrap.min.js"></script>
    <script src="../vendors/datatables.net-buttons/js/buttons.flash.min.js"></script>
    <script src="../vendors/datatables.net-buttons/js/buttons.html5.min.js"></script>
    <script src="../vendors/datatables.net-buttons/js/buttons.print.min.js"></script>
    <script src="../vendors/datatables.net-fixedheader/js/dataTables.fixedHeader.min.js"></script>
    <script src="../vendors/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
    <script src="../vendors/datatables.net-responsive-bs/js/responsive.bootstrap.js"></script>
    <script src="../vendors/datatables.net-scroller/js/dataTables.scroller.min.js"></script>
    
    <script>
    $(document).ready(function() {
        $('#agentsTable').DataTable({
            pageLength: 25,
            responsive: true,
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json'
            }
        });
    });
    </script>
  </body>
</html>

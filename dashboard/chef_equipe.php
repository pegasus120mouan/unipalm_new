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

    <title>Chefs d'équipe</title>

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
          <div class="row">
            <div class="col-md-12">
              <div class="x_panel">
                <div class="x_title">
                  <h2>Gestion des Chefs d'équipe</h2>
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">
                  <button class="btn btn-primary" onclick="openAddModal()">
                    <i class="fa fa-plus"></i> Ajouter un chef d'équipe
                  </button>
                  <br><br>
                  <div class="table-responsive table-container">
                    <table id="chefsTable" class="table table-striped jambo_table bulk_action">
                      <thead>
                        <tr class="headings">
                          <th>
                            <input type="checkbox" id="check-all" class="flat">
                          </th>
                          <th class="column-title">Nom</th>
                          <th class="column-title">Prénoms</th>
                          <th class="column-title">Actions</th>
                          <th class="bulk-actions" colspan="4">
                            <a class="antoo" style="color:#fff; font-weight:500;">Actions groupées ( <span class="action-cnt"> </span> ) <i class="fa fa-chevron-down"></i></a>
                          </th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        require_once 'requete/chef_equipe.php';
                        $chef = new ChefEquipe();
                        $chefs = $chef->getAllChefs();
                        
                        foreach($chefs as $chef) {
                            echo '<tr class="even pointer">';
                            echo '<td class="a-center "><input type="checkbox" class="flat" name="table_records"></td>';
                            echo '<td class="">' . htmlspecialchars($chef['nom']) . '</td>';
                            echo '<td class="">' . htmlspecialchars($chef['prenoms']) . '</td>';
                            echo '<td class="last">
                                <button class="btn btn-primary btn-sm" onclick="editChef(\''.$chef['id_chef'].'\')">
                                    <i class="fa fa-pencil"></i>
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="deleteChef(\''.$chef['id_chef'].'\')">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>';
                            echo '</tr>';
                        }
                        
                        if (empty($chefs)) {
                            echo '<tr><td colspan="4" class="text-center">Aucun chef d\'équipe trouvé</td></tr>';
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
        <!-- /page content -->

        <!-- Modal Ajout Chef -->
        <div class="modal fade" id="addChefModal" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h4 class="modal-title">Ajouter un Chef d'équipe</h4>
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
              </div>
              <div class="modal-body">
                <form id="addChefForm">
                  <div class="form-group">
                    <label>Nom *</label>
                    <input type="text" class="form-control" name="nom" required>
                  </div>
                  <div class="form-group">
                    <label>Prénoms *</label>
                    <input type="text" class="form-control" name="prenoms" required>
                  </div>
                </form>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary" onclick="addChef()">Ajouter</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal Edit Chef -->
        <div class="modal fade" id="editChefModal" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h4 class="modal-title">Modifier un Chef d'équipe</h4>
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
              </div>
              <div class="modal-body">
                <form id="editChefForm">
                  <input type="hidden" name="id_chef" id="edit_id_chef">
                  <div class="form-group">
                    <label>Nom *</label>
                    <input type="text" class="form-control" name="nom" id="edit_nom" required>
                  </div>
                  <div class="form-group">
                    <label>Prénoms *</label>
                    <input type="text" class="form-control" name="prenoms" id="edit_prenoms" required>
                  </div>
                </form>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary" onclick="updateChef()">Enregistrer</button>
              </div>
            </div>
          </div>
        </div>

        <!-- footer content -->
        <?php include 'footer.php'; ?>
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
    
    <!-- Custom Theme Scripts -->
    <script src="../build/js/custom.min.js"></script>
    
    <!-- Custom Scripts -->
    <script src="js/chefs.js"></script>
    
    <script>
    $(document).ready(function() {
        $('#chefsTable').DataTable({
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

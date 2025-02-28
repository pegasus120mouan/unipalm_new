<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="images/favicon.ico" type="image/ico" />

    <title>Utilisateurs | UNIPALM</title>

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
                    <h3>Liste des utilisateurs</h3>
                  </div>
                  <div class="col-md-12 col-sm-12">
                    <div class="x_panel">
                      <div class="x_title">
                        <div class="btn-group">
                          <button type="button" class="btn btn-primary" onclick="openAddModal()">Ajouter un utilisateur</button>
                          <button type="button" class="btn btn-success">Exporter</button>
                          <button type="button" class="btn btn-info">Importer</button>
                        </div>
                        <ul class="nav navbar-right panel_toolbox">
                          <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
                          <li><a class="close-link"><i class="fa fa-close"></i></a></li>
                        </ul>
                        <div class="clearfix"></div>
                      </div>

                      <div class="x_content">
                        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                          <table id="usersTable" class="table table-striped jambo_table bulk_action">
                            <thead style="position: sticky; top: 0; background: #2A3F54; z-index: 1;">
                              <tr class="headings">
                                <th style="color: white; font-weight: bold;">
                                  <input type="checkbox" id="check-all" class="flat">
                                </th>
                                <th class="column-title" style="color: white; font-weight: bold;">Nom</th>
                                <th class="column-title" style="color: white; font-weight: bold;">Prénoms</th>
                                <th class="column-title" style="color: white; font-weight: bold;">Contact</th>
                                <th class="column-title" style="color: white; font-weight: bold;">Login</th>
                                <th class="column-title" style="color: white; font-weight: bold;">Rôle</th>
                                <th class="column-title" style="color: white; font-weight: bold;">Date de création</th>
                                <th class="column-title" style="color: white; font-weight: bold;">Actions</th>
                                <th class="bulk-actions" colspan="7">
                                  <a class="antoo" style="color:#fff; font-weight:500;">Bulk Actions ( <span class="action-cnt"> </span> ) <i class="fa fa-chevron-down"></i></a>
                                </th>
                              </tr>
                            </thead>
                            <tbody>
                              <!-- Les données seront chargées ici via AJAX -->
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
        <!-- /page content -->
      </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">Ajouter un utilisateur</h4>
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
          </div>
          <form id="addUserForm">
            <div class="modal-body">
              <div class="form-group">
                <label>Nom *</label>
                <input type="text" class="form-control" name="nom" required>
              </div>
              <div class="form-group">
                <label>Prénoms *</label>
                <input type="text" class="form-control" name="prenoms" required>
              </div>
              <div class="form-group">
                <label>Contact *</label>
                <input type="text" class="form-control" name="contact" required>
              </div>
              <div class="form-group">
                <label>Login *</label>
                <input type="text" class="form-control" name="login" required>
              </div>
              <div class="form-group">
                <label>Mot de passe *</label>
                <input type="password" class="form-control" name="password" required>
              </div>
              <div class="form-group">
                <label>Rôle *</label>
                <select class="form-control" name="role" required>
                  <option value="admin">Administrateur</option>
                  <option value="operateur">Opérateur</option>
                  <option value="validateur">Validateur</option>
                  <option value="caisse">Caisse</option>
                </select>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
              <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">Modifier l'utilisateur</h4>
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
          </div>
          <form id="editUserForm">
            <input type="hidden" name="id" id="edit_id">
            <input type="hidden" name="action" value="edit">
            <div class="modal-body">
              <div class="form-group">
                <label>Nom *</label>
                <input type="text" class="form-control" name="nom" id="edit_nom" required>
              </div>
              <div class="form-group">
                <label>Prénoms *</label>
                <input type="text" class="form-control" name="prenoms" id="edit_prenoms" required>
              </div>
              <div class="form-group">
                <label>Contact *</label>
                <input type="text" class="form-control" name="contact" id="edit_contact" required>
              </div>
              <div class="form-group">
                <label>Login *</label>
                <input type="text" class="form-control" name="login" id="edit_login" required>
              </div>
              <div class="form-group">
                <label>Nouveau mot de passe</label>
                <input type="password" class="form-control" name="password">
                <small class="text-muted">Laissez vide pour conserver l'ancien mot de passe</small>
              </div>
              <div class="form-group">
                <label>Rôle *</label>
                <select class="form-control" name="role" id="edit_role" required>
                  <option value="admin">Administrateur</option>
                  <option value="operateur">Opérateur</option>
                  <option value="validateur">Validateur</option>
                  <option value="caisse">Caisse</option>
                </select>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
              <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
            </div>
          </form>
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
    <!-- Datatables -->
    <script src="../vendors/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../vendors/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <script src="../vendors/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
    <script src="../vendors/datatables.net-buttons-bs/js/buttons.bootstrap.min.js"></script>
    <script src="../vendors/datatables.net-buttons/js/buttons.flash.min.js"></script>
    <script src="../vendors/datatables.net-buttons/js/buttons.html5.min.js"></script>
    <script src="../vendors/datatables.net-buttons/js/buttons.print.min.js"></script>
    <script src="../vendors/datatables.net-fixedheader/js/dataTables.fixedHeader.min.js"></script>
    <script src="../vendors/datatables.net-keytable/js/dataTables.keyTable.min.js"></script>
    <script src="../vendors/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
    <script src="../vendors/datatables.net-responsive-bs/js/responsive.bootstrap.js"></script>
    <script src="../vendors/datatables.net-scroller/js/dataTables.scroller.min.js"></script>
    <script src="../vendors/jszip/dist/jszip.min.js"></script>
    <script src="../vendors/pdfmake/build/pdfmake.min.js"></script>
    <script src="../vendors/pdfmake/build/vfs_fonts.js"></script>
    <!-- PNotify -->
    <script src="../vendors/pnotify/dist/pnotify.js"></script>
    <script src="../vendors/pnotify/dist/pnotify.buttons.js"></script>
    <script src="../vendors/pnotify/dist/pnotify.nonblock.js"></script>
    <!-- Custom Theme Scripts -->
    <script src="../build/js/custom.min.js"></script>

    <script>
    function openAddModal() {
        $('#addUserForm')[0].reset();
        $('#addUserModal').modal('show');
    }

    $(document).ready(function() {
        // Initialisation de la DataTable
        var table = $('#usersTable').DataTable({
            "ajax": {
                "url": "requete/utilisateur.php",
                "type": "POST",
                "data": {
                    "action": "list"
                }
            },
            "columns": [
                {
                    "data": null,
                    "defaultContent": '<input type="checkbox" class="flat" name="table_records">'
                },
                { "data": "nom" },
                { "data": "prenoms" },
                { "data": "contact" },
                { "data": "login" },
                { "data": "role" },
                { 
                    "data": "created_at",
                    "render": function(data) {
                        return moment(data).format('DD/MM/YYYY HH:mm');
                    }
                },
                {
                    "data": null,
                    "render": function(data, type, row) {
                        return '<button class="btn btn-info btn-sm edit-btn" data-id="' + row.id + '"><i class="fa fa-pencil"></i></button> ' +
                               '<button class="btn btn-danger btn-sm delete-btn" data-id="' + row.id + '"><i class="fa fa-trash"></i></button>';
                    }
                }
            ],
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/French.json"
            },
            "dom": 'Bfrtip',
            "buttons": [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ]
        });

        // Soumission du formulaire d'ajout
        $('#addUserForm').on('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            formData.append('action', 'add');
            
            $.ajax({
                url: 'requete/utilisateur.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    var result = JSON.parse(response);
                    if(result.status === 'success') {
                        $('#addUserModal').modal('hide');
                        $('#addUserForm')[0].reset();
                        table.ajax.reload();
                        new PNotify({
                            title: 'Succès',
                            text: 'Utilisateur ajouté avec succès',
                            type: 'success',
                            styling: 'bootstrap3'
                        });
                    } else {
                        new PNotify({
                            title: 'Erreur',
                            text: result.message,
                            type: 'error',
                            styling: 'bootstrap3'
                        });
                    }
                }
            });
        });

        // Clic sur le bouton modifier
        $('#usersTable').on('click', '.edit-btn', function() {
            var id = $(this).data('id');
            $.ajax({
                url: 'requete/utilisateur.php',
                type: 'POST',
                data: {
                    action: 'get',
                    id: id
                },
                success: function(response) {
                    var user = JSON.parse(response);
                    $('#edit_id').val(user.id);
                    $('#edit_nom').val(user.nom);
                    $('#edit_prenoms').val(user.prenoms);
                    $('#edit_contact').val(user.contact);
                    $('#edit_login').val(user.login);
                    $('#edit_role').val(user.role);
                    $('#editUserModal').modal('show');
                }
            });
        });

        // Soumission du formulaire de modification
        $('#editUserForm').on('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            
            $.ajax({
                url: 'requete/utilisateur.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    var result = JSON.parse(response);
                    if(result.status === 'success') {
                        $('#editUserModal').modal('hide');
                        table.ajax.reload();
                        new PNotify({
                            title: 'Succès',
                            text: 'Utilisateur modifié avec succès',
                            type: 'success',
                            styling: 'bootstrap3'
                        });
                    } else {
                        new PNotify({
                            title: 'Erreur',
                            text: result.message,
                            type: 'error',
                            styling: 'bootstrap3'
                        });
                    }
                }
            });
        });

        // Clic sur le bouton supprimer
        $('#usersTable').on('click', '.delete-btn', function() {
            var id = $(this).data('id');
            if(confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) {
                $.ajax({
                    url: 'requete/utilisateur.php',
                    type: 'POST',
                    data: {
                        action: 'delete',
                        id: id
                    },
                    success: function(response) {
                        var result = JSON.parse(response);
                        if(result.status === 'success') {
                            table.ajax.reload();
                            new PNotify({
                                title: 'Succès',
                                text: 'Utilisateur supprimé avec succès',
                                type: 'success',
                                styling: 'bootstrap3'
                            });
                        } else {
                            new PNotify({
                                title: 'Erreur',
                                text: result.message,
                                type: 'error',
                                styling: 'bootstrap3'
                            });
                        }
                    }
                });
            }
        });
    });
    </script>
  </body>
</html>

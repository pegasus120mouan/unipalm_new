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
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="images/favicon.ico" type="image/ico" />

    <title>Tickets</title>

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
    <!-- Select2 -->
    <link href="../vendors/select2/dist/css/select2.min.css" rel="stylesheet">
    <!-- bootstrap-daterangepicker -->
    <link href="../vendors/bootstrap-daterangepicker/daterangepicker.css" rel="stylesheet">
    <style>
      .badge-warning {
          background-color: #f0ad4e;
          color: white;
          padding: 5px 10px;
          border-radius: 4px;
          font-size: 12px;
      }
      .badge-success {
          background-color: #5cb85c;
          color: white;
          padding: 5px 10px;
          border-radius: 4px;
          font-size: 12px;
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
            <div class="col-md-12 col-sm-12">
              <div class="x_panel">
                <div class="x_title">
                  <h2>Gestion des Tickets</h2>
                  <div class="btn-group pull-right">
                    <button type="button" class="btn btn-primary" onclick="openAddModal()">Nouveau Ticket</button>
                    <button type="button" class="btn btn-success">Exporter</button>
                  </div>
                  <div class="clearfix"></div>
                </div>

                <div class="x_content">
                  <div class="table-responsive">
                    <table class="table table-striped jambo_table bulk_action">
                      <thead>
                        <tr class="headings">
                          <th class="column-title">Numéro Ticket</th>
                          <th class="column-title">Usine</th>
                          <th class="column-title">Date Ticket</th>
                          <th class="column-title">Chef Équipe</th>
                          <th class="column-title">Véhicule</th>
                          <th class="column-title">Poids</th>
                          <th class="column-title">Prix Unitaire</th>
                          <th class="column-title">Montant</th>
                          <th class="column-title">Statut</th>
                          <th class="column-title">Actions</th>
                        </tr>
                      </thead>

                      <tbody>
                        <?php
                          require_once 'requete/ticket.php';
                          $ticket = new Ticket();
                          $tickets = $ticket->getAllTickets();
                          
                          if (is_array($tickets)) {
                            foreach($tickets as $t) {
                              // Calcul du montant
                              $poids = floatval($t['poids'] ?? 0);
                              $prix_unitaire = floatval($t['prix_unitaire'] ?? 0);
                              $montant = $poids * $prix_unitaire;
                              
                              echo '<tr>';
                              echo '<td>'.htmlspecialchars($t['numero_ticket'] ?? '').'</td>';
                              echo '<td>'.htmlspecialchars($t['nom_usine'] ?? '').'</td>';
                              echo '<td>'.htmlspecialchars($t['date_ticket'] ?? '').'</td>';
                              echo '<td>'.htmlspecialchars($t['nom_chef'] ?? '').'</td>';
                              echo '<td>'.htmlspecialchars($t['matricule_vehicule'] ?? '').'</td>';
                              echo '<td>'.($t['poids'] ? number_format($t['poids'], 2) : '0.00').' kg</td>';
                              echo '<td>';
                              if ($prix_unitaire == 0) {
                                  echo '<span class="badge badge-warning">En attente validation Boss</span>';
                              } else {
                                  echo number_format($prix_unitaire, 2).' FCFA';
                              }
                              echo '</td>';
                              echo '<td>'.number_format($montant, 2).' FCFA</td>';
                              echo '<td>';
                              if (!empty($t['date_validation_boss'])) {
                                  echo '<span class="badge badge-success">Validé</span>';
                              } else {
                                  echo '<span class="badge badge-warning">En attente</span>';
                              }
                              echo '</td>';
                              echo '<td>';
                              echo '<button type="button" class="btn btn-info btn-sm" onclick="editTicket('.$t['id_ticket'].')"><i class="fa fa-pencil"></i></button> ';
                              echo '<button type="button" class="btn btn-danger btn-sm" onclick="deleteTicket('.$t['id_ticket'].')"><i class="fa fa-trash"></i></button>';
                              echo '</td>';
                              echo '</tr>';
                            }
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

        <!-- Add Modal -->
        <div class="modal fade" id="addTicketModal" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h4 class="modal-title">Nouveau Ticket</h4>
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
              </div>
              <div class="modal-body">
                <form id="addTicketForm">
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Numéro Ticket</label>
                        <input type="text" class="form-control" id="numero_ticket" name="numero_ticket" required>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Usine</label>
                        <select class="form-control select2" id="id_usine" name="id_usine" required>
                          <?php
                            require_once 'requete/usine.php';
                            $usine = new Usine();
                            $usines = $usine->getAllUsines();
                            if (is_array($usines)) {
                              foreach($usines as $u) {
                                echo '<option value="'.$u['id_usine'].'">'.$u['nom_usine'].'</option>';
                              }
                            }
                          ?>
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Date Ticket</label>
                        <input type="text" class="form-control date" id="date_ticket" name="date_ticket" required>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Chef Équipe</label>
                        <select class="form-control select2" id="id_chef_equipe" name="id_chef_equipe" required>
                          <?php
                            require_once 'requete/chef_equipe.php';
                            $chef = new ChefEquipe();
                            $chefs = $chef->getAllChefs();
                            if (is_array($chefs)) {
                              foreach($chefs as $c) {
                                echo '<option value="'.$c['id_chef'].'">'.$c['nom'].' '.$c['prenoms'].'</option>';
                              }
                            }
                          ?>
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Véhicule</label>
                        <select class="form-control select2" id="vehicule_id" name="vehicule_id" required>
                          <?php
                            require_once 'requete/vehicule.php';
                            $vehicule = new Vehicule();
                            $vehicules = $vehicule->getAllVehicules();
                            if (is_array($vehicules)) {
                              foreach($vehicules as $v) {
                                echo '<option value="'.$v['vehicules_id'].'">'.$v['matricule_vehicule'].'</option>';
                              }
                            }
                          ?>
                        </select>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Poids (kg)</label>
                        <input type="number" step="0.01" class="form-control" id="poids" name="poids" required>
                      </div>
                    </div>
                  </div>
                </form>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary" onclick="addTicket()">Enregistrer</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Edit Modal -->
        <div class="modal fade" id="editTicketModal" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h4 class="modal-title">Modifier Ticket</h4>
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
              </div>
              <div class="modal-body">
                <form id="editTicketForm">
                  <input type="hidden" id="edit_id_ticket" name="id">
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Numéro Ticket</label>
                        <input type="text" class="form-control" id="edit_numero_ticket" name="numero_ticket" required>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Usine</label>
                        <select class="form-control select2" id="edit_id_usine" name="id_usine" required>
                          <?php
                            if (is_array($usines)) {
                              foreach($usines as $u) {
                                echo '<option value="'.$u['id_usine'].'">'.$u['nom_usine'].'</option>';
                              }
                            }
                          ?>
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Date Ticket</label>
                        <input type="text" class="form-control date" id="edit_date_ticket" name="date_ticket" required>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Chef Équipe</label>
                        <select class="form-control select2" id="edit_id_chef_equipe" name="id_chef_equipe" required>
                          <?php
                            if (is_array($chefs)) {
                              foreach($chefs as $c) {
                                echo '<option value="'.$c['id_chef'].'">'.$c['nom'].' '.$c['prenoms'].'</option>';
                              }
                            }
                          ?>
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Véhicule</label>
                        <select class="form-control select2" id="edit_vehicule_id" name="vehicule_id" required>
                          <?php
                            if (is_array($vehicules)) {
                              foreach($vehicules as $v) {
                                echo '<option value="'.$v['vehicules_id'].'">'.$v['matricule_vehicule'].'</option>';
                              }
                            }
                          ?>
                        </select>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Poids (kg)</label>
                        <input type="number" step="0.01" class="form-control" id="edit_poids" name="poids" required>
                      </div>
                    </div>
                  </div>
                </form>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary" onclick="updateTicket()">Enregistrer</button>
              </div>
            </div>
          </div>
        </div>

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
    <!-- Select2 -->
    <script src="../vendors/select2/dist/js/select2.full.min.js"></script>
    <!-- Custom Theme Scripts -->
    <script src="../build/js/custom.min.js"></script>

    <script>
      $(document).ready(function() {
        $('.select2').select2();
        
        $('.date').daterangepicker({
          singleDatePicker: true,
          locale: {
            format: 'YYYY-MM-DD'
          }
        });
      });

      function openAddModal() {
        $('#addTicketModal').modal('show');
      }

      function addTicket() {
        var formData = new FormData($('#addTicketForm')[0]);
        formData.append('action', 'add_ticket');

        $.ajax({
          url: 'requete/ticket.php',
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          success: function(response) {
            if (response.success) {
              new PNotify({
                title: 'Succès',
                text: 'Ticket ajouté avec succès',
                type: 'success',
                styling: 'bootstrap3'
              });
              $('#addTicketModal').modal('hide');
              location.reload();
            } else {
              new PNotify({
                title: 'Erreur',
                text: response.error,
                type: 'error',
                styling: 'bootstrap3'
              });
            }
          },
          error: function() {
            new PNotify({
              title: 'Erreur',
              text: 'Une erreur est survenue',
              type: 'error',
              styling: 'bootstrap3'
            });
          }
        });
      }

      function editTicket(id) {
        $.ajax({
          url: 'requete/ticket.php',
          type: 'POST',
          data: {
            action: 'get_ticket',
            id: id
          },
          success: function(response) {
            if (response.success) {
              var ticket = response.data;
              $('#edit_id_ticket').val(ticket.id_ticket);
              $('#edit_numero_ticket').val(ticket.numero_ticket);
              $('#edit_id_usine').val(ticket.id_usine).trigger('change');
              $('#edit_date_ticket').val(ticket.date_ticket);
              $('#edit_id_chef_equipe').val(ticket.id_chef_equipe).trigger('change');
              $('#edit_vehicule_id').val(ticket.vehicule_id).trigger('change');
              $('#edit_poids').val(ticket.poids);
              $('#editTicketModal').modal('show');
            } else {
              new PNotify({
                title: 'Erreur',
                text: response.error,
                type: 'error',
                styling: 'bootstrap3'
              });
            }
          },
          error: function() {
            new PNotify({
              title: 'Erreur',
              text: 'Une erreur est survenue',
              type: 'error',
              styling: 'bootstrap3'
            });
          }
        });
      }

      function updateTicket() {
        var formData = new FormData($('#editTicketForm')[0]);
        formData.append('action', 'update_ticket');

        $.ajax({
          url: 'requete/ticket.php',
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          success: function(response) {
            if (response.success) {
              new PNotify({
                title: 'Succès',
                text: 'Ticket modifié avec succès',
                type: 'success',
                styling: 'bootstrap3'
              });
              $('#editTicketModal').modal('hide');
              location.reload();
            } else {
              new PNotify({
                title: 'Erreur',
                text: response.error,
                type: 'error',
                styling: 'bootstrap3'
              });
            }
          },
          error: function() {
            new PNotify({
              title: 'Erreur',
              text: 'Une erreur est survenue',
              type: 'error',
              styling: 'bootstrap3'
            });
          }
        });
      }

      function deleteTicket(id) {
        if(confirm('Êtes-vous sûr de vouloir supprimer ce ticket?')) {
          $.ajax({
            url: 'requete/ticket.php',
            type: 'POST',
            data: {
              action: 'delete_ticket',
              id: id
            },
            success: function(response) {
              if (response.success) {
                new PNotify({
                  title: 'Succès',
                  text: 'Ticket supprimé avec succès',
                  type: 'success',
                  styling: 'bootstrap3'
                });
                location.reload();
              } else {
                new PNotify({
                  title: 'Erreur',
                  text: response.error,
                  type: 'error',
                  styling: 'bootstrap3'
                });
              }
            },
            error: function() {
              new PNotify({
                title: 'Erreur',
                text: 'Une erreur est survenue',
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

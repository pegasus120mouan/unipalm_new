<?php
require_once '../inc/functions/connexion.php';
include('header.php');

if (!isset($_GET['id'])) {
    header('Location: gestion_usines.php');
    exit();
}

$id_usine = $_GET['id'];

// Récupérer les informations de l'usine
$sql = "SELECT * FROM usines WHERE id_usine = :id_usine";
$stmt = $conn->prepare($sql);
$stmt->execute([':id_usine' => $id_usine]);
$usine = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usine) {
    header('Location: gestion_usines.php');
    exit();
}

// Récupérer l'historique des paiements
$sql = "SELECT h.*, u.nom, u.prenoms 
        FROM historique_paiements h
        LEFT JOIN utilisateurs u ON h.created_by = u.id
        WHERE h.id_usine = :id_usine 
        ORDER BY h.date_paiement DESC";
$stmt = $conn->prepare($sql);
$stmt->execute([':id_usine' => $id_usine]);
$historique_paiements = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Historique des paiements - <?= htmlspecialchars($usine['nom_usine']) ?></h3>
                    <button class="btn btn-danger" onclick="window.print()">
                        <i class="fas fa-print"></i> Imprimer la liste
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Usine</th>
                                    <th>Date</th>
                                    <th>Montant</th>
                                    <th>Mode de paiement</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $total_montant = 0;
                                if (!empty($historique_paiements)):
                                    foreach ($historique_paiements as $paiement): 
                                        $total_montant += $paiement['montant'];
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($usine['nom_usine']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($paiement['date_paiement'])) ?></td>
                                    <td class="text-right"><?= number_format($paiement['montant'], 0, ',', ' ') ?> FCFA</td>
                                    <td><?= htmlspecialchars($paiement['mode_paiement'] ?: '-') ?></td>
                                    <td class="text-center">
                                        <a href="edit_paiement.php?id=<?= $paiement['id'] ?>" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger btn-sm" 
                                                onclick="confirmDelete(<?= $paiement['id'] ?>, <?= $usine['id_usine'] ?>, '<?= number_format($paiement['montant'], 0, ',', ' ') ?> FCFA')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <tr class="font-weight-bold bg-light">
                                    <td colspan="2" class="text-right">Total:</td>
                                    <td class="text-right"><?= number_format($total_montant, 0, ',', ' ') ?> FCFA</td>
                                    <td colspan="2"></td>
                                </tr>
                                <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">Aucun paiement enregistré</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="fas fa-exclamation-triangle"></i> Confirmation de suppression
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer ce paiement d'un montant de <strong id="montantASupprimer"></strong> ?</p>
                <p class="text-danger mb-0">
                    <i class="fas fa-exclamation-circle"></i> 
                    Cette action est irréversible !
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Annuler
                </button>
                <a href="#" id="confirmDeleteButton" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Supprimer
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .btn-warning.btn-sm {
        background-color: #ffc107;
        border-color: #ffc107;
        padding: 0.25rem 0.5rem;
    }
    .btn-danger.btn-sm {
        background-color: #dc3545;
        border-color: #dc3545;
        padding: 0.25rem 0.5rem;
    }
    .table td {
        vertical-align: middle;
    }
    .text-right {
        text-align: right;
    }
    @media print {
        .btn, .actions {
            display: none !important;
        }
    }
    .modal-header .close {
        padding: 1rem;
        margin: -1rem -1rem -1rem auto;
        opacity: 0.8;
    }
    .modal-header .close:hover {
        opacity: 1;
    }
    .modal-body i.fas {
        margin-right: 0.5rem;
    }
</style>

<script>
function confirmDelete(paiementId, usineId, montant) {
    document.getElementById('montantASupprimer').textContent = montant;
    document.getElementById('confirmDeleteButton').href = 
        'supprimer_paiement.php?id=' + paiementId + '&id_usine=' + usineId;
    $('#deleteModal').modal('show');
}
</script>
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

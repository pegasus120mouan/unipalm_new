<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_tickets.php';
include('header.php');

// Récupérer l'ID de l'utilisateur
$id_user = $_SESSION['user_id'];

// Fonction pour vérifier si un agent a un financement
function getFinancementAgent($conn, $id_agent) {
    $stmt = $conn->prepare("SELECT COALESCE(SUM(montant), 0) as montant_total FROM financement WHERE id_agent = ? AND montant > 0");
    $stmt->execute([$id_agent]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

$getSommeCaisseQuery = "SELECT
    SUM(CASE WHEN type_transaction = 'approvisionnement' THEN montant
             WHEN type_transaction = 'paiement' THEN -montant
             ELSE 0 END) AS solde_caisse
FROM transactions";
$getSommeCaisseQueryStmt = $conn->query($getSommeCaisseQuery);
$somme_caisse = $getSommeCaisseQueryStmt->fetch(PDO::FETCH_ASSOC);




//$solde_caisse = getSoldeCaisse();


$sql_demandes = "
    SELECT d.*, 
           concat(u1.nom, ' ', u1.prenoms) as approbateur,
           concat(u2.nom, ' ', u2.prenoms) as payeur,
           COALESCE(d.montant_payer, 0) as montant_payer,
           d.montant - COALESCE(d.montant_payer, 0) as montant_reste
    FROM demande_sortie d 
    LEFT JOIN utilisateurs u1 ON d.approuve_par = u1.id
    LEFT JOIN utilisateurs u2 ON d.paye_par = u2.id
    WHERE d.statut IN ('approuve', 'paye')
    ORDER BY d.date_demande DESC";

$stmt_demandes = $conn->prepare($sql_demandes);
$stmt_demandes->execute();
$demandes = $stmt_demandes->fetchAll(PDO::FETCH_ASSOC);

?>
<!-- Main row -->
<style>
  .pagination-container {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 20px;
}

.pagination-link {
    padding: 8px;
    text-decoration: none;
    color: white;
    background-color: #007bff;
    border: 1px solid #007bff;
    border-radius: 4px;
    margin-right: 4px;
}

.items-per-page-form {
    margin-left: 20px;
}

label {
    margin-right: 5px;
}

.items-per-page-select {
    padding: 6px;
    border-radius: 4px;
}

.submit-button {
    padding: 6px 10px;
    background-color: #007bff;
    color: #fff;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}
</style>

<link rel="stylesheet" href="../../plugins/select2/css/select2.min.css">
<link rel="stylesheet" href="../../plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">

<div class="row">
    <div class="col-md-12 col-sm-6 col-12">
        <div class="info-box bg-dark">
            <span class="info-box-icon" style="font-size: 48px;">
                <i class="fas fa-hand-holding-usd"></i>
            </span>
            <div class="info-box-content">
                <span style="text-align: center; font-size: 20px;" class="info-box-text">Solde Caisse</span>
                <div class="progress">
                    <div class="progress-bar" style="width: 100%"></div>
                </div>
                <span class="progress-description">
                    <h1 style="text-align: center; font-size: 70px;">
                    <strong><?php echo number_format($somme_caisse['solde_caisse']?? 0, 0, ',', ' '); ?> FCFA</strong>
                    </h1>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Formulaire de filtres -->
<!--<div class="row mb-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Filtres de recherche</h3>
            </div>
            <div class="card-body">
                <form method="GET" class="row">
                    <div class="col-md-3 mb-3">
                        <label for="search_numero">N° de la demande</label>
                        <input type="text" class="form-control" id="search_numero" name="search_numero" value="<?= htmlspecialchars($search_numero) ?>">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="date_debut">Date début</label>
                        <input type="date" class="form-control" id="date_debut" name="date_debut" value="<?= htmlspecialchars($date_debut) ?>">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="date_fin">Date fin</label>
                        <input type="date" class="form-control" id="date_fin" name="date_fin" value="<?= htmlspecialchars($date_fin) ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="status">Statut</label>
                        <select class="form-control" id="status" name="status">
                            <option value="all" <?= $status == 'all' ? 'selected' : '' ?>>Tous</option>
                            <option value="en_attente" <?= $status == 'en_attente' ? 'selected' : '' ?>>En attente</option>
                            <option value="non_soldes" <?= $status == 'non_soldes' ? 'selected' : '' ?>>Non soldés</option>
                            <option value="soldes" <?= $status == 'soldes' ? 'selected' : '' ?>>Soldés</option>
                        </select>
                    </div>

                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Rechercher
                        </button>
                        <a href="paiements.php" class="btn btn-secondary">
                            <i class="fas fa-undo"></i> Réinitialiser
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>-->

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Gestion des Paiements des demandes</h3>
                <?php if (isset($_SESSION['last_receipt_id'])): ?>
                    <div class="float-right">
                        <a href="recu_demande_pdf.php?id=<?= $_SESSION['last_receipt_id'] ?>" target="_blank" class="btn btn-info btn-sm">
                            <i class="fas fa-print"></i> Imprimer le dernier reçu
                        </a>
                    </div>
                    <?php unset($_SESSION['last_receipt_id']); ?>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <table id="example1" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>N° de la demande</th>
                            <th>Motifs</th>
                            <th>Approbateur</th>
                            <th>Montant total</th>
                            <th>Montant payé</th>
                            <th>Reste à payer</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($demandes)) : ?>
                            <?php foreach ($demandes as $item) : ?>
                          
                                    <?php
                                    // Debug bordereaux
                                    echo "<!-- Affichage bordereau: ";
                                    print_r($item);
                                    echo " -->";
                                    ?>
                                    <tr>
                                        <td><?= isset($item['date_approbation']) ? date('Y-m-d', strtotime($item['date_approbation'])) : '-' ?></td>
                                        <td><?= $item['numero_demande'] ?></td>
                                        <td><?= $item['motif'] ?></td>
                                        <td><?= $item['approbateur'] ?></td>
                                        <td><?= number_format($item['montant'], 0, ',', ' ') ?> FCFA</td>
                                        <td><?= number_format(floatval($item['montant_payer']), 0, ',', ' ') ?> FCFA</td>
                                        <td><?= number_format(floatval($item['montant_reste']), 0, ',', ' ') ?> FCFA</td>
                                        <td>
                                            <?php if (floatval($item['montant_reste']) <= 0): ?>
                                                <button type="button" class="btn btn-success btn-sm" disabled>
                                                    <i class="fas fa-check-circle"></i> Demande soldée
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#payer_demande<?= $item['id_demande'] ?>">
                                                    <i class="fas fa-money-bill-wave"></i> Effectuer un paiement
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>

                                    <!-- Payment Modal -->
                                    <div class="modal fade" id="payer_demande<?= $item['id_demande'] ?>" tabindex="-1" role="dialog" aria-labelledby="paymentModalLabel<?= $item['id_demande'] ?>" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="paymentModalLabel<?= $item['id_demande'] ?>">
                                                        Paiement de la demande #<?= $item['numero_demande'] ?>
                                                    </h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <form action="save_paiement_demande.php" method="POST" class="needs-validation" novalidate>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="save_paiement_demande" value="1">
                                                        <input type="hidden" name="id_demande" value="<?= $item['id_demande'] ?>">
                                                        <input type="hidden" name="numero_demande" value="<?= $item['numero_demande'] ?>">
                                                        <input type="hidden" name="type" value="demande">
                                                        <input type="hidden" name="status" value="all">

                                                        <div class="form-group">
                                                            <label>Montant total de la demande</label>
                                                            <div class="input-group">
                                                                <input type="text" class="form-control" value="<?= number_format(floatval($item['montant']), 0, ',', ' ') ?> FCFA" readonly>
                                                            </div>
                                                        </div>

                                                        <div class="form-group">
                                                            <label>Montant déjà payé</label>
                                                            <div class="input-group">
                                                                <input type="text" class="form-control" value="<?= number_format(floatval($item['montant_payer']), 0, ',', ' ') ?> FCFA" readonly>
                                                            </div>
                                                        </div>

                                                        <div class="form-group">
                                                            <label>Reste à payer</label>
                                                            <div class="input-group">
                                                                <input type="text" class="form-control" value="<?= number_format(floatval($item['montant_reste']), 0, ',', ' ') ?> FCFA" readonly>
                                                            </div>
                                                        </div>

                                                        <div class="form-group">
                                                            <label for="montant<?= $item['id_demande'] ?>">Montant du paiement <span class="text-danger">*</span></label>
                                                            <div class="input-group">
                                                                <input type="number" 
                                                                    class="form-control" 
                                                                    id="montant<?= $item['id_demande'] ?>"
                                                                    name="montant"
                                                                    min="1"
                                                                    max="<?= $item['montant_reste'] ?>"
                                                                    required
                                                                    placeholder="Entrez le montant du paiement">
                                                                <div class="input-group-append">
                                                                    <span class="input-group-text">FCFA</span>
                                                                </div>
                                                                <div class="invalid-feedback">
                                                                    Veuillez entrer un montant valide (entre 1 et <?= number_format($item['montant_reste'], 0, ',', ' ') ?> FCFA)
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="form-group">
                                                            <label for="source_paiement<?= $item['id_demande'] ?>">Source de paiement <span class="text-danger">*</span></label>
                                                            <select class="form-control select2" 
                                                                    id="source_paiement<?= $item['id_demande'] ?>" 
                                                                    name="source_paiement"
                                                                    required
                                                                    style="width: 100%">
                                                                <option value="">Sélectionner une source</option>
                                                                <option value="transactions">Transactions</option>
                                                                <option value="financement">Financement</option>
                                                            </select>
                                                            <div class="invalid-feedback">
                                                                Veuillez sélectionner une source de paiement
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="fas fa-save"></i> Effectuer le paiement
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="11" class="text-center">Aucun élément trouvé</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>

<!-- Required scripts -->
<script src="../../plugins/jquery/jquery.min.js"></script>
<script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../../plugins/select2/js/select2.full.min.js"></script>
<script src="../../plugins/jquery-ui/jquery-ui.min.js"></script>
<script src="../../plugins/chart.js/Chart.min.js"></script>
<script src="../../plugins/sparklines/sparkline.js"></script>
<script src="../../plugins/jqvmap/jquery.vmap.min.js"></script>
<script src="../../plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
<script src="../../plugins/jquery-knob/jquery.knob.min.js"></script>
<script src="../../plugins/moment/moment.min.js"></script>
<script src="../../plugins/daterangepicker/daterangepicker.js"></script>
<script src="../../plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<script src="../../plugins/summernote/summernote-bs4.min.js"></script>
<script src="../../plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<script src="../../dist/js/adminlte.js"></script>
<script src="../../plugins/jquery/jquery.min.js"></script>
<script src="../../plugins/jquery-ui/jquery-ui.min.js"></script>
<script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../../plugins/chart.js/Chart.min.js"></script>
<script src="../../plugins/sparklines/sparkline.js"></script>
<script src="../../plugins/jqvmap/jquery.vmap.min.js"></script>
<script src="../../plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
<script src="../../plugins/jquery-knob/jquery.knob.min.js"></script>
<script src="../../plugins/moment/moment.min.js"></script>
<script src="../../plugins/daterangepicker/daterangepicker.js"></script>
<script src="../../plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<script src="../../plugins/summernote/summernote-bs4.min.js"></script>
<script src="../../plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<script src="../../dist/js/adminlte.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $("#example1").DataTable({
        "responsive": true,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json"
        }
    });

    // Initialize Select2 with modern styling
    $('.select2').select2({
        theme: 'bootstrap4',
        minimumResultsForSearch: Infinity, // Disable search since we have few options
        width: '100%',
        placeholder: 'Sélectionnez une source',
        allowClear: false
    });

    // Reset form and Select2 on modal close
    $('.modal').on('hidden.bs.modal', function() {
        $(this).find('form').trigger('reset');
        $(this).find('select').val('').trigger('change');
        $(this).find('.was-validated').removeClass('was-validated');
    });

    // Form validation with helpful feedback
    (function() {
        'use strict';
        window.addEventListener('load', function() {
            var forms = document.getElementsByClassName('needs-validation');
            Array.prototype.filter.call(forms, function(form) {
                form.addEventListener('submit', function(event) {
                    if (form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    })();

    // Add subtle transitions
    $('.modal').on('show.bs.modal', function() {
        $(this).find('.modal-content')
               .css('opacity', 0)
               .animate({ opacity: 1 }, 200);
    });

    // Success message handling
    <?php if (isset($_SESSION['success_message'])): ?>
        toastr.success('<?= $_SESSION['success_message'] ?>');
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    // Error message handling
    <?php if (isset($_SESSION['error_message'])): ?>
        toastr.error('<?= $_SESSION['error_message'] ?>');
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
});
</script>

<style>
.select2-container--bootstrap4 .select2-selection {
    border-radius: 4px;
    padding: .375rem .75rem;
    border: 1px solid #ced4da;
    height: calc(2.25rem + 2px);
    transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
}

.select2-container--bootstrap4 .select2-selection:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

.select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
    line-height: 1.5;
    padding-left: 0;
    color: #495057;
}

.select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
    height: calc(2.25rem + 2px);
}

.modal-content {
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border: none;
    border-radius: 8px;
}

.modal-header {
    border-bottom: 1px solid #dee2e6;
    background-color: #f8f9fa;
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
}

.modal-footer {
    border-top: 1px solid #dee2e6;
    background-color: #f8f9fa;
    border-bottom-left-radius: 8px;
    border-bottom-right-radius: 8px;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    font-weight: 500;
    color: #212529;
}

.input-group-text {
    background-color: #f8f9fa;
    border: 1px solid #ced4da;
    color: #495057;
}

.btn {
    font-weight: 500;
    letter-spacing: 0.5px;
    transition: all 0.2s;
}

.btn-primary {
    background-color: #007bff;
    border-color: #007bff;
}

.btn-primary:hover {
    background-color: #0069d9;
    border-color: #0062cc;
}

.btn-secondary {
    background-color: #6c757d;
    border-color: #6c757d;
}

.btn-secondary:hover {
    background-color: #5a6268;
    border-color: #545b62;
}

.invalid-feedback {
    font-size: 80%;
    color: #dc3545;
}

.form-control.is-invalid,
.was-validated .form-control:invalid {
    border-color: #dc3545;
    padding-right: calc(1.5em + .75rem);
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%23dc3545' viewBox='0 0 12 12'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(.375em + .1875rem) center;
    background-size: calc(.75em + .375rem) calc(.75em + .375rem);
}

.form-control.is-valid,
.was-validated .form-control:valid {
    border-color: #28a745;
    padding-right: calc(1.5em + .75rem);
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8' viewBox='0 0 8 8'%3e%3cpath fill='%2328a745' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(.375em + .1875rem) center;
    background-size: calc(.75em + .375rem) calc(.75em + .375rem);
}
</style>
</body>
</html>

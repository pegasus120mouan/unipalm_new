<?php
require_once '../inc/functions/connexion.php';
include('header.php');

$id_user = $_SESSION['user_id'];

// Get total cash balance
$getSommeCaisseQuery = "SELECT
    SUM(CASE WHEN type_transaction = 'approvisionnement' THEN montant
             WHEN type_transaction = 'paiement' THEN -montant
             ELSE 0 END) AS solde_caisse
FROM transactions";
$getSommeCaisseQueryStmt = $conn->query($getSommeCaisseQuery);
$somme_caisse = $getSommeCaisseQueryStmt->fetch(PDO::FETCH_ASSOC);

// Get all transactions with pagination
$limit = $_GET['limit'] ?? 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$getTransactionsQuery = "SELECT t.*, 
       CONCAT(u.nom, ' ', u.prenoms) AS nom_utilisateur
FROM transactions t
LEFT JOIN utilisateurs u ON t.id_utilisateur = u.id
ORDER BY t.date_transaction DESC";
$getTransactionsStmt = $conn->query($getTransactionsQuery);
$transactions = $getTransactionsStmt->fetchAll(PDO::FETCH_ASSOC);

// Paginate results
$transaction_pages = array_chunk($transactions, $limit);
$transactions_list = $transaction_pages[$page - 1] ?? [];
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

<div class="row mb-3">
    <div class="col-md-12">
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add-transaction">
            <i class="fas fa-plus"></i> Effectuer un approvisionnement
        </button>

        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#print-transaction">   
            <i class="fas fa-print"></i> Imprimer la liste des transactions
        </button>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Historique des Transactions</h3>
            </div>
            <div class="card-body">
                <div style="max-height: 400px; overflow-y: auto;">
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Montant</th>
                                <th>Utilisateur</th>
                                <th>Motifs</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions_list as $transaction) : ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i', strtotime($transaction['date_transaction'])) ?></td>
                                    <td>
                                        <?php if ($transaction['type_transaction'] == 'approvisionnement'): ?>
                                            <span class="badge badge-success">Entrée</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Sortie</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= number_format($transaction['montant'], 0, ',', ' ') ?> FCFA</td>
                                    <td><?= $transaction['nom_utilisateur'] ?></td>
                                    <td><?= $transaction['motifs'] ?></td>
                                    <td>
                                        <a href="#" class="btn btn-info btn-sm" title="Voir détails">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="pagination-container bg-secondary d-flex justify-content-center w-100 text-white p-3">
                    <?php if($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>" class="btn btn-primary"><</a>
                    <?php endif; ?>

                    <span class="mx-2"><?= $page . '/' . count($transaction_pages) ?></span>

                    <?php if($page < count($transaction_pages)): ?>
                        <a href="?page=<?= $page + 1 ?>" class="btn btn-primary">></a>
                    <?php endif; ?>

                    <form action="" method="get" class="items-per-page-form">
                        <label for="limit">Afficher :</label>
                        <select name="limit" id="limit" class="items-per-page-select">
                            <option value="5" <?= $limit == 5 ? 'selected' : '' ?>>5</option>
                            <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10</option>
                            <option value="15" <?= $limit == 15 ? 'selected' : '' ?>>15</option>
                        </select>
                        <button type="submit" class="submit-button">Valider</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for new transaction -->
<div class="modal fade" id="add-transaction">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Effectuer un approvisionnement</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="needs-validation" method="post" action="save_approvisionnement.php" novalidate>
                <div class="modal-body">
                    <input type="hidden" name="save_approvisionnement" value="1">
                    
                    <div class="form-group">
                        <label for="montant">Montant <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" 
                                class="form-control" 
                                id="montant" 
                                name="montant"
                                min="1" 
                                placeholder="Entrez le montant"
                                required>
                            <div class="input-group-append">
                                <span class="input-group-text">FCFA</span>
                            </div>
                            <div class="invalid-feedback">
                                Le montant est requis et doit être supérieur à 0
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="motifs">Motif de l'approvisionnement <span class="text-danger">*</span></label>
                        <textarea 
                            class="form-control" 
                            id="motifs" 
                            name="motifs" 
                            rows="3"
                            placeholder="Entrez le motif de l'approvisionnement"
                            required></textarea>
                        <div class="invalid-feedback">
                            Le motif de l'approvisionnement est requis
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Effectuer l'approvisionnement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="print-transaction">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h4 class="modal-title">
                    <i class="fas fa-print"></i> Imprimer la liste des transactions
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= $_SESSION['error_message'] ?>
                        <?php unset($_SESSION['error_message']); ?>
                    </div>
                <?php endif; ?>
                
                <form class="forms-sample" method="post" action="impression_transactions.php">
                    <div class="form-group">
                        <label for="date_debut" class="font-weight-bold mb-2">Sélectionner la date de début</label>
                        <div class="position-relative">
                            <input type="date" 
                                   class="form-control shadow-sm" 
                                   id="date_debut" 
                                   name="date_debut_transactions"
                                   placeholder="Date de début"
                                   required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="date_fin" class="font-weight-bold mb-2">Sélectionner la date de fin</label>
                        <div class="position-relative">
                            <input type="date" 
                                   class="form-control shadow-sm" 
                                   id="date_fin" 
                                   name="date_fin_transactions"
                                   placeholder="Date de fin"
                                   required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Fermer
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-print"></i> Imprimer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
/* Styles pour les champs de saisie */
.form-control {
    padding: 0.375rem 0.75rem;
    font-size: 1rem;
    line-height: 1.5;
    color: #495057;
    background-color: #fff;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-control:focus {
    color: #495057;
    background-color: #fff;
    border-color: #80bdff;
    outline: 0;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

.form-control::placeholder {
    color: #6c757d;
    opacity: 1;
}

/* Styles pour les labels */
.form-group label {
    font-weight: bold;
    margin-bottom: 0.5rem;
    display: block;
}

/* Effets d'ombre */
.shadow-sm {
    box-shadow: 0 .125rem .25rem rgba(0,0,0,.075)!important;
}

/* Espacement */
.form-group {
    margin-bottom: 1rem;
}

.mb-2 {
    margin-bottom: 0.5rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validation des dates pour l'impression
    document.querySelector('form[action="impression_transactions.php"]').addEventListener('submit', function(e) {
        const dateDebut = document.getElementById('date_debut').value;
        const dateFin = document.getElementById('date_fin').value;

        if (!dateDebut || !dateFin) {
            e.preventDefault();
            alert('Veuillez sélectionner les dates de début et de fin');
            return;
        }

        if (dateDebut > dateFin) {
            e.preventDefault();
            alert('La date de début doit être antérieure à la date de fin');
            return;
        }
    });

    // Réinitialisation du formulaire à la fermeture du modal
    $('#print-transaction').on('hidden.bs.modal', function () {
        document.getElementById('date_debut').value = '';
        document.getElementById('date_fin').value = '';
    });
});
</script>

<script>
// Formatage des nombres avec séparateurs de milliers
document.getElementById('montant').addEventListener('input', function(e) {
    // Enlever tous les espaces et caractères non numériques
    let value = this.value.replace(/\s/g, '').replace(/[^\d]/g, '');
    
    // Formatter le nombre avec des espaces comme séparateurs de milliers
    if (value) {
        value = parseInt(value, 10).toLocaleString('fr-FR').replace(/,/g, ' ');
    }
    
    // Mettre à jour la valeur affichée
    this.value = value;
});

// Avant la soumission du formulaire, nettoyer le format pour n'envoyer que les chiffres
document.querySelector('form').addEventListener('submit', function(e) {
    let montantInput = document.getElementById('montant');
    montantInput.value = montantInput.value.replace(/\s/g, '');
});
</script>

<!-- Required scripts -->
<script src="../../plugins/jquery/jquery.min.js"></script>
<script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
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

<script>
$(function () {
    $('#example1').DataTable({
        "paging": false,
        "lengthChange": false,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
    });
});
</script>

<style>
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

/* Toast notifications styling */
.toast-top-right {
    top: 12px;
    right: 12px;
}

#toast-container > div {
    opacity: 1;
    -ms-filter: progid:DXImageTransform.Microsoft.Alpha(Opacity=100);
    filter: alpha(opacity=100);
}

.toast {
    background-color: #fff;
    border-radius: 4px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.toast-success {
    background-color: #51A351;
}

.toast-error {
    background-color: #BD362F;
}

.toast-info {
    background-color: #2F96B4;
}

.toast-warning {
    background-color: #F89406;
}

.badge {
    padding: 0.5em 0.75em;
    font-weight: 500;
}

.badge-success {
    background-color: #28a745;
}

.badge-danger {
    background-color: #dc3545;
}
</style>

<script>
$(document).ready(function() {
    // Initialize DataTable with French localization
    $("#example1").DataTable({
        "responsive": true,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json"
        },
        "order": [[0, "desc"]] // Sort by date descending
    });

    // Form validation
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

    // Reset form on modal close
    $('.modal').on('hidden.bs.modal', function() {
        $(this).find('form').trigger('reset');
        $(this).find('.was-validated').removeClass('was-validated');
    });

    // Add subtle transitions
    $('.modal').on('show.bs.modal', function() {
        $(this).find('.modal-content')
               .css('opacity', 0)
               .animate({ opacity: 1 }, 200);
    });

    // Success message handling with toastr
    <?php if (isset($_SESSION['success_message'])): ?>
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "5000"
        };
        toastr.success('<?= $_SESSION['success_message'] ?>');
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    // Error message handling with toastr
    <?php if (isset($_SESSION['error_message'])): ?>
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "7000"
        };
        toastr.error('<?= $_SESSION['error_message'] ?>');
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
});
</script>

<?php include('footer.php'); ?>
</body>
</html>

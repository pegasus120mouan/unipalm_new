<?php
require_once '../inc/functions/connexion.php';
require_once 'header.php';

// Récupérer le solde actuel
$stmt = $conn->prepare("SELECT solde FROM transactions ORDER BY date_transaction DESC LIMIT 1");
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$solde_actuel = $result ? floatval($result['solde']) : 0;

// Récupérer l'historique des transactions
$stmt = $conn->prepare("
    SELECT t.*, 
        CONCAT(u.nom, ' ', u.prenoms) as nom_utilisateur
    FROM transactions t
    LEFT JOIN utilisateurs u ON t.id_utilisateur = u.id
    ORDER BY t.date_transaction DESC
");
$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Gestion des transactions</h1>
                </div>
                <div class="col-sm-6">
                    <div class="float-sm-right">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title mb-0">Solde Caisse</h5>
                                <p class="display-6 mb-0"><?= number_format($solde_actuel, 0, ',', ' ') ?> FCFA</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Messages de succès/erreur -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['success_message'] ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $_SESSION['error_message'] ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <!-- Bouton pour nouvel approvisionnement -->
            <div class="row mb-3">
                <div class="col-12">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-approvisionnement">
                        <i class="fas fa-plus"></i> Nouvel approvisionnement
                    </button>
                </div>
            </div>

            <!-- Liste des transactions -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Historique des transactions</h3>
                        </div>
                        <div class="card-body">
                            <table id="transactionsTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Montant</th>
                                        <th>Solde</th>
                                        <th>Motif</th>
                                        <th>Utilisateur</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transactions as $transaction): 
                                        $typeClass = '';
                                        switch ($transaction['type_transaction']) {
                                            case 'paiement':
                                                $typeClass = 'text-danger';
                                                break;
                                            case 'approvisionnement':
                                                $typeClass = 'text-success';
                                                break;
                                            case 'annulation':
                                                $typeClass = 'text-warning';
                                                break;
                                        }
                                    ?>
                                    <tr>
                                        <td><?= date('d/m/Y H:i', strtotime($transaction['date_transaction'])) ?></td>
                                        <td class="<?= $typeClass ?>">
                                            <?= ucfirst($transaction['type_transaction']) ?>
                                        </td>
                                        <td class="<?= $typeClass ?>">
                                            <?= number_format($transaction['montant'], 0, ',', ' ') ?> FCFA
                                        </td>
                                        <td>
                                            <?= number_format($transaction['solde'], 0, ',', ' ') ?> FCFA
                                        </td>
                                        <td><?= htmlspecialchars($transaction['motifs']) ?></td>
                                        <td><?= htmlspecialchars($transaction['nom_utilisateur']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal Approvisionnement -->
<div class="modal fade" id="modal-approvisionnement">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Nouvel approvisionnement</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="save_approvisionnement.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="save_approvisionnement" value="1">
                    
                    <div class="form-group">
                        <label for="montant">Montant (FCFA) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="montant" name="montant" required min="1">
                    </div>

                    <div class="form-group">
                        <label for="motifs">Motif <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="motifs" name="motifs" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>

<!-- DataTables -->
<script src="../../plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../../plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../../plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="../../plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>

<script>
$(function () {
    // Initialiser DataTables
    $('#transactionsTable').DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json"
        },
        "order": [[0, "desc"]],
        "pageLength": 25,
        "buttons": ["copy", "csv", "excel", "pdf", "print"]
    }).buttons().container().appendTo('#transactionsTable_wrapper .col-md-6:eq(0)');
});
</script>

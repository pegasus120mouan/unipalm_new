<?php
require_once '../inc/functions/connexion.php';
include('header.php');


$id_user=$_SESSION['user_id'];

//echo $id_user;
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
                        <strong><?php echo number_format($somme_caisse['solde_caisse'], 0, ',', ' '); ?> FCFA</strong>
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
            <div class="modal-body">
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger">
                        <?= $_SESSION['error_message'] ?>
                        <?php unset($_SESSION['error_message']); ?>
                    </div>
                <?php endif; ?>
                
                <form class="forms-sample" method="post" action="save_transaction.php">
                    <div class="form-group">
                        <label>Type de Transaction</label>
                        <select class="form-control" name="type_transaction" required>
                            <option value="approvisionnement">Entrée de caisse</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Montant</label>
                        <input type="number" class="form-control" name="montant" placeholder="Montant" required>
                    </div>
                    <button type="submit" class="btn btn-primary" name="save_transaction">Enregistrer</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Required scripts -->
<script src="../../plugins/jquery/jquery.min.js"></script>
<script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../../plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../../plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../../dist/js/adminlte.min.js"></script>

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
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

// Déterminer le type d'affichage et le statut
$type = isset($_GET['type']) ? $_GET['type'] : 'all';
$status = isset($_GET['status']) ? $_GET['status'] : 'all';

// Récupérer les bordereaux
$sql_bordereaux = "
    SELECT 
        b.*,
        CONCAT(a.nom, ' ', a.prenom) AS agent_nom_complet,
        a.contact AS agent_contact,
        COALESCE(b.montant_total, 0) as montant_total,
        COALESCE(b.montant_payer, 0) as montant_payer,
        COALESCE(b.montant_reste, b.montant_total - COALESCE(b.montant_payer, 0)) as montant_reste
    FROM bordereau b
    LEFT JOIN agents a ON b.id_agent = a.id_agent
    WHERE b.date_validation_boss IS NOT NULL
    ORDER BY b.date_validation_boss DESC";

$stmt_bordereaux = $conn->prepare($sql_bordereaux);
$stmt_bordereaux->execute();
$all_bordereaux = $stmt_bordereaux->fetchAll(PDO::FETCH_ASSOC);

// Filtrer les bordereaux selon le statut
if ($status === 'en_attente') {
    $bordereaux = array_filter($all_bordereaux, function($bordereau) {
        return $bordereau['montant_payer'] == 0;
    });
} elseif ($status === 'non_soldes') {
    $bordereaux = array_filter($all_bordereaux, function($bordereau) {
        return $bordereau['montant_payer'] > 0 && ($bordereau['montant_total'] - $bordereau['montant_payer']) > 0;
    });
} elseif ($status === 'soldes') {
    $bordereaux = array_filter($all_bordereaux, function($bordereau) {
        return ($bordereau['montant_total'] - $bordereau['montant_payer']) <= 0;
    });
} else {
    $bordereaux = $all_bordereaux;
}

// Récupérer les tickets
$sql_tickets = "
    SELECT 
        t.*,
        CONCAT(a.nom, ' ', a.prenom) AS agent_nom_complet,
        a.contact AS agent_contact,
        us.nom_usine,
        v.matricule_vehicule,
        COALESCE(t.montant_payer, 0) as montant_payer,
        COALESCE(t.montant_reste, t.montant_paie - COALESCE(t.montant_payer, 0)) as montant_reste
    FROM tickets t
    INNER JOIN agents a ON t.id_agent = a.id_agent
    INNER JOIN usines us ON t.id_usine = us.id_usine
    INNER JOIN vehicules v ON t.vehicule_id = v.vehicules_id
    WHERE t.date_validation_boss IS NOT NULL
    AND t.numero_bordereau IS NULL
    ORDER BY t.date_validation_boss DESC";

$stmt_tickets = $conn->prepare($sql_tickets);
$stmt_tickets->execute();
$all_tickets = $stmt_tickets->fetchAll(PDO::FETCH_ASSOC);

// Filtrer les tickets selon le statut
if ($status === 'en_attente') {
    $tickets = array_filter($all_tickets, function($ticket) {
        return $ticket['montant_payer'] == 0;
    });
} elseif ($status === 'non_soldes') {
    $tickets = array_filter($all_tickets, function($ticket) {
        return $ticket['montant_payer'] > 0 && $ticket['montant_reste'] > 0;
    });
} elseif ($status === 'soldes') {
    $tickets = array_filter($all_tickets, function($ticket) {
        return $ticket['montant_reste'] <= 0;
    });
} else {
    $tickets = $all_tickets;
}

// Combiner les éléments selon le type d'affichage
if ($type === 'bordereaux') {
    $items = array_map(function($b) { return array_merge($b, ['type' => 'bordereau']); }, $bordereaux);
} elseif ($type === 'tickets') {
    $items = array_map(function($t) { return array_merge($t, ['type' => 'ticket']); }, $tickets);
} else {
    $items = array_merge(
        array_map(function($b) { return array_merge($b, ['type' => 'bordereau']); }, $bordereaux),
        array_map(function($t) { return array_merge($t, ['type' => 'ticket']); }, $tickets)
    );
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 15;
$total_items = count($items);
$total_pages = ceil($total_items / $items_per_page);
$page = max(1, min($page, $total_pages));
$offset = ($page - 1) * $items_per_page;
$items = array_slice($items, $offset, $items_per_page);

// Get total cash balance
$getSommeCaisseQuery = "SELECT
    SUM(CASE WHEN type_transaction = 'approvisionnement' THEN montant
             WHEN type_transaction = 'paiement' THEN -montant
             ELSE 0 END) AS solde_caisse
FROM transactions";
$getSommeCaisseQueryStmt = $conn->query($getSommeCaisseQuery);
$somme_caisse = $getSommeCaisseQueryStmt->fetch(PDO::FETCH_ASSOC);

// Get all transactions with pagination
$getTransactionsQuery = "SELECT t.*, 
       CONCAT(u.nom, ' ', u.prenoms) AS nom_utilisateur
FROM transactions t
LEFT JOIN utilisateurs u ON t.id_utilisateur = u.id
ORDER BY t.date_transaction DESC";
$getTransactionsStmt = $conn->query($getTransactionsQuery);
$transactions = $getTransactionsStmt->fetchAll(PDO::FETCH_ASSOC);

// Paginate results
$transaction_pages = array_chunk($transactions, 15);
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
                        <strong><?php echo number_format($somme_caisse['solde_caisse'] ?? 0, 0, ',', ' '); ?> FCFA</strong>
                    </h1>
                </span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Gestion des Paiements</h3>
                <div class="float-right">
                    <!-- Filtres par type -->
                    <div class="btn-group mr-2">
                        <a href="paiements.php?type=all&status=<?= $status ?>" 
                           class="btn <?= $type === 'all' ? 'btn-primary' : 'btn-outline-primary' ?>">
                            <i class="fas fa-list"></i> Tous
                        </a>
                        <a href="paiements.php?type=bordereaux&status=<?= $status ?>" 
                           class="btn <?= $type === 'bordereaux' ? 'btn-primary' : 'btn-outline-primary' ?>">
                            <i class="fas fa-file-alt"></i> Bordereaux
                        </a>
                        <a href="paiements.php?type=tickets&status=<?= $status ?>" 
                           class="btn <?= $type === 'tickets' ? 'btn-primary' : 'btn-outline-primary' ?>">
                            <i class="fas fa-ticket-alt"></i> Tickets
                        </a>
                    </div>

                    <!-- Filtres par statut -->
                    <div class="btn-group">
                        <a href="paiements.php?type=<?= $type ?>&status=all" 
                           class="btn <?= $status === 'all' ? 'btn-info' : 'btn-outline-info' ?>">
                            <i class="fas fa-list"></i> Tous
                        </a>
                        <a href="paiements.php?type=<?= $type ?>&status=en_attente" 
                           class="btn <?= $status === 'en_attente' ? 'btn-secondary' : 'btn-outline-secondary' ?>">
                            <i class="fas fa-clock"></i> En attente
                        </a>
                        <a href="paiements.php?type=<?= $type ?>&status=non_soldes" 
                           class="btn <?= $status === 'non_soldes' ? 'btn-warning' : 'btn-outline-warning' ?>">
                            <i class="fas fa-sync"></i> En cours
                        </a>
                        <a href="paiements.php?type=<?= $type ?>&status=soldes" 
                           class="btn <?= $status === 'soldes' ? 'btn-success' : 'btn-outline-success' ?>">
                            <i class="fas fa-check-circle"></i> Soldés
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <table id="example1" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>N° Ticket/Bordereau</th>
                            <th>Usine</th>
                            <th>Chargé de Mission</th>
                            <th>Véhicule</th>
                            <th>Poids</th>
                            <th>Montant total</th>
                            <th>Montant payé</th>
                            <th>Reste à payer</th>
                            <th>Dernier paiement</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($items)) : ?>
                            <?php foreach ($items as $item) : ?>
                                <?php if ($item['type'] === 'bordereau') : ?>
                                    <?php
                                    // Debug bordereaux
                                    echo "<!-- Affichage bordereau: ";
                                    print_r($item);
                                    echo " -->";
                                    ?>
                                    <tr>
                                        <td><?= isset($item['date_debut']) ? date('Y-m-d', strtotime($item['date_debut'])) : '-' ?></td>
                                        <td><?= $item['numero_bordereau'] ?></td>
                                        <td>-</td>
                                        <td><?= $item['agent_nom_complet'] ?></td>
                                        <td>-</td>
                                        <td><?= number_format($item['poids_total'], 0, ',', ' ') ?></td>
                                        <td><?= number_format($item['montant_total'], 0, ',', ' ') ?> FCFA</td>
                                        <td><?= number_format($item['montant_payer'], 0, ',', ' ') ?> FCFA</td>
                                        <td><?= number_format($item['montant_reste'], 0, ',', ' ') ?> FCFA</td>
                                        <td><?= isset($item['date_paie']) ? date('Y-m-d', strtotime($item['date_paie'])) : '-' ?></td>
                                        <td>
                                            <?php if ($item['montant_reste'] <= 0): ?>
                                                <button type="button" class="btn btn-success" disabled>
                                                    <i class="fas fa-check-circle"></i> Bordereau soldé
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#payer_bordereau<?= $item['id_bordereau'] ?>">
                                                    <i class="fas fa-money-bill-wave"></i> Effectuer un paiement
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php else : ?>
                                    <?php
                                    $montant_paye = !isset($item['montant_payer']) || $item['montant_payer'] === null ? 0 : $item['montant_payer'];
                                    $montant_total = $item['montant_paie'];
                                    $montant_reste = $montant_total - $montant_paye;
                                    ?>
                                    <tr>
                                        <td><?= date('Y-m-d', strtotime($item['date_ticket'])) ?></td>
                                        <td><?= $item['numero_ticket'] ?></td>
                                        <td><?= $item['nom_usine'] ?></td>
                                        <td><?= $item['agent_nom_complet'] ?></td>
                                        <td><?= $item['matricule_vehicule'] ?></td>
                                        <td><?= number_format($item['poids'], 0, ',', ' ') ?></td>
                                        <td><?= number_format($montant_total, 0, ',', ' ') ?> FCFA</td>
                                        <td><?= number_format($montant_paye, 0, ',', ' ') ?> FCFA</td>
                                        <td><?= number_format($montant_reste, 0, ',', ' ') ?> FCFA</td>
                                        <td><?= $item['date_paie'] ? date('Y-m-d', strtotime($item['date_paie'])) : '-' ?></td>
                                        <td>                
                                            <?php if ($montant_reste <= 0): ?>
                                                <button type="button" class="btn btn-success" disabled>
                                                    <i class="fas fa-check-circle"></i> Ticket soldé
                                                </button>
                                            <?php elseif ($item['prix_unitaire'] <= 0): ?>
                                                <button type="button" class="btn btn-warning" disabled>
                                                    <i class="fas fa-exclamation-circle"></i> Prix unitaire non défini
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#payer_ticket<?= $item['id_ticket'] ?>">
                                                    <i class="fas fa-money-bill-wave"></i> Effectuer un paiement
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="11" class="text-center">Aucun élément trouvé</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div class="pagination-container bg-secondary d-flex justify-content-center w-100 text-white p-3">
                    <?php if($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>&type=<?= $type ?>&status=<?= $status ?>" class="btn btn-primary"><</a>
                    <?php endif; ?>
                    <span class="mx-2"><?= $page . '/' . $total_pages ?></span>

                    <?php if($page < $total_pages): ?>
                        <a href="?page=<?= $page + 1 ?>&type=<?= $type ?>&status=<?= $status ?>" class="btn btn-primary">></a>
                    <?php endif; ?>
                    <form action="" method="get" class="items-per-page-form">
                        <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
                        <input type="hidden" name="status" value="<?= htmlspecialchars($status) ?>">
                        <label for="limit">Afficher :</label>
                        <select name="limit" id="limit" class="items-per-page-select">
                            <option value="5" <?= 15 == 5 ? '' : 'selected' ?>>5</option>
                            <option value="10" <?= 15 == 10 ? '' : 'selected' ?>>10</option>
                            <option value="15" <?= 15 == 15 ? 'selected' : '' ?>>15</option>
                        </select>
                        <button type="submit" class="submit-button">Valider</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for new transaction -->
<?php foreach ($items as $item) : ?>
    <?php if ($item['type'] === 'bordereau') : ?>
        <?php $financement = getFinancementAgent($conn, $item['id_agent']); ?>
        <div class="modal fade" id="payer_bordereau<?= $item['id_bordereau'] ?>">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Paiement du bordereau #<?= $item['numero_bordereau'] ?></h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form class="forms-sample" method="post" action="save_paiement.php">
                            <input type="hidden" name="id_bordereau" value="<?= $item['id_bordereau'] ?>">
                            <input type="hidden" name="numero_bordereau" value="<?= $item['numero_bordereau'] ?>">
                            <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
                            <input type="hidden" name="status" value="<?= htmlspecialchars($status) ?>">
                            <input type="hidden" name="montant_reste" value="<?= $item['montant_reste'] ?>">
                            
                            <div class="form-group">
                                <label>Montant total à payer</label>
                                <input type="text" class="form-control" value="<?= number_format($item['montant_total'], 0, ',', ' ') ?> FCFA" readonly>
                            </div>
                            
                            <div class="form-group">
                                <label>Montant déjà payé</label>
                                <input type="text" class="form-control" value="<?= number_format($item['montant_payer'], 0, ',', ' ') ?> FCFA" readonly>
                            </div>
                            
                            <div class="form-group">
                                <label>Reste à payer</label>
                                <input type="text" class="form-control" value="<?= number_format($item['montant_reste'], 0, ',', ' ') ?> FCFA" readonly>
                            </div>

                            <div class="form-group">
                                <label>Source de paiement</label>
                                <select class="form-control" name="source_paiement" required>
                                    <option value="transactions">Sortie de caisse</option>
                                    <option value="financement" <?= (!$financement || $financement['montant_total'] <= 0) ? 'disabled style="color: #999; background-color: #f4f4f4;"' : '' ?>>
                                        Financement (Solde: <?= number_format(($financement ? $financement['montant_total'] : 0), 0, ',', ' ') ?> FCFA)
                                    </option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Montant à payer (Max: <?= number_format($item['montant_reste'], 0, ',', ' ') ?> FCFA)</label>
                                <input 
                                    type="text" 
                                    class="form-control montant-input" 
                                    name="montant_affiche" 
                                    required 
                                    data-max="<?= $item['montant_reste'] ?>" 
                                    placeholder="Entrez le montant à payer">

                                <!-- Champ caché contenant la valeur brute (sans espaces) -->
                                <input type="hidden" name="montant" value="">
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                                <button type="submit" name="save_paiement" class="btn btn-primary">Effectuer le paiement</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php else : ?>
        <?php $financement = getFinancementAgent($conn, $item['id_agent']); ?>
        <div class="modal fade" id="payer_ticket<?= $item['id_ticket'] ?>">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Paiement du ticket #<?= $item['numero_ticket'] ?></h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form class="forms-sample" method="post" action="save_paiement.php">
                            <input type="hidden" name="id_ticket" value="<?= $item['id_ticket'] ?>">
                            <input type="hidden" name="numero_ticket" value="<?= $item['numero_ticket'] ?>">
                            <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
                            <input type="hidden" name="status" value="<?= htmlspecialchars($status) ?>">
                            <input type="hidden" name="montant_reste" value="<?= $item['montant_paie'] - (!isset($item['montant_payer']) || $item['montant_payer'] === null ? 0 : $item['montant_payer']) ?>">
                            
                            <div class="form-group">
                                <label>Montant total à payer</label>
                                <input type="text" class="form-control" value="<?= number_format($item['montant_paie'], 0, ',', ' ') ?> FCFA" readonly>
                            </div>
                            
                            <div class="form-group">
                                <label>Montant déjà payé</label>
                                <input type="text" class="form-control" value="<?= !isset($item['montant_payer']) || $item['montant_payer'] === null ? '0 FCFA' : number_format($item['montant_payer'], 0, ',', ' ') . ' FCFA' ?>" readonly>
                            </div>
                            
                            <div class="form-group">
                                <label>Reste à payer</label>
                                <input type="text" class="form-control" value="<?= number_format($item['montant_paie'] - (!isset($item['montant_payer']) || $item['montant_payer'] === null ? 0 : $item['montant_payer']), 0, ',', ' ') ?> FCFA" readonly>
                            </div>

                            <div class="form-group">
                                <label>Source de paiement</label>
                                <select class="form-control" name="source_paiement" required>
                                    <option value="transactions">Sortie de caisse</option>
                                    <option value="financement" <?= (!$financement || $financement['montant_total'] <= 0) ? 'disabled style="color: #999; background-color: #f4f4f4;"' : '' ?>>
                                        Financement (Solde: <?= number_format(($financement ? $financement['montant_total'] : 0), 0, ',', ' ') ?> FCFA)
                                    </option>
                                </select>
                            </div>

                            <div class="form-group">
    <label>Montant à payer (Max: <?= number_format($item['montant_reste'], 0, ',', ' ') ?> FCFA)</label>
    <input 
        type="text" 
        class="form-control montant-input" 
        name="montant_affiche" 
        required 
        data-max="<?= $item['montant_reste'] ?>" 
        placeholder="Entrez le montant à payer">

    <!-- Champ caché contenant la valeur brute (sans espaces) pour l'envoi -->
    <input type="hidden" name="montant" value="">
</div>


                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                                <button type="submit" name="save_paiement" class="btn btn-primary">Effectuer le paiement</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endforeach; ?>

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
        $("#example1").DataTable({
            "responsive": true,
        });
    });
</script>

<script>
 // Fonction pour ajouter des espaces tous les 3 chiffres
function formatNumberWithSpaces(number) {
    return number.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
}

// Appliquer le formatage + contrôle max à toutes les inputs montant-input
document.querySelectorAll('.montant-input').forEach(function(input) {
    input.addEventListener('input', function() {
        let rawValue = input.value.replace(/\s+/g, '').replace(/[^0-9]/g, '');  // Nettoyer
        const max = parseInt(input.getAttribute('data-max'), 10);

        // Mettre à jour le champ caché
        input.nextElementSibling.value = rawValue;

        // Appliquer format
        input.value = formatNumberWithSpaces(rawValue);

        // Contrôle max
        if (parseInt(rawValue) > max) {
            alert('Le montant ne peut pas dépasser ' + formatNumberWithSpaces(max.toString()) + ' FCFA');
            input.value = formatNumberWithSpaces(max.toString());
            input.nextElementSibling.value = max;
        }
    });

    // Format initial si champ pré-rempli
    let initialRawValue = input.value.replace(/\s+/g, '').replace(/[^0-9]/g, '');
    input.value = formatNumberWithSpaces(initialRawValue);
    input.nextElementSibling.value = initialRawValue;
});

</script>

</body>
</html>

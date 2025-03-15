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

// Récupérer les paramètres de filtrage
$type = isset($_GET['type']) ? $_GET['type'] : 'all';
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$search_numero = isset($_GET['search_numero']) ? $_GET['search_numero'] : '';
$search_usine = isset($_GET['search_usine']) ? $_GET['search_usine'] : '';
$search_agent = isset($_GET['search_agent']) ? $_GET['search_agent'] : '';
$search_vehicule = isset($_GET['search_vehicule']) ? $_GET['search_vehicule'] : '';
$date_debut = isset($_GET['date_debut']) ? $_GET['date_debut'] : '';
$date_fin = isset($_GET['date_fin']) ? $_GET['date_fin'] : '';

// Récupérer les listes pour les filtres
$sql_usines = "SELECT id_usine, nom_usine FROM usines ORDER BY nom_usine";
$stmt_usines = $conn->query($sql_usines);
$usines = $stmt_usines->fetchAll(PDO::FETCH_ASSOC);

$sql_agents = "SELECT id_agent, CONCAT(nom, ' ', prenom) as nom_complet FROM agents ORDER BY nom, prenom";
$stmt_agents = $conn->query($sql_agents);
$agents = $stmt_agents->fetchAll(PDO::FETCH_ASSOC);

$sql_vehicules = "SELECT vehicules_id, matricule_vehicule FROM vehicules ORDER BY matricule_vehicule";
$stmt_vehicules = $conn->query($sql_vehicules);
$vehicules = $stmt_vehicules->fetchAll(PDO::FETCH_ASSOC);

$solde_caisse = getSoldeCaisse();

// Construction des conditions WHERE pour les bordereaux
$where_bordereaux = [];
$params_bordereaux = [];

if (!empty($search_numero)) {
    $where_bordereaux[] = "b.numero_bordereau LIKE :numero_bordereau";
    $params_bordereaux[':numero_bordereau'] = "%$search_numero%";
}

if (!empty($search_agent)) {
    $where_bordereaux[] = "b.id_agent = :id_agent";
    $params_bordereaux[':id_agent'] = $search_agent;
}

if (!empty($date_debut)) {
    $where_bordereaux[] = "DATE(b.created_at) >= :date_debut";
    $params_bordereaux[':date_debut'] = $date_debut;
}

if (!empty($date_fin)) {
    $where_bordereaux[] = "DATE(b.created_at) <= :date_fin";
    $params_bordereaux[':date_fin'] = $date_fin;
}

// Condition de statut pour les bordereaux
if ($status !== 'all') {
    switch ($status) {
        case 'en_attente':
            $where_bordereaux[] = "b.date_validation_boss IS NULL";
            break;
        case 'non_soldes':
            $where_bordereaux[] = "b.date_validation_boss IS NOT NULL AND b.montant_reste > 0";
            break;
        case 'soldes':
            $where_bordereaux[] = "b.date_validation_boss IS NOT NULL AND b.montant_reste = 0";
            break;
    }
}

// Construction de la clause WHERE finale pour les bordereaux
$where_clause_bordereaux = !empty($where_bordereaux) ? "WHERE " . implode(" AND ", $where_bordereaux) : "";

// Requête pour les bordereaux
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
    $where_clause_bordereaux
    ORDER BY b.created_at DESC";

$stmt_bordereaux = $conn->prepare($sql_bordereaux);
foreach ($params_bordereaux as $key => $value) {
    $stmt_bordereaux->bindValue($key, $value);
}
$stmt_bordereaux->execute();
$all_bordereaux = $stmt_bordereaux->fetchAll(PDO::FETCH_ASSOC);

// Construction des conditions WHERE pour les tickets
$where_tickets = [];
$params_tickets = [];

if (!empty($search_numero)) {
    $where_tickets[] = "t.numero_ticket LIKE :numero_ticket";
    $params_tickets[':numero_ticket'] = "%$search_numero%";
}

if (!empty($search_usine)) {
    $where_tickets[] = "t.id_usine = :id_usine";
    $params_tickets[':id_usine'] = $search_usine;
}

if (!empty($search_agent)) {
    $where_tickets[] = "t.id_agent = :id_agent";
    $params_tickets[':id_agent'] = $search_agent;
}

if (!empty($search_vehicule)) {
    $where_tickets[] = "t.vehicule_id = :vehicule_id";
    $params_tickets[':vehicule_id'] = $search_vehicule;
}

if (!empty($date_debut)) {
    $where_tickets[] = "DATE(t.created_at) >= :date_debut";
    $params_tickets[':date_debut'] = $date_debut;
}

if (!empty($date_fin)) {
    $where_tickets[] = "DATE(t.created_at) <= :date_fin";
    $params_tickets[':date_fin'] = $date_fin;
}

// Condition de statut pour les tickets
if ($status !== 'all') {
    switch ($status) {
        case 'en_attente':
            $where_tickets[] = "t.date_validation_boss IS NULL";
            break;
        case 'non_soldes':
            $where_tickets[] = "t.date_validation_boss IS NOT NULL AND t.montant_reste > 0";
            break;
        case 'soldes':
            $where_tickets[] = "t.date_validation_boss IS NOT NULL AND t.montant_reste = 0";
            break;
    }
}

// Condition pour les tickets non inclus dans un bordereau
$where_tickets[] = "t.numero_bordereau IS NULL";

// Construction de la clause WHERE finale pour les tickets
$where_clause_tickets = !empty($where_tickets) ? "WHERE " . implode(" AND ", $where_tickets) : "";

// Requête pour les tickets
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
    $where_clause_tickets
    ORDER BY t.created_at DESC";

$stmt_tickets = $conn->prepare($sql_tickets);
foreach ($params_tickets as $key => $value) {
    $stmt_tickets->bindValue($key, $value);
}
$stmt_tickets->execute();
$all_tickets = $stmt_tickets->fetchAll(PDO::FETCH_ASSOC);

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
                    <strong><?php echo number_format($somme_caisse['solde_caisse']?? 0, 0, ',', ' '); ?> FCFA</strong>
                    </h1>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Formulaire de filtres -->
<div class="row mb-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Filtres de recherche</h3>
            </div>
            <div class="card-body">
                <form method="GET" class="row">
                    <div class="col-md-3 mb-3">
                        <label for="search_numero">N° Ticket/Bordereau</label>
                        <input type="text" class="form-control" id="search_numero" name="search_numero" value="<?= htmlspecialchars($search_numero) ?>">
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label for="search_usine">Usine</label>
                        <select class="form-control select2" id="search_usine" name="search_usine">
                            <option value="">Toutes les usines</option>
                            <?php foreach ($usines as $usine): ?>
                                <option value="<?= $usine['id_usine'] ?>" <?= $search_usine == $usine['id_usine'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($usine['nom_usine']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="search_agent">Chargé de Mission</label>
                        <select class="form-control select2" id="search_agent" name="search_agent">
                            <option value="">Tous les agents</option>
                            <?php foreach ($agents as $agent): ?>
                                <option value="<?= $agent['id_agent'] ?>" <?= $search_agent == $agent['id_agent'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($agent['nom_complet']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="search_vehicule">Véhicule</label>
                        <select class="form-control select2" id="search_vehicule" name="search_vehicule">
                            <option value="">Tous les véhicules</option>
                            <?php foreach ($vehicules as $vehicule): ?>
                                <option value="<?= $vehicule['vehicules_id'] ?>" <?= $search_vehicule == $vehicule['vehicules_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($vehicule['matricule_vehicule']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
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
                        <label for="type">Type</label>
                        <select class="form-control" id="type" name="type">
                            <option value="all" <?= $type == 'all' ? 'selected' : '' ?>>Tous</option>
                            <option value="bordereaux" <?= $type == 'bordereaux' ? 'selected' : '' ?>>Bordereaux</option>
                            <option value="tickets" <?= $type == 'tickets' ? 'selected' : '' ?>>Tickets</option>
                        </select>
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
                            <input type="hidden" name="save_paiement" value="1">
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
                                    placeholder="Entrez le montant à payer"
                                    onkeyup="updateMontant(this, <?= $item['id_bordereau'] ?>);">
                                <input type="hidden" name="montant" id="montant_<?= $item['id_bordereau'] ?>" value="">
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                                <button type="submit" class="btn btn-primary">Enregistrer le paiement</button>
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
                            <input type="hidden" name="save_paiement" value="1">
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
                                    placeholder="Entrez le montant à payer"
                                    onkeyup="updateMontant(this, <?= $item['id_ticket'] ?>);">
                                <input type="hidden" name="montant" id="montant_<?= $item['id_ticket'] ?>" value="">
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                                <button type="submit" class="btn btn-primary">Enregistrer le paiement</button>
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
function formatNumber(number) {
    // Enlever tous les caractères non numériques
    number = number.replace(/[^\d]/g, '');
    // Convertir en nombre
    let value = parseInt(number);
    if (isNaN(value)) return '';
    // Formater avec des espaces comme séparateurs de milliers
    return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
}

function unformatNumber(formattedNumber) {
    // Enlever tous les espaces et convertir en nombre
    return formattedNumber.replace(/\s/g, '');
}

function updateMontant(input, id) {
    // Formater l'affichage
    input.value = formatNumber(input.value);
    // Mettre à jour le champ caché avec la valeur non formatée
    document.getElementById('montant_' + id).value = unformatNumber(input.value);
}

$(document).ready(function() {
    // Initialisation des select2
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });

    // Validation du montant maximum
    $('.montant-input').on('change', function() {
        var montant = parseInt(unformatNumber($(this).val()));
        var max = parseInt($(this).data('max'));
        
        if (montant > max) {
            alert('Le montant ne peut pas dépasser ' + formatNumber(max.toString()) + ' FCFA');
            $(this).val('');
            $(this).focus();
            // Réinitialiser aussi le champ caché
            var id = $(this).closest('form').find('input[name="id_bordereau"]').val();
            document.getElementById('montant_' + id).value = '';
        }
    });

    // Initialiser les datepickers
    $('#date_debut, #date_fin').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true,
        language: 'fr'
    });

    // Gérer le type d'affichage (tickets/bordereaux)
    $('#type').change(function() {
        var type = $(this).val();
        if (type === 'tickets') {
            $('.tickets-section').show();
            $('.bordereaux-section').hide();
        } else if (type === 'bordereaux') {
            $('.tickets-section').hide();
            $('.bordereaux-section').show();
        } else {
            $('.tickets-section').show();
            $('.bordereaux-section').show();
        }
    }).trigger('change');
});
</script>

<?php include('footer.php'); ?>
</body>
</html>

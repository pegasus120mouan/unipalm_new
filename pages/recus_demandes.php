<?php
require_once '../inc/functions/connexion.php';
//session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Titre de la page
$page_title = "Liste des Reçus de Demande";
include('header.php');

// Récupération des filtres
$date_debut = isset($_GET['date_debut']) ? $_GET['date_debut'] : '';
$date_fin = isset($_GET['date_fin']) ? $_GET['date_fin'] : '';
$numero_demande = isset($_GET['numero_demande']) ? $_GET['numero_demande'] : '';
$source_paiement = isset($_GET['source_paiement']) ? $_GET['source_paiement'] : '';

// Construction de la requête SQL de base
$sql = "
    SELECT 
        r.*,
        CONCAT(u.nom, ' ', u.prenoms) as caissier_name
    FROM recus_demandes r
    LEFT JOIN utilisateurs u ON r.caissier_id = u.id
    WHERE 1=1
";

$params = array();

// Ajout des conditions de filtrage
if (!empty($date_debut)) {
    $sql .= " AND DATE(r.date_paiement) >= ?";
    $params[] = $date_debut;
}
if (!empty($date_fin)) {
    $sql .= " AND DATE(r.date_paiement) <= ?";
    $params[] = $date_fin;
}
if (!empty($numero_demande)) {
    $sql .= " AND r.numero_demande LIKE ?";
    $params[] = "%$numero_demande%";
}
if (!empty($source_paiement)) {
    $sql .= " AND r.source_paiement = ?";
    $params[] = $source_paiement;
}

$sql .= " ORDER BY r.date_paiement DESC";

// Exécution de la requête
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$recus = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les sources de paiement uniques pour le filtre
$stmt = $conn->query("SELECT DISTINCT source_paiement FROM recus_demandes ORDER BY source_paiement");
$sources_paiement = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Liste des Reçus de Demande</h1>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <!-- Filtres -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Filtres</h3>
            </div>
            <div class="card-body">
                <form method="GET" class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Date début</label>
                            <input type="date" name="date_debut" class="form-control" value="<?= htmlspecialchars($date_debut) ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Date fin</label>
                            <input type="date" name="date_fin" class="form-control" value="<?= htmlspecialchars($date_fin) ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>N° Demande</label>
                            <input type="text" name="numero_demande" class="form-control" value="<?= htmlspecialchars($numero_demande) ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Source de paiement</label>
                            <select name="source_paiement" class="form-control">
                                <option value="">Tous</option>
                                <?php foreach ($sources_paiement as $source): ?>
                                    <option value="<?= htmlspecialchars($source) ?>" <?= $source_paiement === $source ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($source) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filtrer
                        </button>
                        <a href="recus_demandes.php" class="btn btn-secondary">
                            <i class="fas fa-undo"></i> Réinitialiser
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Liste des reçus -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Reçus de demande</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Date Paiement</th>
                                <th>N° Demande</th>
                                <th>Montant</th>
                                <th>Source</th>
                                <th>Caissier</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recus as $recu): ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i', strtotime($recu['date_paiement'])) ?></td>
                                    <td><?= htmlspecialchars($recu['numero_demande']) ?></td>
                                    <td><?= number_format($recu['montant'], 0, ',', ' ') ?> FCFA</td>
                                    <td><?= htmlspecialchars($recu['source_paiement']) ?></td>
                                    <td><?= htmlspecialchars($recu['caissier_name']) ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="recu_demande_pdf.php?id=<?= $recu['id'] ?>" 
                                               class="btn btn-sm btn-info" 
                                               target="_blank"
                                               title="Imprimer">
                                                <i class="fas fa-print"></i>
                                            </a>
                                            <?php if (!isset($recu['date_validation_boss']) || $recu['date_validation_boss'] === null): ?>
                                                <button type="button" 
                                                        class="btn btn-sm btn-danger" 
                                                        onclick="confirmerSuppression(<?= $recu['id'] ?>, '<?= htmlspecialchars($recu['numero_demande']) ?>')"
                                                        title="Supprimer">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($recus)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">Aucun reçu trouvé</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function confirmerSuppression(id, numeroDemande) {
    if (confirm('Êtes-vous sûr de vouloir supprimer le reçu de la demande ' + numeroDemande + ' ? Cette action est irréversible.')) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = 'delete_recu_demande.php';
        
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'id_recu';
        input.value = id;
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
}

// Afficher les messages de succès/erreur s'ils existent
<?php if (isset($_SESSION['success_message'])): ?>
    toastr.success("<?= addslashes($_SESSION['success_message']) ?>");
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    toastr.error("<?= addslashes($_SESSION['error_message']) ?>");
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>
</script>

<?php include('footer.php'); ?>

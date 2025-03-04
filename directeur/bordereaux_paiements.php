<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_tickets.php';
include('header.php');

// Récupérer l'ID de l'utilisateur
$id_user = $_SESSION['user_id'];

// Filtrage
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Requête pour les bordereaux
$sql_bordereaux = "
    SELECT b.*, 
           CONCAT(a.nom, ' ', a.prenom) AS agent_nom_complet, 
           a.contact AS agent_contact,
           (SELECT SUM(t.poids) FROM tickets t WHERE t.numero_bordereau = b.numero_bordereau) as total_poids,
           (SELECT SUM(t.montant_paie) FROM tickets t WHERE t.numero_bordereau = b.numero_bordereau) as montant_total,
           (SELECT SUM(t.montant_payer) FROM tickets t WHERE t.numero_bordereau = b.numero_bordereau) as montant_payer
    FROM bordereau b
    LEFT JOIN agents a ON b.id_agent = a.id_agent
    WHERE b.date_validation_boss IS NOT NULL
    ORDER BY b.date_validation_boss DESC";

$stmt_bordereaux = $conn->prepare($sql_bordereaux);
$stmt_bordereaux->execute();
$all_bordereaux = $stmt_bordereaux->fetchAll(PDO::FETCH_ASSOC);

// Mettre à jour le montant_total si nécessaire
foreach ($all_bordereaux as &$bordereau) {
    // Si le montant_total est null ou 0 mais qu'on a un total_poids
    if ((!isset($bordereau['montant_total']) || $bordereau['montant_total'] == 0) && 
        isset($bordereau['total_poids']) && $bordereau['total_poids'] > 0) {
            
        // Mettre à jour dans la base de données
        $update = $conn->prepare("
            UPDATE bordereau 
            SET montant_total = :montant 
            WHERE id_bordereau = :id");
        $update->execute([
            ':montant' => $bordereau['montant_total'],
            ':id' => $bordereau['id_bordereau']
        ]);
    }
    
    // Calculer le montant restant
    $montant_total = $bordereau['montant_total'] ?? 0;
    $montant_paye = $bordereau['montant_payer'] ?? 0;
    $bordereau['montant_reste'] = $montant_total - $montant_paye;
}

// Filtrer les bordereaux
if ($filter === 'non_soldes') {
    $bordereaux = array_filter($all_bordereaux, function($bordereau) {
        return !isset($bordereau['montant_payer']) || 
               $bordereau['montant_payer'] === null || 
               $bordereau['montant_reste'] > 0;
    });
} elseif ($filter === 'soldes') {
    $bordereaux = array_filter($all_bordereaux, function($bordereau) {
        return isset($bordereau['montant_payer']) && 
               $bordereau['montant_payer'] !== null && 
               $bordereau['montant_reste'] <= 0;
    });
} else {
    $bordereaux = $all_bordereaux;
}
?>

<!-- Main row -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Gestion des Bordereaux</h3>
                <div class="float-right">
                    <a href="bordereaux_paiements.php?filter=all" class="btn <?= $filter === 'all' ? 'btn-primary' : 'btn-outline-primary' ?>">
                        <i class="fas fa-list"></i> Tous
                    </a>
                    <a href="bordereaux_paiements.php?filter=non_soldes" class="btn <?= $filter === 'non_soldes' ? 'btn-warning' : 'btn-outline-warning' ?>">
                        <i class="fas fa-exclamation-triangle"></i> Non soldés
                    </a>
                    <a href="bordereaux_paiements.php?filter=soldes" class="btn <?= $filter === 'soldes' ? 'btn-success' : 'btn-outline-success' ?>">
                        <i class="fas fa-check-circle"></i> Soldés
                    </a>
                </div>
            </div>
            <div class="card-body">
                <table id="bordereaux_table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>N° Bordereau</th>
                            <th>Agent</th>
                            <th>Contact Agent</th>
                            <th>Poids Total</th>
                            <th>Montant Total</th>
                            <th>Montant Payé</th>
                            <th>Reste à Payer</th>
                            <th>Date Validation</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($bordereaux)): ?>
                            <?php foreach ($bordereaux as $bordereau): ?>
                                <tr>
                                    <td><?= date('Y-m-d', strtotime($bordereau['date_debut'])) ?></td>
                                    <td><?= $bordereau['numero_bordereau'] ?></td>
                                    <td><?= $bordereau['agent_nom_complet'] ?></td>
                                    <td><?= $bordereau['agent_contact'] ?></td>
                                    <td><?= number_format($bordereau['total_poids'], 0, ',', ' ') ?> Kg</td>
                                    <td><?= number_format($bordereau['montant_total'], 0, ',', ' ') ?> FCFA</td>
                                    <td><?= number_format($bordereau['montant_payer'] ?? 0, 0, ',', ' ') ?> FCFA</td>
                                    <td><?= number_format($bordereau['montant_reste'], 0, ',', ' ') ?> FCFA</td>
                                    <td><?= date('Y-m-d H:i', strtotime($bordereau['date_validation_boss'])) ?></td>
                                    <td>
                                        <?php if ($bordereau['montant_reste'] <= 0): ?>
                                            <button type="button" class="btn btn-success btn-sm" disabled>
                                                <i class="fas fa-check-circle"></i> Soldé
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#payer_bordereau<?= $bordereau['id_bordereau'] ?>">
                                                <i class="fas fa-money-bill-wave"></i> Payer
                                            </button>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-info btn-sm" onclick="window.location.href='voir_tickets_bordereau.php?id=<?= $bordereau['id_bordereau'] ?>'">
                                            <i class="fas fa-eye"></i> Voir tickets
                                        </button>
                                    </td>
                                </tr>

                                <!-- Modal de paiement -->
                                <div class="modal fade" id="payer_bordereau<?= $bordereau['id_bordereau'] ?>">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h4 class="modal-title">Paiement du bordereau #<?= $bordereau['numero_bordereau'] ?></h4>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="save_paiement.php" method="post">
                                                    <input type="hidden" name="id_bordereau" value="<?= $bordereau['id_bordereau'] ?>">
                                                    <input type="hidden" name="numero_bordereau" value="<?= $bordereau['numero_bordereau'] ?>">
                                                    
                                                    <div class="form-group">
                                                        <label>Montant total</label>
                                                        <input type="text" class="form-control" value="<?= number_format($bordereau['montant_total'], 0, ',', ' ') ?> FCFA" readonly>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label>Déjà payé</label>
                                                        <input type="text" class="form-control" value="<?= number_format($bordereau['montant_payer'] ?? 0, 0, ',', ' ') ?> FCFA" readonly>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label>Reste à payer</label>
                                                        <input type="text" class="form-control" value="<?= number_format($bordereau['montant_reste'], 0, ',', ' ') ?> FCFA" readonly>
                                                        <input type="hidden" name="montant_reste" value="<?= $bordereau['montant_reste'] ?>">
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label>Montant à payer <span class="text-danger">*</span></label>
                                                        <input type="number" class="form-control" name="montant" required 
                                                               max="<?= $bordereau['montant_reste'] ?>" 
                                                               placeholder="Entrez le montant">
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
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center">Aucun bordereau trouvé</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- DataTables -->
<script>
$(document).ready(function() {
    $('#bordereaux_table').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json"
        }
    });
});
</script>

<?php include('footer.php'); ?>

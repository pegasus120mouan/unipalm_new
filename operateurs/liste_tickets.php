<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_tickets.php';
require_once '../inc/functions/requete/requete_bordereaux.php';
include('header.php');

// Récupérer tous les tickets validés
$stmt = $conn->prepare(
    "SELECT 
        t.id_ticket,
        t.numero_ticket,
        t.date_ticket,
        t.poids,
        t.prix_unitaire,
        t.montant_paie,
        t.montant_payer,
        t.date_validation_boss,
        CONCAT(a.nom, ' ', a.prenom) AS agent_nom_complet,
        us.nom_usine,
        v.matricule_vehicule,
        (t.poids * t.prix_unitaire) as montant_total
    FROM 
        tickets t
    INNER JOIN 
        agents a ON t.id_agent = a.id_agent
    INNER JOIN 
        usines us ON t.id_usine = us.id_usine
    INNER JOIN 
        vehicules v ON t.vehicule_id = v.vehicules_id
    LEFT JOIN 
        bordereau_tickets bt ON t.id_ticket = bt.id_ticket
    WHERE 
        t.date_validation_boss IS NOT NULL
        AND bt.id_ticket IS NULL
    ORDER BY 
        t.date_ticket DESC"
);
$stmt->execute();
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Liste des Tickets Validés</h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Tickets validés en attente de paiement</h3>
                            <div class="card-tools">
                                <a href="paiements.php" class="btn btn-primary">
                                    <i class="fas fa-arrow-left"></i> Retour aux paiements
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="ticketsTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>N° Ticket</th>
                                        <th>Agent</th>
                                        <th>Usine</th>
                                        <th>Véhicule</th>
                                        <th>Poids</th>
                                        <th>Prix Unitaire</th>
                                        <th>Montant Total</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tickets as $ticket): ?>
                                        <tr>
                                            <td><?= date('d/m/Y', strtotime($ticket['date_ticket'])) ?></td>
                                            <td><?= $ticket['numero_ticket'] ?></td>
                                            <td><?= $ticket['agent_nom_complet'] ?></td>
                                            <td><?= $ticket['nom_usine'] ?></td>
                                            <td><?= $ticket['matricule_vehicule'] ?></td>
                                            <td><?= number_format($ticket['poids'], 0, ',', ' ') ?> Kg</td>
                                            <td><?= number_format($ticket['prix_unitaire'], 0, ',', ' ') ?> FCFA</td>
                                            <td><?= number_format($ticket['montant_total'], 0, ',', ' ') ?> FCFA</td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#payer_ticket<?= $ticket['id_ticket'] ?>">
                                                    <i class="fas fa-money-bill-wave"></i> Payer
                                                </button>
                                            </td>
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

<!-- Modals pour le paiement -->
<?php foreach ($tickets as $ticket): ?>
    <div class="modal fade" id="payer_ticket<?= $ticket['id_ticket'] ?>">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Paiement du ticket #<?= $ticket['numero_ticket'] ?></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form class="forms-sample" method="post" action="save_paiement.php">
                        <input type="hidden" name="id_ticket" value="<?= $ticket['id_ticket'] ?>">
                        <input type="hidden" name="numero_ticket" value="<?= $ticket['numero_ticket'] ?>">
                        
                        <div class="form-group">
                            <label>Montant total à payer</label>
                            <input type="text" class="form-control" value="<?= number_format($ticket['montant_total'], 0, ',', ' ') ?> FCFA" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label>Montant déjà payé</label>
                            <input type="text" class="form-control" value="<?= number_format($ticket['montant_payer'] ?? 0, 0, ',', ' ') ?> FCFA" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label>Reste à payer</label>
                            <?php $reste = $ticket['montant_total'] - ($ticket['montant_payer'] ?? 0); ?>
                            <input type="text" class="form-control" value="<?= number_format($reste, 0, ',', ' ') ?> FCFA" readonly>
                            <input type="hidden" name="montant_reste" value="<?= $reste ?>">
                        </div>

                        <div class="form-group">
                            <label>Type de Transaction</label>
                            <select class="form-control" name="type_transaction" required>
                                <option value="paiement">Sortie de caisse</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Montant à payer</label>
                            <input type="number" step="0.01" class="form-control" name="montant" required 
                                   max="<?= $reste ?>"
                                   placeholder="Entrez le montant à payer">
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

<!-- DataTables & Plugins -->
<script src="../../plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../../plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../../plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="../../plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>

<script>
$(function () {
    $('#ticketsTable').DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json"
        }
    });
});
</script>

<?php include('footer.php'); ?>

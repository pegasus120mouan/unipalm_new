<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_tickets.php';
include('header.php');

if (!isset($_GET['date_debut']) || !isset($_GET['date_fin'])) {
    header('Location: tickets.php');
    exit();
}

$date_debut = $_GET['date_debut'];
$date_fin = $_GET['date_fin'];
$tickets = searchTicketsByDateRange($conn, $date_debut, $date_fin);
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    Tickets du <?= date('d/m/Y', strtotime($date_debut)) ?> au <?= date('d/m/Y', strtotime($date_fin)) ?>
                </h3>
                <div class="card-tools">
                    <a href="tickets.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Retour
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Date ticket</th>
                                <th>Numero Ticket</th>
                                <th>Usine</th>
                                <th>Chargé de Mission</th>
                                <th>Vehicule</th>
                                <th>Poids</th>
                                <th>Ticket crée par</th>
                                <th>Prix Unitaire</th>
                                <th>Date validation</th>
                                <th>Montant</th>
                                <th>Date Paie</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($tickets)) : ?>
                                <?php foreach ($tickets as $ticket) : ?>
                                    <tr>
                                        <td><?= date('Y-m-d', strtotime($ticket['date_ticket'])) ?></td>
                                        <td><?= $ticket['numero_ticket'] ?></td>
                                        <td><?= $ticket['nom_usine'] ?></td>
                                        <td><?= $ticket['agent_nom_complet'] ?></td>
                                        <td><?= $ticket['matricule_vehicule'] ?></td>
                                        <td><?= $ticket['poids'] ?></td>
                                        <td><?= $ticket['utilisateur_nom_complet'] ?></td>
                                        <td>
                                            <?php if ($ticket['prix_unitaire'] === null || $ticket['prix_unitaire'] == 0.00): ?>
                                                <button class="btn btn-danger btn-sm" disabled>
                                                    En Attente de validation
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-dark btn-sm" disabled>
                                                    <?= $ticket['prix_unitaire'] ?>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($ticket['date_validation_boss'] === null): ?>
                                                <button class="btn btn-warning btn-sm" disabled>
                                                    En cours
                                                </button>
                                            <?php else: ?>
                                                <?= date('Y-m-d', strtotime($ticket['date_validation_boss'])) ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($ticket['montant_paie'] === null): ?>
                                                <button class="btn btn-primary btn-sm" disabled>
                                                    En attente de PU
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-info btn-sm" disabled>
                                                    <?= $ticket['montant_paie'] ?>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($ticket['date_paie'] === null): ?>
                                                <button class="btn btn-secondary btn-sm" disabled>
                                                    Non payé
                                                </button>
                                            <?php else: ?>
                                                <?= date('Y-m-d', strtotime($ticket['date_paie'])) ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#view_ticket_<?= $ticket['id_ticket'] ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($ticket['prix_unitaire'] === null || $ticket['prix_unitaire'] == 0.00): ?>
                                                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#edit_ticket_<?= $ticket['id_ticket'] ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="12" class="text-center">Aucun ticket trouvé pour cette période</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>

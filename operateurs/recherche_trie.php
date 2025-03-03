<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_tickets.php';
require_once '../inc/functions/requete/requete_usines.php';
require_once '../inc/functions/requete/requete_chef_equipes.php';
require_once '../inc/functions/requete/requete_vehicules.php';
require_once '../inc/functions/requete/requete_agents.php';

include('header.php');

$agents = getAgents($conn);
$usines = getUsines($conn);
$vehicules = getVehicules($conn);

// Initialisation des filtres
$filters = [];

// Récupération des filtres depuis l'URL
if (isset($_GET['agent']) && !empty($_GET['agent'])) {
    $filters['agent'] = $_GET['agent'];
}

if (isset($_GET['usine']) && !empty($_GET['usine'])) {
    $filters['usine'] = $_GET['usine'];
}

if (isset($_GET['vehicule']) && !empty($_GET['vehicule'])) {
    $filters['vehicule'] = $_GET['vehicule'];
}

if (isset($_GET['date_debut']) && !empty($_GET['date_debut'])) {
    $filters['date_debut'] = $_GET['date_debut'];
}

if (isset($_GET['date_fin']) && !empty($_GET['date_fin'])) {
    $filters['date_fin'] = $_GET['date_fin'];
}

// Récupération des tickets filtrés
$tickets_list = getTickets($conn, $filters);
?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Recherche Avancée</h1>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <!-- Search Form -->
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Critères de recherche</h3>
            </div>
            <div class="card-body">
                <form id="searchForm" action="" method="GET" class="form-horizontal">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="agent">Agent</label>
                            <select class="form-control" name="agent" id="agent">
                                <option value="">Tous les agents</option>
                                <?php foreach($agents as $agent): ?>
                                    <option value="<?= $agent['id_agent'] ?>" 
                                        <?= (isset($_GET['agent']) && $_GET['agent'] == $agent['id_agent']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($agent['nom_complet_agent']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="usine">Usine</label>
                            <select class="form-control" name="usine" id="usine">
                                <option value="">Toutes les usines</option>
                                <?php foreach($usines as $usine): ?>
                                    <option value="<?= $usine['id_usine'] ?>" 
                                        <?= (isset($_GET['usine']) && $_GET['usine'] == $usine['id_usine']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($usine['nom_usine']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="vehicule">Véhicule</label>
                            <select class="form-control" name="vehicule" id="vehicule">
                                <option value="">Tous les véhicules</option>
                                <?php foreach($vehicules as $vehicule): ?>
                                    <option value="<?= $vehicule['vehicules_id'] ?>" 
                                        <?= (isset($_GET['vehicule']) && $_GET['vehicule'] == $vehicule['vehicules_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($vehicule['matricule_vehicule']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-3">
                            <label for="date_debut">Date début</label>
                            <input type="date" class="form-control" id="date_debut" name="date_debut" 
                                value="<?= isset($_GET['date_debut']) ? htmlspecialchars($_GET['date_debut']) : '' ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="date_fin">Date fin</label>
                            <input type="date" class="form-control" id="date_fin" name="date_fin" 
                                value="<?= isset($_GET['date_fin']) ? htmlspecialchars($_GET['date_fin']) : '' ?>">
                        </div>
                        <div class="col-md-3">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Rechercher
                                </button>
                                <a href="recherche_trie.php" class="btn btn-secondary">
                                    <i class="fas fa-redo"></i> Réinitialiser
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Spinner de chargement -->
        <div id="loadingSpinner" style="display: none;" class="text-center my-4">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Chargement...</span>
            </div>
            <p class="mt-2">Chargement des résultats...</p>
        </div>

        <!-- Table des résultats -->
        <div id="resultsTable">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Résultats de la recherche</h3>
                    <div class="card-tools">
                        <button type="button" id="printButton" class="btn btn-primary">
                            <i class="fas fa-print"></i> Imprimer
                        </button>
                    </div>
                </div>
                <div class="card-body table-responsive">
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>DATE RECEPTION</th>
                                <th>DATE TICKET</th>
                                <th>N° TICKET</th>
                                <th>USINE</th>
                                <th>POIDS</th>
                                <th>PRIX UNITAIRE</th>
                                <th>NOM AGENT</th>
                                <th>VEHICULE</th>
                                <th>ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($tickets_list)): ?>
                                <?php foreach ($tickets_list as $ticket): ?>
                                    <tr>
                                        <td><?= isset($ticket['created_at']) ? date('d/m/Y', strtotime($ticket['created_at'])) : '01/01/1970' ?></td>
                                        <td><?= isset($ticket['date_ticket']) ? date('d/m/Y', strtotime($ticket['date_ticket'])) : '-' ?></td>
                                        <td><?= $ticket['numero_ticket'] ?></td>
                                        <td><?= $ticket['nom_usine'] ?></td>
                                        <td><?= $ticket['poids'] ?></td>
                                        <td>
                                            <?php if (!isset($ticket['prix_unitaire']) || $ticket['prix_unitaire'] === null || $ticket['prix_unitaire'] == 0.00): ?>
                                                <button class="btn btn-danger btn-block" disabled>
                                                    En Attente de validation
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-dark btn-block" disabled>
                                                    <?= $ticket['prix_unitaire'] ?>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= isset($ticket['nom_complet_agent']) ? $ticket['nom_complet_agent'] : '-' ?></td>
                                        <td><?= isset($ticket['matricule_vehicule']) ? $ticket['matricule_vehicule'] : '-' ?></td>
                                        <td>
                                            <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#ticketModal<?= $ticket['id_ticket'] ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center">Aucun ticket trouvé</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Ticket Detail Modals -->
<?php if (!empty($tickets_list)): ?>
    <?php foreach ($tickets_list as $ticket): ?>
        <div class="modal fade" id="ticketModal<?= $ticket['id_ticket'] ?>" tabindex="-1" role="dialog" aria-labelledby="ticketModalLabel<?= $ticket['id_ticket'] ?>" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-info">
                        <h5 class="modal-title text-white" id="ticketModalLabel<?= $ticket['id_ticket'] ?>">
                            Détails du ticket #<?= $ticket['numero_ticket'] ?>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Date de réception:</strong> <?= isset($ticket['created_at']) ? date('d/m/Y', strtotime($ticket['created_at'])) : '01/01/1970' ?></p>
                                <p><strong>Date du ticket:</strong> <?= isset($ticket['date_ticket']) ? date('d/m/Y', strtotime($ticket['date_ticket'])) : '-' ?></p>
                                <p><strong>Numéro de ticket:</strong> <?= $ticket['numero_ticket'] ?></p>
                                <p><strong>Usine:</strong> <?= $ticket['nom_usine'] ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Poids:</strong> <?= $ticket['poids'] ?> kg</p>
                                <p><strong>Prix unitaire:</strong> <?= $ticket['prix_unitaire'] ?> FCFA</p>
                                <p><strong>Agent:</strong> <?= isset($ticket['nom_complet_agent']) ? $ticket['nom_complet_agent'] : '-' ?></p>
                                <p><strong>Véhicule:</strong> <?= isset($ticket['matricule_vehicule']) ? $ticket['matricule_vehicule'] : '-' ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

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

<?php if (isset($_SESSION['popup']) && $_SESSION['popup'] == true): ?>
    <script>
        var Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });

        Toast.fire({
            icon: 'success',
            title: 'Action effectuée avec succès.'
        });
    </script>
    <?php $_SESSION['popup'] = false; ?>
<?php endif; ?>

<?php if (isset($_SESSION['delete_pop']) && $_SESSION['delete_pop'] == true): ?>
    <script>
        var Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });

        Toast.fire({
            icon: 'error',
            title: 'Action échouée.'
        });
    </script>
    <?php $_SESSION['delete_pop'] = false; ?>
<?php endif; ?>

<script src="assets/js/recherche.js"></script>

<script>
    document.getElementById('printButton').addEventListener('click', function() {
        // Récupérer tous les paramètres du formulaire
        var formData = new URLSearchParams(new FormData(document.getElementById('searchForm'))).toString();
        // Ouvrir la page d'impression dans une nouvelle fenêtre
        window.open('imprimer_recherche.php?' + formData, '_blank');
    });
</script>

<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_commandes.php';
require_once '../inc/functions/requete/requetes_selection_employes.php';
include('header.php');

// Récupération des employés
$liste_employees = $liste_employes->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- CSS Styles -->
<style>
    .pagination-container { display: flex; align-items: center; justify-content: center; margin-top: 20px; }
    .pagination-link { padding: 8px; text-decoration: none; color: white; background-color: #007bff; border: 1px solid #007bff; border-radius: 4px; margin-right: 4px; }
    .items-per-page-form { margin-left: 20px; }
    label { margin-right: 5px; }
    .items-per-page-select, .submit-button { padding: 6px; border-radius: 4px; }
    .submit-button { background-color: #007bff; color: #fff; border: none; cursor: pointer; }
    .custom-icon { color: green; font-size: 24px; margin-right: 8px; }
    .spacing { margin-right: 10px; margin-bottom: 20px; }
    @media only screen and (max-width: 767px) {
        th { display: none; }
        tbody tr { display: block; margin-bottom: 20px; border: 1px solid #ccc; padding: 10px; }
    }
    .margin-right-15 { margin-right: 15px; }
    .block-container { background-color: #d7dbdd; padding: 20px; border-radius: 5px; width: 100%; margin-bottom: 20px; }
</style>

<!-- Main content -->
<div class="row">
    <!-- Action Buttons -->
    <div class="block-container">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCommandeModal">
            <i class="fa fa-edit"></i> Enregistrer une commande
        </button>
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#addPointModal">
            <i class="fa fa-print"></i> Imprimer un point
        </button>
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#searchCommandeModal">
            <i class="fa fa-search"></i> Rechercher un point
        </button>
        <button type="button" class="btn btn-dark" onclick="window.location.href='export_commandes.php'">
            <i class="fa fa-print"></i> Exporter la liste des commandes
        </button>
    </div>

    <!-- Employees Table -->
    <div class="table-responsive">
        <table id="employeesTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Position</th>
                    <th>Salaire</th>
                    <th>Type de paiement</th>
                    <th>Date Ajout</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($liste_employees as $employee) : ?>
                    <tr>
                        <td><?= htmlspecialchars($employee['nom']) ?></td>
                        <td><?= htmlspecialchars($employee['position']) ?></td>
                        <td>
                            <span id="salaire-<?= $employee['id'] ?>"><?= base64_encode($employee['salaire']); ?></span>
                            <button 
                                onclick="showModal(<?= $employee['id'] ?>, '<?= base64_encode($employee['salaire']) ?>', '<?= htmlspecialchars($employee['salaire']) ?>')" 
                                class="btn btn-link">
                                <i class="fas fa-eye" style="color: green;"></i>
                            </button>
                        </td>
                        <td><?= htmlspecialchars($employee['payment_type']) ?></td>
                        <td><?= htmlspecialchars($employee['created_at']) ?></td>
                        <td>
                            <a href="commandes_update.php?id=<?= $employee['id'] ?>" class="edit">
                                <i class="fas fa-pen" style="font-size: 24px; color: blue;"></i>
                            </a>
                            <a href="delete_commandes.php?id=<?= $employee['id'] ?>" class="trash">
                                <i class="fas fa-trash" style="font-size: 24px; color: red;"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal for verifying salary -->
<div class="modal fade" id="codeModal" tabindex="-1" aria-labelledby="codeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="codeModalLabel">Vérification du Salaire</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="employeeId">
                <input type="hidden" id="encodedSalaire">
                <div class="mb-3">
                    <label for="plainSalaire" class="form-label">Salaire :</label>
                    <input type="text" class="form-control" id="plainSalaire" readonly>
                </div>
                <div class="mb-3">
                    <label for="verificationCode" class="form-label">Code :</label>
                    <input type="password" class="form-control" id="verificationCode">
                    <div id="codeError" class="text-danger" style="display: none;">Code incorrect.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="verifyCode()">Vérifier</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
    function showModal(id, encodedSalaire, salaire) {
        document.getElementById('employeeId').value = id;
        document.getElementById('encodedSalaire').value = encodedSalaire;
        document.getElementById('plainSalaire').value = salaire;
        document.getElementById('verificationCode').value = '';
        document.getElementById('codeError').style.display = 'none';

        var modal = new bootstrap.Modal(document.getElementById('codeModal'));
        modal.show();
    }

    function verifyCode() {
        var code = document.getElementById('verificationCode').value;
        if (code === 'Pegasus120*/-') {
            alert('Code validé.');
        } else {
            document.getElementById('codeError').style.display = 'block';
        }
    }
</script>

<!-- Include Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

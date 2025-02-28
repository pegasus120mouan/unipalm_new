<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_vehicules.php';
include('header.php');

// Récupérer la liste des véhicules
$stmt = $conn->prepare("SELECT * FROM vehicules ORDER BY created_at DESC");
$stmt->execute();
$vehicules = $stmt->fetchAll();
?>

<div class="row">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="col-12">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $_SESSION['success'] ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="col-12">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $_SESSION['error'] ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Gestion des Véhicules</h3>
                <button type="button" class="btn btn-primary float-right" data-toggle="modal" data-target="#addVehiculeModal">
                    <i class="fas fa-plus"></i> Ajouter un véhicule
                </button>
            </div>
            <div class="card-body">
                <table id="vehiculesTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">Type</th>
                            <th>Matricule</th>
                            <th class="text-center" style="width: 150px;">Date d'ajout</th>
                            <th class="text-center" style="width: 150px;">Dernière modification</th>
                            <th class="text-center" style="width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vehicules as $vehicule): ?>
                            <tr>
                                <td class="text-center">
                                    <?php if ($vehicule['type_vehicule'] == 'voiture'): ?>
                                        <i class="fas fa-car fa-lg text-primary" title="Voiture"></i>
                                    <?php else: ?>
                                        <i class="fas fa-motorcycle fa-lg text-success" title="Moto"></i>
                                    <?php endif; ?>
                                </td>
                                <td><?= $vehicule['matricule_vehicule'] ?></td>
                                <td class="text-center"><?= date('d/m/Y', strtotime($vehicule['created_at'])) ?></td>
                                <td class="text-center"><?= date('d/m/Y', strtotime($vehicule['updated_at'])) ?></td>
                                <td class="text-center">
                                    <button type="button" 
                                            class="btn btn-sm btn-warning edit-btn" 
                                            data-id="<?= htmlspecialchars($vehicule['vehicules_id']) ?>"
                                            data-matricule="<?= htmlspecialchars($vehicule['matricule_vehicule']) ?>"
                                            data-type="<?= htmlspecialchars($vehicule['type_vehicule']) ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger delete-btn"
                                            data-id="<?= htmlspecialchars($vehicule['vehicules_id']) ?>">
                                        <i class="fas fa-trash"></i>
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

<!-- Modal Ajout Véhicule -->
<div class="modal fade" id="addVehiculeModal" tabindex="-1" role="dialog" aria-labelledby="addVehiculeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addVehiculeModalLabel">Ajouter un véhicule</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="add_vehicule.php" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="matricule">Matricule</label>
                        <input type="text" class="form-control" id="matricule" name="matricule" required>
                    </div>
                    <div class="form-group">
                        <label for="type_vehicule">Type de véhicule</label>
                        <select class="form-control" id="type_vehicule" name="type_vehicule" required>
                            <option value="voiture">Voiture</option>
                            <option value="moto">Moto</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Modification Véhicule -->
<div class="modal fade" id="editVehiculeModal" tabindex="-1" role="dialog" aria-labelledby="editVehiculeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editVehiculeModalLabel">Modifier un véhicule</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="update_vehicule.php" method="POST" id="editVehiculeForm">
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-group">
                        <label for="edit_matricule">Matricule</label>
                        <input type="text" class="form-control" id="edit_matricule" name="matricule" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_type_vehicule">Type de véhicule</label>
                        <select class="form-control" id="edit_type_vehicule" name="type_vehicule" required>
                            <option value="voiture">Voiture</option>
                            <option value="moto">Moto</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn btn-primary">Modifier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Form pour la suppression -->
<form id="deleteForm" action="delete_vehicule.php" method="POST" style="display: none;">
    <input type="hidden" name="id" id="delete_id">
</form>

<script src="../../plugins/jquery/jquery.min.js"></script>
<script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../../plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../../plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    // Initialisation de la DataTable
    var table = $('#vehiculesTable').DataTable({
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

    // Gestionnaire d'événement pour le bouton d'édition
    $(document).on('click', '.edit-btn', function(e) {
        e.preventDefault();
        
        var id = $(this).data('id');
        var matricule = $(this).data('matricule');
        var type = $(this).data('type');
        
        console.log('ID:', id);
        console.log('Matricule:', matricule);
        console.log('Type:', type);
        
        $('#edit_id').val(id);
        $('#edit_matricule').val(matricule);
        $('#edit_type_vehicule').val(type);
        
        $('#editVehiculeModal').modal('show');
    });

    // Gestionnaire pour la suppression
    $('.delete-btn').on('click', function() {
        if(confirm('Êtes-vous sûr de vouloir supprimer ce véhicule ?')) {
            var id = $(this).data('id');
            $('#delete_id').val(id);
            $('#deleteForm').submit();
        }
    });
});
</script>
</body>
</html>

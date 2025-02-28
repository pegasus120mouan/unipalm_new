<?php
require_once '../inc/functions/connexion.php';
include('header.php');
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Gestion des Véhicules</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Liste des Véhicules</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addVehiculeModal">
                                    <i class="fas fa-plus"></i> Ajouter un véhicule
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="example1" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Matricule</th>
                                            <th>Date de création</th>
                                            <th>Dernière modification</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $query = "SELECT * FROM vehicules ORDER BY created_at DESC";
                                        $stmt = $conn->prepare($query);
                                        $stmt->execute();
                                        $vehicules = $stmt->fetchAll();

                                        foreach($vehicules as $vehicule): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($vehicule['vehicules_id']) ?></td>
                                                <td><?= htmlspecialchars($vehicule['matricule_vehicule']) ?></td>
                                                <td><?= date('d/m/Y H:i', strtotime($vehicule['created_at'])) ?></td>
                                                <td><?= date('d/m/Y H:i', strtotime($vehicule['updated_at'])) ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#editVehicule<?= $vehicule['vehicules_id'] ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete(<?= $vehicule['vehicules_id'] ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>

                                            <!-- Modal d'édition -->
                                            <div class="modal fade" id="editVehicule<?= $vehicule['vehicules_id'] ?>" tabindex="-1" role="dialog">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Modifier le véhicule</h5>
                                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        </div>
                                                        <form action="../inc/functions/vehicule/update_vehicule.php" method="POST">
                                                            <div class="modal-body">
                                                                <input type="hidden" name="vehicules_id" value="<?= $vehicule['vehicules_id'] ?>">
                                                                <div class="form-group">
                                                                    <label>Matricule</label>
                                                                    <input type="text" class="form-control" name="matricule_vehicule" value="<?= htmlspecialchars($vehicule['matricule_vehicule']) ?>" required>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                                                                <button type="submit" class="btn btn-primary">Enregistrer</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal d'ajout -->
<div class="modal fade" id="addVehiculeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter un véhicule</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="../inc/functions/vehicule/add_vehicule.php" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Matricule</label>
                        <input type="text" class="form-control" name="matricule_vehicule" required>
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

<script>
function confirmDelete(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce véhicule ?')) {
        window.location.href = '../inc/functions/vehicule/delete_vehicule.php?id=' + id;
    }
}
</script>

<?php include('footer.php'); ?>

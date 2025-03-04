<?php
require_once '../inc/functions/connexion.php';
include('header.php');

$stmt = $conn->prepare("SELECT CONCAT(u.nom, ' ', u.prenoms) AS fullname, c.date_commande, SUM(c.cout_livraison) AS total_cout_livraison
FROM utilisateurs u
JOIN commandes c ON u.id = c.livreur_id
WHERE c.statut = 'Livré'
GROUP BY fullname, c.date_commande
ORDER BY c.date_commande DESC");
$stmt->execute();
$point_montants= $stmt->fetchAll();


$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 15;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

$Montants_pages = array_chunk($point_montants, $limit);
$montants_list = $Montants_pages[$page - 1] ?? [];
?>
<!DOCTYPE html>
<html>

<head>
    <title>Point journalier</title>
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
</head>

<body>
<div class="row">
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#recherche_point" style="margin-bottom: 15px;">
    Voir un point
</button>

</div>

<table id="example1" class="table table-bordered table-striped">
    <thead>
    <tr>
        <th>Livreur</th>
        <th>Montant</th>
        <th>Date</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($montants_list as $montant_livreur) : ?>
        <tr>
            <td><?= htmlspecialchars($montant_livreur['fullname']) ?></td>
            <td><?= htmlspecialchars($montant_livreur['total_cout_livraison']) ?></td>
            <td><?= htmlspecialchars($montant_livreur['date_commande']) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<div class="pagination-container bg-secondary d-flex justify-content-center w-100 text-white p-3">
    <?php if ($page > 1) : ?>
        <a href="?page=<?= $page - 1 ?>&limit=<?= $limit ?>" class="btn btn-primary"><</a>
    <?php endif; ?>

    <span><?= $page . '/' . count($point_montants) ?></span>

    <?php if ($page < count($point_montants)) : ?>
        <a href="?page=<?= $page + 1 ?>&limit=<?= $limit ?>" class="btn btn-primary">></a>
    <?php endif; ?>

    <form action="" method="get" class="items-per-page-form">
        <label for="limit">Afficher :</label>
        <select name="limit" id="limit" class="items-per-page-select">
            <option value="5" <?php if ($limit == 5) { echo 'selected'; } ?>>5</option>
            <option value="10" <?php if ($limit == 10) { echo 'selected'; } ?>>10</option>
            <option value="15" <?php if ($limit == 15) { echo 'selected'; } ?>>15</option>
        </select>
        <button type="submit" class="submit-button">Valider</button>
    </form>
</div>

<div class="modal fade" id="recherche_point">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Rechercher un point</h4>
            </div>
            <div class="modal-body">
                <form class="forms-sample" method="post" action="recherche_gain.php">

                    <div class="form-group">
                        <label for="exampleInputPassword1">Selectionnez la date</label>
                        <input type="date" class="form-control" id="exampleInputPassword1" placeholder="date" name="date">
                    </div>
                    <button type="submit" class="btn btn-primary mr-2">Recherche</button>
                    <button type="button" class="btn btn-light" data-dismiss="modal">Annuler</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="../../plugins/jquery/jquery.min.js"></script>
<script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../../dist/js/adminlte.js"></script>

<?php if (isset($_SESSION['popup']) && $_SESSION['popup'] == true) : ?>
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

</body>

</html>

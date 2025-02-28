<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_contrats.php';
include('header.php');
?>

<div class="row">
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add-contrat">
        Enregistrer un contrat
    </button>

    <a class="btn btn-outline-secondary" href="commandes_print.php">
        <i class="fa fa-print" style="font-size:24px;color:green"></i>
    </a>
    <table style="max-height: 90vh !important; overflow-y: scroll !important" id="example1"
        class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>Avatar</th>
                <th>Numéro chassis</th>
                <th>Plaque d'Immatriculation</th>
                <th>Statut</th>
                <th>Vignette Debut</th>
                <th>Vignette Fin</th>
                <th>Nombre de jour restants</th>
                <th>Assurance Debut</th>
                <th>Assurance Fin</th>
                <th>Nombre de jour restants</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($contrats as $contrat) :
                $currentDate = new DateTime();
                $vignetteEndDate = new DateTime($contrat['vignette_date_fin']);
                $assuranceEndDate = new DateTime($contrat['assurance_date_fin']);

                $vignetteDaysRemaining = $currentDate->diff($vignetteEndDate)->days;
                $assuranceDaysRemaining = $currentDate->diff($assuranceEndDate)->days;

                if ($vignetteEndDate < $currentDate) {
                    $vignetteDaysRemaining = 0;
                }
                if ($assuranceEndDate < $currentDate) {
                    $assuranceDaysRemaining = 0;
                }

                // Calculate color for vignetteDaysRemaining
                $vignetteOneThird = 364 / 3;
                $vignetteTwoThirds = 2 * $vignetteOneThird;
                if ($vignetteDaysRemaining <= $vignetteOneThird) {
                    $vignetteColor = 'red';
                    $vignetteTextColor = 'white';
                } elseif ($vignetteDaysRemaining <= $vignetteTwoThirds) {
                    $vignetteColor = 'orange';
                    $vignetteTextColor = 'black';
                } else {
                    $vignetteColor = 'blue';
                    $vignetteTextColor = 'white';
                }

                // Calculate color for assuranceDaysRemaining
                $assuranceOneThird = 364 / 3;
                $assuranceTwoThirds = 2 * $assuranceOneThird;
                if ($assuranceDaysRemaining <= $assuranceOneThird) {
                    $assuranceColor = 'red';
                    $assuranceTextColor = 'white';
                } elseif ($assuranceDaysRemaining <= $assuranceTwoThirds) {
                    $assuranceColor = 'orange';
                    $assuranceTextColor = 'black';
                } else {
                    $assuranceColor = 'blue';
                    $assuranceTextColor = 'white';
                }
            ?>

            <tr>
                <td>
                 <img src="../dossiers_images/<?php echo $contrat['avatar']; ?>" alt="Avatar" width="50" height="50" title="<?= $contrat['fullname'] ?>">
                </td>
                 <td>
    <?php if ($contrat['numero_chassis'] !== null) : ?>
        <b><i title="Marque: <?= $contrat['marque'] ?>, Couleur: <?= $contrat['couleur'] ?>"><?= $contrat['numero_chassis'] ?></i></b>
    <?php else : ?>
        <span class="badge badge-pill badge-danger">Numero Chassis manquant</span>
    <?php endif; ?>
</td>
                <td style="background-color: black; color: white"><?= $contrat['plaque_immatriculation'] ?></td>
   <td>
    <?php if ($contrat['statut_engin'] === 'En Utilisation') : ?>
        <span class="badge badge-pill badge-success badge-custom"><?= $contrat['statut_engin'] ?></span>
    <?php elseif ($contrat['statut_engin'] === 'Pas attribuée') : ?>
        <span class="badge badge-pill badge-warning badge-custom"><?= $contrat['statut_engin'] ?></span>
    <?php else : ?>
        <span class="badge badge-pill badge-danger badge-custom"><?= $contrat['statut_engin'] ?></span>
    <?php endif; ?>
</td>
                <td>
                <?= date('d-m-y', strtotime($contrat['vignette_date_debut'])) ?>
                </td>
                 <td style="font-weight: bold">
                  <?= date('d-m-y', strtotime($contrat['vignette_date_fin'])) ?>
                 </td>
                <td style="background-color: <?= $vignetteColor ?>; color: <?= $vignetteTextColor ?>;">
                    <?= $vignetteDaysRemaining ?> jours</td>
                <td>
                <?= date('d-m-y', strtotime($contrat['assurance_date_debut'])) ?>
                </td>
                 <td style="font-weight: bold">
                 <?= date('d-m-y', strtotime($contrat['assurance_date_fin'])) ?>
                 </td>
                <td style="background-color: <?= $assuranceColor ?>; color: <?= $assuranceTextColor ?>;">
                    <?= $assuranceDaysRemaining ?> jours</td>
                <td class="actions">
                    <a href="contrats_update.php?id=<?= $contrat['contrat_id'] ?>" class="edit">
                        <i class="fas fa-pen fa-xs" style="font-size:24px;color:blue"></i>
                    </a>
                    <a href="delete_contrat.php?id=<?= $contrat['contrat_id'] ?>" class="trash">
                        <i class="fas fa-trash fa-xs" style="font-size:24px;color:red"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="col-md-6">
        <div class="row">
            <div class="col-md-6">
                <canvas id="vignettePieChart" width="200" height="200"></canvas>
            </div>
            <div class="col-md-6">
                <canvas id="assurancePieChart" width="200" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="modal fade" id="add-contrat">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Enregistrer un contrat</h4>
                </div>
                <div class="modal-body">
                    <form class="forms-sample" method="post" action="save_contrats.php">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="vignette_date_debut">Date de Début vignette</label>
                                <input type="date" class="form-control" id="vignette_date_debut"
                                    name="vignette_date_debut">
                            </div>
                            <div class="form-group">
                                <label for="vignette_date_fin">Date de Fin vignette</label>
                                <input type="date" class="form-control" id="vignette_date_fin"
                                    name="vignette_date_fin">
                            </div>
                            <div class="form-group">
                                <label for="assurance_date_debut">Date de Début Assurance</label>
                                <input type="date" class="form-control" id="assurance_date_debut"
                                    name="assurance_date_debut">
                            </div>
                            <div class="form-group">
                                <label for="assurance_date_fin">Date de Fin Assurance</label>
                                <input type="date" class="form-control" id="assurance_date_fin"
                                    name="assurance_date_fin">
                            </div>
                            <div class="form-group">
                                <label for="id_engin">Plaque d'immatriculation</label>
                                <select id="id_engin" name="id_engin" class="form-control">
                                    <?php
                                    while ($typeEngins = $type_engins->fetch(PDO::FETCH_ASSOC)) {
                                        echo '<option value="' . $typeEngins['engin_id'] . '">' . $typeEngins['plaque_immatriculation'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary mr-2">Enregistrer</button>
                            <button class="btn btn-light">Annuler</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- /.row (main row) -->
</div><!-- /.container-fluid -->
<!-- /.content -->
</div>
<!-- /.content-wrapper -->

<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
</aside>
<!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="../../plugins/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="../../plugins/jquery-ui/jquery-ui.min.js"></script>
<!-- Bootstrap 4 -->
<script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 -->
<script src="../../plugins/sweetalert2/sweetalert2.min.js"></script>
<!-- AdminLTE App -->
<script src="../../dist/js/adminlte.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', (event) => {
        // Récupérer les données PHP pour les jours restants
        let vignetteDaysRemaining = [];
        let assuranceDaysRemaining = [];
        <?php foreach ($contrats as $contrat) :
            $currentDate = new DateTime();
            $vignetteEndDate = new DateTime($contrat['vignette_date_fin']);
            $assuranceEndDate = new DateTime($contrat['assurance_date_fin']);

            $vignetteDaysRemaining = $currentDate->diff($vignetteEndDate)->days;
            $assuranceDaysRemaining = $currentDate->diff($assuranceEndDate)->days;

            if ($vignetteEndDate < $currentDate) {
                $vignetteDaysRemaining = 0;
            }
            if ($assuranceEndDate < $currentDate) {
                $assuranceDaysRemaining = 0;
            }
        ?>
        vignetteDaysRemaining.push(<?= $vignetteDaysRemaining ?>);
        assuranceDaysRemaining.push(<?= $assuranceDaysRemaining ?>);
        <?php endforeach; ?>

        // Configuration du graphique pour la vignette
        let vignettePieChart = document.getElementById('vignettePieChart').getContext('2d');
        let vignetteChart = new Chart(vignettePieChart, {
            type: 'pie',
            data: {
                labels: ['Moins de 122 jours', 'Entre 122 et 244 jours', 'Plus de 244 jours'],
                datasets: [{
                    label: 'Jours restants vignette',
                    data: [0, 0, 0],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.6)',
                        'rgba(255, 159, 64, 0.6)',
                        'rgba(54, 162, 235, 0.6)'
                    ]
                }]
            },
            options: {
                title: {
                    display: true,
                    text: 'État des vignettes'
                }
            }
        });

        // Configuration du graphique pour l'assurance
        let assurancePieChart = document.getElementById('assurancePieChart').getContext('2d');
        let assuranceChart = new Chart(assurancePieChart, {
            type: 'pie',
            data: {
                labels: ['Moins de 122 jours', 'Entre 122 et 244 jours', 'Plus de 244 jours'],
                datasets: [{
                    label: 'Jours restants assurance',
                    data: [0, 0, 0],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.6)',
                        'rgba(255, 159, 64, 0.6)',
                        'rgba(54, 162, 235, 0.6)'
                    ]
                }]
            },
            options: {
                title: {
                    display: true,
                    text: 'État des assurances'
                }
            }
        });

        // Mettre à jour les données du graphique pour la vignette
        updateChartData(vignetteChart, vignetteDaysRemaining);

        // Mettre à jour les données du graphique pour l'assurance
        updateChartData(assuranceChart, assuranceDaysRemaining);

        // Fonction pour mettre à jour les données du graphique
        function updateChartData(chart, daysRemaining) {
            let count1 = 0;
            let count2 = 0;
            let count3 = 0;

            daysRemaining.forEach(days => {
                if (days < 122) {
                    count1++;
                } else if (days >= 122 && days <= 244) {
                    count2++;
                } else {
                    count3++;
                }
            });

            chart.data.datasets[0].data = [count1, count2, count3];
            chart.update();
        }

        // Gestion des popups avec SweetAlert2
        $('.trash').on('click', function (e) {
            e.preventDefault();
            var link = $(this).attr('href');
            Swal.fire({
                title: 'Êtes-vous sûr(e) de vouloir supprimer ce contrat ?',
                text: "Cette action est irréversible !",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Oui, supprimer !'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = link;
                }
            });
        });

    });
</script>
<?php if (isset($_SESSION['popup']) && $_SESSION['popup'] == true) : ?>
    <script>
        var audio = new Audio("../inc/sons/notification.mp3");
        audio.volume = 1.0; // Assurez-vous que le volume n'est pas à zéro
        audio.play().then(() => {
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
        }).catch((error) => {
            console.error('Erreur de lecture audio :', error);
        });
    </script>
    <?php $_SESSION['popup'] = false; ?>
<?php endif; ?>

<?php if (isset($_SESSION['delete_pop']) && $_SESSION['delete_pop'] == true) : ?>
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

</body>
</html>

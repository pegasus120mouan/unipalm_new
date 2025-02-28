<?php
include('header.php');
require_once '../inc/functions/connexion.php';

// Fonction pour traduire les mois en français
function moisEnFrancais($moisAnglais) {
    $mois = array(
        'January' => 'Janvier',
        'February' => 'Février',
        'March' => 'Mars',
        'April' => 'Avril',
        'May' => 'Mai',
        'June' => 'Juin',
        'July' => 'Juillet',
        'August' => 'Août',
        'September' => 'Septembre',
        'October' => 'Octobre',
        'November' => 'Novembre',
        'December' => 'Décembre'
    );
    return isset($mois[$moisAnglais]) ? $mois[$moisAnglais] : $moisAnglais;
}

// Requête pour les statistiques des commandes
$sql = "SELECT 
    DATE_FORMAT(date_commande, '%Y') AS annee,
    DATE_FORMAT(date_commande, '%M') AS mois,
    MONTH(date_commande) AS mois_numero,
    SUM(recette) AS total_recette,
    SUM(depense) AS total_depense,
    SUM(recette) - SUM(depense) AS gain
FROM 
    points_livreurs
GROUP BY 
    annee, mois, mois_numero
ORDER BY 
    annee DESC, mois_numero DESC";

$requete = $conn->prepare($sql);
$requete->execute();

$dataPoints = array();

while ($row = $requete->fetch(PDO::FETCH_ASSOC)) {
    $dataPoints[] = array(
        'annee' => $row['annee'],
        'mois' => moisEnFrancais($row['mois']),
        'total_recette' => $row['total_recette'],
        'total_depense' => $row['total_depense'],
        'gain' => $row['gain']
    );
}

$jsonData = json_encode($dataPoints);




// Requête pour les statistiques des commandes
$sql_livreurs = "SELECT 
    YEAR(c.date_commande) AS annee,
    MONTH(c.date_commande) AS mois_numero,
    CASE MONTH(c.date_commande)
        WHEN 1 THEN 'Janvier'
        WHEN 2 THEN 'Février'
        WHEN 3 THEN 'Mars'
        WHEN 4 THEN 'Avril'
        WHEN 5 THEN 'Mai'
        WHEN 6 THEN 'Juin'
        WHEN 7 THEN 'Juillet'
        WHEN 8 THEN 'Août'
        WHEN 9 THEN 'Septembre'
        WHEN 10 THEN 'Octobre'
        WHEN 11 THEN 'Novembre'
        WHEN 12 THEN 'Décembre'
    END AS mois,
    c.livreur_id,
    CONCAT(u.nom, ' ', u.prenoms) AS livreur_nom,
    COUNT(c.id) AS nombre_commandes,
    SUM(CASE WHEN c.statut = 'Livré' THEN 1 ELSE 0 END) AS nombre_commandes_livre,
    SUM(CASE WHEN c.statut = 'Non Livré' THEN 1 ELSE 0 END) AS nombre_commandes_non_livre,
    SUM(CASE WHEN c.statut = 'Livré' THEN c.cout_livraison ELSE 0 END) AS total_cout_livraison,
    IFNULL(SUM(pl.depense), 0) AS total_depense,
    IFNULL(SUM(pl.recette), 0) AS total_recette
FROM 
    commandes c
INNER JOIN 
    utilisateurs u ON c.livreur_id = u.id
LEFT JOIN 
    points_livreurs pl ON c.livreur_id = pl.utilisateur_id AND DATE(pl.date_commande) = DATE(c.date_commande)
WHERE 
    YEAR(c.date_commande) = YEAR(CURDATE()) -- Filtre pour l'année en cours
GROUP BY 
    annee, mois_numero, mois, c.livreur_id, livreur_nom
ORDER BY 
    annee DESC, mois_numero DESC, nombre_commandes DESC";

$requete_livreurs = $conn->prepare($sql_livreurs);
$requete_livreurs->execute();

$dataPoints_livreurs = array();

while ($row_clients = $requete_livreurs->fetch(PDO::FETCH_ASSOC)) {
    $dataPoints_livreurs[] = array(
        'annee' => $row_clients['annee'],
        'mois' =>  $row_clients['mois'],
        'livreur_nom' => $row_clients['livreur_nom'],
        'nombre_commandes' => $row_clients['nombre_commandes'],
        'nombre_commandes_livre' => $row_clients['nombre_commandes_livre'],
        'nombre_commandes_non_livre' => $row_clients['nombre_commandes_non_livre'],
    );
}

$jsonData_livreurs = json_encode($dataPoints_livreurs);





















// Requête pour les boutiques avec le plus grand nombre de commandes
$sqlBoutiques = "SELECT 
    YEAR(c.date_commande) AS annee,
    MONTH(c.date_commande) AS mois_numero,
    CASE MONTH(c.date_commande)
        WHEN 1 THEN 'Janvier'
        WHEN 2 THEN 'Février'
        WHEN 3 THEN 'Mars'
        WHEN 4 THEN 'Avril'
        WHEN 5 THEN 'Mai'
        WHEN 6 THEN 'Juin'
        WHEN 7 THEN 'Juillet'
        WHEN 8 THEN 'Août'
        WHEN 9 THEN 'Septembre'
        WHEN 10 THEN 'Octobre'
        WHEN 11 THEN 'Novembre'
        WHEN 12 THEN 'Décembre'
    END AS mois,
    c.livreur_id,
    CONCAT(u.nom, ' ', u.prenoms) AS livreur_nom,
    COUNT(c.id) AS nombre_commandes,
    SUM(CASE WHEN c.statut = 'Livré' THEN 1 ELSE 0 END) AS nombre_commandes_livre,
    SUM(CASE WHEN c.statut = 'Non Livré' THEN 1 ELSE 0 END) AS nombre_commandes_non_livre,
    SUM(CASE WHEN c.statut = 'Livré' THEN c.cout_livraison ELSE 0 END) AS total_cout_livraison,
    IFNULL(SUM(pl.depense), 0) AS total_depense,
    IFNULL(SUM(pl.recette), 0) AS total_recette
FROM 
    commandes c
INNER JOIN 
    utilisateurs u ON c.livreur_id = u.id
LEFT JOIN 
    points_livreurs pl ON c.livreur_id = pl.utilisateur_id AND DATE(pl.date_commande) = DATE(c.date_commande)
WHERE 
    YEAR(c.date_commande) = YEAR(CURDATE()) -- Filtre pour l'année en cours
GROUP BY 
    annee, mois_numero, mois, c.livreur_id, livreur_nom
ORDER BY 
    annee DESC, mois_numero DESC, nombre_commandes DESC;";

$requeteBoutiques = $conn->prepare($sqlBoutiques);
$requeteBoutiques->execute();

$boutiqueData = array();

while ($row = $requeteBoutiques->fetch(PDO::FETCH_ASSOC)) {
    $boutiqueData[] = array(
        'annee' => $row['annee'],
        'mois' => $row['mois'],
        'livreur_nom' => $row['livreur_nom'],
        'nombre_commandes' => $row['nombre_commandes'],
        'nombre_commandes_livre' => $row['nombre_commandes_livre'],
        'nombre_commandes_non_livre' => $row['nombre_commandes_non_livre']
    );
}

$jsonBoutiqueData = json_encode($boutiqueData);



$sqlGain = "SELECT YEAR(date_commande) AS annee, 
       SUM(depense) AS total_depense, 
       SUM(gain_jour) AS total_gain_jour,
       SUM(recette) AS total_recette
FROM points_livreurs
GROUP BY YEAR(date_commande)
ORDER BY annee DESC";
$requeteGain = $conn->prepare($sqlGain);
$requeteGain->execute();

?>



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Graphique des Montants</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <!-- Inclure DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
    <style>
        #myTable {
            height: 400px !important;
            overflow-y: scroll;
        }
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
        .custom-icon {
            color: green;
            font-size: 24px;
            margin-right: 8px;
        }
        .spacing {
            margin-right: 10px; 
            margin-bottom: 20px;
        }
        @media only screen and (max-width: 767px) {
            th {
                display: none; 
            }
            tbody tr {
                display: block;
                margin-bottom: 20px;
                border: 1px solid #ccc;
                padding: 10px;
            }
            tbody tr td::before {
                font-weight: bold;
                margin-right: 5px;
            }
        }
        .margin-right-15 {
            margin-right: 15px;
        }
        .block-container {
            background-color: #d7dbdd;
            padding: 20px;
            border-radius: 5px;
            width: 100%;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <div class="row">
        <div class="block-container">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#stat-clients">
                <i class="fas fa-chart-bar"></i> Statistiques clients
            </button>
            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#stat-livreurs">
                <i class="fas fa-chart-pie"></i> Statistiques livreurs
            </button>
            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#stat-gains">
                <i class="fa fa-money"></i> Point global des entrances
            </button>
        </div>
        <div class="col-md-6" id="myTable">
            <?php
            if (!empty($dataPoints)) {
                echo "<table class='table table-striped table-valign-middle'>
                    <thead>
                        <tr>
                            <th>Année</th>
                            <th>Mois</th>
                            <th>Total Recette</th>
                            <th>Total Dépense</th>
                            <th>Gain du Mois</th>
                        </tr>
                    </thead>
                    <tbody>";

                foreach ($dataPoints as $row) {
                    echo "<tr>
                        <td>" . htmlspecialchars($row['annee']) . "</td>
                        <td>" . htmlspecialchars($row['mois']) . "</td>
                        <td>" . htmlspecialchars($row['total_recette']) . "</td>
                        <td>" . htmlspecialchars($row['total_depense']) . "</td>
                        <td>" . htmlspecialchars($row['gain']) . "</td>
                    </tr>";
                }

                echo "</tbody></table>";
            } else {
                echo "Aucun résultat trouvé.";
            }
            ?>
        </div>
        <div class="col-md-6">
            <div class="position-relative mb-4">
                <canvas id="myChart" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Modal Add Commande -->
    <div class="modal fade" id="stat-clients" tabindex="-1" role="dialog" aria-labelledby="addCommandeLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="addCommandeLabel">Les clients les plus prolifiques</h3>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Contenu du modal -->
                    <p><i>Voici les informations sur les clients les plus prolifiques.</i></p>
                    <canvas id="clientsChart" height="400"></canvas> <!-- Canvas pour le graphique en secteurs -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Données des boutiques pour le graphique en secteurs
        var clientData = <?php echo $jsonBoutiqueData; ?>;
        var clientNames = [];
        var clientValues = [];

        // Remplir les noms et valeurs des boutiques à partir des données JSON
        for (var i = 0; i < clientData.length; i++) {
            clientNames.push(clientData[i].boutique_nom); // Correction ici
            clientValues.push(clientData[i].nombre_commandes);
        }

        // Création du graphique des boutiques
        var ctxClients = document.getElementById('clientsChart').getContext('2d');
        var clientsChart = new Chart(ctxClients, {
            type: 'pie',
            data: {
                labels: clientNames,
                datasets: [{
                    label: 'Nombre de Commandes',
                    data: clientValues,
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 159, 64, 0.2)',
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)'
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.label + ': ' + tooltipItem.raw;
                            }
                        }
                    }
                }
            }
        });

        // Données des dépenses et recettes pour le graphique
        var jsonData = <?php echo $jsonData; ?>;
        var labels = [];
        var depenses = [];
        var recettes = [];
        var gains = [];

        for (var i = 0; i < jsonData.length; i++) {
            labels.push(jsonData[i].mois + ' ' + jsonData[i].annee);
            depenses.push(jsonData[i].total_depense);
            recettes.push(jsonData[i].total_recette);
            gains.push(jsonData[i].gain);
        }

        // Création du graphique des dépenses, recettes et gains
        var ctx = document.getElementById('myChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Total Dépenses',
                        data: depenses,
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Total Recettes',
                        data: recettes,
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Gain',
                        data: gains,
                        backgroundColor: 'rgba(153, 102, 255, 0.2)',
                        borderColor: 'rgba(153, 102, 255, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>


    <div class="modal fade" id="stat-livreurs" tabindex="-1" role="dialog" aria-labelledby="stat-boutiquesLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="stat-boutiquesLabel">Statistiques des Boutiques</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                                <table id="boutiqueTable" class="display table table-striped table-bordered">

                         <thead class="thead-dark">
                            <tr>
                                <th>Année</th>
                                <th>Mois</th>
                                <th>Nom Livreur</th>
                                <th>Nombre colis</th>
                                <th>Nombre de colis livrés</th>
                                <th>Nombre de colis non livrés</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($boutiqueData as $boutique): ?>
                            <tr>
                                <td><?php echo $boutique['annee']; ?></td>
                                <td><?php echo $boutique['mois']; ?></td>
                                <td><?php echo $boutique['livreur_nom']; ?></td>
                                <td><?php echo $boutique['nombre_commandes']; ?></td>
                                <td><?php echo $boutique['nombre_commandes_livre']; ?></td>
                                <td><?php echo $boutique['nombre_commandes_non_livre']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="stat-gains" tabindex="-1" role="dialog" aria-labelledby="stat-boutiquesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="stat-boutiquesLabel">Statistiques des gains</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Table Display -->
                <table id="boutiqueTable" class="display table table-striped table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>Année d'exercice</th>
                            <th>Total de recettes par Année</th>
                            <th>Total des dépenses par Année</th>
                            <th>Gain Total par Année</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Prepare arrays for JavaScript usage
                        $years = [];
                        $totalRecettes = [];
                        $totalDepenses = [];
                        $totalGains = [];

                        foreach ($requeteGain as $gain):
                            // Fill the arrays
                            $years[] = $gain['annee'];
                            $totalRecettes[] = $gain['total_recette'];
                            $totalDepenses[] = $gain['total_depense'];
                            $totalGains[] = $gain['total_gain_jour'];
                        ?>
                        <tr>
                            <td><?php echo $gain['annee']; ?></td>
                            <td><?php echo $gain['total_recette']; ?></td>
                            <td><?php echo $gain['total_depense']; ?></td>
                            <td><?php echo $gain['total_gain_jour']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Bar Chart Display -->
                <canvas id="gainChart" width="400" height="200"></canvas>

                <!-- Pass PHP arrays to JavaScript -->
                <script>
                    var years = <?php echo json_encode($years); ?>;
                    var totalRecettes = <?php echo json_encode($totalRecettes); ?>;
                    var totalDepenses = <?php echo json_encode($totalDepenses); ?>;
                    var totalGains = <?php echo json_encode($totalGains); ?>;
                </script>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- Script to generate the bar chart -->
<script>
    var ctx = document.getElementById('gainChart').getContext('2d');
    var gainChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: years, // x-axis labels
            datasets: [
                {
                    label: 'Total Recettes',
                    data: totalRecettes,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Total Dépenses',
                    data: totalDepenses,
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Total Gains',
                    data: totalGains,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>


    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var ctx = document.getElementById('myChart').getContext('2d');
            var chartData = <?php echo $jsonData; ?>;

            var labels = chartData.map(function(data) { return data.mois + ' ' + data.annee; });
            var recettes = chartData.map(function(data) { return data.total_recette; });
            var depenses = chartData.map(function(data) { return data.total_depense; });

            var myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Recettes',
                            data: recettes,
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Dépenses',
                            data: depenses,
                            backgroundColor: 'rgba(255, 99, 132, 0.2)',
                            borderColor: 'rgba(255, 99, 132, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Initialisation des tables
            $('#livreurTable').DataTable();
            $('#boutiqueTable').DataTable();
        });
    </script>


    <!-- Inclure jQuery et Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <!-- Inclure DataTables JS -->
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>
</body>
</html>

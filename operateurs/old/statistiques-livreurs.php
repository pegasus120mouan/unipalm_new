<?php
include('header_livreurs.php');
require_once '../inc/functions/connexion.php';


    $id_user = $_SESSION['user_id'];

    $sql = "SELECT pl.date_commande, SUM(pl.depense) AS total_depense, 
            concat(u.nom, ' ', u.prenoms) AS nom_livreur
            FROM points_livreurs pl
            JOIN utilisateurs u ON pl.utilisateur_id = u.id
            WHERE pl.utilisateur_id = :id_user
            GROUP BY pl.date_commande
            ORDER BY pl.date_commande DESC";

    $requete = $conn->prepare($sql);
    $requete->bindParam(':id_user', $id_user, PDO::PARAM_INT);
    $requete->execute();

    $dataPoints = array();

    while ($row = $requete->fetch(PDO::FETCH_ASSOC)) {
        $dataPoints[] = array(
            'date_commande' => $row['date_commande'],
            'total_depense' => $row['total_depense'],
            'nom_livreur' => $row['nom_livreur']
        );
    }

    $jsonData = json_encode($dataPoints);
?>

<!DOCTYPE html>
<html lang="en">
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
    </style>
</head>
<body>

    <div class="row">
        <div class="col-md-6" id='myTable'>
            <?php
            if (!empty($dataPoints)) {
                echo "<table class='table table-striped table-valign-middle'>
                    <thead>
                        <tr>
                            <th>Nom Livreur</th>
                            <th>Date de Commande</th>
                            <th>Total Dépenses</th>
                        </tr>
                    </thead>
                    <tbody>";

                foreach ($dataPoints as $row) {
                    echo "<tr>
                        <td>" . (isset($row['nom_livreur']) ? $row['nom_livreur'] : '') . "</td>
                        <td>" . (isset($row['date_commande']) ? $row['date_commande'] : '') . "</td>
                        <td>" . (isset($row['total_depense']) ? $row['total_depense'] : '') . "</td>
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

    <script>
        var jsonData = <?php echo $jsonData; ?>;
        var dates = [];
        var montants = [];

        for (var i = 0; i < jsonData.length; i++) {
            dates.push(jsonData[i].date_commande);
            montants.push(jsonData[i].total_depense); // Correction ici
        }

        var ctx = document.getElementById('myChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: dates,
                datasets: [{
                    label: 'Montant Total des Dépenses',
                    data: montants,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
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

    <!-- Inclure DataTables JS -->
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>
    <script src="../plugins/jquery/jquery.min.js"></script>
    <!-- ... Autres scripts ... -->

    <!-- Inclure d'autres scripts JS ici -->

</body>
</html>

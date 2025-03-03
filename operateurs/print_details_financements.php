<?php
require_once('../inc/functions/connexion.php');

// Récupérer l'ID de l'agent depuis l'URL
$id_agent = isset($_GET['id_agent']) ? intval($_GET['id_agent']) : 0;

// Requête pour obtenir les détails des financements de l'agent
$query = "SELECT f.numero_financement, a.nom as nom_agent, DATE_FORMAT(f.date_financement, '%d/%m/%Y') as date_financement, 
          f.montant, f.motif 
          FROM financement f 
          JOIN agents a ON f.id_agent = a.id_agent
          WHERE f.id_agent = :id_agent
          ORDER BY f.date_financement DESC";

$stmt = $conn->prepare($query);
$stmt->execute(['id_agent' => $id_agent]);

// Récupérer le nom de l'agent
$nom_agent = "";
$financements = $stmt->fetchAll(PDO::FETCH_ASSOC);
if(count($financements) > 0) {
    $nom_agent = $financements[0]['nom_agent'];
}

// Calculer le total
$total = array_sum(array_column($financements, 'montant'));
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Détails des financements - <?= htmlspecialchars($nom_agent) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 14px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header img {
            max-width: 150px;
            margin-bottom: 10px;
        }
        .date {
            text-align: right;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .total {
            text-align: right;
            font-weight: bold;
            margin-top: 20px;
            font-size: 16px;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="../inc/images/logo.png" alt="UNIPALM">
        <h2>Détails des financements</h2>
        <h3><?= htmlspecialchars($nom_agent) ?></h3>
    </div>

    <div class="date">
        Date d'impression: <?= date('d/m/Y') ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>N° Financement</th>
                <th>Agent</th>
                <th>Date</th>
                <th style="text-align: right;">Montant</th>
                <th>Motif</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($financements as $f): ?>
            <tr>
                <td><?= htmlspecialchars($f['numero_financement']) ?></td>
                <td><?= htmlspecialchars($f['nom_agent']) ?></td>
                <td><?= htmlspecialchars($f['date_financement']) ?></td>
                <td style="text-align: right;"><?= number_format($f['montant'], 0, ',', ' ') ?> FCFA</td>
                <td><?= htmlspecialchars($f['motif']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="total">
        Total: <?= number_format($total, 0, ',', ' ') ?> FCFA
    </div>

    <div class="no-print" style="margin-top: 20px;">
        <button onclick="window.print()">Imprimer</button>
        <button onclick="window.close()">Fermer</button>
    </div>

    <script>
        // Imprimer automatiquement
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>

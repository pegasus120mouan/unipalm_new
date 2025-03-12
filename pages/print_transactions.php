<?php
session_start();
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_approvisionnement.php';

if (!isset($_POST['date_debut_transactions']) || !isset($_POST['date_fin_transactions'])) {
    $_SESSION['error_message'] = "Les dates sont requises";
    header('Location: approvisionnement.php');
    exit;
}

$date_debut = $_POST['date_debut_transactions'];
$date_fin = $_POST['date_fin_transactions'];

// Récupérer les transactions de la période
$transactions = getTransactionsByPeriod($conn, $date_debut, $date_fin);

// Calcul des totaux
$total_entrees = 0;
$total_sorties = 0;
foreach ($transactions as $transaction) {
    if ($transaction['type_transaction'] == 'entree') {
        $total_entrees += $transaction['montant'];
    } else {
        $total_sorties += $transaction['montant'];
    }
}

$solde = $total_entrees - $total_sorties;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des transactions</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .periode {
            text-align: center;
            margin-bottom: 20px;
            font-style: italic;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
        }
        .total {
            margin-top: 20px;
            text-align: right;
        }
        .total-box {
            border: 1px solid #ddd;
            padding: 10px;
            display: inline-block;
            margin-left: 20px;
        }
        .print-button {
            text-align: center;
            margin-top: 20px;
        }
        @media print {
            .print-button {
                display: none;
            }
        }
        .entree {
            color: green;
        }
        .sortie {
            color: red;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Liste des transactions</h1>
    </div>

    <div class="periode">
        <p>Période du <?= date('d/m/Y', strtotime($date_debut)) ?> au <?= date('d/m/Y', strtotime($date_fin)) ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Description</th>
                <th>Montant</th>
                <th>Utilisateur</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($transactions as $transaction): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($transaction['date_transaction'])) ?></td>
                    <td class="<?= $transaction['type_transaction'] == 'entree' ? 'entree' : 'sortie' ?>">
                        <?= ucfirst($transaction['type_transaction']) ?>
                    </td>
                    <td><?= htmlspecialchars($transaction['description']) ?></td>
                    <td><?= number_format($transaction['montant'], 0, ',', ' ') ?> FCFA</td>
                    <td><?= htmlspecialchars($transaction['nom_utilisateur']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="total">
        <div class="total-box">
            <p><strong>Total Entrées:</strong> <?= number_format($total_entrees, 0, ',', ' ') ?> FCFA</p>
            <p><strong>Total Sorties:</strong> <?= number_format($total_sorties, 0, ',', ' ') ?> FCFA</p>
            <hr>
            <p><strong>Solde:</strong> <?= number_format($solde, 0, ',', ' ') ?> FCFA</p>
        </div>
    </div>

    <div class="print-button">
        <button onclick="window.print()">Imprimer</button>
        <button onclick="window.location.href='approvisionnement.php'">Retour</button>
    </div>
</body>
</html>

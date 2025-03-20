<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_usines.php';

// Récupération des paramètres
$usine_id = isset($_GET['usine_id']) ? $_GET['usine_id'] : null;
$date_debut = $_GET['date_debut'];
$date_fin = $_GET['date_fin'];

// Construction de la requête SQL de base
$sql = "SELECT u.nom_usine, t.numero_ticket, t.montant_paie, t.date_creation, p.montant as montant_paye, p.date_paiement, p.source_paiement 
        FROM tickets t 
        LEFT JOIN usines u ON t.id_usine = u.id_usine 
        LEFT JOIN paiements p ON t.id_ticket = p.id_ticket 
        WHERE t.date_creation BETWEEN :date_debut AND :date_fin";

// Ajout du filtre par usine si spécifié
if ($usine_id) {
    $sql .= " AND t.id_usine = :usine_id";
}

$sql .= " ORDER BY u.nom_usine, t.date_creation";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':date_debut', $date_debut);
$stmt->bindParam(':date_fin', $date_fin);

if ($usine_id) {
    $stmt->bindParam(':usine_id', $usine_id);
}

$stmt->execute();
$paiements = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcul des totaux
$total_montant = 0;
$total_paye = 0;
$total_restant = 0;

foreach ($paiements as $paiement) {
    $total_montant += $paiement['montant_paie'];
    $total_paye += $paiement['montant_paye'] ?? 0;
}
$total_restant = $total_montant - $total_paye;

// Formatage des dates pour l'affichage
$date_debut_fr = date('d/m/Y', strtotime($date_debut));
$date_fin_fr = date('d/m/Y', strtotime($date_fin));
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des paiements par usine</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .period {
            font-size: 14px;
            margin-bottom: 20px;
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
        .totals {
            margin-top: 20px;
            font-weight: bold;
        }
        .print-footer {
            position: fixed;
            bottom: 20px;
            width: 100%;
            text-align: center;
            font-size: 10px;
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
        <div class="title">LISTE DES PAIEMENTS PAR USINE</div>
        <div class="period">Période du <?= $date_debut_fr ?> au <?= $date_fin_fr ?></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Usine</th>
                <th>N° Ticket</th>
                <th>Montant</th>
                <th>Date création</th>
                <th>Montant payé</th>
                <th>Date paiement</th>
                <th>Source</th>
                <th>Reste</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($paiements as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['nom_usine']) ?></td>
                    <td><?= htmlspecialchars($p['numero_ticket']) ?></td>
                    <td><?= number_format($p['montant_paie'], 0, ',', ' ') ?> FCFA</td>
                    <td><?= date('d/m/Y', strtotime($p['date_creation'])) ?></td>
                    <td><?= $p['montant_paye'] ? number_format($p['montant_paye'], 0, ',', ' ') . ' FCFA' : '-' ?></td>
                    <td><?= $p['date_paiement'] ? date('d/m/Y', strtotime($p['date_paiement'])) : '-' ?></td>
                    <td><?= htmlspecialchars($p['source_paiement'] ?? '-') ?></td>
                    <td><?= number_format($p['montant_paie'] - ($p['montant_paye'] ?? 0), 0, ',', ' ') ?> FCFA</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="totals">
        <p>Total montant: <?= number_format($total_montant, 0, ',', ' ') ?> FCFA</p>
        <p>Total payé: <?= number_format($total_paye, 0, ',', ' ') ?> FCFA</p>
        <p>Total restant: <?= number_format($total_restant, 0, ',', ' ') ?> FCFA</p>
    </div>

    <div class="print-footer">
        Imprimé le <?= date('d/m/Y H:i') ?>
    </div>

    <button class="no-print" onclick="window.print()" style="margin-top: 20px; padding: 10px;">
        Imprimer
    </button>
</body>
</html>

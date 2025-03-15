<?php
require_once '../inc/functions/connexion.php';
//session_start();

if (!isset($_GET['receipt']) || empty($_GET['receipt'])) {
    header('Location: paiements_demande.php');
    exit();
}

$numero_demande = $_GET['receipt'];

// Get receipt details
$stmt = $conn->prepare("
    SELECT 
        r.*,
        d.motif,
        CONCAT(u.nom, ' ', u.prenoms) as caissier_name,
        d.montant as montant_total,
        d.montant_payer as montant_paye,
        d.montant - d.montant_payer as montant_reste
    FROM recus_demandes r
    JOIN demande_sortie d ON r.demande_id = d.id_demande
    JOIN utilisateurs u ON r.caissier_id = u.id
    WHERE r.numero_demande = ?
");
$stmt->execute([$numero_demande]);
$receipt = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$receipt) {
    header('Location: paiements_demande.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reçu de Paiement</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f6f9;
        }
        .receipt {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
            border: 1px solid #ddd;
            background-color: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #2c3e50;
            margin: 0;
            font-size: 28px;
        }
        .header h3 {
            color: #7f8c8d;
            margin: 10px 0 0 0;
        }
        .receipt-details {
            margin-bottom: 30px;
        }
        .receipt-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .receipt-details td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        .receipt-details td:first-child {
            font-weight: bold;
            width: 200px;
            color: #34495e;
        }
        .amount {
            font-size: 28px;
            font-weight: bold;
            text-align: center;
            margin: 30px 0;
            color: #2c3e50;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            color: #7f8c8d;
            font-style: italic;
        }
        .actions {
            text-align: center;
            margin-top: 30px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 0 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        .btn-print {
            background-color: #007bff;
            color: white;
        }
        .btn-return {
            background-color: #6c757d;
            color: white;
        }
        .btn:hover {
            opacity: 0.9;
        }
        @media print {
            body {
                background-color: white;
                padding: 0;
            }
            .receipt {
                box-shadow: none;
                border: none;
            }
            .actions {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <h1>REÇU DE PAIEMENT</h1>
            <h3><?php echo $receipt['numero_demande']; ?></h3>
        </div>
        
        <div class="receipt-details">
            <table>
                <tr>
                    <td>Date:</td>
                    <td><?php echo date('d/m/Y H:i', strtotime($receipt['date_paiement'])); ?></td>
                </tr>
                <tr>
                    <td>N° Demande:</td>
                    <td><?php echo $receipt['numero_demande']; ?></td>
                </tr>
                <tr>
                    <td>Motif:</td>
                    <td><?php echo $receipt['motif']; ?></td>
                </tr>
                <tr>
                    <td>Source de paiement:</td>
                    <td><?php echo $receipt['source_paiement']; ?></td>
                </tr>
                <tr>
                    <td>Caissier:</td>
                    <td><?php echo $receipt['caissier_name']; ?></td>
                </tr>
            </table>
        </div>

        <div class="amount">
            Montant payé: <?php echo number_format($receipt['montant'], 0, ',', ' '); ?> FCFA
        </div>

        <div class="receipt-details">
            <table>
                <tr>
                    <td>Montant total:</td>
                    <td><?php echo number_format($receipt['montant_total'], 0, ',', ' '); ?> FCFA</td>
                </tr>
                <tr>
                    <td>Montant payé (total):</td>
                    <td><?php echo number_format($receipt['montant_paye'], 0, ',', ' '); ?> FCFA</td>
                </tr>
                <tr>
                    <td>Reste à payer:</td>
                    <td><?php echo number_format($receipt['montant_reste'], 0, ',', ' '); ?> FCFA</td>
                </tr>
            </table>
        </div>

        <div class="footer">
            <p>Merci de votre confiance!</p>
        </div>
    </div>

    <div class="actions">
        <button class="btn btn-print" onclick="window.print()">
            <i class="fas fa-print"></i> Imprimer le reçu
        </button>
        <button class="btn btn-return" onclick="window.location.href='paiements_demande.php'">
            <i class="fas fa-arrow-left"></i> Retour
        </button>
    </div>
</body>
</html>

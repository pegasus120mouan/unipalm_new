<?php
require_once '../inc/functions/connexion.php';
// Suppression du session_start() car déjà appelé dans connexion.php

// Vérifier si l'ID du paiement est fourni
if (!isset($_GET['id_ticket']) && !isset($_GET['id_bordereau'])) {
    header("Location: paiements.php");
    exit;
}

// Vérifier si le montant du paiement est disponible
if (!isset($_SESSION['montant_paiement'])) {
    header("Location: paiements.php");
    exit;
}

$montant_actuel = floatval($_SESSION['montant_paiement']);
unset($_SESSION['montant_paiement']); // Nettoyer la session

// Récupérer les informations du paiement
if (isset($_GET['id_ticket'])) {
    $id_ticket = $_GET['id_ticket'];
    $stmt = $conn->prepare("
        SELECT 
            t.*,
            CONCAT(a.nom, ' ', a.prenom) as agent_nom,
            a.contact as agent_contact,
            us.nom_usine,
            v.matricule_vehicule,
            CONCAT(u.nom, ' ', u.prenoms) as caissier_nom
        FROM tickets t
        LEFT JOIN agents a ON t.id_agent = a.id_agent
        LEFT JOIN usines us ON t.id_usine = us.id_usine
        LEFT JOIN vehicules v ON t.vehicule_id = v.vehicules_id
        LEFT JOIN utilisateurs u ON t.id_utilisateur = u.id
        WHERE t.id_ticket = ?
    ");
    $stmt->execute([$id_ticket]);
    $paiement = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $numero_document = $paiement['numero_ticket'];
    $type_document = 'Ticket';
} else {
    $id_bordereau = $_GET['id_bordereau'];
    $stmt = $conn->prepare("
        SELECT 
            b.*,
            CONCAT(a.nom, ' ', a.prenom) as agent_nom,
            a.contact as agent_contact,
            CONCAT(u.nom, ' ', u.prenoms) as caissier_nom
        FROM bordereau b
        LEFT JOIN agents a ON b.id_agent = a.id_agent
        LEFT JOIN utilisateurs u ON b.id_utilisateur = u.id
        WHERE b.id_bordereau = ?
    ");
    $stmt->execute([$id_bordereau]);
    $paiement = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $numero_document = $paiement['numero_bordereau'];
    $type_document = 'Bordereau';
}

// Si le paiement n'existe pas
if (!$paiement) {
    header("Location: paiements.php");
    exit;
}

// Générer un numéro de reçu unique
$numero_recu = date('Ymd') . sprintf("%04d", rand(1, 9999));

// Calculer les montants (s'assurer qu'ils sont traités comme des nombres)
$montant_total = floatval($paiement['montant_paie']);
$montant_deja_paye = floatval($paiement['montant_payer']) - $montant_actuel;
$reste_a_payer = $montant_total - floatval($paiement['montant_payer']);

// S'assurer que les montants ne sont pas négatifs
$montant_deja_paye = max(0, $montant_deja_paye);
$reste_a_payer = max(0, $reste_a_payer);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reçu de Paiement - <?= $numero_document ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 14px;
        }
        .recu {
            max-width: 700px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #000;
            position: relative;
            background-color: white;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.1;
            z-index: 0;
            pointer-events: none;
            width: 80%;
            max-width: 500px;
        }
        .content {
            position: relative;
            z-index: 1;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .logo {
            width: 100px;
            height: auto;
        }
        .header-center {
            flex-grow: 1;
            text-align: center;
            margin: 0 20px;
        }
        .header-right {
            text-align: right;
        }
        h1 {
            font-size: 24px;
            margin: 10px 0;
        }
        .section-title {
            font-weight: bold;
            font-size: 16px;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        .info-row {
            display: flex;
            margin-bottom: 10px;
        }
        .info-label {
            font-weight: bold;
            width: 150px;
        }
        .info-value {
            flex: 1;
        }
        .separator {
            border-top: 1px solid #000;
            margin: 20px 0;
        }
        .montants {
            margin-top: 20px;
        }
        .montant-total {
            font-weight: bold;
            font-size: 16px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            color: #666;
        }
        .print-button {
            text-align: center;
            margin-top: 20px;
        }
        @media print {
            .print-button {
                display: none;
            }
            body {
                padding: 0;
            }
            .recu {
                border: none;
            }
        }
    </style>
</head>
<body>
    <div class="recu">
        <img src="../../dist/img/logo.png" alt="Watermark" class="watermark">
        <div class="content">
            <div class="header">
                <img src="../../dist/img/logo.png" alt="Logo" class="logo">
                <div class="header-center">
                    <h1>Reçu de Paiement</h1>
                    <p>N° <?= $type_document ?>: <?= $numero_document ?></p>
                    <p>Date: <?= date('d/m/Y H:i') ?></p>
                </div>
                <div class="header-right">
                    N° <?= $numero_recu ?>
                </div>
            </div>

            <div class="section-title">Informations Agent</div>
            <div class="info-row">
                <div class="info-label">Nom de l'agent:</div>
                <div class="info-value"><?= htmlspecialchars($paiement['agent_nom']) ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Contact:</div>
                <div class="info-value"><?= htmlspecialchars($paiement['agent_contact']) ?></div>
            </div>

            <?php if (isset($paiement['nom_usine'])): ?>
            <div class="section-title">Informations Transport</div>
            <div class="info-row">
                <div class="info-label">Usine:</div>
                <div class="info-value"><?= htmlspecialchars($paiement['nom_usine']) ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Véhicule:</div>
                <div class="info-value"><?= htmlspecialchars($paiement['matricule_vehicule']) ?></div>
            </div>
            <?php endif; ?>

            <div class="separator"></div>

            <div class="montants">
                <div class="info-row">
                    <div class="info-label">Montant total:</div>
                    <div class="info-value"><?= number_format($montant_total, 0, ',', ' ') ?> FCFA</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Montant payé:</div>
                    <div class="info-value"><?= number_format($montant_actuel, 0, ',', ' ') ?> FCFA</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Déjà payé:</div>
                    <div class="info-value"><?= number_format($montant_deja_paye, 0, ',', ' ') ?> FCFA</div>
                </div>
                <div class="info-row montant-total">
                    <div class="info-label">Reste à payer:</div>
                    <div class="info-value"><?= number_format($reste_a_payer, 0, ',', ' ') ?> FCFA</div>
                </div>
            </div>

            <div class="footer">
                <p>Caissier: <?= htmlspecialchars($paiement['caissier_nom']) ?></p>
                <p>Ce reçu est généré électroniquement et ne nécessite pas de signature.</p>
            </div>
        </div>
    </div>

    <div class="print-button">
        <button onclick="window.print()">Imprimer le reçu</button>
        <button onclick="window.location.href='paiements.php'">Retour à la liste</button>
    </div>
</body>
</html>

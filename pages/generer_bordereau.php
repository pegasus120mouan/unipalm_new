<?php
require_once '../inc/functions/connexion.php';
session_start();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "ID du ticket non spécifié";
    header('Location: paiements.php');
    exit();
}

$ticket_id = $_GET['id'];

// Récupérer les informations du ticket
$stmt = $conn->prepare("
    SELECT 
        t.*,
        CONCAT(u.nom, ' ', u.prenoms) AS utilisateur_nom_complet,
        u.contact AS utilisateur_contact,
        v.matricule_vehicule,
        CONCAT(a.nom, ' ', a.prenom) AS agent_nom_complet,
        us.nom_usine
    FROM 
        tickets t
    INNER JOIN 
        utilisateurs u ON t.id_utilisateur = u.id
    INNER JOIN 
        vehicules v ON t.vehicule_id = v.vehicules_id
    INNER JOIN 
        agents a ON t.id_agent = a.id_agent
    INNER JOIN 
        usines us ON t.id_usine = us.id_usine
    WHERE 
        t.id_ticket = ?
");

$stmt->execute([$ticket_id]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {
    $_SESSION['error'] = "Ticket non trouvé";
    header('Location: paiements.php');
    exit();
}

// Vérifier si le ticket est entièrement payé
$montant_paye = !isset($ticket['montant_payer']) || $ticket['montant_payer'] === null ? 0 : $ticket['montant_payer'];
$montant_total = $ticket['montant_paie'];
$montant_reste = $montant_total - $montant_paye;

if ($montant_reste > 0) {
    $_SESSION['error'] = "Le ticket n'est pas encore entièrement payé";
    header('Location: paiements.php');
    exit();
}

// Récupérer l'historique des paiements
$stmt = $conn->prepare("
    SELECT * 
    FROM transactions 
    WHERE id_ticket = ? 
    ORDER BY date_transaction ASC
");
$stmt->execute([$ticket_id]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Générer le PDF avec TCPDF
require_once('../vendor/tecnickcom/tcpdf/tcpdf.php');

// Créer une nouvelle instance de TCPDF
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');

// Définir les informations du document
$pdf->SetCreator('UNIPALM');
$pdf->SetAuthor('UNIPALM');
$pdf->SetTitle('Bordereau de Paiement - Ticket #' . $ticket['numero_ticket']);

// Supprimer les en-têtes et pieds de page par défaut
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Ajouter une nouvelle page
$pdf->AddPage();

// Définir la police
$pdf->SetFont('helvetica', '', 12);

// Logo et en-tête
$pdf->Image('../dist/img/logo.png', 10, 10, 30);
$pdf->Cell(0, 10, 'BORDEREAU DE PAIEMENT', 0, 1, 'C');
$pdf->Ln(10);

// Informations du ticket
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Informations du Ticket', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(60, 7, 'Numéro du ticket:', 0, 0);
$pdf->Cell(0, 7, $ticket['numero_ticket'], 0, 1);
$pdf->Cell(60, 7, 'Date du ticket:', 0, 0);
$pdf->Cell(0, 7, date('d/m/Y', strtotime($ticket['date_ticket'])), 0, 1);
$pdf->Cell(60, 7, 'Usine:', 0, 0);
$pdf->Cell(0, 7, $ticket['nom_usine'], 0, 1);
$pdf->Cell(60, 7, 'Véhicule:', 0, 0);
$pdf->Cell(0, 7, $ticket['matricule_vehicule'], 0, 1);
$pdf->Cell(60, 7, 'Chargé de mission:', 0, 0);
$pdf->Cell(0, 7, $ticket['agent_nom_complet'], 0, 1);
$pdf->Cell(60, 7, 'Poids:', 0, 0);
$pdf->Cell(0, 7, number_format($ticket['poids'], 0, ',', ' ') . ' kg', 0, 1);
$pdf->Cell(60, 7, 'Prix unitaire:', 0, 0);
$pdf->Cell(0, 7, number_format($ticket['prix_unitaire'], 0, ',', ' ') . ' FCFA', 0, 1);
$pdf->Cell(60, 7, 'Montant total:', 0, 0);
$pdf->Cell(0, 7, number_format($montant_total, 0, ',', ' ') . ' FCFA', 0, 1);
$pdf->Ln(10);

// Historique des paiements
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Historique des Paiements', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 12);

$pdf->Cell(50, 7, 'Date', 1, 0, 'C');
$pdf->Cell(70, 7, 'Montant', 1, 0, 'C');
$pdf->Cell(70, 7, 'Mode de paiement', 1, 1, 'C');

foreach ($transactions as $transaction) {
    $pdf->Cell(50, 7, date('d/m/Y', strtotime($transaction['date_transaction'])), 1, 0, 'C');
    $pdf->Cell(70, 7, number_format($transaction['montant'], 0, ',', ' ') . ' FCFA', 1, 0, 'C');
    $pdf->Cell(70, 7, $transaction['mode_paiement'], 1, 1, 'C');
}

// Signature et date
$pdf->Ln(20);
$pdf->Cell(0, 10, 'Date d\'émission: ' . date('d/m/Y'), 0, 1, 'R');
$pdf->Cell(0, 10, 'Signature:', 0, 1, 'R');

// Générer le PDF
$pdf->Output('Bordereau_' . $ticket['numero_ticket'] . '.pdf', 'D');

<?php
require_once '../inc/functions/connexion.php';
session_start();

// Récupérer tous les tickets payés
$stmt = $conn->prepare("
    SELECT 
        t.*,
        CONCAT(u.nom, ' ', u.prenoms) AS utilisateur_nom_complet,
        u.contact AS utilisateur_contact,
        v.matricule_vehicule,
        CONCAT(a.nom, ' ', a.prenom) AS agent_nom_complet,
        us.nom_usine,
        tr.date_transaction,
        tr.montant as montant_transaction,
        tr.mode_paiement
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
    LEFT JOIN
        transactions tr ON t.id_ticket = tr.id_ticket
    WHERE 
        t.prix_unitaire > 0
        AND t.montant_payer IS NOT NULL
        AND t.montant_payer > 0
    ORDER BY 
        t.date_ticket DESC
");

$stmt->execute();
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($tickets)) {
    $_SESSION['error'] = "Aucun ticket payé trouvé";
    header('Location: paiements.php');
    exit();
}

// Générer le PDF avec TCPDF
require_once('../vendor/tecnickcom/tcpdf/tcpdf.php');

// Créer une nouvelle instance de TCPDF
$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8');

// Définir les informations du document
$pdf->SetCreator('UNIPALM');
$pdf->SetAuthor('UNIPALM');
$pdf->SetTitle('Bordereau Global des Paiements');

// Supprimer les en-têtes et pieds de page par défaut
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Ajouter une nouvelle page
$pdf->AddPage();

// Logo et en-tête
$pdf->Image('../dist/img/logo.png', 10, 10, 30);
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'BORDEREAU GLOBAL DES PAIEMENTS', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 10, 'Date d\'édition : ' . date('d/m/Y'), 0, 1, 'R');
$pdf->Ln(10);

// En-têtes du tableau
$pdf->SetFont('helvetica', 'B', 8);
$header = array(
    'Date ticket', 
    'N° Ticket', 
    'Usine', 
    'Chargé Mission',
    'Véhicule',
    'Poids (kg)',
    'Prix Unit.',
    'Montant Total',
    'Montant Payé',
    'Mode Paiement',
    'Date Paiement'
);

// Largeurs des colonnes
$w = array(20, 25, 30, 35, 25, 20, 20, 25, 25, 25, 25);

// En-têtes
for($i = 0; $i < count($header); $i++) {
    $pdf->Cell($w[$i], 7, $header[$i], 1, 0, 'C');
}
$pdf->Ln();

// Données
$pdf->SetFont('helvetica', '', 8);
$total_global = 0;

foreach($tickets as $ticket) {
    $pdf->Cell($w[0], 6, date('d/m/Y', strtotime($ticket['date_ticket'])), 1, 0, 'C');
    $pdf->Cell($w[1], 6, $ticket['numero_ticket'], 1, 0, 'C');
    $pdf->Cell($w[2], 6, $ticket['nom_usine'], 1, 0, 'L');
    $pdf->Cell($w[3], 6, $ticket['agent_nom_complet'], 1, 0, 'L');
    $pdf->Cell($w[4], 6, $ticket['matricule_vehicule'], 1, 0, 'C');
    $pdf->Cell($w[5], 6, number_format($ticket['poids'], 0, ',', ' '), 1, 0, 'R');
    $pdf->Cell($w[6], 6, number_format($ticket['prix_unitaire'], 0, ',', ' '), 1, 0, 'R');
    $pdf->Cell($w[7], 6, number_format($ticket['montant_paie'], 0, ',', ' '), 1, 0, 'R');
    $pdf->Cell($w[8], 6, number_format($ticket['montant_payer'], 0, ',', ' '), 1, 0, 'R');
    $pdf->Cell($w[9], 6, $ticket['mode_paiement'], 1, 0, 'C');
    $pdf->Cell($w[10], 6, $ticket['date_transaction'] ? date('d/m/Y', strtotime($ticket['date_transaction'])) : '-', 1, 0, 'C');
    $pdf->Ln();
    
    $total_global += $ticket['montant_payer'];
}

// Total global
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Ln();
$pdf->Cell(array_sum(array_slice($w, 0, 8)), 7, 'TOTAL GLOBAL:', 1, 0, 'R');
$pdf->Cell($w[8], 7, number_format($total_global, 0, ',', ' ') . ' FCFA', 1, 0, 'R');
$pdf->Cell(array_sum(array_slice($w, 9)), 7, '', 1, 1, 'C');

// Signature
$pdf->Ln(20);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 10, 'Signature et Cachet:', 0, 1, 'R');

// Générer le PDF
$pdf->Output('Bordereau_Global_' . date('Y-m-d') . '.pdf', 'D');

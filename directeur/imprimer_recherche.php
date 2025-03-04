<?php
require('../fpdf/fpdf.php');
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_tickets.php';

// Récupérer les filtres de l'URL
$filters = [];
if (isset($_GET['agent']) && !empty($_GET['agent'])) {
    $filters['agent'] = $_GET['agent'];
}
if (isset($_GET['usine']) && !empty($_GET['usine'])) {
    $filters['usine'] = $_GET['usine'];
}
if (isset($_GET['vehicule']) && !empty($_GET['vehicule'])) {
    $filters['vehicule'] = $_GET['vehicule'];
}
if (isset($_GET['date_debut']) && !empty($_GET['date_debut'])) {
    $filters['date_debut'] = $_GET['date_debut'];
}
if (isset($_GET['date_fin']) && !empty($_GET['date_fin'])) {
    $filters['date_fin'] = $_GET['date_fin'];
}

// Connexion à la base de données
$conn = getConnexion();

// Récupérer les tickets filtrés
$tickets = getTickets($conn, $filters);

class PDF extends FPDF {
    function Header() {
        if (file_exists('../dist/img/logo.png')) {
            $this->Image('../dist/img/logo.png', 10, 10, 30);
        }
        
        // Titre de l'entreprise en vert
        $this->SetTextColor(0, 128, 0);
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, 'UNIPALM COOP - CA', 0, 1, 'C');
        
        // Sous-titre en vert clair
        $this->SetTextColor(144, 238, 144);
        $this->SetFont('Arial', '', 11);
        $this->Cell(0, 5, utf8_decode('Société Coopérative Agricole Unie pour le Palmier'), 0, 1, 'C');
        
        $this->Ln(15);
    }

    function Footer() {
        $this->SetY(-20);
        
        // Ligne verte
        $this->SetDrawColor(144, 238, 144);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        
        // Texte en vert clair
        $this->SetTextColor(144, 238, 144);
        $this->SetFont('Arial', '', 8);
        $this->Cell(0, 5, utf8_decode('Siège Social : Divo Quartier millionnaire non loin de l\'hôtel Boya'), 0, 1, 'C');
        $this->Cell(0, 5, 'NCC : 2050R910 / TEL : (00225) 27 34 75 92 36 / 07 49 17 16 32', 0, 1, 'C');
    }
}

$pdf = new PDF();
$pdf->AddPage('L'); // Format paysage
$pdf->SetAutoPageBreak(true, 35);

// Titre du document
$pdf->SetFont('Arial', 'BU', 16);
$pdf->SetTextColor(0);
$pdf->Cell(0, 12, utf8_decode('RAPPORT DES TICKETS'), 0, 1, 'C', false);
$pdf->Ln(5);

// Afficher les filtres appliqués
$pdf->SetFont('Arial', 'B', 11);
if (!empty($filters)) {
    if (isset($filters['agent'])) {
        $pdf->Cell(50, 8, 'Agent:', 0, 0);
        $pdf->SetFont('Arial', '', 11);
        $stmt = $conn->prepare("SELECT CONCAT(nom, ' ', prenom) as nom_complet FROM agents WHERE id_agent = ?");
        $stmt->execute([$filters['agent']]);
        $agent = $stmt->fetch(PDO::FETCH_ASSOC);
        $pdf->Cell(0, 8, utf8_decode($agent['nom_complet']), 0, 1);
        $pdf->SetFont('Arial', 'B', 11);
    }
    
    if (isset($filters['usine'])) {
        $pdf->Cell(50, 8, 'Usine:', 0, 0);
        $pdf->SetFont('Arial', '', 11);
        $stmt = $conn->prepare("SELECT nom_usine FROM usines WHERE id_usine = ?");
        $stmt->execute([$filters['usine']]);
        $usine = $stmt->fetch(PDO::FETCH_ASSOC);
        $pdf->Cell(0, 8, utf8_decode($usine['nom_usine']), 0, 1);
        $pdf->SetFont('Arial', 'B', 11);
    }

    if (isset($filters['date_debut']) && isset($filters['date_fin'])) {
        $pdf->Cell(50, 8, utf8_decode('Période du:'), 0, 0);
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(0, 8, date('d/m/Y', strtotime($filters['date_debut'])) . ' au ' . date('d/m/Y', strtotime($filters['date_fin'])), 0, 1);
    }
}
$pdf->Ln(5);

// Regrouper les tickets par usine
$tickets_par_usine = [];
foreach ($tickets as $ticket) {
    $usine = $ticket['nom_usine'];
    if (!isset($tickets_par_usine[$usine])) {
        $tickets_par_usine[$usine] = [
            'tickets' => [],
            'total_poids' => 0,
            'nombre_tickets' => 0
        ];
    }
    $tickets_par_usine[$usine]['tickets'][] = $ticket;
    $tickets_par_usine[$usine]['total_poids'] += $ticket['poids'];
    $tickets_par_usine[$usine]['nombre_tickets']++;
}

$grand_total_poids = 0;
$grand_total_tickets = 0;

foreach ($tickets_par_usine as $usine => $data) {
    // En-tête de l'usine
    $pdf->SetTextColor(0);
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Ln(2);
    $pdf->Cell(0, 8, utf8_decode($usine), 0, 1, 'C');
    $pdf->Ln(2);

    // En-têtes du tableau
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(230, 230, 230);
    $pdf->SetDrawColor(0);

    $w = array(35, 35, 40, 40, 35, 50, 40);
    
    $pdf->Cell($w[0], 8, utf8_decode('Date Réception'), 1, 0, 'C', true);
    $pdf->Cell($w[1], 8, 'Date Ticket', 1, 0, 'C', true);
    $pdf->Cell($w[2], 8, utf8_decode('N° Ticket'), 1, 0, 'C', true);
    $pdf->Cell($w[3], 8, utf8_decode('Véhicule'), 1, 0, 'C', true);
    $pdf->Cell($w[4], 8, 'Poids (kg)', 1, 0, 'C', true);
    $pdf->Cell($w[5], 8, 'Prix Unitaire', 1, 0, 'C', true);
    $pdf->Cell($w[6], 8, 'Agent', 1, 1, 'C', true);

    // Données
    $pdf->SetFont('Arial', '', 10);
    $fill = false;

    foreach ($data['tickets'] as $ticket) {
        $pdf->Cell($w[0], 7, date('d/m/Y', strtotime($ticket['created_at'])), 1, 0, 'C', $fill);
        $pdf->Cell($w[1], 7, date('d/m/Y', strtotime($ticket['date_ticket'])), 1, 0, 'C', $fill);
        $pdf->Cell($w[2], 7, $ticket['numero_ticket'], 1, 0, 'C', $fill);
        $pdf->Cell($w[3], 7, utf8_decode($ticket['matricule_vehicule']), 1, 0, 'C', $fill);
        $pdf->Cell($w[4], 7, number_format($ticket['poids'], 0, ',', ' '), 1, 0, 'R', $fill);
        $pdf->Cell($w[5], 7, number_format($ticket['prix_unitaire'], 0, ',', ' '), 1, 0, 'R', $fill);
        $pdf->Cell($w[6], 7, utf8_decode($ticket['nom_complet_agent']), 1, 1, 'L', $fill);
        $fill = !$fill;
    }

    // Sous-total pour l'usine
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(array_sum($w)-35, 8, 'Sous-total ' . utf8_decode($usine) . ' (' . $data['nombre_tickets'] . ' tickets)', 1, 0, 'R', true);
    $pdf->Cell(35, 8, number_format($data['total_poids'], 0, ',', ' '), 1, 1, 'R', true);
    
    $pdf->Ln(4);
    
    $grand_total_poids += $data['total_poids'];
    $grand_total_tickets += $data['nombre_tickets'];
}

// Total général
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(array_sum($w)-35, 8, utf8_decode('TOTAL GÉNÉRAL (' . $grand_total_tickets . ' tickets)'), 1, 0, 'R', true);
$pdf->Cell(35, 8, number_format($grand_total_poids, 0, ',', ' '), 1, 1, 'R', true);

// Signature
$pdf->Ln(15);
$pdf->SetTextColor(0);
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(0, 10, utf8_decode('Fait à Divo, le ' . date('d/m/Y')), 0, 1, 'R');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, 'UNIPALM COOP-CA', 0, 1, 'R');

// Génération du PDF
$file_name = 'Rapport_Tickets_' . date('d-m-Y') . '.pdf';

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $file_name . '"');
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

$pdf->Output('I', $file_name);

$conn = null;

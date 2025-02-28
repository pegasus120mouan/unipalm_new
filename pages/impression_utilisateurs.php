<?php
require('../fpdf/fpdf.php');
require_once '../inc/functions/connexion.php';

// Execute SQL query to fetch data
$sql = "SELECT * FROM utilisateurs";
$requete = $conn->prepare($sql);
$requete->execute();
$resultat = $requete->fetchAll(PDO::FETCH_ASSOC);

class PDF extends FPDF {
    // Page header
    function Header() {
        // Add logo
        $this->Image('../dist/img/logo.png', 10, 6, 30);
        
        // Right-aligned date
        $this->SetFont('Arial', 'I', 11);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 10, utf8_decode('Fait à Divo, le 31/12/2024'), 0, 1, 'R');
        
        // UNIPALM COOP-CA right aligned
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, 'UNIPALM COOP-CA', 0, 1, 'R');
        
        // Add spacing
        $this->Ln(30);
        
        // Document title
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(44, 62, 80);
        $this->Cell(0, 10, 'LISTE DES UTILISATEURS', 0, 1, 'C');
        
        // Line break
        $this->Ln(10);
    }

    // Page footer
    function Footer() {
        // Position at 2 cm from bottom
        $this->SetY(-35);
        
        // Draw a line
        $this->SetDrawColor(144, 238, 144); // Light green color
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        
        // Address and contact info
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(0, 128, 0); // Green color
        $this->Ln(5);
        $this->Cell(0, 5, utf8_decode('Siège Social : Divo Quartier millionnaire non loin de l\'hôtel Boya'), 0, 1, 'C');
        $this->Cell(0, 5, 'NGC : 2050R910 / TEL : (00225) 27 34 75 92 36 / 07 49 17 16 32', 0, 1, 'C');
    }
}

// Create PDF
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// Table headers
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(52, 152, 219);
$pdf->SetTextColor(255, 255, 255);

// Headers with proper width
$pdf->Cell(60, 10, 'Nom', 1, 0, 'C', true);
$pdf->Cell(60, 10, utf8_decode('Prénoms'), 1, 0, 'C', true);
$pdf->Cell(50, 10, 'Contact', 1, 1, 'C', true);

// Data
$pdf->SetFont('Arial', '', 11);
$pdf->SetTextColor(44, 62, 80);
$pdf->SetFillColor(245, 247, 250);

$alternate = false;
foreach ($resultat as $row) {
    $pdf->Cell(60, 10, utf8_decode($row['nom']), 1, 0, 'L', $alternate);
    $pdf->Cell(60, 10, utf8_decode($row['prenoms']), 1, 0, 'L', $alternate);
    $pdf->Cell(50, 10, $row['contact'], 1, 1, 'C', $alternate);
    $alternate = !$alternate;
}

// Output PDF
$pdf->Output('I', 'liste_utilisateurs.pdf');

// Close database connection
$conn = null;
?>

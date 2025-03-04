<?php
require('../fpdf/fpdf.php');
require_once '../inc/functions/connexion.php';

// Execute SQL query to fetch data
$sql = "SELECT nom, prenoms FROM chef_equipe";
$requete = $conn->prepare($sql);
$requete->execute();
$resultat = $requete->fetchAll(PDO::FETCH_ASSOC);

// Create PDF
$pdf = new FPDF();
$pdf->AddPage();

// Title
$pdf->SetFont('Arial', 'B', 15);
$pdf->Cell(0, 10, utf8_decode("Liste des chefs d'équipe"), 1, 1, 'C');
$pdf->Ln(7);

// Table headers
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(192); 
$pdf->Cell(60, 10, 'Nom', 1, 0, 'C', true); 
$pdf->Cell(60, 10, utf8_decode('Prénoms'), 1, 1, 'C', true); // Corrected line for 'Prénoms'
$pdf->SetFillColor(255);

// Data
$pdf->SetFont('Arial', '', 12);
foreach ($resultat as $row) {
    // Calculate the total width and center the data based on it
    $totalWidth = 120; // The sum of your two columns' widths
    $xPos = ($pdf->GetPageWidth() - $totalWidth) / 2; // Centered position

    // Position the X-coordinate for the current row, ensuring centered alignment
    $pdf->SetX($xPos);
    
    $pdf->Cell(60, 10, utf8_decode($row['nom']), 1, 0, 'C');  // Correct column for 'nom'
    $pdf->Cell(60, 10, utf8_decode($row['prenoms']), 1, 1, 'C'); // Correct column for 'prenoms'
}

// Output PDF with the specific file name
$file_name = 'liste_chefs_equipe.pdf'; // File name for the output
$pdf->Output('I', $file_name);

// Close database connection
$conn = null;
?>

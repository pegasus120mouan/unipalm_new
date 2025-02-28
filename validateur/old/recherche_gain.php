<?php
require('../fpdf/fpdf.php');
require_once '../inc/functions/connexion.php';

if (isset($_POST['date'])) {
    $date = $_POST['date'];
    $formatted_date = date("d-m-Y", strtotime($date));

    // Execute SQL query to fetch data
    $sql = "SELECT CONCAT(u.nom, ' ', u.prenoms) AS fullname, c.date_commande, SUM(c.cout_livraison) AS total_cout_livraison
            FROM utilisateurs u
            JOIN commandes c ON u.id = c.livreur_id
            WHERE c.date_commande = :date AND c.statut = 'Livré'
            GROUP BY fullname, c.date_commande
            ORDER BY c.date_commande DESC";
    $requete = $conn->prepare($sql);
    $requete->bindParam(':date', $date);
    $requete->execute();
    $resultat = $requete->fetchAll(PDO::FETCH_ASSOC);

    // Create PDF
    $pdf = new FPDF();
    $pdf->AddPage();

    // Title
    $pdf->SetFont('Arial', 'B', 15);
    $pdf->Cell(0, 10, utf8_decode('Point des totaux de livraison'), 1, 1, 'C');
    $pdf->Ln(7);

    // Client
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, "Date: $formatted_date", 0, 1, 'L');

    // Table headers
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(192);
    $pdf->Cell(60, 10, 'Livreur', 1, 0, 'C', true);
    $pdf->Cell(60, 10, 'Date livraison', 1, 0, 'C', true);
    $pdf->Cell(60, 10, 'Montant', 1, 1, 'C', true);
    $pdf->SetFillColor(255);

    // Data
    $pdf->SetFont('Arial', '', 12);
    $total = 0;
    foreach ($resultat as $row) {
        $total += $row['total_cout_livraison'];
        $pdf->Cell(60, 10, utf8_decode($row['fullname']), 1, 0, 'C');
        $pdf->Cell(60, 10, utf8_decode($row['date_commande']), 1, 0, 'C');
        $pdf->Cell(60, 10, $row['total_cout_livraison'], 1, 1, 'C');
    }

    // Total
    $pdf->SetFont('Arial', 'B', 20);
    $pdf->SetFillColor(0);
    $pdf->SetTextColor(255);
    $pdf->Cell(120, 10, 'Total', 1, 0, 'R', true);
    $pdf->Cell(60, 10, $total, 1, 1, 'C', true);

    // Output PDF
    $pdf->Output();

    // Close database connection
    $conn = null;
} else {
    echo "Veuillez sélectionner une date.";
}
?>

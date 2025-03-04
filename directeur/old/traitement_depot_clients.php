<?php
require('../fpdf/fpdf.php');
require_once '../inc/functions/connexion.php';

if (isset($_POST['client']) && isset($_POST['date_debut']) && isset($_POST['date_fin'])) {
    $client = $_POST['client'];
    
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];

    $date_debut_fr = date('d/m/Y', strtotime($date_debut));
    $date_fin_fr = date('d/m/Y', strtotime($date_fin));

    // Execute SQL query to fetch data
    $sql = "SELECT
        b.nom AS nom_boutique,
        c.date_commande AS date_commande,
        c.statut AS commande_statut,
        SUM(c.cout_reel) AS cout_reel_journalier
    FROM
        boutiques b
    JOIN
        utilisateurs u ON b.id = u.boutique_id
    JOIN
        commandes c ON u.id = c.utilisateur_id
    WHERE
        c.date_commande BETWEEN :dateDebut AND :dateFin  AND b.nom = :client AND c.statut = 'Livré'
    GROUP BY
        b.nom, c.date_commande
    ORDER BY
        b.nom, c.date_commande";

    $requete = $conn->prepare($sql);
    $requete->bindParam(':client', $client);
    $requete->bindParam(':dateDebut', $date_debut);
    $requete->bindParam(':dateFin', $date_fin);
    $requete->execute();
    $resultat = $requete->fetchAll(PDO::FETCH_ASSOC);

    // Create PDF
    $pdf = new FPDF();
    $pdf->AddPage();

    // Title
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, utf8_decode('Rapport des dépots effectués'), 1, 1, 'C');


    // Client and date range
    $pdf->SetFont('Arial', '', 12); // Set font to regular
    $pdf->Cell(0, 10, "Partenaire: $client", 0, 1, 'L'); // Regular text
    
    $pdf->SetFont('Arial', 'B', 12); // Set font to bold
    $pdf->Cell(20, 10, "Du: ", 0, 0, 'L'); // Bold text
    $pdf->SetFont('Arial', '', 12); // Reset font to regular
    $pdf->Cell(50, 10, "$date_debut_fr", 0, 0, 'L'); // Regular text
    $pdf->SetFont('Arial', 'B', 12); // Set font to bold
    $pdf->Cell(20, 10, "au: ", 0, 0, 'L'); // Bold text
    $pdf->SetFont('Arial', '', 12); // Reset font to regular
    $pdf->Cell(0, 10, "$date_fin_fr", 0, 1, 'L'); // Regular text

    // Table headers
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(192); // Gray color for background
    $pdf->Cell(60, 10, 'Boutique', 1, 0, 'C', true); // true indicates fill
    $pdf->Cell(60, 10, 'Date', 1, 0, 'C', true); // true indicates fill
    $pdf->Cell(60, 10, 'Montant', 1, 1, 'C', true); // true indicates fill
    $pdf->SetFillColor(255); // Reset fill color

    // Data
    $pdf->SetFont('Arial', '', 12);
    $total = 0;
    foreach ($resultat as $row) {
        $total += $row['cout_reel_journalier'];
        $pdf->Cell(60, 10, $row['nom_boutique'], 1, 0, 'C');
        $date_commande_fr = date('d-m-Y', strtotime($row['date_commande']));
        $pdf->Cell(60, 10, $date_commande_fr, 1, 0, 'C');
        $pdf->Cell(60, 10, $row['cout_reel_journalier'], 1, 1, 'C');
    }

    // Total
    

    $pdf->SetFont('Arial', 'B', 20);
    $pdf->SetFillColor(0); // Black background
    $pdf->SetTextColor(255); // White text
    $pdf->Cell(120, 10, 'Total', 1, 0, 'R', true); // true for filling cell
    $pdf->Cell(60, 10, $total, 1, 1, 'C', true); // true for filling cell

    // Output PDF
    $pdf->Output();
    
    // Close database connection
    $conn = null;
} else {
    echo "Veuillez sélectionner un client et une date.";
}
?>

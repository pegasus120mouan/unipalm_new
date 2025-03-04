<?php
require('../fpdf/fpdf.php');
require_once '../inc/functions/connexion.php';

if (isset($_POST['id_livreur']) && isset($_POST['start_date']) && isset($_POST['end_date'])) {
    $id_user = $_POST['id_livreur'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // Convert dates to French format
    $startDateObj = new DateTime($start_date);
    $endDateObj = new DateTime($end_date);

    $startDateFormatted = $startDateObj->format('d/m/Y');
    $endDateFormatted = $endDateObj->format('d/m/Y');

    // Exécuter la requête SQL pour récupérer les données
    $sql = "SELECT 
        c.date_commande AS date_jour,
        CONCAT(u.nom, ' ', u.prenoms) AS fullname_livreur,
        SUM(c.cout_global) AS total_cout_global,
        p.depense AS total_depense,
        SUM(c.cout_global) - (p.depense) AS montant_depot
    FROM
        commandes c
    JOIN
        utilisateurs u ON c.livreur_id = u.id
    LEFT JOIN
        points_livreurs p ON u.id = p.utilisateur_id 
                          AND p.date_commande = c.date_commande
    WHERE
        c.date_commande BETWEEN :start_date AND :end_date
        AND c.statut = 'Livré'
        AND u.id = :id_user
    GROUP BY
        c.date_commande,
        u.nom,
        u.prenoms
    ORDER BY
        c.date_commande,
        fullname_livreur";

    $requete = $conn->prepare($sql);
    $requete->bindParam(':id_user', $id_user);
    $requete->bindParam(':start_date', $start_date);
    $requete->bindParam(':end_date', $end_date);
    $requete->execute();
    $resultat = $requete->fetchAll(PDO::FETCH_ASSOC);

    // Créer un fichier PDF
    $pdf = new FPDF();
    $pdf->AddPage();

    // Titre
    $pdf->SetFont('Arial', 'B', 15);
    $pdf->Cell(0, 10, utf8_decode('Point des versements à effectuer'), 1, 1, 'C');
    $pdf->Ln(7);

    // Informations sur le livreur et la date
    $pdf->SetFont('Arial', 'B', 12); // Set font to Arial bold for the courier name
    $pdf->Cell(0, 10, "Coursier: " . (count($resultat) > 0 ? $resultat[0]['fullname_livreur'] : 'Non spécifié'), 0, 1, 'L');

    $pdf->SetFont('Arial', '', 12); // Set font to Arial normal for the dates
    $pdf->Cell(10, 10, utf8_decode("Du : "), 0, 0, 'L'); // Normal text
    $pdf->SetFont('Arial', 'B', 12); // Bold start date
    $pdf->Cell(25, 10, utf8_decode($startDateFormatted), 0, 0, 'L');
    $pdf->SetFont('Arial', '', 12); // Normal text
    $pdf->Cell(10, 10, utf8_decode(" Au : "), 0, 0, 'L');
    $pdf->SetFont('Arial', 'B', 12); // Bold end date
    $pdf->Cell(25, 10, utf8_decode($endDateFormatted), 0, 1, 'L');
    $pdf->SetFont('Arial', '', 12); // Reset to normal text for further usage
    $pdf->Ln(10);

    // En-tête du tableau
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(192); 
    $pdf->Cell(30, 10, 'Date', 1, 0, 'C', true); 
    $pdf->Cell(50, 10, 'Montant Global', 1, 0, 'C', true); 
    $pdf->Cell(50, 10, utf8_decode("Dépenses"), 1, 0, 'C', true); 
    $pdf->Cell(50, 10, utf8_decode("Montant à déposer"), 1, 0, 'C', true); 
    $pdf->SetFillColor(255);
    $pdf->Ln();

    // Données du tableau
    $pdf->SetFont('Arial', '', 12);
    $total_depot = 0;
    foreach ($resultat as $row) {
        $pdf->Cell(30, 10, (new DateTime($row['date_jour']))->format('d/m/Y'), 1, 0, 'C');
        $pdf->Cell(50, 10, number_format($row['total_cout_global'], 0, '', ' '), 1, 0, 'C');
        $pdf->Cell(50, 10, number_format($row['total_depense'], 0, '', ' '), 1, 0, 'C');
        $pdf->Cell(50, 10, number_format($row['montant_depot'], 0, '', ' '), 1, 0, 'C');
        $pdf->Ln();
        $total_depot += $row['montant_depot'];
    }

    // Total
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(173, 216, 230);
    $pdf->Cell(130, 10, 'Total', 1, 0, 'C', true);
    $pdf->Cell(50, 10, number_format($total_depot, 0, '', ' '), 1, 1, 'C', true);

    // Générer le fichier PDF
    $pdf->Output();

} else {
    echo "Veuillez sélectionner un livreur et une date.";
}
?>

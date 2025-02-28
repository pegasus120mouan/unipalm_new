<?php
require('../fpdf/fpdf.php');
require_once '../inc/functions/connexion.php';

if (isset($_POST['livreur_id']) && isset($_POST['date'])) {
    $id_user = $_POST['livreur_id'];
    $date = $_POST['date'];

    // Exécuter la requête SQL pour récupérer les données
    $sql = "SELECT
                c.id AS commande_id,
                c.communes AS communes,
                c.cout_global AS cout_global,
                c.cout_livraison AS cout_livraison,
                c.cout_reel AS cout_reel,
                c.statut AS statut,
                c.date_commande AS date_commande,
                concat(u.nom, ' ', u.prenoms) as fullname_livreur,
                b.nom as boutique_nom
            FROM
                commandes c
            JOIN
                livreurs u ON c.livreur_id = u.id
            JOIN
                clients cl ON c.utilisateur_id = cl.id
            JOIN
                boutiques b ON b.id = cl.boutique_id
            WHERE
                c.date_commande = :date
                AND u.id = :id_user";

    $requete = $conn->prepare($sql);
    $requete->bindParam(':id_user', $id_user);
    $requete->bindParam(':date', $date);
    $requete->execute();
    $resultat = $requete->fetchAll(PDO::FETCH_ASSOC);

    // Créer un fichier PDF
    $pdf = new FPDF();
    $pdf->AddPage();

    // Titre
    $pdf->SetFont('Arial', 'B', 15);
    $pdf->Cell(0, 10, utf8_decode('Point des colis reçus'), 1, 1, 'C');
    $pdf->Ln(7);

    // Informations sur le livreur et la date
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, "Coursier: " . $resultat[0]['fullname_livreur'], 0, 1, 'L');
    $pdf->Cell(0, 10, "Date: $date", 0, 1, 'L');

    // En-tête du tableau
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(192); 
    $pdf->Cell(50, 10, 'Communes', 1, 0, 'C', true); 
    $pdf->Cell(50, 10, 'Montant', 1, 0, 'C', true); 
    $pdf->Cell(50, 10, 'Statut', 1, 0, 'C', true); 
    $pdf->Cell(40, 10, 'Boutique', 1, 1, 'C', true); 
    $pdf->SetFillColor(255);

    // Données du tableau
    $pdf->SetFont('Arial', '', 12);
    $total = 0;
    foreach ($resultat as $row) {
       if ($row['statut'] == 'Livré') {
        $total += $row['cout_global'];
        $pdf->Cell(50, 10, utf8_decode($row['communes']), 1, 0, 'C');
        $pdf->Cell(50, 10, $row['cout_global'], 1, 0, 'C');
        $pdf->Cell(50, 10, utf8_decode($row['statut']), 1, 0, 'C');
        $pdf->Cell(40, 10, utf8_decode($row['boutique_nom']), 1, 1, 'C');
        
        
       } else {
        $pdf->SetFillColor(255, 0, 0); // Rouge
        $pdf->SetTextColor(255, 0, 0); // Blanc pour le texte
        $pdf->Cell(50, 10, utf8_decode($row['communes']), 1, 0, 'C');
        $pdf->Cell(50, 10, $row['cout_global'], 1, 0, 'C');
        $pdf->Cell(50, 10, utf8_decode($row['statut']), 1, 0, 'C');
        $pdf->Cell(40, 10, utf8_decode($row['boutique_nom']), 1, 1, 'C');
       }
       $pdf->SetTextColor(0);
    }

    // Total
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(173, 216, 230);
    $pdf->Cell(150, 10, 'Total', 1, 0, 'C', true);
    $pdf->Cell(40, 10, $total, 1, 1, 'C', true);

    // Générer le fichier PDF
    $pdf->Output();

} else {
    echo "Veuillez sélectionner un livreur et une date.";
}
?>

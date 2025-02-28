<?php
require('../fpdf/fpdf.php');
require_once '../inc/functions/connexion.php';

if (isset($_POST['client']) && isset($_POST['date'])) {
    $client = $_POST['client'];
    $date = $_POST['date'];
    $formatted_date = date("d-m-Y", strtotime($date));

    // Requête SQL
    $sql = "SELECT 
    commandes.id AS commande_id,
    commandes.communes AS commande_communes,
    commandes.cout_global AS commande_cout_global, 
    commandes.cout_livraison AS commande_cout_livraison,
    commandes.cout_reel AS commande_cout_reel,
    commandes.statut AS commande_statut,
    commandes.date_commande AS date_commande,
    CONCAT(livreurs.nom, ' ', livreurs.prenoms) AS nom_livreur,
    clients.nom AS nom_client,
    boutiques.nom AS nom_boutique
FROM 
    commandes
JOIN 
    livreurs ON livreurs.id = commandes.livreur_id
JOIN 
    clients ON clients.id = commandes.utilisateur_id
JOIN 
    boutiques ON boutiques.id = clients.boutique_id
WHERE 
     boutiques.nom = :client AND commandes.date_commande=:date";
    
    $requete = $conn->prepare($sql);
    $requete->bindParam(':client', $client);
    $requete->bindParam(':date', $date);
    $requete->execute();
    $resultat = $requete->fetchAll(PDO::FETCH_ASSOC);

    // Création du PDF
    $pdf = new FPDF();
    $pdf->AddPage();

    // Titre
    $pdf->SetFont('Arial', 'B', 15);
    $pdf->Cell(0, 10, utf8_decode('Point des colis donnés'), 1, 1, 'C');
    $pdf->Ln(7);

    // Informations sur le client et la date
    $pdf->SetFont('Arial', 'BU', 12);
    $pdf->Cell(0, 10, "Partenaire: " . $client, 0, 1, 'L');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, "Date: $formatted_date", 0, 1, 'L');

    // En-têtes de table
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(192); 
    $pdf->Cell(35, 10, 'Communes', 1, 0, 'C', true); 
    $pdf->Cell(35, 10, 'Montant Global', 1, 0, 'C', true); 
    $pdf->Cell(35, 10, utf8_decode('Montant Réel'), 1, 0, 'C', true); 
    $pdf->Cell(35, 10, utf8_decode('Coursier'), 1, 0, 'C', true); 
    $pdf->Cell(35, 10, 'Statut', 1, 1, 'C', true); 
    $pdf->SetFillColor(255);

    // Données
    $pdf->SetFont('Helvetica', '', 9);
    foreach ($resultat as $row) {
        if ($row['commande_statut'] != 'Livré') {
            $pdf->SetFillColor(255, 0, 0); // Rouge pour les non-livrés
            $pdf->SetTextColor(255, 0, 0); // Texte rouge
        } else {
            $pdf->SetFillColor(255); // Blanc pour les livrés
            $pdf->SetTextColor(0); // Texte noir
        }
        $pdf->Cell(35, 10, utf8_decode($row['commande_communes']), 1, 0, 'C');
        $pdf->Cell(35, 10, $row['commande_cout_global'], 1, 0, 'C');
        $pdf->Cell(35, 10, $row['commande_cout_reel'], 1, 0, 'C');
        $pdf->Cell(35, 10, $row['nom_livreur'], 1, 0, 'C');
        $pdf->Cell(35, 10, utf8_decode($row['commande_statut']), 1, 1, 'C');
    }

    // Génération du nom de fichier
    $formatted_date_for_filename = date("d-m-Y", strtotime($date));
    $file_name = 'Point_du_' . $formatted_date_for_filename . '_de_' . $client . '.pdf';

    // Envoi du PDF
    $pdf->Output('I', $file_name);

    // Fermeture de la connexion
    $conn = null;
} else {
    echo "Veuillez sélectionner un client et une date.";
}
?>

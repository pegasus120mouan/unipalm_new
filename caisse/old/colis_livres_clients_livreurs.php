<?php
// Inclure les bibliothèques FPDF et la connexion à la base de données
require('../fpdf/fpdf.php'); 
require_once '../inc/functions/connexion.php';

// Récupérer l'ID de l'utilisateur et la date actuelle
$id_user = $_GET['id'];
$date = date('Y-m-d');

try {
    // Étape 2 : Exécuter la requête SQL pour récupérer les données de commande
    $sql = "
        SELECT
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
            AND u.id = :id_user  
            AND c.statut LIKE 'Livr%'";

    $requete = $conn->prepare($sql);
    $requete->bindParam(':id_user', $id_user);
    $requete->bindParam(':date', $date);
    $requete->execute();
    $resultat = $requete->fetchAll(PDO::FETCH_ASSOC);

    // Vérifier si des résultats ont été trouvés
    if (empty($resultat)) {
        throw new Exception('Aucune commande trouvée pour cet utilisateur à cette date.');
    }

    // Étape 3 : Créer un fichier PDF
    $pdf = new FPDF();
    $pdf->AddPage();

    // Définir la police et la taille de la police
    $pdf->SetFont('Arial', 'I', 14);

    // Ajouter un titre
    $pdf->SetY(55);
    $pdf->SetX(10);
    $pdf->SetFont('Helvetica', 'B', 12);
    $pdf->Cell(50, 10, "Point des colis ", 0, 1);
    $pdf->SetFont('Helvetica', '', 12);
    $pdf->Cell(50, 7, $resultat[0]['fullname_livreur'], 0, 1);
    $pdf->Cell(50, 7, "$date", 0, 1);
    $pdf->Ln(7);

    // Ajouter les données dans le PDF
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetFillColor(200, 200, 200);
    $pdf->Cell(50, 5, "Communes", 1, 0, 'C', 1);
    $pdf->Cell(50, 5, "Montant", 1, 0, 'C', 1);
    $pdf->Cell(50, 5, "Statut", 1, 0, 'C', 1);
    $pdf->Cell(30, 5, "Boutique", 1, 1, 'C', 1);

    // Calculer le total et remplir le tableau
 $total = 0;
foreach ($resultat as $row) {
    $total += $row['cout_global'];
    $pdf->Cell(50, 5, utf8_decode($row['communes']), 1);
    $pdf->Cell(50, 5, $row['cout_global'], 1);
    $pdf->Cell(50, 5, utf8_decode($row['statut']), 1);
    $pdf->Cell(30, 5, utf8_decode($row['boutique_nom']), 1);
    $pdf->Ln();
}

    // Ajouter le total
    $pdf->SetFillColor(173, 216, 230);
    $pdf->Cell(50, 10, "Total", 1, 0, 'C', 1);
    $pdf->Cell(130, 10, $total, 1, 0, 'C', 1);

    // Étape 4 : Générer le fichier PDF
    $pdf->Output();

} catch (Exception $e) {
    // Gérer les erreurs en affichant un message ou en enregistrant dans un journal d'erreurs
    echo "Erreur : " . $e->getMessage();
}

// Étape 5 : Fermer la connexion à la base de données
$conn = null;
?>

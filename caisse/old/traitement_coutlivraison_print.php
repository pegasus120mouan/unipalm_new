<?php
require('../fpdf/fpdf.php'); 
require_once '../inc/functions/connexion.php';   

//if (isset($_POST['communes'])) {
   // $id_communes = $_POST['communes'];

   $id_communes = $_GET['id'];

    try {
        // Étape 2 : Exécuter la requête SQL pour récupérer les données des communes et des prix correspondants
        $sql = "SELECT c.nom_commune, p.montant, z.nom_zone
        FROM prix p
        JOIN communes c ON p.commune_id = c.commune_id
        JOIN zones z ON p.zone_id = z.zone_id
        WHERE p.zone_id = :id_communes";
        $requete = $conn->prepare($sql);
        $requete->bindParam(':id_communes', $id_communes, PDO::PARAM_INT);
        $requete->execute();
        $resultat = $requete->fetchAll(PDO::FETCH_ASSOC);
        
        // Étape 3 : Créer un fichier PDF
        $pdf = new FPDF();
        $pdf->AddPage();
        
       // $pdf->SetFont('Arial', 'I', 14);
        $pdf-> SetFont('Arial', 'B', 20, 'UTF-8');
        $pdf->SetFillColor(247, 200, 0);

        // Titre du document PDF
        $pdf->Cell(0, 10, utf8_decode("Les coûts des livraisons"), 'LTRB', 1, 'C',true);

        $pdf->Ln(); // Passer à la ligne suivante

        $pdf->SetFont('Arial', '', 12); // Définit la police et la taille de la police
$pdf->SetFillColor(192, 192, 192); // Gris clair
$pdf->MultiCell(0, 8, utf8_decode("Type de Livraisons : Programmées"), 0, 'C', true); // La bordure est définie sur 0 pour supprimer les bordures
$pdf->Ln(); // Saut de ligne après le titre
 
        $pdf-> SetFont('Arial', 'I', 14, 'UTF-8');
        // En-têtes des colonnes
    
        $pdf->SetFillColor(192, 192, 192); // Gris clair
$pdf->Cell(70, 10, utf8_decode("Commune de récuperation"), 1, 0, 'C', true); // Le dernier paramètre true indique de remplir la cellule avec la couleur définie
$pdf->SetFillColor(192, 192, 192); // Gris clair
$pdf->Cell(70, 10, "Commune de destination", 1, 0, 'C', true);
$pdf->SetFillColor(192, 192, 192); // Gris clair
$pdf->Cell(50, 10, utf8_decode("Coût de livraison"), 1, 1, 'C', true);

        // Affichage des données
        foreach ($resultat as $row) {
            $pdf->Cell(70,10, utf8_decode($row['nom_zone']),1,0);
            $pdf->Cell(70,10, utf8_decode($row['nom_commune']),1,0);
            $pdf->Cell(50,10,$row['montant'],1,1);
        }

        // Étape 4 : Générer le fichier PDF
        $pdf->Output();

    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }

    // Étape 5 : Fermer la connexion à la base de données
    $conn = null;
  
//} else {
 //   echo "Veuillez sélectionner une commune.";
//}
?>

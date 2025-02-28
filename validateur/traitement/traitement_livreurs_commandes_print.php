<?php
require('../fpdf/fpdf.php');
require_once '../../inc/functions/connexion.php';

if (isset($_POST['client']) && isset($_POST['date'])) {

    $id_user = $_POST['client'];
    $date = $_POST['date'];

    // $id_user=$_SESSION['user_id'];
    
    // Étape 1 : Etablir la connexion à la base de données
 //   $serveur = "localhost";
 //   $nomUtilisateur = "root";
 //   $motDePasse = "";
  //  $nomBaseDeDonnees = "db_ovl";

   // try {
   //     $connexion = new PDO("mysql:host=$serveur;dbname=$nomBaseDeDonnees", $nomUtilisateur, $motDePasse);
   //     $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Étape 2 : Exécuter la requête SQL pour récupérer les données du pays et de la date sélectionnée
        $sql =
            "SELECT
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
        join clients cl on c.utilisateur_id=cl.id
        join boutiques b on b.id=cl.boutique_id
        WHERE
            c.date_commande = :date
            AND u.id = :id_user  
            AND c.statut like 'Livr%'";
            
        $requete = $conn->prepare($sql);
        $requete->bindParam(':id_user', $id_user);
        $requete->bindParam(':date', $date);
        $requete->execute();
        $resultat = $requete->fetchAll(PDO::FETCH_ASSOC);

        //var_dump($resultat); die;

        //$montantClient = $connexion->prepare('SELECT SUM(cout_reel) AS reel_somme FROM commandes where client="Uniko Perfume"');
        //$montantClient->execute();

        //$row = $montantClient->fetch(PDO::FETCH_ASSOC);
        //$sum = $row['reel_somme'];

        // Étape 3 : Créez un fichier PDF
        $pdf = new FPDF();
        $pdf->AddPage();

        // Définissez la police et la taille de la police
        $pdf->SetFont('Arial', 'I', 14);

        // Ajoutez un titre
        $pdf->SetY(55);
        $pdf->SetX(10);
        $pdf->SetFont('Helvetica', 'B', 12);
        //$pdf->Cell(50, 10, str_repeat('_', strlen('Point des colis')), 0, 1);
        $pdf->Cell(50, 10, "Point des colis ", 0, 1);
        // $pdf->Cell(50, 2, str_repeat('_', strlen('Point des colis')), 0, 1);
        $pdf->SetFont('Helvetica', '', 12);
        $pdf->Cell(50, 7, $resultat[0]['fullname_livreur'], 0, 1);
        $pdf->SetFont('Helvetica', '', 12);
        $pdf->Cell(50, 7, "$date", 0, 1);
        $pdf->Ln(7);
        // $pdf->Cell(50,7,$info["address"],0,1);
        // $pdf->Cell(50,7,$info["city"],0,1);





        // $pdf->Cell(0, 10, "Point de $client", 'TB', 1, 'C');
        // $pdf->Cell(0, 10, "Date $date", 0, 1, "C");
        // $pdf->Cell(0, 10, "Point de : $client et la date du : $date", 0, 1, 'C');

        // Ajoutez les données dans le PDF
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetFillColor(200, 200, 200);
        $pdf->Cell(50, 5, "Communes", 1, 0, 'C', 1);
        $pdf->Cell(50, 5, "Montant", 1, 0, 'C', 1);
        $pdf->Cell(50, 5, "Statut", 1, 0, 'C', 1);
        $pdf->Cell(30, 5, "Boutique", 1, 1, 'C', 1);


        $total = 0;
        foreach ($resultat as $row) {
            $total = $total + $row['cout_global'];
            $pdf->Cell(50, 5, $row['communes'], 1);
            $pdf->Cell(50, 5, $row['cout_global'], 1);
            $pdf->Cell(50, 5, utf8_decode($row['statut']), 1);
            $pdf->Cell(30, 5, $row['boutique_nom'], 1);
            $pdf->Ln();
        }

        $pdf->SetFillColor(173, 216, 230);
        $pdf->Cell(50, 10, "Total", 1, 0, 'C', 1);
        $pdf->SetFillColor(173, 216, 230);
        $pdf->SetFont('Helvetica', '', 25);
        $pdf->Cell(130, 10, $total, 1, 0, 'C', 1);

        // Étape 4 : Générez le fichier PDF
        $pdf->Output();

        // Étape 5 : Fermer la connexion à la base de données
        $conn = null;
} else {
    echo "Veuillez sélectionner un client et une date.";
}
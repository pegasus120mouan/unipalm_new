<?php
require('../fpdf/fpdf.php'); 
//include('header.php'); // Assurez-vous que le chemin vers le fichier FPDF est correct

if (isset($_POST['client']) && isset($_POST['date'])) {
    $client = $_POST['client'];
    $date = $_POST['date'];

    // Étape 1 : Etablir la connexion à la base de données
    $serveur = "localhost";
    $nomUtilisateur = "root";
    $motDePasse = "";
    $nomBaseDeDonnees = "db_ovl";

    try {
        $connexion = new PDO("mysql:host=$serveur;dbname=$nomBaseDeDonnees", $nomUtilisateur, $motDePasse);
        $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Étape 2 : Exécuter la requête SQL pour récupérer les données du pays et de la date sélectionnée
        $sql = 
        "SELECT 
commandes.id as commande_id,
utilisateur_id, livreur_id, communes, cout_global,
cout_livraison, cout_reel, statut, date_commande, clients.id as id_client,
clients.nom as client_nom, prenoms, contact, login, avatar, boutique_id, boutiques.nom as boutique_nom

FROM `commandes`  
join (select * from utilisateurs where role = 'clients')  as clients on clients.id=commandes.utilisateur_id
join boutiques on clients.boutique_id=boutiques.id having boutique_nom=:client and date_commande=:date and statut like 'Livr%' ";
        $requete = $connexion->prepare($sql);
        $requete->bindParam(':client', $client);
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
        $pdf->SetFont('Helvetica','B',12);
        //$pdf->Cell(50, 10, str_repeat('_', strlen('Point des colis')), 0, 1);
        $pdf->Cell(50,10,"Point des colis ",0,1);
       // $pdf->Cell(50, 2, str_repeat('_', strlen('Point des colis')), 0, 1);
        $pdf->SetFont('Helvetica','',12);
        $pdf->Cell(50,7,"$client",0,1);
        $pdf->SetFont('Helvetica','',12);
        $pdf->Cell(50,7,"$date",0,1);
        $pdf->Ln(7);
       // $pdf->Cell(50,7,$info["address"],0,1);
       // $pdf->Cell(50,7,$info["city"],0,1);





       // $pdf->Cell(0, 10, "Point de $client", 'TB', 1, 'C');
       // $pdf->Cell(0, 10, "Date $date", 0, 1, "C");
       // $pdf->Cell(0, 10, "Point de : $client et la date du : $date", 0, 1, 'C');

        // Ajoutez les données dans le PDF
        $pdf->SetFont('Helvetica', '', 12);
        $pdf->SetFillColor(200, 200, 200);
        $pdf->Cell(50, 10, "Communes", 1, 0, 'C', 1);
        $pdf->Cell(50, 10, "Montant", 1, 0, 'C', 1);
        $pdf->Cell(50, 10, "Statut", 1, 1, 'C', 1);


        $total = 0;
        foreach ($resultat as $row) {
            $total = $total + $row['cout_reel'];
            $pdf->Cell(50, 10, $row['communes'], 1);
            $pdf->Cell(50, 10, $row['cout_reel'], 1);
            $pdf->Cell(50, 10, $row['statut'], 1);
            $pdf->Ln();
        }
        
        $pdf->SetFillColor(173, 216, 230);
        $pdf->Cell(50, 10, "Total", 1, 0, 'C', 1);
        $pdf->SetFillColor(173, 216, 230);
        $pdf->SetFont('Helvetica', '', 25);
        $pdf->Cell(100, 10, $total  , 1,0,'C',1);

        // Étape 4 : Générez le fichier PDF
        $pdf->Output();

        // Étape 5 : Fermer la connexion à la base de données
        $connexion = null;
    } catch (PDOException $e) {
        echo "Erreur de connexion à la base de données : " . $e->getMessage();
    }
} else {
    echo "Veuillez sélectionner un client et une date.";
}
?>

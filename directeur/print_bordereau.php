<?php
// Ajout de la gestion d'erreurs au début du fichier
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Définition du chemin racine
$root_path = dirname(dirname(__FILE__));

require($root_path . '/fpdf/fpdf.php');
require_once $root_path . '/inc/functions/connexion.php';

if (isset($_POST['id_agent']) && isset($_POST['date_debut']) && isset($_POST['date_fin'])) {
    $id_agent = $_POST['id_agent'];
    $date_debut = $_POST['date_debut'] . ' 00:00:00';
    $date_fin = $_POST['date_fin'] . ' 23:59:59';

    $sql = "SELECT 
        t.*,
        u.nom_usine,
        v.matricule_vehicule,
        v.type_vehicule,
        CONCAT(COALESCE(a.nom, ''), ' ', COALESCE(a.prenom, '')) AS nom_complet_agent,
        DATE(t.date_ticket) as date_ticket_only,
        DATE(t.created_at) as date_reception
    FROM tickets t
    INNER JOIN usines u ON t.id_usine = u.id_usine
    INNER JOIN vehicules v ON t.vehicule_id = v.vehicules_id
    INNER JOIN agents a ON t.id_agent = a.id_agent
    WHERE 1=1
        AND t.id_agent = :id_agent 
        AND t.date_ticket BETWEEN :date_debut AND :date_fin
        AND t.status = 'validé'
    ORDER BY 
        u.nom_usine ASC,
        t.date_ticket ASC,
        t.created_at ASC";

    $requete = $conn->prepare($sql);
    $requete->bindParam(':id_agent', $id_agent);
    $requete->bindParam(':date_debut', $date_debut);
    $requete->bindParam(':date_fin', $date_fin);
    $requete->execute();
    $resultat = $requete->fetchAll(PDO::FETCH_ASSOC);

    $sql = "SELECT t.*, u.nom_usine, v.matricule_vehicule, 
            CONCAT(COALESCE(a.nom, ''), ' ', COALESCE(a.prenom, '')) AS nom_complet_agent
            FROM tickets t
            INNER JOIN usines u ON t.id_usine = u.id_usine
            INNER JOIN vehicules v ON t.vehicule_id = v.vehicules_id
            INNER JOIN agents a ON t.id_agent = a.id_agent
            WHERE t.id_agent = :id_agent
            AND t.created_at BETWEEN :date_debut AND :date_fin
            ORDER BY u.nom_usine, t.created_at;";

    $requete = $conn->prepare($sql);
    $requete->bindParam(':id_agent', $id_agent);
    $requete->bindParam(':date_debut', $date_debut);
    $requete->bindParam(':date_fin', $date_fin);
    $requete->execute();
    $resultat = $requete->fetchAll(PDO::FETCH_ASSOC);
    class PDF extends FPDF {
        function Header() {
            $logo_path = dirname(dirname(__FILE__)) . '/dist/img/logo.png';
            if (file_exists($logo_path)) {
                $this->Image($logo_path, 10, 10, 30);
            } else {
                // Log l'erreur si le logo n'est pas trouvé
                error_log("Logo non trouvé: " . $logo_path);
            }
            
            // Titre de l'entreprise en vert
            $this->SetTextColor(0, 128, 0);
            $this->SetFont('Arial', 'B', 16);
            $this->Cell(0, 10, 'UNIPALM COOP - CA', 0, 1, 'C');
            
            // Sous-titre en vert clair
            $this->SetTextColor(144, 238, 144);
            $this->SetFont('Arial', '', 11);
            $this->Cell(0, 5, utf8_decode('Société Coopérative Agricole Unie pour le Palmier'), 0, 1, 'C');
            
            $this->Ln(15);
        }

        function Footer() {
            $this->SetY(-20);
            
            // Ligne verte
            $this->SetDrawColor(144, 238, 144);
            $this->Line(10, $this->GetY(), 200, $this->GetY());
            
            // Texte en vert clair
            $this->SetTextColor(144, 238, 144);
            $this->SetFont('Arial', '', 8);
            $this->Cell(0, 5, utf8_decode('Siège Social : Divo Quartier millionnaire non loin de l\'hôtel Boya'), 0, 1, 'C');
            $this->Cell(0, 5, 'NCC : 2050R910 / TEL : (00225) 27 34 75 92 36 / 07 49 17 16 32', 0, 1, 'C');
        }
    }

    $pdf = new PDF();
    $pdf->AddPage();
    $pdf->SetAutoPageBreak(true, 35);

    // Titre du document
    $pdf->SetFont('Arial', 'BU', 16); 
    $pdf->SetTextColor(0); 
    $pdf->Cell(0, 12, utf8_decode('BORDEREAU DE DÉCHARGEMENT'), 0, 1, 'C', false);
    $pdf->Ln(5);

    // Informations du chargeur
    $pdf->SetTextColor(0);
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(50, 8, 'CHARGE DE MISSION:', 0, 0);
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(0, 8, utf8_decode(strtoupper($resultat[0]['nom_complet_agent'])), 0, 1);

    // Période
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(50, 8, utf8_decode('Période du:'), 0, 0);
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(0, 8, date('d/m/y', strtotime($date_debut)) . ' au ' . date('d/m/y', strtotime($date_fin)), 0, 1);
    $pdf->Ln(5);

    // Regrouper les tickets par usine
    $tickets_par_usine = [];
    foreach ($resultat as $row) {
        $usine = $row['nom_usine'];
        if (!isset($tickets_par_usine[$usine])) {
            $tickets_par_usine[$usine] = [
                'tickets' => [],
                'total_poids' => 0,
                'nombre_tickets' => 0
            ];
        }
        $tickets_par_usine[$usine]['tickets'][] = $row;
        $tickets_par_usine[$usine]['total_poids'] += $row['poids'];
        $tickets_par_usine[$usine]['nombre_tickets']++;
    }

    $grand_total_poids = 0;
    $grand_total_tickets = 0;

    foreach ($tickets_par_usine as $usine => $data) {
        // En-tête de l'usine
        $pdf->SetTextColor(0);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Ln(2);
        $pdf->Cell(0, 8, utf8_decode($usine), 0, 1, 'C');
        $pdf->Ln(2);

        // En-têtes du tableau
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->SetDrawColor(0);

        $w = array(35, 35, 40, 40, 40);
        
        $pdf->Cell($w[0], 8, utf8_decode('Date Réception'), 1, 0, 'C', true);
        $pdf->Cell($w[1], 8, 'Date Ticket', 1, 0, 'C', true);
        $pdf->Cell($w[2], 8, utf8_decode('Véhicule'), 1, 0, 'C', true);
        $pdf->Cell($w[3], 8, utf8_decode('N° Ticket'), 1, 0, 'C', true);
        $pdf->Cell($w[4], 8, 'Poids (kg)', 1, 1, 'C', true);

        // Données
        $pdf->SetFont('Arial', '', 10);
        $fill = true;

        foreach ($data['tickets'] as $ticket) {
            $pdf->Cell($w[0], 7, date('d/m/y', strtotime($ticket['created_at'])), 1, 0, 'C', $fill);
            $pdf->Cell($w[1], 7, date('d/m/y', strtotime($ticket['date_ticket'])), 1, 0, 'C', $fill);
            $pdf->Cell($w[2], 7, utf8_decode($ticket['matricule_vehicule']), 1, 0, 'C', $fill);
            $pdf->Cell($w[3], 7, $ticket['numero_ticket'], 1, 0, 'C', $fill);
            $pdf->Cell($w[4], 7, number_format($ticket['poids'], 0, ',', ' '), 1, 1, 'R', $fill);
        }

        // Sous-total pour l'usine
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(array_sum($w)-40, 8, 'Sous-total ' . utf8_decode($usine) . ' (' . $data['nombre_tickets'] . ' tickets)', 1, 0, 'R', true);
        $pdf->Cell(40, 8, number_format($data['total_poids'], 0, ',', ' '), 1, 1, 'R', true);
        
        $pdf->Ln(4);
        
        $grand_total_poids += $data['total_poids'];
        $grand_total_tickets += $data['nombre_tickets'];
    }

    // Total général
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(array_sum($w)-40, 8, utf8_decode('TOTAL GÉNÉRAL (' . $grand_total_tickets . ' tickets)'), 1, 0, 'R', true);
    $pdf->Cell(40, 8, number_format($grand_total_poids, 0, ',', ' '), 1, 1, 'R', true);

    // Signature
    $pdf->Ln(15);
    $pdf->SetTextColor(0);
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 10, utf8_decode('Fait à Divo, le ' . date('d/m/y')), 0, 1, 'R');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 10, 'UNIPALM COOP-CA', 0, 1, 'R');

    // Génération du PDF
    $file_name = 'Bordereau_' . date('d-m-Y', strtotime($date_debut)) . '_' . $resultat[0]['nom_complet_agent'] . '.pdf';
    
    // Forcer l'ouverture dans une nouvelle fenêtre
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . $file_name . '"');
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    
    $pdf->Output('I', $file_name);

    $conn = null;
} else {
    echo "Veuillez sélectionner un agent et une période.";
}
?>
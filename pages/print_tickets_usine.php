<?php
// Ajout de la gestion d'erreurs au début du fichier
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Définition du chemin racine
$root_path = dirname(dirname(__FILE__));

require($root_path . '/fpdf/fpdf.php');
require_once $root_path . '/inc/functions/connexion.php';

if (isset($_POST['id_usine']) && isset($_POST['date_debut']) && isset($_POST['date_fin'])) {
    $id_usine = $_POST['id_usine'];
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
    WHERE t.id_usine = :id_usine 
        AND t.date_ticket BETWEEN :date_debut AND :date_fin
        AND t.date_validation_boss IS NOT NULL
    ORDER BY t.date_ticket ASC, t.created_at ASC";

    $requete = $conn->prepare($sql);
    $requete->bindParam(':id_usine', $id_usine);
    $requete->bindParam(':date_debut', $date_debut);
    $requete->bindParam(':date_fin', $date_fin);
    $requete->execute();
    $tickets = $requete->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($tickets)) {
        class PDF extends FPDF {
            function Header() {
                $this->SetFont('Arial', 'B', 15);
                $this->Cell(0, 10, 'Liste des Tickets par Usine', 0, 1, 'C');
                $this->Ln(10);
            }

            function Footer() {
                $this->SetY(-15);
                $this->SetFont('Arial', 'I', 8);
                $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
            }
        }

        $pdf = new PDF();
        $pdf->AliasNbPages();
        $pdf->AddPage('L'); // Landscape orientation
        $pdf->SetFont('Arial', 'B', 10);

        // En-têtes
        $pdf->Cell(30, 7, 'Date', 1);
        $pdf->Cell(40, 7, 'Numero Ticket', 1);
        $pdf->Cell(50, 7, 'Usine', 1);
        $pdf->Cell(40, 7, 'Vehicule', 1);
        $pdf->Cell(30, 7, 'Poids', 1);
        $pdf->Cell(50, 7, 'Agent', 1);
        $pdf->Cell(30, 7, 'Status', 1);
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 10);
        foreach ($tickets as $ticket) {
            $pdf->Cell(30, 6, date('d/m/Y', strtotime($ticket['date_ticket'])), 1);
            $pdf->Cell(40, 6, $ticket['numero_ticket'], 1);
            $pdf->Cell(50, 6, utf8_decode($ticket['nom_usine']), 1);
            $pdf->Cell(40, 6, $ticket['matricule_vehicule'], 1);
            $pdf->Cell(30, 6, $ticket['poids'] . ' kg', 1);
            $pdf->Cell(50, 6, utf8_decode($ticket['nom_complet_agent']), 1);
            $pdf->Cell(30, 6, $ticket['date_validation_boss'] ? 'Validé' : 'En attente', 1);
            $pdf->Ln();
        }

        $pdf->Output();
    } else {
        echo "Aucun ticket trouvé pour cette période.";
    }
} else {
    echo "Paramètres manquants.";
}
?>

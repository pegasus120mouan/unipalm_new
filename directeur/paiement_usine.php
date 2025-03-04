<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_usines.php';
include('header.php');

if (isset($_GET['id'])) {
    $id_usine = $_GET['id'];
    
    // Récupérer les informations de l'usine
    $sql = "SELECT * FROM usines WHERE id_usine = :id_usine";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id_usine' => $id_usine]);
    $usine = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usine) {
        // Si le formulaire est soumis
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $montant = $_POST['montant'];
            $date_paiement = $_POST['date_paiement'];
            $description = $_POST['description'];

            try {
                // Insérer le paiement dans la table paiements_usines
                $sql = "INSERT INTO paiements_usines (id_usine, montant, date_paiement, description) 
                        VALUES (:id_usine, :montant, :date_paiement, :description)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':id_usine' => $id_usine,
                    ':montant' => $montant,
                    ':date_paiement' => $date_paiement,
                    ':description' => $description
                ]);

                // Mettre à jour le montant payé dans la table usines
                $sql = "UPDATE usines SET montant_paye = montant_paye + :montant, 
                        montant_restant = montant_restant - :montant 
                        WHERE id_usine = :id_usine";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':montant' => $montant,
                    ':id_usine' => $id_usine
                ]);

                $_SESSION['success'] = "Le paiement a été enregistré avec succès.";
                header("Location: gestion_usines.php");
                exit();
            } catch(PDOException $e) {
                $_SESSION['error'] = "Erreur lors de l'enregistrement du paiement: " . $e->getMessage();
            }
        }
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h3 class="card-title">Effectuer un paiement - <?= htmlspecialchars($usine['nom_usine']) ?></h3>
                </div>
                <div class="card-body">
                    <form action="" method="POST">
                        <div class="form-group">
                            <label for="montant">Montant du paiement (FCFA)</label>
                            <input type="number" class="form-control" id="montant" name="montant" required>
                        </div>
                        <div class="form-group">
                            <label for="date_paiement">Date du paiement</label>
                            <input type="date" class="form-control" id="date_paiement" name="date_paiement" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Enregistrer le paiement
                            </button>
                            <a href="gestion_usines.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Retour
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
    } else {
        $_SESSION['error'] = "Usine non trouvée.";
        header("Location: gestion_usines.php");
        exit();
    }
} else {
    $_SESSION['error'] = "ID de l'usine non spécifié.";
    header("Location: gestion_usines.php");
    exit();
}

include('footer.php');
?>

<?php
require_once '../inc/functions/connexion.php';

if (!isset($_GET['id'])) {
    header('Location: gestion_usines.php');
    exit();
}

$id_paiement = $_GET['id'];

// Récupérer les informations du paiement
$sql = "SELECT h.*, u.nom_usine 
        FROM historique_paiements h
        INNER JOIN usines u ON h.id_usine = u.id_usine
        WHERE h.id = :id";
$stmt = $conn->prepare($sql);
$stmt->execute([':id' => $id_paiement]);
$paiement = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$paiement) {
    header('Location: gestion_usines.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $montant = $_POST['montant'];
    $date_paiement = $_POST['date_paiement'];
    $mode_paiement = $_POST['mode_paiement'];
    $reference = $_POST['reference'];

    try {
        $conn->beginTransaction();

        // 1. Mettre à jour le paiement
        $sql = "UPDATE historique_paiements 
                SET montant = :montant,
                    date_paiement = :date_paiement,
                    mode_paiement = :mode_paiement,
                    reference = :reference
                WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':montant' => $montant,
            ':date_paiement' => $date_paiement,
            ':mode_paiement' => $mode_paiement,
            ':reference' => $reference,
            ':id' => $id_paiement
        ]);

        // 2. Mettre à jour les montants de l'usine
        $difference = $montant - $paiement['montant'];
        if ($difference != 0) {
            $sql = "UPDATE usines 
                    SET montant_paye = montant_paye + :difference,
                        montant_restant = montant_restant - :difference 
                    WHERE id_usine = :id_usine";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':difference' => $difference,
                ':id_usine' => $paiement['id_usine']
            ]);
        }

        $conn->commit();
        $_SESSION['success'] = "Le paiement a été modifié avec succès.";
        header("Location: details_usine.php?id=" . $paiement['id_usine']);
        exit();

    } catch(PDOException $e) {
        $conn->rollBack();
        $_SESSION['error'] = "Erreur lors de la modification du paiement : " . $e->getMessage();
    }
}

include('header.php');
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2>Modifier le paiement - <?= htmlspecialchars($paiement['nom_usine']) ?></h2>
            <a href="details_usine.php?id=<?= $paiement['id_usine'] ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <form method="POST">
                        <div class="form-group">
                            <label for="montant">Montant (FCFA)</label>
                            <input type="number" class="form-control" id="montant" name="montant" 
                                   value="<?= $paiement['montant'] ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="date_paiement">Date du paiement</label>
                            <input type="date" class="form-control" id="date_paiement" name="date_paiement" 
                                   value="<?= date('Y-m-d', strtotime($paiement['date_paiement'])) ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="mode_paiement">Mode de paiement</label>
                            <select class="form-control" id="mode_paiement" name="mode_paiement" required>
                                <option value="Espèces" <?= $paiement['mode_paiement'] == 'Espèces' ? 'selected' : '' ?>>Espèces</option>
                                <option value="Chèque" <?= $paiement['mode_paiement'] == 'Chèque' ? 'selected' : '' ?>>Chèque</option>
                                <option value="Virement" <?= $paiement['mode_paiement'] == 'Virement' ? 'selected' : '' ?>>Virement</option>
                                <option value="Mobile Money" <?= $paiement['mode_paiement'] == 'Mobile Money' ? 'selected' : '' ?>>Mobile Money</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="reference">Référence/Motif</label>
                            <input type="text" class="form-control" id="reference" name="reference" 
                                   value="<?= htmlspecialchars($paiement['reference']) ?>">
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Enregistrer les modifications
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>

<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_bordereaux.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_ticket = $_POST['id_ticket'] ?? null;
    $id_bordereau = $_POST['id_bordereau'] ?? null;

    if ($id_ticket && $id_bordereau) {
        try {
            // Vérifier si le ticket n'est pas déjà associé à un bordereau
            $stmt_check = $conn->prepare("SELECT id_ticket FROM bordereau_tickets WHERE id_ticket = ?");
            $stmt_check->execute([$id_ticket]);
            
            if ($stmt_check->rowCount() > 0) {
                $_SESSION['error'] = "Ce ticket est déjà associé à un bordereau.";
                header("Location: tickets.php");
                exit;
            }

            // Associer le ticket au bordereau
            $stmt = $conn->prepare("INSERT INTO bordereau_tickets (id_bordereau, id_ticket) VALUES (?, ?)");
            $stmt->execute([$id_bordereau, $id_ticket]);

            // Mettre à jour le montant total du bordereau
            $stmt_update = $conn->prepare("
                UPDATE bordereaux b 
                SET montant_total = (
                    SELECT SUM(t.poids * t.prix_unitaire)
                    FROM tickets t
                    INNER JOIN bordereau_tickets bt ON t.id_ticket = bt.id_ticket
                    WHERE bt.id_bordereau = b.id_bordereau
                )
                WHERE id_bordereau = ?
            ");
            $stmt_update->execute([$id_bordereau]);

            // Mettre à jour le statut du bordereau
            updateBordereauStatus($conn, $id_bordereau);

            $_SESSION['success'] = "Le ticket a été associé au bordereau avec succès.";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Une erreur est survenue lors de l'association du ticket au bordereau.";
        }
    } else {
        $_SESSION['error'] = "Données manquantes pour l'association du ticket au bordereau.";
    }
} else {
    $_SESSION['error'] = "Méthode non autorisée.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Associer un ticket à un bordereau</title>
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../dist/css/adminlte.min.css">
</head>
<body>
    <div class="container-fluid mt-3">
        <h3>Associer un ticket à un bordereau</h3>
        <form method="post">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th style="width: 40px">
                                <input type="checkbox" id="select-all">
                            </th>
                            <th>Date</th>
                            <th>Numéro</th>
                            <th>Véhicule</th>
                            <th>Usine</th>
                            <th>Poids</th>
                            <th>Prix unitaire</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            // Récupérer les tickets pour ce bordereau
                            $tickets = getTicketsForBordereau($conn, $bordereau['id_agent'], $bordereau['date_debut'], $bordereau['date_fin']);
                            foreach ($tickets as $ticket) : ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="tickets[]" value="<?= $ticket['id_ticket'] ?>" 
                                            <?= isset($ticket['numero_bordereau']) && $ticket['numero_bordereau'] == $numero_bordereau ? 'checked disabled' : '' ?>>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($ticket['date_ticket'])) ?></td>
                                    <td><?= htmlspecialchars($ticket['numero_ticket']) ?></td>
                                    <td><?= htmlspecialchars($ticket['matricule_vehicule']) ?></td>
                                    <td><?= htmlspecialchars($ticket['nom_usine']) ?></td>
                                    <td><?= number_format($ticket['poids'], 2, ',', ' ') ?> kg</td>
                                    <td><?= number_format($ticket['prix_unitaire'], 0, ',', ' ') ?> FCFA</td>
                                    <td>
                                        <?php if (isset($ticket['numero_bordereau']) && $ticket['numero_bordereau'] == $numero_bordereau) : ?>
                                            <span class="badge badge-success">Associé</span>
                                        <?php elseif (isset($ticket['numero_bordereau'])) : ?>
                                            <span class="badge badge-warning">Autre bordereau</span>
                                        <?php else : ?>
                                            <span class="badge badge-info">Disponible</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                <button type="button" class="btn btn-secondary" onclick="window.close()">Fermer</button>
                <button type="submit" class="btn btn-primary">Associer les tickets</button>
            </div>
        </form>
    </div>

    <!-- jQuery -->
    <script src="../plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="../dist/js/adminlte.min.js"></script>
    <script>
        // Gestion de la sélection globale
        document.getElementById('select-all').addEventListener('change', function() {
            var checkboxes = document.querySelectorAll('input[name="tickets[]"]:not(:disabled)');
            checkboxes.forEach(function(checkbox) {
                checkbox.checked = this.checked;
            });
        });
    </script>
</body>
</html>
<?php 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header("Location: tickets.php");
    exit;
}
?>

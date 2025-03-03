<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_tickets.php';

// Vérifier si la connexion est établie
$conn = getConnexion();
if (!$conn) {
    header("Location: bordereaux.php?error=db_connection");
    exit;
}

// Récupérer le numéro du bordereau et les tickets
$numero_bordereau = isset($_POST['bordereau']) ? $_POST['bordereau'] : '';
$selected_tickets = isset($_POST['tickets']) ? (array)$_POST['tickets'] : [];

// Vérifier si les données nécessaires sont présentes
if (empty($numero_bordereau) || empty($selected_tickets)) {
    header("Location: bordereaux.php?error=missing_data");
    exit;
}

try {
    $conn->beginTransaction();
    
    // Mise à jour des tickets
    foreach ($selected_tickets as $id_ticket) {
        $stmt = $conn->prepare("UPDATE tickets SET numero_bordereau = ? WHERE id_ticket = ?");
        $stmt->execute([$numero_bordereau, $id_ticket]);
    }
    
    $conn->commit();
    header("Location: bordereaux.php?success=tickets_associated");
    exit;
} catch (Exception $e) {
    if ($conn) {
        $conn->rollBack();
    }
    header("Location: bordereaux.php?error=update_failed");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Association des tickets</title>
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <div class="content-wrapper" style="margin-left: 0;">
            <section class="content-header">
                <div class="container-fluid">
                    <h1>Association des tickets</h1>
                </div>
            </section>

            <section class="content">
                <div class="container-fluid">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Bordereau n° <?= htmlspecialchars($numero_bordereau) ?></h3>
                        </div>
                        <div class="card-body">
                            <?php if (isset($error)) : ?>
                                <div class="alert alert-danger">
                                    <h5><i class="icon fas fa-ban"></i> Erreur</h5>
                                    <p><?= htmlspecialchars($error) ?></p>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($selected_tickets)) : ?>
                                <div class="alert alert-info">
                                    <h5><i class="icon fas fa-info"></i> Tickets sélectionnés (<?= count($selected_tickets) ?>) :</h5>
                                    <ul>
                                        <?php foreach ($selected_tickets as $ticket_id) : ?>
                                            <li>ID Ticket : <?= htmlspecialchars($ticket_id) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                
                                <form method="post" action="update_tickets_bordereau.php">
                                    <input type="hidden" name="bordereau" value="<?= htmlspecialchars($numero_bordereau) ?>">
                                    <?php foreach ($selected_tickets as $ticket_id) : ?>
                                        <input type="hidden" name="tickets[]" value="<?= htmlspecialchars($ticket_id) ?>">
                                    <?php endforeach; ?>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save"></i> Confirmer l'association
                                    </button>
                                </form>
                            <?php else : ?>
                                <div class="alert alert-warning">
                                    <h5><i class="icon fas fa-exclamation-triangle"></i> Attention</h5>
                                    <p>Aucun ticket n'a été sélectionné.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer">
                            <a href="bordereaux.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Retour aux bordereaux
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- jQuery -->
    <script src="../plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="../dist/js/adminlte.min.js"></script>
    <script>
        $(document).ready(function() {
            $('form').on('submit', function(e) {
                e.preventDefault();
                var $form = $(this);
                var $submitBtn = $form.find('button[type="submit"]');
                
                // Désactiver le bouton pendant la soumission
                $submitBtn.prop('disabled', true);
                
                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: $form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            window.location.href = 'bordereaux.php?success=1';
                        } else {
                            alert('Erreur : ' + (response.error || 'Une erreur est survenue'));
                            $submitBtn.prop('disabled', false);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('XHR:', xhr);
                        console.error('Status:', status);
                        console.error('Error:', error);
                        
                        var errorMessage = 'Erreur de communication avec le serveur';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = 'Erreur : ' + xhr.responseJSON.error;
                        }
                        alert(errorMessage);
                        $submitBtn.prop('disabled', false);
                    }
                });
            });
        });
    </script>
</body>
</html>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats de recherche</title>
    <!-- Ajoutez ici les liens vers vos fichiers CSS ou JavaScript si nécessaire -->
</head>
<body>

<h1>Résultats de recherche</h1>

<?php
// Assurez-vous d'avoir la connexion à la base de données et d'autres fichiers inclus ici
require_once '../inc/functions/connexion.php';

// Vérifiez si des résultats ont été obtenus de la page de traitement
if (isset($result) && !empty($result)) {
    echo '<ul>';
    foreach ($result as $row) {
        echo '<li>' . $row['communes'] . '</li>';
        // Ajoutez d'autres informations que vous souhaitez afficher
    }
    echo '</ul>';
} else {
    echo '<p>Aucun résultat trouvé.</p>';
}

?>

<!-- Ajoutez ici d'autres éléments HTML ou du code PHP si nécessaire -->

</body>
</html>

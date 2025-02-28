<?php
include('header.php'); // Inclure l'en-tête contenant la connexion à la base de données
require_once '../inc/functions/connexion.php';

// Récupérer les positions de la base de données avec PDO
$sql = "SELECT 
    p.position_id,
    p.engin_id,
    p.latitude,
    p.longitude,
    p.vitesse,
    p.direction,
    p.etat_mouvement,
    p.precision,
    p.date_enregistrement,
    e.marque, 
    e.plaque_immatriculation,
    CONCAT(u.nom, ' ', u.prenoms) AS utilisateur_nom_complet
FROM position p
JOIN engins e ON p.engin_id = e.engin_id
JOIN utilisateurs u ON p.utilisateur_id = u.id
WHERE p.date_enregistrement = (
    SELECT MAX(date_enregistrement) 
    FROM position 
    WHERE engin_id = p.engin_id
)";
$stmt = $conn->prepare($sql);
$stmt->execute();

// Récupérer les résultats
$positions = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $positions[] = [
        'latitude' => (float)$row['latitude'],
        'longitude' => (float)$row['longitude'],
        'etat_mouvement' => $row['etat_mouvement'],
        'utilisateur_nom_complet' => $row['utilisateur_nom_complet'],
        'plaque_immatriculation' => $row['plaque_immatriculation']
    ];
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Carte avec positions</title>
<meta name="viewport" content="initial-scale=1,maximum-scale=1,user-scalable=no">
<script src="https://api.mapbox.com/mapbox-gl-js/v3.8.0/mapbox-gl.js"></script>
<style>
#map-container {
    position: relative;
    top: 10px; /* Ajustez cette valeur pour déplacer la carte */
    width: 100%;
    height: calc(100vh - 50px); /* Compense la marge supérieure */
}
#map {
    position: absolute;
    top: 0;
    bottom: 0;
    width: 100%;
    height: 100%; /* Remplit tout le conteneur */
}
</style>
</head>
<body>

<div class="row">

    <div class="block-container">
    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#add-commande">
      <i class="fa fa-car"></i>Les Engins en Arrêt
    </button>

    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#add-point">
      <i class="fa fa-car"></i> Les Engins en Mouvement
    </button>



   
</div>

<div id="map-container">
    <div id="map"></div>
</div>
<script>
    // Ajouter votre clé API Mapbox
    mapboxgl.accessToken = 'pk.eyJ1IjoicGVnYXN1czEyMG1vdWFuIiwiYSI6ImNtNDFpOGR0bDExYncyanM1dTlneXN2angifQ.8aXSgctKqtdljXgahLakIA';
    
    // Initialiser la carte
    const map = new mapboxgl.Map({
        container: 'map', // ID du conteneur
        style: 'mapbox://styles/mapbox/streets-v12', // Style de la carte
        center: [-4.0083, 5.3097], // Coordonnées d'Abidjan [longitude, latitude]
        zoom: 10 // Niveau de zoom initial
    });

    // Ajouter des contrôles de navigation (zoom et rotation)
    map.addControl(new mapboxgl.NavigationControl());

    // Récupérer les données de positions en JSON depuis PHP
    const positions = <?php echo json_encode($positions); ?>;

    // Vérifier si les données sont bien reçues
    console.log(positions); // Afficher dans la console pour déboguer

    // Ajouter des marqueurs pour chaque position
    positions.forEach(position => {
        const { latitude, longitude, etat_mouvement, utilisateur_nom_complet, plaque_immatriculation } = position;

        // Créer un marqueur avec un popup
        new mapboxgl.Marker()
            .setLngLat([longitude, latitude]) // Coordonnées du marqueur
            .setPopup(new mapboxgl.Popup({ offset: 25 }) // Ajouter un popup
                .setHTML(`
                    <strong>${utilisateur_nom_complet}</strong><br>
                    Mouvement: ${etat_mouvement}<br>
                    Plaque: ${plaque_immatriculation}
                `)
            )
            .addTo(map); // Ajouter le marqueur à la carte
    });
</script>
</body>
</html>

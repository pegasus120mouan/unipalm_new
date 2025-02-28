<?php
require_once '../inc/functions/connexion.php';
include('header.php');

// Récupération de l'ID de la commande depuis l'URL (par exemple, edit_commande.php?id=1)
$id_points_livreur = $_GET['id'];
$id_utilisateur = $_GET['utilisateur_id'];

// Requête pour récupérer les anciennes valeurs de la commande
$sql = "SELECT * FROM points_livreurs WHERE id = :id_points_livreur";
$requete = $conn->prepare($sql);
$requete->bindParam(':id_points_livreur', $id_points_livreur, PDO::PARAM_INT);
$requete->execute();
$modif_points_livreurs = $requete->fetch(PDO::FETCH_ASSOC);

// Requête pour récupérer les informations du livreur
$livreur_sql = "SELECT id, CONCAT(nom, ' ', prenoms) AS nom_prenoms 
                FROM utilisateurs 
                WHERE role = 'livreur' AND statut_compte = 1";
$livreurs_selection = $conn->query($livreur_sql);
?>

<!DOCTYPE html>
<html>

<head>
  <title>Édition des recettes</title>
</head>

<body>
  <form class="forms-sample" method="post" action="traitement/traitement_update_point_livraison.php">
    <div class="form-group">
      <input type="hidden" class="form-control" id="exampleInputName1" name="id" value="<?php echo htmlspecialchars($modif_points_livreurs['id']); ?>">
    </div>

    <div class="form-group">
      <label>Nom et prénoms du livreur</label>
      <select id="select" name="utilisateur_id" class="form-control" disabled>
        <?php
        while ($rowLivreur = $livreurs_selection->fetch(PDO::FETCH_ASSOC)) {
          $selected = ($rowLivreur['id'] == $id_utilisateur) ? 'selected' : '';
          echo '<option value="' . htmlspecialchars($rowLivreur['id']) . '" ' . $selected . '>' . htmlspecialchars($rowLivreur['nom_prenoms']) . '</option>';
        }
        ?>
      </select>
      <input type="hidden" name="utilisateur_id" value="<?php echo htmlspecialchars($id_utilisateur); ?>">
    </div>

    <div class="form-group">
      <label for="exampleInputPassword1">Recettes du jour</label>
      <input type="text" class="form-control" id="exampleInputPassword1" name="recette" value="<?php echo htmlspecialchars($modif_points_livreurs['recette']); ?>">
    </div>

    <div class="form-group">
      <label for="exampleInputPassword1">Dépenses du jour</label>
      <input type="text" class="form-control" id="exampleInputPassword1" name="depense" value="<?php echo htmlspecialchars($modif_points_livreurs['depense']); ?>">
    </div>

    <div class="form-group">
      <label for="exampleInputPassword1">Date</label>
      <input type="date" class="form-control" id="exampleInputPassword1" name="date" value="<?php echo htmlspecialchars($modif_points_livreurs['date_commande']); ?>">
    </div>

    <button type="submit" class="btn btn-success mr-2" name="savePLivraison">Modifier</button>
  </form>
</body>

</html>

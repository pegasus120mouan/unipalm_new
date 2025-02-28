<!DOCTYPE html>
<html>

<head>
  <title>Édition des recettes</title>
</head>

<body>

  <?php
  // Connexion à la base de données (à adapter avec vos informations)
  require_once '../inc/functions/connexion.php';
  include('header_livreurs.php');

$id_user=$_SESSION['user_id'];;

  // Récupération de l'ID de la commande depuis l'URL (par exemple, edit_commande.php?id=1)
  $id_points_livreur = $_GET['id'];

  // Requête pour récupérer les anciennes valeurs de la commande
  $sql = "SELECT * FROM points_livreurs WHERE id = :id_points_livreur";
  $requete = $conn->prepare($sql);
  $requete->bindParam(':id_points_livreur', $id_points_livreur, PDO::PARAM_INT);
  $requete->execute();
  $modif_points_livreurs = $requete->fetch(PDO::FETCH_ASSOC);


  // Selection le livreur
  //$getLivreursQuery = "SELECT id, CONCAT(nom, ' ', prenoms) AS nom_complet
  //FROM livreurs";
  //$getLivreursQueryStmt = $conn->query($getLivreursQuery);


  // $rowMontant = $montantGlobal->fetch(PDO::FETCH_ASSOC);
  //$montantColisGlobal = $rowMontant['montant_global_colis'];



  $requete = $conn->query("SELECT nom_boutique FROM clients");

  $livreurs_selection = $conn->query("SELECT id, CONCAT(nom, ' ', prenoms) AS nom_prenoms FROM livreurs");

  $cout_livraison = $conn->query("SELECT cout_livraison FROM cout_livraison");
  ?>
  <form class="forms-sample" method="post" action="traitement/traitement_update_point_livraison.php">

    <div class="form-group">
      <label for="exampleInputName1"></label>
      <input type="hidden" class="form-control" id="exampleInputName1" placeholder="id" name="id"
        value="<?php echo $modif_points_livreurs['id']; ?>">
    </div>


    <div class="form-group">
      <label for="exampleInputPassword1">Dépenses du jour</label>
      <input type="text" class="form-control" id="exampleInputPassword1" name="depense"
        value="<?php echo $modif_points_livreurs['depense']; ?>">
    </div>

    <div class="form-group">
      <label for="exampleInputPassword1">Date</label>
      <input type="date" class="form-control" id="exampleInputPassword1" name="date"
        value="<?php echo $modif_points_livreurs['date_commande']; ?>">
    </div>


    <button type="submit" class="btn btn-success mr-2" name="savePLivraison">Modifier</button>

    </div>
  </form>
</body>

</html>
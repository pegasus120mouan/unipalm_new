<!DOCTYPE html>
<html>

<head>
  <title>Édition des zones</title>
</head>

<body>

  <?php
  // Connexion à la base de données (à adapter avec vos informations)
  require_once '../inc/functions/connexion.php';
  include('header.php');

  // Récupération de l'ID de la commande depuis l'URL (par exemple, edit_commande.php?id=1)
  $id_zone = $_GET['id'];

  // Requête pour récupérer les anciennes valeurs de la commande
  $sql = "SELECT * FROM zones WHERE zone_id = :id_zone";
  $requete = $conn->prepare($sql);
  $requete->bindParam(':id_zone', $id_zone, PDO::PARAM_INT);
  $requete->execute();
  $modif_zones = $requete->fetch(PDO::FETCH_ASSOC);
  // $rows = $getLivreurs->fetchAll(PDO::FETCH_ASSOC);


  // $rowMontant = $montantGlobal->fetch(PDO::FETCH_ASSOC);
  //$montantColisGlobal = $rowMontant['montant_global_colis'];



  //$requete = $conn->query("SELECT nom_boutique FROM clients");   

  //$livreurs_selection = $conn->query("SELECT prenom_livreur FROM livreurs"); 

  //$cout_livraison = $conn->query("SELECT cout_livraison FROM cout_livraison"); 
  ?>
  <form class="forms-sample" method="post" action="traitement/traitement_update_zones.php">

    <div class="form-group">
      <label for="exampleInputName1"></label>
      <input type="hidden" class="form-control" id="exampleInputName1" placeholder="Zones" name="id"
        value="<?php echo $modif_zones['zone_id']; ?>">
    </div>

    <div class="form-group">
      <label for="exampleInputName1">Zones</label>
      <input type="text" class="form-control" id="exampleInputName1" placeholder="zones" name="zones"
        value="<?php echo $modif_zones['nom_zone']; ?>">
    </div>



    </div>

    <button type="submit" class="btn btn-success mr-2" name="saveCommande">Modifier</button>
    <button class="btn btn-light">Annuler</button>
  </form>
</body>

</html>
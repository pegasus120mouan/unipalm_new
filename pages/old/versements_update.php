<!DOCTYPE html>
<html>

<head>
  <title>Édition des versements</title>
</head>

<body>

  <?php
  // Connexion à la base de données (à adapter avec vos informations)
  require_once '../inc/functions/connexion.php';
  require_once '../inc/functions/requete/requete_commandes.php';
  include('header.php');

  // Récupération de l'ID de la commande depuis l'URL (par exemple, edit_commande.php?id=1)
  $id_versement = $_GET['id'];

  // Requête pour récupérer les anciennes valeurs de la commande
  $sql = "SELECT * FROM versements WHERE id = :id_versement";
  $requete = $conn->prepare($sql);
  $requete->bindParam(':id_versement', $id_versement, PDO::PARAM_INT);
  $requete->execute();
  $modif_versements = $requete->fetch(PDO::FETCH_ASSOC);
  // $rows = $getLivreurs->fetchAll(PDO::FETCH_ASSOC);


  // $rowMontant = $montantGlobal->fetch(PDO::FETCH_ASSOC);
  //$montantColisGlobal = $rowMontant['montant_global_colis'];



  //$requete = $conn->query("SELECT nom_boutique FROM clients");   

  //$livreurs_selection = $conn->query("SELECT prenom_livreur FROM livreurs"); 

  //$cout_livraison = $conn->query("SELECT cout_livraison FROM cout_livraison"); 
  ?>
  <form class="forms-sample" method="post" action="traitement/traitement_update_versements.php">

    <div class="form-group">
      <label for="exampleInputName1"></label>
      <input type="hidden" class="form-control" id="exampleInputName1" placeholder="Communes" name="id"
        value="<?php echo $modif_versements['id']; ?>">
    </div>

    <div class="form-group">
      <label for="exampleInputName1">Versements</label>
      <input type="text" class="form-control" id="exampleInputName1" placeholder="versements" name="versement"
        value="<?php echo $modif_versements['montant_versement']; ?>">
    </div>
    <div class="form-group">
      <label for="exampleInputName1">Date</label>
      <input type="date" class="form-control" id="exampleInputName1" placeholder="date" name="date"
        value="<?php echo $modif_versements['date_versement']; ?>">
    </div>
    </div>

    <button type="submit" class="btn btn-success mr-2" name="saveCommande">Modifier</button>
    <button class="btn btn-light">Annuler</button>
  </form>
</body>

</html>
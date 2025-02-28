<!DOCTYPE html>
<html>

<head>
  <title>Édition des dettes</title>
</head>

<body>

  <?php
  // Connexion à la base de données (à adapter avec vos informations)
  require_once '../inc/functions/connexion.php';
  require_once '../inc/functions/requete/requete_commandes.php';
  include('header.php');

  // Récupération de l'ID de la commande depuis l'URL (par exemple, edit_commande.php?id=1)
  $id_imprevu = $_GET['id'];

  // Requête pour récupérer les anciennes valeurs de la commande
  $sql = "SELECT * FROM  imprevu WHERE id = :id_imprevu";
  $requete = $conn->prepare($sql);
  $requete->bindParam(':id_imprevu', $id_imprevu, PDO::PARAM_INT);
  $requete->execute();
  $modif_imprevus= $requete->fetch(PDO::FETCH_ASSOC);
  // $rows = $getLivreurs->fetchAll(PDO::FETCH_ASSOC);


  // $rowMontant = $montantGlobal->fetch(PDO::FETCH_ASSOC);
  //$montantColisGlobal = $rowMontant['montant_global_colis'];



  //$requete = $conn->query("SELECT nom_boutique FROM clients");   

  //$livreurs_selection = $conn->query("SELECT prenom_livreur FROM livreurs"); 

  //$cout_livraison = $conn->query("SELECT cout_livraison FROM cout_livraison"); 
  ?>
  <form class="forms-sample" method="post" action="traitement/traitement_update_imprevu.php">
    <div class="form-group">
      <label for="exampleInputName1"></label>
      <input type="hidden" class="form-control" id="exampleInputName1" placeholder="id" name="id"
        value="<?php echo $modif_imprevus['id']; ?>">
    </div>

    <div class="form-group">
      <label for="exampleInputName1">Montant</label>
      <input type="text" class="form-control" id="exampleInputName1" placeholder="Montant" name="montant"
        value="<?php echo $modif_imprevus['montant']; ?>">
    </div>
    <div class="form-group">
      <label for="exampleInputName1">Motif</label>
      <input type="text" class="form-control" id="exampleInputName1" placeholder="date" name="motif"
        value="<?php echo $modif_imprevus['motif']; ?>">
    </div>
    <div class="form-group">
      <label for="exampleInputEmail3">Date</label>
      <input type="date" class="form-control" id="exampleInputEmail3" placeholder="Côut Global" name="date_contraction"
        value="<?php echo $modif_imprevus['date_contraction']; ?>">
    </div>

    <button type="submit" class="btn btn-success mr-2" name="saveCommande">Modifier</button>
    <button class="btn btn-light">Annuler</button>
  </form>
</body>

</html>
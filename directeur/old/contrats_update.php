<!DOCTYPE html>
<html>

<head>
  <title>Édition des contrats</title>
</head>

<body>

  <?php
  // Connexion à la base de données (à adapter avec vos informations)
  require_once '../inc/functions/connexion.php';
  require_once '../inc/functions/requete/requete_engins.php';

  include('header.php');

  // Récupération de l'ID de la commande depuis l'URL (par exemple, edit_commande.php?id=1)
  $id_contrat= $_GET['id'];

  // Requête pour récupérer les anciennes valeurs de la commande
  $sql = "SELECT * FROM contrats WHERE contrat_id = :id_contrat";
  $requete = $conn->prepare($sql);
  $requete->bindParam(':id_contrat', $id_contrat, PDO::PARAM_INT);
  $requete->execute();
  $modif_contrats= $requete->fetch(PDO::FETCH_ASSOC);
  // $rows = $getLivreurs->fetchAll(PDO::FETCH_ASSOC);


  // $rowMontant = $montantGlobal->fetch(PDO::FETCH_ASSOC);
  //$montantColisGlobal = $rowMontant['montant_global_colis'];



  //$requete = $conn->query("SELECT nom_boutique FROM clients");   

  //$livreurs_selection = $conn->query("SELECT prenom_livreur FROM livreurs"); 

  //$cout_livraison = $conn->query("SELECT cout_livraison FROM cout_livraison"); 
  ?>
  <form class="forms-sample" method="post" action="traitement/traitement_update_contrats.php">

    <div class="form-group">
      <label for="exampleInputName1"></label>
      <input type="hidden" class="form-control" id="exampleInputName1" placeholder="id" name="id"
        value="<?php echo $modif_contrats['contrat_id']; ?>">
    </div>


    <div class="form-group">
      <label for="exampleInputEmail3">Date de Début vignette</label>
      <input type="date" class="form-control" id="exampleInputEmail3" 
        placeholder="Année de Fabrication" name="vignette_date_debut"
        value="<?php echo $modif_contrats['vignette_date_debut']; ?>">
    </div>
    
    <div class="form-group">
      <label for="exampleInputName1">Date de Fin vignette</label>
      <input type="date" class="form-control" id="exampleInputName1" 
        placeholder="date" name="vignette_date_fin"
        value="<?php echo $modif_contrats['vignette_date_fin']; ?>">
    </div>

    <div class="form-group">
      <label for="exampleInputName1"> Date de Début Assurance </label>
      <input type="date" class="form-control" id="exampleInputName1" 
        placeholder="date" name="assurance_date_debut"
        value="<?php echo $modif_contrats['assurance_date_debut']; ?>">
    </div>

    <div class="form-group">
      <label for="exampleInputName1">Date de Fin Assurance</label>
      <input type="date" class="form-control" id="exampleInputName1" 
      placeholder="date" name="assurance_date_fin"
        value="<?php echo $modif_contrats['assurance_date_fin']; ?>">
    </div>

    <button type="submit" class="btn btn-success mr-2">Modifier</button>
    <button class="btn btn-light">Annuler</button>
  </form>
</body>

</html>
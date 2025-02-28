<!DOCTYPE html>
<html>

<head>
  <title>Édition des commandes</title>
</head>

<body>

  <?php
  // Connexion à la base de données (à adapter avec vos informations)
  include('header_livreurs.php');
  //require_once '../inc/functions/connexion.php';
  require_once '../inc/functions/requete/livreurs/requete_commandes_livreurs.php';


  // Récupération de l'ID de la commande depuis l'URL (par exemple, edit_commande.php?id=1)
  $id_commande = $_GET['id'];

  // Requête pour récupérer les anciennes valeurs de la commande
  $sql = "SELECT * FROM commandes WHERE id = :id_commande";
  $requete = $conn->prepare($sql);
  $requete->bindParam(':id_commande', $id_commande, PDO::PARAM_INT);
  $requete->execute();
  $modif_commandes = $requete->fetch(PDO::FETCH_ASSOC);
  // $rows = $getLivreurs->fetchAll(PDO::FETCH_ASSOC);


  // $rowMontant = $montantGlobal->fetch(PDO::FETCH_ASSOC);
  //$montantColisGlobal = $rowMontant['montant_global_colis'];



  //$requete = $conn->query("SELECT nom_boutique FROM clients");   

  //$livreurs_selection = $conn->query("SELECT prenom_livreur FROM livreurs"); 

  //$cout_livraison = $conn->query("SELECT cout_livraison FROM cout_livraison"); 
  ?>
  <form class="forms-sample" method="post" action="traitement/traitement_update_commandes.php">

    <div class="form-group">
      <label for="exampleInputName1"></label>
      <input type="hidden" class="form-control" id="exampleInputName1" placeholder="Communes" name="id" value="<?php echo $modif_commandes['id']; ?>">
    </div>

    <div class="form-group">
      <label for="exampleInputName1">Communes</label>
      <input type="text" class="form-control" id="exampleInputName1" placeholder="Communes" name="communes" value="<?php echo $modif_commandes['communes']; ?>">
    </div>
    <div class="form-group">
      <label for="exampleInputEmail3">Côut Global</label>
      <input type="text" class="form-control" id="exampleInputEmail3" placeholder="Côut Global" name="cout_global" value="<?php echo $modif_commandes['cout_global']; ?>">
    </div>
    <div class="form-group row">
      <label for="select" class="col-3 col-form-label">Coût Livraison</label>
      <div class="col-9">
        <?php
        echo  '<select id="select" name="livraison" class="custom-select">';
        while ($coutLivraison = $cout_livraison->fetch(PDO::FETCH_ASSOC)) {
          echo '<option value="' . $coutLivraison['cout_livraison'] . '">' . $coutLivraison['cout_livraison'] . '</option>';
        }
        echo '</select>'
        ?>
      </div>
    </div>




    <div class="form-group">
      <label for="exampleInputName1">Date</label>
      <input type="date" class="form-control" id="exampleInputName1" placeholder="date" name="date" value="<?php echo $modif_commandes['date_commande']; ?>">
    </div>
    </div>

    <button type="submit" class="btn btn-success mr-2" name="saveCommande">Modifier</button>
    <button class="btn btn-light">Annuler</button>
  </form>
</body>

</html>
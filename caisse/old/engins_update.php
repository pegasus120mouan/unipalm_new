<!DOCTYPE html>
<html>

<head>
  <title>Édition des engins</title>
</head>

<body>

  <?php
  // Connexion à la base de données (à adapter avec vos informations)
  require_once '../inc/functions/connexion.php';
  require_once '../inc/functions/requete/requete_engins.php';

  include('header.php');

  // Récupération de l'ID de la commande depuis l'URL (par exemple, edit_commande.php?id=1)
  $id_engin = $_GET['id'];

  // Requête pour récupérer les anciennes valeurs de la commande
  $sql = "SELECT * FROM engins WHERE engin_id = :id_engin";
  $requete = $conn->prepare($sql);
  $requete->bindParam(':id_engin', $id_engin, PDO::PARAM_INT);
  $requete->execute();
  $modif_engins = $requete->fetch(PDO::FETCH_ASSOC);
  // $rows = $getLivreurs->fetchAll(PDO::FETCH_ASSOC);


  // $rowMontant = $montantGlobal->fetch(PDO::FETCH_ASSOC);
  //$montantColisGlobal = $rowMontant['montant_global_colis'];



  //$requete = $conn->query("SELECT nom_boutique FROM clients");   

  //$livreurs_selection = $conn->query("SELECT prenom_livreur FROM livreurs"); 

  //$cout_livraison = $conn->query("SELECT cout_livraison FROM cout_livraison"); 
  ?>
  <form class="forms-sample" method="post" action="traitement/traitement_update_engins.php">

    <div class="form-group">
      <label for="exampleInputName1"></label>
      <input type="hidden" class="form-control" id="exampleInputName1" placeholder="id" name="id"
        value="<?php echo $modif_engins['engin_id']; ?>">
    </div>

    <div class="form-group">
                <label>Type d'engin</label>
                <?php
                echo  '<select id="select" name="type_engin" class="form-control">';
                while ($typeEngins= $type_engins->fetch(PDO::FETCH_ASSOC)) {
                  echo '<option value="' . $typeEngins['type'] . '">' . $typeEngins['type'] . '</option>';
                }
                echo '</select>'
                ?>
              </div>
    <div class="form-group">
      <label for="exampleInputEmail3">Année Fabrication</label>
      <input type="text" class="form-control" id="exampleInputEmail3" placeholder="Année de Fabrication" name="annee_fabrication"
        value="<?php echo $modif_engins['annee_fabrication']; ?>">
    </div>
    <div class="form-group">
      <label for="exampleInputName1">Numéro Chassis</label>
      <input type="text" class="form-control" id="exampleInputName1" placeholder="Numéro Chassis" name="numero_chassis"
        value="<?php echo $modif_engins['numero_chassis']; ?>">
    </div>
    <div class="form-group">
      <label for="exampleInputName1">Plaque d'immatriculation</label>
      <input type="text" class="form-control" id="exampleInputName1" placeholder="date" name="plaque_immatriculation"
        value="<?php echo $modif_engins['plaque_immatriculation']; ?>">
    </div>

    <div class="form-group">
      <label for="exampleInputName1">Couleur</label>
      <input type="text" class="form-control" id="exampleInputName1" placeholder="date" name="couleur"
        value="<?php echo $modif_engins['couleur']; ?>">
    </div>

    <div class="form-group">
      <label for="exampleInputName1">Marque</label>
      <input type="text" class="form-control" id="exampleInputName1" placeholder="date" name="marque"
        value="<?php echo $modif_engins['marque']; ?>">
    </div>

    <div class="form-group">
      <label for="exampleInputName1">Date</label>
      <input type="date" class="form-control" id="exampleInputName1" placeholder="date" name="date"
        value="<?php echo $modif_engins['date_ajout']; ?>">
    </div>


    <button type="submit" class="btn btn-success mr-2" name="saveCommande">Modifier</button>
    <button class="btn btn-light">Annuler</button>
  </form>
</body>

</html>
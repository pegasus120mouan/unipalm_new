<!DOCTYPE html>
<html>

<head>
  <title>Édition dU Livreur</title>
</head>

<body>
  <?php
    // Connexion à la base de données (à adapter avec vos informations)
    require_once '../inc/functions/connexion.php';
    include('header.php');

    // Récupération de l'ID de la commande depuis l'URL (par exemple, edit_commande.php?id=1)
    $id_livreur = $_GET['id'];

    // Requête pour récupérer les anciennes valeurs de la commande
    $sql = "SELECT * FROM utilisateurs WHERE id = :id_livreur";
    $requete = $conn->prepare($sql);
    $requete->bindParam(':id_livreur', $id_livreur, PDO::PARAM_INT);
    $requete->execute();
    $modif_livreur = $requete->fetch(PDO::FETCH_ASSOC);
    ?>
  <form class="forms-sample" method="post" action="traitement_update_livreurs.php">
    <div class="form-group">
      <div>
        <label for="exampleInputName1"></label>
        <input type="hidden" class="form-control" id="exampleInputName1" placeholder="Nom du livreur" name="id"
          value="<?php echo $modif_livreur['id']; ?>">
      </div>
      <div class="form-group">
        <label for="exampleInputName1">Nom du livreur</label>
        <input type="text" class="form-control" id="exampleInputName1" placeholder="Nom du livreur" name="nom_livreur"
          value="<?php echo $modif_livreur['nom']; ?>">
      </div>
      <div class="form-group">
        <label for="exampleInputEmail3">Prenom du livreur</label>
        <input type="text" class="form-control" id="exampleInputEmail3" placeholder="Prenom du livreur"
          name="prenom_livreur" value="<?php echo $modif_livreur['prenoms']; ?>">
      </div>
      <div class="form-group">
        <label for="exampleInputPassword4">Contact</label>
        <input type="text" class="form-control" id="exampleInputPassword4" placeholder="Contact" name="contact_livreur"
          value="<?php echo $modif_livreur['contact']; ?>">
      </div>
      <button type="submit" class="btn btn-success mr-2" name="saveLivreur">Modification livreur</button>
      <button class="btn btn-light">Annuler</button>
  </form>
  </div>
  </div>
  </div>
  </div>
  </div>
  </form>
</body>

</html>
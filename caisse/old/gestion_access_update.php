<!DOCTYPE html>
<html>

<head>
  <title>Modification des utilisateurs</title>
</head>

<body>
  <?php
    // Connexion à la base de données (à adapter avec vos informations)
    require_once '../inc/functions/connexion.php';
    include('header.php');

    // Récupération de l'ID de la commande depuis l'URL (par exemple, edit_commande.php?id=1)
    $id_utilisateur = $_GET['id'];

    // Requête pour récupérer les anciennes valeurs de la commande
    $sql = "SELECT * FROM utilisateurs WHERE id = :id_utilisateur";
    $requete = $conn->prepare($sql);
    $requete->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
    $requete->execute();
    $modif_utilisateur = $requete->fetch(PDO::FETCH_ASSOC);
    ?>
  <form class="forms-sample" method="post" action="traitement_update_gestion_utilisateur.php">
    <div class="form-group">
      <div>
        <label for="exampleInputName1"></label>
        <input type="hidden" class="form-control" id="exampleInputName1" placeholder="Nom utilisateur" name="id"
          value="<?php echo $modif_utilisateur['id']; ?>">
      </div>
      <div class="form-group">
        <label for="exampleInputName1">Nom du livreur</label>
        <input type="text" class="form-control" id="exampleInputName1" placeholder="Nom utilisateur" name="nom_utilisateur"
          value="<?php echo $modif_utilisateur['nom']; ?>">
      </div>
      <div class="form-group">
        <label for="exampleInputEmail3">Prenom du livreur</label>
        <input type="text" class="form-control" id="exampleInputEmail3" placeholder="Prenoms utilisateur"
          name="prenom_utilisateur" value="<?php echo $modif_utilisateur['prenoms']; ?>">
      </div>
      <div class="form-group">
        <label for="exampleInputPassword4">Contact</label>
        <input type="text" class="form-control" id="exampleInputPassword4" placeholder="Contact" name="contact_utilisateur"
          value="<?php echo $modif_utilisateur['contact']; ?>">
      </div>
      <button type="submit" class="btn btn-success mr-2" name="saveLivreur">Modification Utilisateur</button>
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
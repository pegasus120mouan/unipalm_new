<?php
require_once '../inc/functions/connexion.php';
//require_once '../inc/functions/requete/requetes_selection_clients_boutique.php'; 
include('header.php');
$aujourdhui = date("d-m-Y");

$id_user = $_GET['id'];

$sql = "SELECT utilisateurs.id as utilisateur_id, 
 utilisateurs.nom as utilisateur_nom, 
 utilisateurs.prenoms as utilisateur_prenoms, 
 utilisateurs.contact as utilisateur_contact,
 utilisateurs.avatar as utilisateur_avatar,
 boutiques.nom as boutique_nom 
 FROM utilisateurs 
 JOIN boutiques ON utilisateurs.boutique_id = boutiques.id 
 WHERE utilisateurs.id = :id_user";

$requete = $conn->prepare($sql);
$requete->bindParam(':id_user', $id_user, PDO::PARAM_INT);
$requete->execute();


?>

<h2>Impression des points <?php echo $aujourdhui; ?></h2>
<div class="col-lg-3 col-6">
  <!-- small box -->






  <form action="traitement_clients_commandes_print.php" method="POST">
    <div class="form-group row">
      <label for="client" class="col-4 col-form-label">Select</label>
      <div class="form-group">
        <select name="utilisateur_id" class="form-control">
          <?php
          while ($selection = $requete->fetch()) {
            echo '<option value="' . $selection['boutique_nom'] . '">' . $selection['boutique_nom'] . '</option>';
          }
          ?></select>

      </div>
    </div>
    <div class="form-group row">
      <label for="date" class="col-4 col-form-label">Date</label>
      <div class="col-8">
        <div class="input-group">
          <div class="input-group-prepend">
            <div class="input-group-text">
              <i class="fa fa-calendar"></i>
            </div>
          </div>
          <input id="date" name="date" type="date" class="form-control">
        </div>
      </div>
    </div>
    <div class="form-group row">
      <div class="offset-4 col-8">
        <button type="submit" class="btn btn-warning btn-rounded btn-fw">Imprimer</button>
      </div>

    </div>
  </form>
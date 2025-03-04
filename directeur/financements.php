<?php
require_once '../inc/functions/connexion.php';
//require_once '../inc/functions/requete/requetes_selection_boutique.php';
include('header.php');


//$_SESSION['user_id'] = $user['id'];
$id_user=$_SESSION['user_id'];
//echo $id_user;

////$stmt = $conn->prepare("SELECT * FROM users");
$sql_agents = "SELECT id_agent, CONCAT(nom, ' ', prenom) as nom_complet FROM agents ORDER BY nom, prenom";
$stmt_agents = $conn->prepare($sql_agents);
$stmt_agents->execute();
$agents = $stmt_agents->fetchAll(PDO::FETCH_ASSOC);

// Récupération des agents avec leurs montants totaux de financement
$sql_totaux = "SELECT 
    a.id_agent,
    CONCAT(a.nom, ' ', a.prenom) AS nom_agent,
    COALESCE(SUM(f.montant), 0) AS montant_total,
    COALESCE(COUNT(f.Numero_financement), 0) AS nombre_financements
FROM agents a
LEFT JOIN financement f ON a.id_agent = f.id_agent
GROUP BY a.id_agent, a.nom, a.prenom
ORDER BY montant_total DESC, a.nom, a.prenom";
$stmt_totaux = $conn->prepare($sql_totaux);
$stmt_totaux->execute();
$agents_financements = $stmt_totaux->fetchAll(PDO::FETCH_ASSOC);

// Récupération des financements avec les noms des agents
$sql = "SELECT f.*, CONCAT(a.nom, ' ', a.prenom) as nom_agent 
       FROM financement f 
       INNER JOIN agents a ON f.id_agent = a.id_agent 
       ORDER BY f.Numero_financement DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$financements = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organiser les financements par agent
$financements_par_agent = [];
foreach ($financements as $financement) {
    $id_agent = $financement['id_agent'];
    if (!isset($financements_par_agent[$id_agent])) {
        $financements_par_agent[$id_agent] = [];
    }
    $financements_par_agent[$id_agent][] = $financement;
}


//$usines = getUsines($conn);
//$chefs_equipes=getChefEquipes($conn);
//$vehicules=getVehicules($conn);
//$agents=getAgents($conn);



// Vérifiez si des tickets existent avant de procéder
//if (!empty($tickets)) {
//    $ticket_pages = array_chunk($tickets, $limit); // Divise les tickets en pages
//    $tickets_list = $ticket_pages[$page - 1] ?? []; // Tickets pour la page actuelle
//} else {
//    $tickets_list = []; // Aucun ticket à afficher
//}

?>


<!-- Main row -->
<style>
  .pagination-container {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 20px;
}

.pagination-link {
    padding: 8px;
    text-decoration: none;
    color: white;
    background-color: #007bff; 
    border: 1px solid #007bff;
    border-radius: 4px; 
    margin-right: 4px;
}

.items-per-page-form {
    margin-left: 20px;
}

label {
    margin-right: 5px;
}

.items-per-page-select {
    padding: 6px;
    border-radius: 4px; 
}

.submit-button {
    padding: 6px 10px;
    background-color: #007bff;
    color: #fff;
    border: none;
    border-radius: 4px; 
    cursor: pointer;
}
 .custom-icon {
            color: green;
            font-size: 24px;
            margin-right: 8px;
 }
 .spacing {
    margin-right: 10px; 
    margin-bottom: 20px;
}
</style>

  <style>
        @media only screen and (max-width: 767px) {
            
            th {
                display: none; 
            }
            tbody tr {
                display: block;
                margin-bottom: 20px;
                border: 1px solid #ccc;
                padding: 10px;
            }
            tbody tr td::before {

                font-weight: bold;
                margin-right: 5px;
            }
        }
        .margin-right-15 {
        margin-right: 15px;
       }
        .block-container {
      background-color:  #d7dbdd ;
      padding: 20px;
      border-radius: 5px;
      width: 100%;
      margin-bottom: 20px;
    }
    </style>


<div class="row">
    <?php if (isset($_SESSION['warning'])): ?>
        <div class="col-12">
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <?= $_SESSION['warning'] ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
        <?php unset($_SESSION['warning']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['popup'])): ?>
        <div class="col-12">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Ticket enregistré avec succès
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
        <?php unset($_SESSION['popup']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['delete_pop'])): ?>
        <div class="col-12">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                Une erreur s'est produite
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
        <?php unset($_SESSION['delete_pop']); ?>
    <?php endif; ?>

    <div class="block-container">
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add-financement">
      <i class="fa fa-edit"></i>Enregistrer un financement
    </button>

    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#print-bordereau">
      <i class="fa fa-print"></i> Imprimer la liste des financements
    </button>

    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#search_ticket">
      <i class="fa fa-search"></i> Rechercher un ticket
    </button>

    <button type="button" class="btn btn-dark" onclick="window.location.href='export_tickets.php'">
              <i class="fa fa-print"></i> Exporter la liste les tickets
             </button>
</div>



 <!-- <button type="button" class="btn btn-primary spacing" data-toggle="modal" data-target="#add-commande">
    Enregistrer une commande
  </button>


    <button type="button" class="btn btn-outline-secondary spacing" data-toggle="modal" data-target="#recherche-commande1">
        <i class="fas fa-print custom-icon"></i>
    </button>


  <a class="btn btn-outline-secondary" href="commandes_print.php"><i class="fa fa-print" style="font-size:24px;color:green"></i></a>


     Utilisation du formulaire Bootstrap avec ms-auto pour aligner à droite
<form action="page_recherche.php" method="GET" class="d-flex ml-auto">
    <input class="form-control me-2" type="search" name="recherche" style="width: 400px;" placeholder="Recherche..." aria-label="Search">
    <button class="btn btn-outline-primary spacing" style="margin-left: 15px;" type="submit">Rechercher</button>
</form>

-->




<div class="container-fluid">
    <!-- Résumé des financements -->
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">Résumé des financements par agent</h3>
                <div>
                    <button type="button" class="btn btn-info mr-2" data-toggle="modal" data-target="#listeDetaillee">
                        <i class="fas fa-list"></i> Liste détaillée des financements
                    </button>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add-financement">
                        <i class="fas fa-plus"></i> Nouveau financement
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tableResume" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Agent</th>
                            <th class="text-center">Nombre de financements</th>
                            <th class="text-right">Montant total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($agents_financements as $agent): ?>
                        <tr>
                            <td>
                                <a href="#" class="text-dark" data-toggle="modal" data-target="#detailsModal<?= $agent['id_agent'] ?>">
                                    <?= htmlspecialchars($agent['nom_agent']) ?>
                                </a>
                            </td>
                            <td class="text-center"><?= $agent['nombre_financements'] ?></td>
                            <td class="text-right"><?= number_format($agent['montant_total'], 0, ',', ' ') ?> FCFA</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>




<div class="modal fade" id="add-financement" tabindex="-1" role="dialog" aria-labelledby="addFinancementModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addFinancementModalLabel">Nouveau Financement</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="add_financements.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label for="id_agent">Agent</label>
                        <select class="form-control" id="id_agent" name="id_agent" required>
                            <option value="">Sélectionner un agent</option>
                            <?php foreach ($agents as $agent): ?>
                            <option value="<?= $agent['id_agent'] ?>"><?= htmlspecialchars($agent['nom_complet']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="montant">Montant (FCFA)</label>
                        <input type="number" class="form-control" id="montant" name="montant" required>
                    </div>
                    <div class="form-group">
                        <label for="motif">Motif</label>
                        <textarea class="form-control" id="motif" name="motif" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Modal Modification Financement -->
<div class="modal fade" id="editFinancementModal" tabindex="-1" role="dialog" aria-labelledby="editFinancementModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editFinancementModalLabel">Modifier Financement</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="edit_financements.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="numero_financement" id="edit_numero_financement">
                    <div class="form-group">
                        <label for="edit_id_agent">Agent</label>
                        <select class="form-control" id="edit_id_agent" name="id_agent" required>
                            <option value="">Sélectionner un agent</option>
                            <?php foreach ($agents as $agent): ?>
                            <option value="<?= $agent['id_agent'] ?>"><?= htmlspecialchars($agent['nom_complet']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_montant">Montant (FCFA)</label>
                        <input type="number" class="form-control" id="edit_montant" name="montant" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_motif">Motif</label>
                        <textarea class="form-control" id="edit_motif" name="motif" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                </div>
            </form>
        </div>
    </div>
</div>

  <div class="modal fade" id="print-bordereau">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Impression bordereau</h4>
        </div>
        <div class="modal-body">
          <form class="forms-sample" method="post" action="print_bordereau.php" target="_blank">
            <div class="card-body">
              <div class="form-group">
                  <label>Chargé de Mission</label>
                  <select id="select" name="id_agent" class="form-control">
                      <?php
                      // Vérifier si des usines existent
                      if (!empty($agents)) {
                          foreach ($agents as $agent) {
                              echo '<option value="' . htmlspecialchars($agent['id_agent']) . '">' . htmlspecialchars($agent['nom_complet_agent']) . '</option>';
                          }
                      } else {
                          echo '<option value="">Aucune chef eéuipe disponible</option>';
                      }
                      ?>
                  </select>
              </div>
              <div class="form-group">
                <label for="exampleInputPassword1">Date de debut</label>
                <input type="date" class="form-control" id="exampleInputPassword1" placeholder="Poids" name="date_debut">
              </div>
              <div class="form-group">
                <label for="exampleInputPassword1">Date Fin</label>
                <input type="date" class="form-control" id="exampleInputPassword1" placeholder="Poids" name="date_fin">
              </div>

              <button type="submit" class="btn btn-primary mr-2" name="saveCommande">Imprimer</button>
              <button type="button" class="btn btn-light" data-dismiss="modal">Annuler</button>
            </div>
          </form>
        </div>
      </div>
      <!-- /.modal-content -->
    </div>


    <!-- /.modal-dialog -->
  </div>

<!-- Recherche par tickets-->
<div class="modal fade" id="search_ticket">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-search mr-2"></i>Rechercher un ticket
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="d-flex flex-column">
                    <button type="button" class="btn btn-primary btn-block mb-3" data-toggle="modal" data-target="#searchByAgentModal" data-dismiss="modal">
                        <i class="fas fa-user-tie mr-2"></i>Recherche par chargé de Mission
                    </button>
                    
                    <button type="button" class="btn btn-primary btn-block mb-3" data-toggle="modal" data-target="#searchByUsineModal" data-dismiss="modal">
                        <i class="fas fa-industry mr-2"></i>Recherche par Usine
                    </button>
                    
                    <button type="button" class="btn btn-primary btn-block mb-3" data-toggle="modal" data-target="#searchByDateModal" data-dismiss="modal">
                        <i class="fas fa-calendar-alt mr-2"></i>Recherche par Date
                    </button>

                    <button type="button" class="btn btn-primary btn-block mb-3" data-toggle="modal" data-target="#searchByBetweendateModal" data-dismiss="modal">
                        <i class="fas fa-calendar-alt mr-2"></i>Recherche entre 2 dates
                    </button>
                    
                    <button type="button" class="btn btn-primary btn-block mb-3" data-toggle="modal" data-target="#searchByVehiculeModal" data-dismiss="modal">
                        <i class="fas fa-truck mr-2"></i>Recherche par Véhicule
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Recherche par Agent -->
<div class="modal fade" id="searchByAgentModal" tabindex="-1" role="dialog" aria-labelledby="searchByAgentModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="searchByAgentModalLabel">
                    <i class="fas fa-user-tie mr-2"></i>Recherche par chargé de Mission
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="searchByAgentForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="agent_id">Sélectionner un chargé de Mission</label>
                        <select class="form-control" name="agent_id" id="agent_id" required>
                            <option value="">Choisir un chargé de Mission</option>
                            <?php foreach ($agents as $agent): ?>
                                <option value="<?= $agent['id_agent'] ?>">
                                    <?= $agent['nom_complet_agent'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Rechercher</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Recherche par Usine -->
<div class="modal fade" id="searchByUsineModal" tabindex="-1" role="dialog" aria-labelledby="searchByUsineModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="searchByUsineModalLabel">
                    <i class="fas fa-industry mr-2"></i>Recherche par Usine
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="searchByUsineForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="usine">Sélectionner une Usine</label>
                        <select class="form-control" name="usine" id="usine" required>
                            <option value="">Choisir une usine</option>
                            <?php foreach ($usines as $usine): ?>
                                <option value="<?= $usine['id_usine'] ?>">
                                    <?= $usine['nom_usine'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Rechercher</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Recherche par Date -->
<div class="modal fade" id="searchByDateModal" tabindex="-1" role="dialog" aria-labelledby="searchByDateModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="searchByDateModalLabel">
                    <i class="fas fa-calendar-alt mr-2"></i>Recherche par Date
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="searchByDateForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="date_creation">Sélectionner une Date</label>
                        <input type="date" class="form-control" id="date_creation" name="date_creation" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Rechercher</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="searchByBetweendateModal" tabindex="-1" role="dialog" aria-labelledby="searchByDateModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="searchByBetweendateModalLabel">
                    <i class="fas fa-calendar-alt mr-2"></i>Recherche entre 2 dates
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="searchByBetweendateForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="date_debut">Sélectionner date Début</label>
                        <input type="date" class="form-control" id="date_debut" name="date_debut" placeholder="date debut" required>
                    </div>
                    <div class="form-group">
                        <label for="date_fin">Sélectionner date de Fin</label>
                        <input type="date" class="form-control" id="date_fin" name="date_fin" placeholder="date fin" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Rechercher</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Recherche par Véhicule -->
<div class="modal fade" id="searchByVehiculeModal" tabindex="-1" role="dialog" aria-labelledby="searchByVehiculeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="searchByVehiculeModalLabel">
                    <i class="fas fa-truck mr-2"></i>Recherche par Véhicule
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="searchByVehiculeForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="chauffeur">Sélectionner un Véhicule</label>
                        <select class="form-control" name="chauffeur" id="chauffeur" required>
                            <option value="">Choisir un véhicule</option>
                            <?php foreach ($vehicules as $vehicule): ?>
                                <option value="<?= $vehicule['vehicules_id'] ?>">
                                    <?= $vehicule['matricule_vehicule'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Rechercher</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Gestionnaire pour le formulaire de recherche par usine
document.getElementById('searchByUsineForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const usineId = document.getElementById('usine').value;
    if (usineId) {
        window.location.href = 'tickets.php?usine=' + usineId;
    }
});

// Gestionnaire pour le formulaire de recherche par date
document.getElementById('searchByDateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const date = document.getElementById('date_creation').value;
    if (date) {
        window.location.href = 'tickets.php?date_creation=' + date;
    }
});

// Gestionnaire pour le formulaire de recherche par véhicule
document.getElementById('searchByVehiculeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const vehiculeId = document.getElementById('chauffeur').value;
    if (vehiculeId) {
        window.location.href = 'tickets.php?chauffeur=' + vehiculeId;
    }
});

// Gestionnaire pour le formulaire de recherche entre deux dates
document.getElementById('searchByBetweendateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const date_debut = document.getElementById('date_debut').value;
    const date_fin = document.getElementById('date_fin').value;
    if (date_debut && date_fin) {
        window.location.href = 'tickets.php?date_debut=' + date_debut + '&date_fin=' + date_fin;
    }
});
</script>


</body>

</html>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius: 15px;">
            <div class="modal-body text-center p-4">
                <div class="mb-4">
                    <div style="width: 70px; height: 70px; background-color: #4CAF50; border-radius: 50%; display: inline-flex; justify-content: center; align-items: center; margin-bottom: 20px;">
                        <i class="fas fa-check" style="font-size: 35px; color: white;"></i>
                    </div>
                    <h4 class="mb-3" style="font-weight: 600;">SUCCESS</h4>
                    <?php if (isset($_SESSION['message'])): ?>
                        <p class="mb-4"><?= $_SESSION['message'] ?></p>
                        <?php unset($_SESSION['message']); ?>
                    <?php else: ?>
                        <p class="mb-4">Ticket ajouté avec succès!</p>
                        <p style="color: #666;">Le prix unitaire pour cette période est : <strong><?= isset($_SESSION['prix_unitaire']) ? number_format($_SESSION['prix_unitaire'], 2, ',', ' ') : '0,00' ?> FCFA</strong></p>
                    <?php endif; ?>
                </div>
                <button type="button" class="btn btn-success px-4 py-2" data-dismiss="modal" style="min-width: 120px; border-radius: 25px;">CONTINUE</button>
            </div>
        </div>
    </div>
</div>

<!-- Warning Modal -->
<div class="modal fade" id="warningModal" tabindex="-1" role="dialog" aria-labelledby="warningModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius: 15px;">
            <div class="modal-body text-center p-4">
                <div class="mb-4">
                    <div style="width: 70px; height: 70px; background-color: #FFC107; border-radius: 50%; display: inline-flex; justify-content: center; align-items: center; margin-bottom: 20px;">
                        <i class="fas fa-exclamation" style="font-size: 35px; color: white;"></i>
                    </div>
                    <h4 class="mb-3" style="font-weight: 600;">ATTENTION</h4>
                    <p style="color: #666;"><?= isset($_SESSION['warning']) ? $_SESSION['warning'] : '' ?></p>
                </div>
                <button type="button" class="btn btn-warning px-4 py-2" data-dismiss="modal" style="min-width: 120px; border-radius: 25px;">CONTINUE</button>
            </div>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="errorModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius: 15px;">
            <div class="modal-body text-center p-4">
                <div class="mb-4">
                    <div style="width: 70px; height: 70px; background-color: #dc3545; border-radius: 50%; display: inline-flex; justify-content: center; align-items: center; margin-bottom: 20px;">
                        <i class="fas fa-times" style="font-size: 35px; color: white;"></i>
                    </div>
                    <h4 class="mb-3" style="font-weight: 600;">ERROR</h4>
                    <p style="color: #666;">Une erreur s'est produite lors de l'opération.</p>
                </div>
                <button type="button" class="btn btn-danger px-4 py-2" data-dismiss="modal" style="min-width: 120px; border-radius: 25px;">AGAIN</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation de tous les modals
    $('.modal').modal({
        keyboard: false,
        backdrop: 'static',
        show: false
    });

    // Gestion de la suppression
    $('.trash').click(function(e) {
        e.preventDefault();
        var ticketId = $(this).data('id');
        $('#confirmDeleteBtn').attr('href', 'traitement_tickets.php?action=delete&id=' + ticketId);
    });
});
</script>

<?php if (isset($_SESSION['success_modal'])): ?>
<script>
    $(document).ready(function() {
        $('#successModal').modal('show');
        var audio = new Audio("../inc/sons/notification.mp3");
        audio.volume = 1.0;
        audio.play().catch((error) => {
            console.error('Erreur de lecture audio :', error);
        });
    });
</script>
<?php 
    unset($_SESSION['success_modal']);
    unset($_SESSION['prix_unitaire']);
endif; ?>

<?php if (isset($_SESSION['warning'])): ?>
<script>
    $(document).ready(function() {
        $('#warningModal').modal('show');
    });
</script>
<?php 
    unset($_SESSION['warning']);
endif; ?>

<?php if (isset($_SESSION['delete_pop'])): ?>
<script>
    $(document).ready(function() {
        $('#errorModal').modal('show');
    });
</script>
<?php 
    unset($_SESSION['delete_pop']);
endif; ?>
<script src="../../plugins/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="../../plugins/jquery-ui/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<!-- <script>
  $.widget.bridge('uibutton', $.ui.button)
</script>-->
<!-- Bootstrap 4 -->
<script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- ChartJS -->
<script src="../../plugins/chart.js/Chart.min.js"></script>
<!-- Sparkline -->
<script src="../../plugins/sparklines/sparkline.js"></script>
<!-- JQVMap -->
<script src="../../plugins/jqvmap/jquery.vmap.min.js"></script>
<script src="../../plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
<!-- jQuery Knob Chart -->
<script src="../../plugins/jquery-knob/jquery.knob.min.js"></script>
<!-- daterangepicker -->
<script src="../../plugins/moment/moment.min.js"></script>
<script src="../../plugins/daterangepicker/daterangepicker.js"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="../../plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- Summernote -->
<script src="../../plugins/summernote/summernote-bs4.min.js"></script>
<!-- overlayScrollbars -->
<script src="../../plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- AdminLTE App -->
<script src="../../dist/js/adminlte.js"></script>
<?php

if (isset($_SESSION['popup']) && $_SESSION['popup'] ==  true) {
  ?>
    <script>
      var audio = new Audio("../inc/sons/notification.mp3");
      audio.volume = 1.0; // Assurez-vous que le volume n'est pas à zéro
      audio.play().then(() => {
        // Lecture réussie
        var Toast = Swal.mixin({
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000
        });
  
        Toast.fire({
          icon: 'success',
          title: 'Action effectuée avec succès.'
        });
      }).catch((error) => {
        console.error('Erreur de lecture audio :', error);
      });
    </script>
  <?php
    $_SESSION['popup'] = false;
  }
  ?>



<!------- Delete Pop--->
<?php

if (isset($_SESSION['delete_pop']) && $_SESSION['delete_pop'] ==  true) {
?>
  <script>
    var Toast = Swal.mixin({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 3000
    });

    Toast.fire({
      icon: 'error',
      title: 'Action échouée.'
    })
  </script>

<?php
  $_SESSION['delete_pop'] = false;
}
?>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<!--<script src="dist/js/pages/dashboard.js"></script>-->
<script>
function showSearchModal(modalId) {
  // Hide all modals
  document.querySelectorAll('.modal').forEach(modal => {
    $(modal).modal('hide');
  });

  // Show the selected modal
  $('#' + modalId).modal('show');
}
</script>

<script>
// Code pour le modal de modification
$(document).ready(function() {
    $('.edit-btn').click(function() {
        var numero = $(this).data('numero');
        var agent = $(this).data('agent');
        var montant = $(this).data('montant');
        var motif = $(this).data('motif');

        $('#edit_numero_financement').val(numero);
        $('#edit_id_agent').val(agent);
        $('#edit_montant').val(montant);
        $('#edit_motif').val(motif);
    });
});
</script>

<?php foreach ($agents_financements as $agent): ?>
    <div class="modal fade" id="detailsModal<?= $agent['id_agent'] ?>" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Détails des financements - <?= htmlspecialchars($agent['nom_agent']) ?></h5>
                    <div class="ml-auto">
                        <button type="button" class="btn btn-danger mr-2" onclick="window.open('print_details_financements.php?id_agent=<?= $agent['id_agent'] ?>', '_blank')">
                            <i class="fas fa-print"></i> Imprimer
                        </button>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="detailsTable<?= $agent['id_agent'] ?>">
                            <thead>
                                <tr>
                                    <th>N° Financement</th>
                                    <th>Agent</th>
                                    <th>Date</th>
                                    <th class="text-right">Montant</th>
                                    <th>Motif</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($financements_par_agent[$agent['id_agent']])): ?>
                                    <?php foreach ($financements_par_agent[$agent['id_agent']] as $financement): ?>
                                    <tr>
                                        <td><?= $financement['Numero_financement'] ?></td>
                                        <td><?= htmlspecialchars($financement['nom_agent']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($financement['date_financement'])) ?></td>
                                        <td class="text-right"><?= number_format($financement['montant'], 0, ',', ' ') ?> FCFA</td>
                                        <td><?= htmlspecialchars($financement['motif']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">Aucun financement trouvé pour cet agent.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-right">Total:</th>
                                    <th class="text-right"><?= number_format($agent['montant_total'], 0, ',', ' ') ?> FCFA</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<script>
function printDetailsFinancements(agentId, agentNom) {
    var table = $('#detailsTable' + agentId);
    var total = 0;
    
    // Créer une nouvelle fenêtre pour l'impression
    var printWindow = window.open('', '_blank');
    var html = `
        <html>
        <head>
            <title>Détails des financements - ${agentNom}</title>
            <style>
                table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f4f4f4; }
                .header { text-align: center; margin-bottom: 20px; }
                .date { text-align: right; margin-bottom: 20px; }
                @media print {
                    .no-print { display: none; }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h2>UNIPALM</h2>
                <h3>Détails des financements - ${agentNom}</h3>
            </div>
            <div class="date">
                Date d'impression: ${new Date().toLocaleDateString()}
            </div>
            <table>
                <thead>
                    <tr>
                        <th>N° Financement</th>
                        <th>Agent</th>
                        <th>Date</th>
                        <th class="text-right">Montant</th>
                        <th>Motif</th>
                    </tr>
                </thead>
                <tbody>
    `;

    // Ajouter toutes les lignes du tableau
    table.find('tbody tr').each(function() {
        var cells = $(this).find('td');
        html += '<tr>';
        cells.each(function(index) {
            if (index < 5) { // Exclure les colonnes d'actions
                html += '<td>' + $(this).text() + '</td>';
            }
            if (index === 3) { // Colonne du montant
                total += parseInt($(this).text().replace(/[^\d]/g, ''));
            }
        });
        html += '</tr>';
    });

    html += `
                </tbody>
            </table>
            <div class="total">
                Total: ${total.toLocaleString()} FCFA
            </div>
            <div class="no-print">
                <button onclick="window.print()">Imprimer</button>
                <button onclick="window.close()">Fermer</button>
            </div>
        </body>
        </html>
    `;

    printWindow.document.write(html);
    printWindow.document.close();
}
</script>

<!-- Modal Liste détaillée des financements -->
<div class="modal fade" id="listeDetaillee" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Liste détaillée des financements</h5>
                <button type="button" class="btn btn-danger ml-2" onclick="printListeDetaillee()">
                    <i class="fas fa-print"></i> Imprimer la liste
                </button>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="tableListeDetaillee">
                        <thead>
                            <tr>
                                <th>N° Financement</th>
                                <th>Agent</th>
                                <th>Date</th>
                                <th class="text-right">Montant</th>
                                <th>Motif</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($financements as $financement): ?>
                            <tr>
                                <td><?= $financement['Numero_financement'] ?></td>
                                <td><?= htmlspecialchars($financement['nom_agent']) ?></td>
                                <td><?= date('d/m/Y', strtotime($financement['date_financement'])) ?></td>
                                <td class="text-right"><?= number_format($financement['montant'], 0, ',', ' ') ?> FCFA</td>
                                <td><?= htmlspecialchars($financement['motif']) ?></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-warning edit-btn" 
                                        data-toggle="modal" 
                                        data-target="#editFinancementModal"
                                        data-numero="<?= $financement['Numero_financement'] ?>"
                                        data-agent="<?= $financement['id_agent'] ?>"
                                        data-montant="<?= $financement['montant'] ?>"
                                        data-motif="<?= htmlspecialchars($financement['motif']) ?>">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <a href="delete_financement.php?id=<?= $financement['Numero_financement'] ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce financement ?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<script>
function printListeDetaillee() {
    var table = $('#tableListeDetaillee').DataTable();
    var allData = table.rows().data();
    
    // Créer une nouvelle fenêtre pour l'impression
    var printWindow = window.open('', '_blank');
    var html = `
        <html>
        <head>
            <title>Liste détaillée des financements</title>
            <style>
                table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f4f4f4; }
                .header { text-align: center; margin-bottom: 20px; }
                .date { text-align: right; margin-bottom: 20px; }
                @media print {
                    .no-print { display: none; }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h2>UNIPALM</h2>
                <h3>Liste détaillée des financements</h3>
            </div>
            <div class="date">
                Date d'impression: ${new Date().toLocaleDateString()}
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>N° Ticket</th>
                        <th>Usine</th>
                        <th>Agent</th>
                        <th>Véhicule</th>
                        <th>Poids</th>
                        <th>Prix Unitaire</th>
                        <th>Montant</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
    `;

    // Ajouter toutes les lignes du tableau
    table.rows().every(function() {
        var data = this.data();
        html += '<tr>';
        for(var i = 0; i < data.length; i++) {
            // Exclure la colonne des actions
            if(i < data.length - 1) {
                html += '<td>' + data[i] + '</td>';
            }
        }
        html += '</tr>';
    });

    html += `
                </tbody>
            </table>
            <div class="no-print">
                <button onclick="window.print()">Imprimer</button>
                <button onclick="window.close()">Fermer</button>
            </div>
        </body>
        </html>
    `;

    printWindow.document.write(html);
    printWindow.document.close();
}
</script>

<script>
    $(document).ready(function() {
        $('#tableListeDetaillee').DataTable({
            "responsive": true,
            "autoWidth": false,
            "order": [[0, "desc"]],
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/French.json"
            },
            "dom": "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                  "<'row'<'col-sm-12'tr>>" +
                  "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            "pagingType": "simple_numbers",
            "lengthMenu": [[15, 25, 50, -1], [15, 25, 50, "Tout"]],
            "pageLength": 15
        });
    });
</script>

<script>
    $(document).ready(function() {
        $('#tableResume').DataTable({
            "responsive": true,
            "autoWidth": false,
            "order": [[2, "desc"]],
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/French.json"
            },
            "dom": "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                  "<'row'<'col-sm-12'tr>>" +
                  "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            "pagingType": "simple_numbers", 
            "lengthMenu": [[15, 25, 50, -1], [15, 25, 50, "Tout"]],
            "pageLength": 15,
            "columnDefs": [
                { "orderable": true, "targets": 0 },
                { "orderable": true, "targets": 1, "type": "numeric" },
                { "orderable": true, "targets": 2, "type": "numeric",
                  "render": function(data, type, row) {
                    if (type === 'sort') {
                        return data.replace(/[^\d]/g, '');
                    }
                    return data;
                  }
                }
            ]
        });
    });
</script>
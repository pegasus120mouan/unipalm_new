<?php
require_once '../../inc/functions/connexion.php';


setlocale(LC_TIME, 'fr_FR.UTF-8'); // Définit la locale en français

// Récupérer le mois actuel en français
$mois_actuel = strftime("%B");


if (!isset($_SESSION['user_id'])) {
  // Redirigez vers la page de connexion si l'utilisateur n'est pas connecté
  header("Location: ../index.php");
  exit();
}
// Mes statisques Colis
    // SQL query to calculate total delivery cost
    $sql_somme_livraison = "
        SELECT SUM(cout_livraison) AS total_cout_livraison
        FROM commandes
        WHERE statut = 'Livré' 
        AND date_commande >= DATE_FORMAT(NOW(), '%Y-01-01')";
    $requVueGeneral = $conn->prepare($sql_somme_livraison);
    $requVueGeneral->execute();
    $vuegenerale_livraison = $requVueGeneral->fetch(PDO::FETCH_ASSOC);

    /*--------------------Depenses----------------------*/
    $getSommeDepenseQuery = "
        SELECT SUM(depense) AS total_depenses
        FROM points_livreurs
        WHERE date_commande >= DATE_FORMAT(NOW(), '%Y-01-01')";
    $getDepensesQueryStmt = $conn->prepare($getSommeDepenseQuery);
    $getDepensesQueryStmt->execute();
    $somme_depenses = $getDepensesQueryStmt->fetch(PDO::FETCH_ASSOC);

    /*------------------Nombre de colis ---------------------------*/
    $sql_colis_livres = "
        SELECT 
            SUM(cout_livraison) AS total_cout_livraison,
            COUNT(*) AS total_commandes_livrees
        FROM commandes
        WHERE statut = 'Livré' 
        AND date_commande >= DATE_FORMAT(NOW(), '%Y-01-01')";
    $getNombreColisLivres = $conn->prepare($sql_colis_livres);
    $getNombreColisLivres->execute();
    $nombre_colis_livres = $getNombreColisLivres->fetch(PDO::FETCH_ASSOC);

    ///*****************--------------Somme Caisse */
    $montant_caisse = $vuegenerale_livraison['total_cout_livraison'] - $somme_depenses['total_depenses'];






$getSommeDetteQuery = "SELECT SUM(montant_actuel) AS total_montant_actuel
        FROM dette
        WHERE date_contraction >= DATE_FORMAT(NOW(), '%Y-%m-01')";
$getSommeDetteQueryStmt = $conn->query($getSommeDetteQuery);
$somme_dettes = $getSommeDetteQueryStmt->fetch(PDO::FETCH_ASSOC);



$getSomme_payes_Query = "SELECT SUM(montants_payes) AS total_montants_payes
        FROM dette
        WHERE date_contraction >= DATE_FORMAT(NOW(), '%Y-%m-01')";
$getSomme_payes_QueryStmt = $conn->query($getSomme_payes_Query);
$somme_montants_payes = $getSomme_payes_QueryStmt->fetch(PDO::FETCH_ASSOC);

// Nombre de livreurs

 $getSommeLivreursQuery = "
        SELECT COUNT(*) AS total_livreurs
        FROM utilisateurs
        WHERE role = 'Livreur'";
    $getSommeLivreursQueryStmt = $conn->prepare($getSommeLivreursQuery);
    $getSommeLivreursQueryStmt->execute();
    $nombre_livreurs = $getSommeLivreursQueryStmt->fetch(PDO::FETCH_ASSOC);


// Nombre de livreurs
   /*------------------Nombre de livreurs actifs ---------------------------*/
    $getSommeLivreursActifQuery = "
        SELECT COUNT(*) AS total_livreurs_actif
        FROM utilisateurs
        WHERE role = 'Livreur' AND statut_compte = 1";
    $getSommeLivreursActifQueryStmt = $conn->prepare($getSommeLivreursActifQuery);
    $getSommeLivreursActifQueryStmt->execute();
    $nombre_livreurs_actif = $getSommeLivreursActifQueryStmt->fetch(PDO::FETCH_ASSOC);

    ///*****************--------------Somme Caisse */
    $montant_caisse = $vuegenerale_livraison['total_cout_livraison'] - $somme_depenses['total_depenses'];






$total_reste_dettes=$somme_dettes['total_montant_actuel'] - $somme_montants_payes['total_montants_payes'];


$getSomme_imprevu_Query = "SELECT SUM(montant) AS total_imprevu
        FROM imprevu
        WHERE date_contraction >= DATE_FORMAT(NOW(), '%Y-%m-01')";
$getSomme_imprevu_QueryStmt = $conn->query($getSomme_imprevu_Query);
$somme_imprevus = $getSomme_imprevu_QueryStmt->fetch(PDO::FETCH_ASSOC);


//$montant_caisse = $vuegenerale_livraison['total_cout_livraison']- $somme_depenses['total_depenses']-$total_reste_dettes-$somme_imprevus['total_imprevu'];


/*$totalLivraison = $somme_global['sum_cout_global'] - $somme_cout_reel['sum_cout_reel'];*/


/*$getSommeDepenseQuery = "SELECT depense as somme_depense,date_commande,livreurs.nom,livreurs.prenoms from points_livreurs 
JOIN livreurs ON points_livreurs.utilisateur_id=livreurs.id WHERE date_commande=CURRENT_DATE()-1 AND livreurs.nom='Kone' AND livreurs.prenoms='Lassina'";
$getSommeDepenseQueryStmt = $conn->query($getSommeDepenseQuery);
$somme_depense = $getSommeDepenseQueryStmt->fetch(PDO::FETCH_ASSOC);

$getSommeDepenseQuery = "SELECT depense as somme_depense,date_commande,livreurs.nom,livreurs.prenoms from points_livreurs 
JOIN livreurs ON points_livreurs.utilisateur_id=livreurs.id WHERE date_commande=CURRENT_DATE()-1 AND livreurs.nom='Kone' AND livreurs.prenoms='Lassina'";
$getSommeDepenseQueryStmt = $conn->query($getSommeDepenseQuery);
$somme_depense = $getSommeDepenseQueryStmt->fetch(PDO::FETCH_ASSOC);


$getSommeGainQuery = "SELECT gain_jour as gain,date_commande,livreurs.nom,livreurs.prenoms from points_livreurs 
JOIN livreurs ON points_livreurs.utilisateur_id=livreurs.id WHERE date_commande=CURRENT_DATE()-1 AND livreurs.nom='Kone' AND livreurs.prenoms='Lassina'";
$getSommeGainQueryStmt = $conn->query($getSommeGainQuery);
$somme_gain = $getSommeGainQueryStmt->fetch(PDO::FETCH_ASSOC);

//$somme_depense = $somme_depense ?? ['somme_depense'=>0];

if (is_array($somme_global) && isset($somme_global['sum_cout_global'])) {
  $totalAVerser = (int) $somme_global['sum_cout_global'];
} else {
  $totalAVerser = 0; // or another default value
}

if (is_array($somme_depense) && isset($somme_depense['somme_depense'])) {
  $totalAVerser -= (int) $somme_depense['somme_depense'];
}


//$totalAVerser = (int) $somme_global['sum_cout_global'] -  $somme_depense['somme_depense'];*/
//Point Livreurs-----------------------------------


?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Mes statisques</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet"
    href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../../plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../../dist/css/adminlte.min.css">
</head>

<body class="hold-transition sidebar-mini">
  <div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
      <!-- Left navbar links -->
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
          <a href="../dashboard.php" class="nav-link">Acceuil</a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
          <a href="../commandes.php" class="nav-link">Les commandes</a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
          <a href="../../logout.php" class="nav-link">Déconnexion</a>
        </li>
      </ul>

      <!-- Right navbar links -->
      <ul class="navbar-nav ml-auto">
        <!-- Navbar Search -->
        <li class="nav-item">
          <a class="nav-link" data-widget="navbar-search" href="#" role="button">
            <i class="fas fa-search"></i>
          </a>
          <div class="navbar-search-block">
            <form class="form-inline">
              <div class="input-group input-group-sm">
                <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
                <div class="input-group-append">
                  <button class="btn btn-navbar" type="submit">
                    <i class="fas fa-search"></i>
                  </button>
                  <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
            </form>
          </div>
        </li>

        <!-- Messages Dropdown Menu -->
        <li class="nav-item dropdown">
          <a class="nav-link" data-toggle="dropdown" href="#">
            <i class="far fa-comments"></i>
            <span class="badge badge-danger navbar-badge">3</span>
          </a>
          <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
            <a href="#" class="dropdown-item">
              <!-- Message Start -->
              <div class="media">
                <img src="dist/img/user1-128x128.jpg" alt="User Avatar" class="img-size-50 mr-3 img-circle">
                <div class="media-body">
                  <h3 class="dropdown-item-title">
                    Brad Diesel
                    <span class="float-right text-sm text-danger"><i class="fas fa-star"></i></span>
                  </h3>
                  <p class="text-sm">Call me whenever you can...</p>
                  <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
                </div>
              </div>
              <!-- Message End -->
            </a>
            <div class="dropdown-divider"></div>
            <a href="#" class="dropdown-item">
              <!-- Message Start -->
              <div class="media">
                <img src="dist/img/user8-128x128.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">
                <div class="media-body">
                  <h3 class="dropdown-item-title">
                    John Pierce
                    <span class="float-right text-sm text-muted"><i class="fas fa-star"></i></span>
                  </h3>
                  <p class="text-sm">I got your message bro</p>
                  <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
                </div>
              </div>
              <!-- Message End -->
            </a>
            <div class="dropdown-divider"></div>
            <a href="#" class="dropdown-item">
              <!-- Message Start -->
              <div class="media">
                <img src="dist/img/user3-128x128.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">
                <div class="media-body">
                  <h3 class="dropdown-item-title">
                    Nora Silvester
                    <span class="float-right text-sm text-warning"><i class="fas fa-star"></i></span>
                  </h3>
                  <p class="text-sm">The subject goes here</p>
                  <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
                </div>
              </div>
              <!-- Message End -->
            </a>
            <div class="dropdown-divider"></div>
            <a href="#" class="dropdown-item dropdown-footer">See All Messages</a>
          </div>
        </li>
        <!-- Notifications Dropdown Menu -->
        <li class="nav-item dropdown">
          <a class="nav-link" data-toggle="dropdown" href="#">
            <i class="far fa-bell"></i>
            <span class="badge badge-warning navbar-badge">15</span>
          </a>
          <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
            <span class="dropdown-item dropdown-header">15 Notifications</span>
            <div class="dropdown-divider"></div>
            <a href="#" class="dropdown-item">
              <i class="fas fa-envelope mr-2"></i> 4 new messages
              <span class="float-right text-muted text-sm">3 mins</span>
            </a>
            <div class="dropdown-divider"></div>
            <a href="#" class="dropdown-item">
              <i class="fas fa-users mr-2"></i> 8 friend requests
              <span class="float-right text-muted text-sm">12 hours</span>
            </a>
            <div class="dropdown-divider"></div>
            <a href="#" class="dropdown-item">
              <i class="fas fa-file mr-2"></i> 3 new reports
              <span class="float-right text-muted text-sm">2 days</span>
            </a>
            <div class="dropdown-divider"></div>
            <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
          </div>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-widget="fullscreen" href="#" role="button">
            <i class="fas fa-expand-arrows-alt"></i>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-widget="control-sidebar" data-controlsidebar-slide="true" href="#" role="button">
            <i class="fas fa-th-large"></i>
          </a>
        </li>
      </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
      <!-- Brand Logo -->
      <a href="commandes.php" class="brand-link">
        <img src="../../dist/img/logo.png" alt="OVL Delivery Services" class="brand-image img-circle elevation-3"
          style="opacity: .8">
        <span class="brand-text font-weight-light">OVL Delivery Services</span>
      </a>

      <!-- Sidebar -->
      <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
          <div class="image">
            <img src="../../dossiers_images/<?php echo $_SESSION['avatar']; ?>" class="img-circle elevation-2" alt="Logo">
            <!-- <img src="../../dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">-->
          </div>
          <div class="info">
            <a href="#" class="d-block"><?php echo $_SESSION['nom']; ?> <?php echo $_SESSION['prenoms']; ?></a>
          </div>
        </div>

        <!-- SidebarSearch Form -->
        <div class="form-inline">
          <div class="input-group" data-widget="sidebar-search">
            <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
            <div class="input-group-append">
              <button class="btn btn-sidebar">
                <i class="fas fa-search fa-fw"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
            <li class="nav-item menu-open">
              <a href="#" class="nav-link active">
                <i class="nav-icon fas fa-tachometer-alt"></i>
                <p>
                  Mes tableaux de bords
                  <i class="right fas fa-angle-left"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="dashboard.php" class="nav-link active">
                    <i class="fas fa-motorcycle"></i>
                    <p>Bilan aujourd'hui</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="vuegeneral.php" class="nav-link">
                    <i class="fas fa-bicycle"></i>
                    <p>Bilan Hier</p>
                  </a>
                </li>
              </ul>
            </li>
            <li class="nav-item">
              <a href="#" class="nav-link">
                <i class="fas fa-money-bill-wave-alt"></i>
                <p>
                  Points de livraisons
                  <i class="fas fa-angle-left right"></i>
                  <span class="badge badge-info right">6</span>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="point_client.php" class="nav-link">
                    <i class="fas fa-balance-scale"></i>
                    <p>Points par clients</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="point_livraison.php" class="nav-link">
                    <i class="fas fa-wallet"></i>
                    <p>Points des Livreurs</p>
                  </a>
                </li>
              </ul>
            </li>
            <li class="nav-item">
              <a href="#" class="nav-link">
                <i class="nav-icon fas fa-chart-pie"></i>
                <p>
                  Commandes
                  <i class="right fas fa-angle-left"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="commandes.php" class="nav-link">
                    <i class="fas fa-clone"></i>
                    <p>Liste des commandes</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="commandes_livrees.php" class="nav-link">
                    <i class="far fa-clone"></i>
                    <p>Commandes Livrées</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="commandes_non_livrees.php" class="nav-link">
                    <i class="fas fa-file"></i>
                    <p>Commandes non livrées</p>
                  </a>
                </li>
              </ul>
            </li>
            <li class="nav-item">
              <a href="#" class="nav-link">
                <i class="fas fa-male"></i>
                <p>
                  Clients
                  <i class="fas fa-angle-left right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="clients.php" class="nav-link">
                    <i class="fas fa-user-alt"></i>
                    <p>Liste des clients</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="pages/UI/general.html" class="nav-link">
                    <i class="fas fa-user-tie"></i>
                    <p>Particulier</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="pages/UI/icons.html" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Boutique</p>
                  </a>
                </li>
              </ul>
            </li>
            <li class="nav-item">
              <a href="#" class="nav-link">
                <i class="nav-icon fas fa-table"></i>
                <p>
                  Engins
                  <i class="fas fa-angle-left right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="pages/tables/simple.html" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Listes des engins</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="pages/tables/data.html" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Contrats</p>
                  </a>
                </li>

              </ul>
            </li>



            <li class="nav-item">
              <a href="#" class="nav-link">
                <i class="nav-icon fas fa-table"></i>
                <p>
                  Listes des utilisateurs
                  <i class="fas fa-angle-left right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="liste_livreurs.php" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Listes des livreurs</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="admin_users.php" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Listes des admins</p>
                  </a>
                </li>


              </ul>
            </li>
            <li class="nav-header">CAISSE</li>
            <li class="nav-item">
              <a href="pages/calendar.html" class="nav-link">
                <i class="nav-icon far fa-calendar-alt"></i>
                <p>
                  Soldes
                  <span class="badge badge-info right">2</span>
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="pages/gallery.html" class="nav-link">
                <i class="nav-icon far fa-image"></i>
                <p>
                  Caisse
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="pages/kanban.html" class="nav-link">
                <i class="nav-icon fas fa-columns"></i>
                <p>
                  Depenses
                </p>
              </a>
            </li>
          </ul>
        </nav
        <!-- /.sidebar -->
      </div>
      <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
      <!-- Content Header (Page header) -->
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>Point de mes statisques</h1>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Vue Générale</a></li>
                <li class="breadcrumb-item active"><?php echo $_SESSION['user_role']; ?></li>
              </ol>
            </div>
          </div>
        </div><!-- /.container-fluid -->
      </section>

      <!-- Main content -->
      <section class="content">
        <div class="container-fluid">
          <h5 class="mb-2">Mois: <?php echo $mois_actuel?></h5>
          <div class="row">
            <div class="col-md-3 col-sm-6 col-12">
              <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-money-bill"></i></span>

                <div class="info-box-content">
                  <span class="info-box-text"><i>Total Livraison</i></span>
                  <span class="info-box-number" style="font-size: 24px;">
                    <?php if ($vuegenerale_livraison == 0) {
                      echo "0";
                    } else {
                      echo $vuegenerale_livraison['total_cout_livraison'];
                    } ?>
                  </span>
                </div>


                <!-- /.info-box-content -->
              </div>
              <!-- /.info-box -->
            </div>
            <!-- /.col -->
            <div class="col-md-2 col-sm-6 col-12">
              <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-money-bill-wave"></i></span>

                <div class="info-box-content">
                  <span class="info-box-text"><i>Dépenses</i></span>
                  <span class="info-box-number" style="font-size: 24px;">
                    <?= $somme_depenses['total_depenses']; ?>
                  </span>
                </div>
                <!-- /.info-box-content -->
              </div>
              <!-- /.info-box -->
            </div>
            <!-- /.col -->
            <div class="col-md-2 col-sm-6 col-12">
              <div class="info-box">
                <span class="info-box-icon bg-primary"><i class="fas fa-receipt"></i></span>

                <div class="info-box-content">
                  <span class="info-box-text"><i>Nombre de  colis livrés</i></span>
                  <span class="info-box-number" style="font-size: 24px;">
                    <?php if ($nombre_colis_livres == 0) {
                      echo "0";
                    } else {
                      echo $nombre_colis_livres['total_commandes_livrees'];
                    } ?>

                  </span>
                </div>
                <!-- /.info-box-content -->
              </div>
              <!-- /.info-box -->
            </div>
            <!-- /.col -->
            <div class="col-md-3 col-sm-6 col-12">
              <div class="info-box">
                <span class="info-box-icon bg-danger"><i class="far fa-copy"></i></span>

                <div class="info-box-content">
                  <span class="info-box-text"><i>Nombre de Livreurs</i></span>
                  <span class="info-box-number" style="font-size: 24px;">
                    <?php if ($nombre_livreurs == 0) {
                      echo "0";
                    } else {
                        echo '<i class="fas fa-user"></i> ' . $nombre_livreurs['total_livreurs'] . '<br>'; 

                    } ?>
                  </span>
                </div>

                <div class="info-box-content">
                  <span class="info-box-text"><i>Livreurs Actifs</i></span>
                  <span class="info-box-number" style="font-size: 24px;">
                    <?php if ($nombre_livreurs_actif == 0) {
                      echo "0";
                    } else {
                       echo '<i class="fas fa-user-check"></i> ' . $nombre_livreurs_actif['total_livreurs_actif']; 

                    } ?>
                  </span>
                </div>
                <!-- /.info-box-content -->
              </div>
              <!-- /.info-box -->
            </div>

            <div class="col-md-2 col-sm-6 col-12">
              <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-wallet"></i></span>

                <div class="info-box-content">
                  <span class="info-box-text"><i>Total Imprévus</i></span>
                  <span class="info-box-number" style="font-size: 24px;">
                    <?php if ($somme_imprevus == 0) {
                      echo "0";
                    } else {
                      echo $somme_imprevus['total_imprevu'];
                    } ?>
                  </span>
                </div>
                <!-- /.info-box-content -->
              </div>
              <!-- /.info-box -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->

        <!-- =========================================================== -->

        <!-- =========================================================== -->
        <h5 class="mt-4 mb-2"></h5>
        <div class="row">
          <div class="col-md-12 col-sm-6 col-12">
            <div class="info-box bg-dark">
            <span class="info-box-icon" style="font-size: 48px;">
                <i class="fas fa-hand-holding-usd">
                </i></span>
              
              <div class="info-box-content">
                <span style="text-align: center; font-size: 20px;" class="info-box-text">Total caisse</span>

                <div class="progress">
                  <div class="progress-bar" style="width: 100%"></div>
                </div>
                <span class="progress-description">
                <h1 style="text-align: center; font-size: 70px;"><strong><?php echo $montant_caisse; ?></strong></h1>
                </span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
        </div>
        <!-- /.row -->

        <!-- =========================================================== -->
       

        <!-- =========================================================== -->

    </div>
    <!-- ./wrapper -->

    <!-- jQuery -->
    <script src="../../plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="../../dist/js/adminlte.min.js"></script>
    <!-- AdminLTE for demo purposes -->

</body>

</html>
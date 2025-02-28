<?php
//session_start();
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_dashboard.php';

$currentDateTime = date('d-m-Y');


$date=date('Y-m-d H:i:s');
$pointParclients = $getPoints_clients->fetchAll(PDO::FETCH_ASSOC);
$totalAmount = array_sum(array_column($pointParclients, 'total_amount'));
$totalVersement = array_sum(array_column($pointParclients, 'total_cout_reel'));
$totalCout_livraison = array_sum(array_column($pointParclients, 'total_cout_livraison'));

$pointParlivreurs= $getPoints_a_donners->fetchAll(PDO::FETCH_ASSOC);


$pointParlivreur_gains = $getPoints_Livreurs->fetchAll(PDO::FETCH_ASSOC);


?>
 
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tableau de bord</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="icon" href="../dist/img/logo.png" type="image/x-icon">
  <link rel="shortcut icon" href="../dist/img/logo.png" type="image/x-icon">

  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../../plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">

  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
  <!-- Tempusdominus Bootstrap 4 -->
  <link rel="stylesheet" href="../../plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="../../plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- JQVMap -->
  <link rel="stylesheet" href="../../plugins/jqvmap/jqvmap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../../dist/css/adminlte.min.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="../../plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="../../plugins/daterangepicker/daterangepicker.css">
  <!-- summernote -->
  <link rel="stylesheet" href="../../plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">
  <link rel="stylesheet" href="../../plugins/summernote/summernote-bs4.min.css">
  <link rel="stylesheet" href="../../plugins/fontawesome-free/css/all.min.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="../../plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
  <link rel="stylesheet" href="../../plugins/datatables-buttons/css/buttons.bootstrap4.min.css"> 
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"> 
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../../plugins/fontawesome-free/css/all.min.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="../../plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
  <link rel="stylesheet" href="../../plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../../dist/css/adminlte.min.css">
  <link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
  <script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>

</head>

<body class="hold-transition dark-mode sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
  <div class="wrapper">

    <!-- Preloader -->

        <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
      <!-- Left navbar links -->
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
          <a href="dashboard.php" class="nav-link">Acceuil</a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
          <a href="commandes.php" class="nav-link">Les commandes</a>
        </li>
      </ul>

      <!-- Right navbar links -->
      <ul class="navbar-nav ml-auto">
        <!-- Navbar Search -->
        <li class="nav-item">
          <a class="nav-link" data-widget="navbar-search" href="recherche_colis.php" role="button">
            <i class="fas fa-search"></i>
          </a>
          <div class="navbar-search-block">
            <form class="form-inline">
              <div class="input-group input-group-sm">
                <input class="form-control form-control-navbar" type="search" name="communeInput" placeholder="Recherche un colis" aria-label="Search">
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

        <!-- Notifications Dropdown Menu -->
        <li class="nav-item dropdown">
          <a class="nav-link" data-toggle="dropdown" href="#">
            <i class="far fa-bell"></i>
            <span class="badge badge-warning navbar-badge">15</span>
          </a>
          <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
            <span class="dropdown-item dropdown-header">0 Notifications</span>
            <div class="dropdown-divider"></div>
            <a href="#" class="dropdown-item">
              <i class="fas fa-envelope mr-2"></i> 0 Nouveaux Messages
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
            <img src="../dossiers_images/<?php echo $_SESSION['avatar']; ?>" class="img-circle elevation-2" alt="Logo">
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
                 <li class="nav-item">
                  <a href="liste_montants.php" class="nav-link">
                    <i class="fas fa-list"></i>
                    <p>Liste des montants</p>
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
              </ul>
            </li>
            <li class="nav-item">
              <a href="#" class="nav-link">
                <i class="fas fa-taxi"></i>
              
                <p>
                  Engins
                  <i class="fas fa-angle-left right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="listes_engins.php" class="nav-link">
                    <i class="fas fa-bicycle"></i>
                    <p>Listes des engins</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="contrats.php" class="nav-link">
                    <i class="fas fa-folder-open"></i>
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
                    <i class="fas fa-male	"></i>
                    <p>Listes des livreurs</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="liste_admins.php" class="nav-link">
                  <i class="fas fa-user-tie"></i>
                    <p>Listes des admins</p>
                  </a>
                </li>

                 <li class="nav-item">
                  <a href="gestion_access.php" class="nav-link">
                  <i class="fas fa-lock"></i>
                    <p>Gestion des acccès</p>
                  </a>
                </li>


              </ul>
            </li>
             <li class="nav-item">
              <a href="cout_livraison.php" class="nav-link">
                <i class="nav-icon far fa-calendar-alt"></i>
                <p>
                  Coût Livraison
                </p>
              </a>
             </li>





          <li class="nav-header"><strong>GESTION CAISSE</strong></li>
             <li class="nav-item">
              <a href="analytics/vuegenerale_soldes.php" class="nav-link">
                <i class="nav-icon far fa-calendar-alt"></i>
                <p>
                  Soldes
                  <span class="badge badge-info right">2</span>
                </p>
              </a>
             </li>
            <li class="nav-item">
              <a href="analytics/vue_gestion_caisse.php" class="nav-link">
                <i class="nav-icon far fa-image"></i>
                <p>
                  Caisse
                </p>
              </a>
            </li>


                        <li class="nav-item">
              <a href="#" class="nav-link">
                <i class="nav-icon fas fa-chart-pie"></i>
                <p>
                  Points des dépots
                  <i class="right fas fa-angle-left"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="listes_des_depots.php" class="nav-link">
                    <i class="fas fa-clone"></i>
                    <p>Liste des dépots</p>
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
              <a href="analytics/vue_gestion_caisse.php" class="nav-link">
                <i class="nav-icon far fa-image"></i>
                <p>
                  Caisse
                </p>
              </a>
            </li>

              <li class="nav-item">
              <a href="analytics/vue_ovl_statisques.php" class="nav-link">
                <i class="nav-icon fas fa-balance-scale"></i>
                <p>
                 <strong>Mes statisques</strong>
                </p>
              </a>
            </li>

            <li class="nav-item">
              <a href="dettes.php" class="nav-link">
                <i class='fas fa-coins'></i>
                <p>
                  Dettes
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="imprevus.php" class="nav-link">
                <i class='fas fa-question-circle'></i>
                <p>
                  Imprevus
                </p>
              </a>
           </li>

           <li class="nav-item">
              <a href="#" class="nav-link">
                <i class="fa fa-camera-retro"></i>
                <p>
                  Banners
                  <i class="fas fa-angle-left right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="banner_admin.php" class="nav-link">
                   <i class="fa fa-solid fa-camera"></i>
                    <p>Banner Administrateur</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="livreurs_analytics.php" class="nav-link">
                    <i class="fa fa-solid fa-image"></i>
                    <p>Livreurs</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="gestion_caisse.php" class="nav-link">
                    <i class="fa fa-pie-chart"></i>
                    <p>Caisse</p>
                  </a>
                </li>
              </ul>
            </li>









           <li class="nav-item">
              <a href="#" class="nav-link">
                <i class="fa fa-area-chart"></i>
                <p>
                  Analytics
                  <i class="fas fa-angle-left right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="clients_analytics.php" class="nav-link">
                    <i class="fa fa-bar-chart"></i>
                    <p>Clients</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="livreurs_analytics.php" class="nav-link">
                    <i class="fa fa-line-chart"></i>
                    <p>Livreurs</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="gestion_caisse.php" class="nav-link">
                    <i class="fa fa-pie-chart"></i>
                    <p>OVL</p>
                  </a>
                </li>
              </ul>
            </li>

            <li class="nav-item">
              <a href="../logout.php" class="nav-link">
              <i class="fa fa-arrow-right"></i>

                <p>
                  Déconnexion
                </p>
              </a>
           </li>

          </ul>
        </nav>
        <!-- /.sidebar-menu -->
      </div>
      <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
      <!-- Content Header (Page header) -->
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1 class="m-0">Vue Générale <?php echo $currentDateTime; ?></h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Tableau de Bord</a></li>
                <li class="breadcrumb-item active"><?php echo $_SESSION['user_role']; ?></li>
              </ol>
            </div><!-- /.col -->
          </div><!-- /.row -->
        </div><!-- /.container-fluid -->
      </div>
      <!-- /.content-header -->

      <!-- Main content -->
      <section class="content">
        <div class="container-fluid">
          <!-- Info boxes -->
          <div class="row">
            <div class="col-12 col-sm-6 col-md-3">
              <div class="info-box">
                <span class="info-box-icon bg-info elevation-1"><i class="fas fa-cog"></i></span>

                <div class="info-box-content">
                  <span class="info-box-text">Montant Global</span>
                  <span class="info-box-number">
                    <?php
                    if ($totalAmount == 0) {
                      echo "0";
                    } else {
                      echo $totalAmount;
                    }
                    ?>
                    <small>CFA</small>
                  </span>
                </div>
                <!-- /.info-box-content -->
              </div>
              <!-- /.info-box -->
            </div>
            <!-- /.col -->
            <div class="col-12 col-sm-6 col-md-3">
              <div class="info-box mb-3">
                <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-thumbs-up"></i></span>

                <div class="info-box-content">
                  <span class="info-box-text">Montant à donner</span>
                  <span class="info-box-number">
                    <?php
                    if ($totalVersement == 0) {
                      echo "0";
                    } else {
                      echo $totalVersement;
                    }
                    ?>
                    <small>CFA</small>
                  </span>

                </div>
                <!-- /.info-box-content -->
              </div>
              <!-- /.info-box -->
            </div>
            <!-- /.col -->

            <!-- fix for small devices only -->
            <div class="clearfix hidden-md-up"></div>

            <div class="col-12 col-sm-6 col-md-3">
              <div class="info-box mb-3">
                <span class="info-box-icon bg-success elevation-1"><i class="fas fa-shopping-cart"></i></span>

                <div class="info-box-content">
                  <span class="info-box-text">Recette Global</span>
                  <span class="info-box-number">

                    <?php
                    if ($totalCout_livraison == 0) {
                      echo "0";
                    } else {
                      echo $totalCout_livraison;
                    }
                    ?>
                    <small>CFA</small>
                  </span>
                </div>
                <!-- /.info-box-content -->
              </div>
              <!-- /.info-box -->
            </div>
            <!-- /.col -->
            <div class="col-12 col-sm-6 col-md-3">
              <div class="info-box mb-3">
                <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-users"></i></span>

                <div class="info-box-content">
                  <span class="info-box-text">Nombre de colis livré aujourd'hui</span>
                  <span class="info-box-number">
                    <a href="analytics/vue_generale_colis_livres.php"><?php echo $nombreColisLivre_aujourdhui; ?> 
                    <small> Colis livrés</small>
                    </a>
                  </span>
                </div>
                <!-- /.info-box-content -->
              </div>
              <!-- /.info-box -->
            </div>
            <!-- /.col -->
          </div>
          <!-- /.row -->

          <div class="row">
            <div class="col-md-12">
              <div class="card">
                <div class="card-header">
                  <h5 class="card-title">Points par Clients</h5>
                  <div class="card-tools">
                    <a href="#" class="btn btn-tool btn-sm">
                      <i class="fas fa-download"></i>
                    </a>
                    <a href="#" class="btn btn-tool btn-sm">
                      <i class="fas fa-bars"></i>
                    </a>
                  </div>
                </div>
                <div class="card-body table-responsive p-0">
                  <table class="table table-striped table-valign-middle">
                    <thead>
                      <tr>
                        <th>Clients</th>
                        <th>Montant Global</th>
                        <th>Gain  livraison</th>
                        <th>Versements</th>
                        <th>Nbre de colis Récu</th>
                        <th>Nbre de livré</th>
                        <th>Nbre de colis non Livré</th>
                      </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($pointParclients as $pointParclient) : ?>
                      <tr>
                        <td>
                        
                       <a href="colis_livres_clients.php?id=<?= $pointParclient['utilisateur_id'] ?>">                 
                        <?= $pointParclient['boutique_nom'] ?>
                        </a> 
                  
                        </td>
                        <td><?= $pointParclient['total_amount'] ?></td>
                        <td><?= $pointParclient['total_cout_livraison'] ?></td>
                        <td><?= $pointParclient['total_cout_reel'] ?></td>
                        <td>
                        <a href="analytics/vuegenerale_colis_livres_clients.php?id=<?= $pointParclient['utilisateur_id'] ?>">                 
                        <?= $pointParclient['total_orders'] ?>
                        </a>                    
                      </td>
                        <td>
                        <a href="analytics/vuegenerale_colis_livres_clients.php?id=<?= $pointParclient['utilisateur_id'] ?>">                 
                        <?= $pointParclient['total_delivered_orders'] ?>
                        </a>                    
                      </td>                      
                        <td>
                        <a href="analytics/vuegenerale_colis_nonlivres_clients.php?id=<?= $pointParclient['utilisateur_id'] ?>">
                        <?= $pointParclient['total_undelivered_orders'] ?>
                        </a>
                      </td>
                      </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>


              <div class="card">
                <div class="card-header bg-primary">
                  <h5 class="card-title">Versement</h5>
                  <div class="card-tools">
                    <a href="#" class="btn btn-tool btn-sm">
                      <i class="fas fa-download"></i>
                    </a>
                    <a href="#" class="btn btn-tool btn-sm">
                      <i class="fas fa-bars"></i>
                    </a>
                  </div>
                </div>
                <div class="card-body table-responsive p-0">
                  <table class="table table-striped table-valign-middle table-info">
                    <thead>
                      <tr>
                        <th style="color: black">Nom  du livreur</th>
                        <th style="color: black">Montant Global</th>
                        <th style="color: black">Dépenses</th>
                        <th style="color: black">Montant à remettre</th>
                      </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($pointParlivreurs as $pointParlivreur) : ?>
                      <tr>
                        
                      <td style="color: black;">
                        
                        <a href="colis_livres_clients_livreurs.php?id=<?= $pointParlivreur['livreur_id'] ?>">                 
                         <?= $pointParlivreur['fullname'] ?>
                         </a> 
                   
                      </td>
                      <td style="color: black;"><?= $pointParlivreur['cout_global'] ?></td>
                      <td style="color: black;"><?= $pointParlivreur['depense'] ?></td>
                      <td style="color: black;"><?= $pointParlivreur['montant_a_remettre'] ?></td>
                      </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- /.card -->
            </div>
            <!-- /.col -->
          </div>
          <!-- /.row -->

          <!-- Main row -->
          <div class="row">
            <!-- Left col -->
            <div class="col-md-8">
              <!-- MAP & BOX PANE -->
              <div class="card">
                <!-- /.card-header -->
                <div class="card-body p-0">
                  <div class="d-md-flex">


                  </div><!-- /.d-md-flex -->
                </div>
                <!-- /.card-body -->
              </div>
              <!-- /.card -->

              <!-- /.card-header -->
              <!-- /.card-body -->
              <!-- /.card-footer -->
            </div>
            <!--/.card -->
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->

        <!-- TABLE: LATEST ORDERS -->
        <div class="card">
          <div class="card-header border-transparent">
            <h3 class="card-title">Point livreur</h3>

            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
              </button>
              <button type="button" class="btn btn-tool" data-card-widget="remove">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>
          <!-- /.card-header -->
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table m-0">
                <thead>
                  <tr>
                    <th>Livreur</th>
                    <th>Recette</th>
                    <th>Dépense</th>
                    <th>Gain</th>
                  </tr>
                </thead>
                <tbody>
                <?php foreach ($pointParlivreur_gains as $pointParlivreur_gain) : ?>
                  <tr>
                    <td>              
                        <?= $pointParlivreur_gain['nom_livreur'] ?>
                  </td>
                    <td>
                    <?= $pointParlivreur_gain['somme_cout_livraison'] ?>
                    </td>
                    <td><span class="badge badge-success"><?= $pointParlivreur_gain['somme_depenses'] ?></span></td>
                    <td>
                      <div class="sparkbar" data-color="#00a65a" data-height="20"><?= $pointParlivreur_gain['gain_par_livreur'] ?></div>
                    </td>
                  </tr>

                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <!-- /.table-responsive -->
          </div>
          <!-- /.card-body -->
          <!-- /.card-footer -->
        </div>
        <!-- /.card -->
    </div>
    <!-- /.col -->
  </div>
  <!-- /.row -->
  </div>
  <!--/. container-fluid -->
  </section>
  <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->

  <!-- Main Footer -->
  </div>
  <!-- ./wrapper -->

  <!-- REQUIRED SCRIPTS -->
  <!-- jQuery -->
  <script src="../../plugins/jquery/jquery.min.js"></script>
  <!-- Bootstrap -->
  <script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <!-- overlayScrollbars -->
  <script src="../../plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
  <!-- AdminLTE App -->
  <script src="../../dist/js/adminlte.js"></script>

  <!-- PAGE PLUGINS -->
  <!-- jQuery Mapael -->
  <!-- ChartJS -->
  <script src="../../plugins/chart.js/Chart.min.js"></script>

  <!-- AdminLTE for demo purposes -->

</body>

</html>

</html>
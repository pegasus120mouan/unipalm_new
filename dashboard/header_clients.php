<?php
require_once '../inc/functions/connexion.php';


//session_start();


if (!isset($_SESSION['user_id'])) {
    // Redirigez vers la page de connexion si l'utilisateur n'est pas connecté
    header("Location: ../index.php");
    exit();
  }

  $id_user=$_SESSION['user_id'];

  // Calculez le montant à remettre aux clients
  $sql_client_cout_reel = "SELECT SUM(cout_reel) AS total_cout_reel FROM commandes 
  WHERE utilisateur_id=:id_user 
  AND date_commande=CURRENT_DATE
  AND statut='Livré'";
  $requClientCr = $conn->prepare($sql_client_cout_reel);
  $requClientCr->bindParam(':id_user', $id_user, PDO::PARAM_INT);
  $requClientCr->execute();
  $head_requClientCr = $requClientCr->fetch(PDO::FETCH_ASSOC);


  // Le nombre de colis donné
  $sql_client_cout_colis = "SELECT COUNT(*) AS total_colis FROM commandes 
  WHERE utilisateur_id=:id_user 
  AND date_commande=CURRENT_DATE";
  $requNbreCl = $conn->prepare($sql_client_cout_colis);
  $requNbreCl->bindParam(':id_user', $id_user, PDO::PARAM_INT);
  $requNbreCl->execute();
  $head_requNbreCl= $requNbreCl->fetch(PDO::FETCH_ASSOC);


// Le nombre de colis livré
  $sql_client_colis_livre = "SELECT COUNT(*) AS total_colis_livre FROM commandes 
  WHERE utilisateur_id=:id_user 
  AND date_commande=CURRENT_DATE
  AND statut='Livré'";
  $requNbreCll = $conn->prepare($sql_client_colis_livre);
  $requNbreCll->bindParam(':id_user', $id_user, PDO::PARAM_INT);
  $requNbreCll->execute();
  $head_requNbreCll= $requNbreCll->fetch(PDO::FETCH_ASSOC);



  // Le nombre de colis non livré
  $sql_client_colis_non_livre = "SELECT COUNT(*) AS total_colis_non_livre FROM commandes 
  WHERE utilisateur_id=:id_user 
  AND date_commande=CURRENT_DATE
  AND statut='Non Livré'";
  $requNbreClnl = $conn->prepare($sql_client_colis_non_livre);
  $requNbreClnl->bindParam(':id_user', $id_user, PDO::PARAM_INT);
  $requNbreClnl->execute();
  $head_requNbreClnl= $requNbreClnl->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!--<meta http-equiv="refresh" content="60;url=clients_dashboard.php">-->
  <title>Tableau de bord</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Tempusdominus Bootstrap 4 -->
  <link rel="stylesheet" href="../plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="../plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- JQVMap -->
  <link rel="stylesheet" href="../plugins/jqvmap/jqvmap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../dist/css/adminlte.min.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="../plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="../plugins/daterangepicker/daterangepicker.css">
  <!-- summernote -->
  <link rel="stylesheet" href="../plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">
  <link rel="stylesheet" href="../plugins/summernote/summernote-bs4.min.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <!-- Preloader 
  <div class="preloader flex-column justify-content-center align-items-center">
    <img class="animation__shake" src="dist/img/AdminLTELogo.png" alt="AdminLTELogo" height="60" width="60">
  </div>-->

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="clients_dashboard.php" class="nav-link">Acceuil</a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="clients_dashboard.php" class="nav-link">Mes commandes</a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="../logout.php" class="nav-link">Déconnexion</a>
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
    <a href="index3.html" class="brand-link">
    <img src="../../dist/img/logo.png" alt="OVL Delivery Services" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">OVL Delivery Services</span>
    </a>
    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
        <img src="../dossiers_images/<?php echo $_SESSION['avatar']; ?> "  class="img-circle elevation-2"  alt="Logo">
          <!-- <img src="../../dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">-->
        </div>
        <div class="info">
        <a href="client_profile.php" class="d-block"><?php echo $_SESSION['nom']; ?>  <?php echo $_SESSION['prenoms']; ?></a>
        </div>
      </div>
      <!--<script src="dossier_photos/de.jpg"-->
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
                <a href="clients_dashboard.php" class="nav-link active">
                  <i class="fas fa-coins"></i>
                  <p>Bilan aujourd'hui</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="vue_general_hier.php" class="nav-link">
                  <i class="fas fa-money-bill"></i>
                  <p>Bilan Hier</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item">
              <a href="#" class="nav-link">
                <i class="nav-icon fas fa-chart-pie"></i>
                <p>
                  Mes colis
                  <i class="right fas fa-angle-left"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="clients_dashboard.php" class="nav-link">
                    <i class="fas fa-file-alt"></i>
                    <p>Liste des colis</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="colis_livres.php" class="nav-link">
                    <i class="fa fa-motorcycle"></i>
                    <p>Colis Livrées</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="colis_non_livres.php" class="nav-link">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Colis non Livrés</p>
                  </a>
                </li>
              </ul>
            </li>

          <li class="nav-header">CAISSE</li>
          <li class="nav-item">
            <a href="statistiques_clients.php" class="nav-link">
              <i class="nav-icon far fa-calendar-alt"></i>
              <p>
                Statistiques
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
            <h1 class="m-0">Tableau de bord</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Acceuil</a></li>
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
        <!-- Small boxes (Stat box) -->
        <div class="row">
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-info">
              <div class="inner">
              <h3><?php echo ($head_requClientCr['total_cout_reel'] == 0) ? '0' : $head_requClientCr['total_cout_reel']; ?></h3>
                <p>Montant du jour</p>
              </div>
              <div class="icon">
                <i class="ion-monitor"></i>
              </div>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-success">
              <div class="inner">
              <h3><?php echo ($head_requNbreCl['total_colis'] == 0) ? '0' : $head_requNbreCl['total_colis']; ?></h3>
                <p>Nombre de colis donnés</p>
              </div>
              <div class="icon">
                <i class="ion ion-stats-bars"></i>
              </div>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-warning">
              <div class="inner">
              <h3><?php echo ($head_requNbreCll['total_colis_livre'] == 0) ? '0' : $head_requNbreCll['total_colis_livre']; ?></h3>
                <p>Total colis livrés</p>
              </div>
              <div class="icon">
                <i class="ion ion-person-add"></i>
              </div>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-danger">
              <div class="inner">
              <h3><?php echo ($head_requNbreClnl['total_colis_non_livre'] == 0) ? '0' : $head_requNbreClnl['total_colis_non_livre']; ?></h3>
                <p>Total colis non Livré</p>
              </div>
              <div class="icon">
                <i class="ion ion-pie-graph"></i>
              </div>
            </div>
          </div>
          <!-- ./col -->
        </div>
        <!-- /.row -->
<?php
header('Content-Type: text/html; charset=UTF-8');

setlocale(LC_TIME, 'fr_FR.utf8', 'fra');  // Force la configuration en français

require_once '../inc/functions/connexion.php';
require_once '../inc/functions/get_solde.php';

// Nombre de ticket Total
$sql_ticket_total = "SELECT COUNT(id_ticket) AS nb_ticket_tt FROM tickets";
$requete_tt = $conn->prepare($sql_ticket_total);
$requete_tt->execute();
$ticket_total = $requete_tt->fetch(PDO::FETCH_ASSOC);

// Nombre de ticket en attente
$sql_ticket_nv = "SELECT COUNT(id_ticket) AS nb_ticket_nv FROM tickets WHERE  date_validation_boss IS NULL";
$requete_tnv = $conn->prepare($sql_ticket_nv);
$requete_tnv->execute();
$ticket_non_valide = $requete_tnv->fetch(PDO::FETCH_ASSOC);

// Nombre de tickets validés
$sql_ticket_v = "SELECT COUNT(id_ticket) AS nb_ticket_nv FROM tickets
WHERE date_validation_boss IS NOT NULL";
$requete_tv = $conn->prepare($sql_ticket_v);
$requete_tv->execute();
$ticket_valide = $requete_tv->fetch(PDO::FETCH_ASSOC);

// Nombre de colis tickés payes
$sql_ticket_paye = "SELECT COUNT(id_ticket) AS nb_ticket_paye FROM tickets WHERE date_paie IS NULL AND date_validation_boss IS NOT NULL";
$requete_tpaye = $conn->prepare($sql_ticket_paye);
$requete_tpaye->execute();
$ticket_paye = $requete_tpaye->fetch(PDO::FETCH_ASSOC);

$solde_caisse = getSoldeCaisse();

if (!isset($_SESSION['user_id'])) {
    // Redirigez vers la page de connexion si l'utilisateur n'est pas connecté
    header("Location: ../index.php");
    exit();
}

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
  <!-- Select2 -->
  <link href="../../plugins/select2/css/select2.min.css" rel="stylesheet" />
  <link href="../../plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css" rel="stylesheet" />
  
  <!-- Scripts nécessaires -->
  <script src="../../plugins/jquery/jquery.min.js"></script>
  <script src="../../plugins/select2/js/select2.full.min.js"></script>

  <style>
    .select2-container .select2-selection--single {
        height: 38px !important;
        background-color: #fff !important;
        border: 1px solid #ced4da !important;
        border-radius: 4px !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1) !important;
    }

    .select2-container .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        padding-left: 12px !important;
        color: #495057 !important;
    }

    .select2-container .select2-selection--single .select2-selection__placeholder {
        color: #6c757d !important;
    }

    .select2-container .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
        width: 30px !important;
        right: 3px !important;
    }

    .select2-container .select2-dropdown {
        border: 1px solid #ced4da !important;
        border-radius: 4px !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
    }

    .select2-container .select2-results__option {
        padding: 8px 12px !important;
        font-size: 1rem !important;
    }

    .select2-container .select2-results__option--highlighted[aria-selected] {
        background-color: #f8f9fa !important;
        color: #16181b !important;
    }

    .select2-container .select2-results__option[aria-selected=true] {
        background-color: #e9ecef !important;
    }

    .select2-container--open {
        z-index: 9999 !important;
    }
  </style>

  <script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>

    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link href="https://api.mapbox.com/mapbox-gl-js/v3.8.0/mapbox-gl.css" rel="stylesheet">

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
          <a href="tickets.php" class="nav-link">Acceuil</a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
          <a href="tickets.php" class="nav-link">Les Tickets</a>
        </li>

        <li class="nav-item d-none d-sm-inline-block">
          <a href="paiements.php" class="nav-link">Paiements</a>
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
          <a class="nav-link text-danger" href="../logout.php" role="button">
            <i class="fas fa-power-off fa-lg"></i> <!-- Ajout de fa-lg pour une taille plus grande -->
          </a>
        </li>
      </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
      <!-- Brand Logo -->
      <a href="tickets.php" class="brand-link">
        <img src="../../dist/img/logo.png" alt="Unipalm" class="brand-image img-circle elevation-3"
          style="opacity: .8">
        <span class="brand-text font-weight-light">Unipalm</span>
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
                  Mes tickets
                  <i class="right fas fa-angle-left"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="tickets.php" class="nav-link active">
                    <i class="fas fa-ticket-alt"></i>
                    <p>Liste des tickets</p>
                  </a>
                </li>

                <li class="nav-item">
                  <a href="tickets_jour.php" class="nav-link">
                    <i class="fas fa-calendar-day"></i>
                    <p>Tickets du jour</p>
                  </a>
                </li>

                <li class="nav-item">
                  <a href="tickets_attente.php" class="nav-link">
                    <i class="fas fa-clock"></i>
                    <p>Tickets en Attente</p>
                  </a>
                </li>

                <li class="nav-item">
                  <a href="tickets_valides.php" class="nav-link">
                    <i class="fas fa-check-circle"></i>
                    <p>Tickets en Validés</p>
                  </a>
                </li>

                <li class="nav-item">
                  <a href="tickets_payes.php" class="nav-link">
                    <i class="fas fa-money-bill-wave"></i>
                    <p>Tickets Payés</p>
                  </a>
                </li>

                <li class="nav-item">
                  <a href="tickets_modifications.php" class="nav-link">
                    <i class="fas fa-edit"></i>
                    <p>Modifications de tickets</p>
                  </a>
                </li>

                <li class="nav-item">
                  <a href="recherche_trie.php" class="nav-link">
                    <i class="fas fa-search"></i>
                    <p>Recherche avancée</p>
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
                  <a href="utilisateurs.php" class="nav-link">
                    <i class="fas fa-male"></i>
                    <p>Listes des utilisateurs</p>
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
              <a href="#" class="nav-link">
                <i class="nav-icon fas fa-cogs"></i>
                <p>
                  Gestion
                  <i class="fas fa-angle-left right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="chef_equipe.php" class="nav-link">
                    <i class="fas fa-users"></i>
                    <p>Gestion chef equipe</p>
                  </a>
                </li>

                <li class="nav-item">
                  <a href="agents.php" class="nav-link">
                    <i class="fas fa-user-plus"></i>
                    <p>Gestion des agents</p>
                  </a>
                </li>

                <li class="nav-item">
                  <a href="usines.php" class="nav-link">
                    <i class="fas fa-industry"></i>
                    <p>Gestion des usines</p>
                  </a>
                </li>

                <li class="nav-item">
                  <a href="vehicules.php" class="nav-link">
                    <i class="fas fa-car"></i>
                    <p>Gestion des véhicules</p>
                  </a>
                </li>
              </ul>
            </li>

            <li class="nav-item">
              <a href="#" class="nav-link">
                <i class="nav-icon fas fa-money-bill-alt"></i>
                <p>
                  Gestion financière
                  <i class="fas fa-angle-left right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="prix_unitaires.php" class="nav-link">
                    <i class="fas fa-tags"></i>
                    <p>Prix unitaires</p>
                  </a>
                </li>

                <li class="nav-item">
                  <a href="bordereaux.php" class="nav-link">
                    <i class="fas fa-file-invoice"></i>
                    <p>Bordereaux</p>
                  </a>
                </li>

                <li class="nav-item">
                  <a href="financements.php" class="nav-link">
                    <i class="fas fa-money-bill-wave"></i>
                    <p>Financements</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="gestion_usines.php" class="nav-link">
                    <i class="fas fa-money-bill-wave"></i>
                    <p>Montant Usines</p>
                  </a>
                </li>
                
                <li class="nav-item">
                  <a href="recus.php" class="nav-link">
                    <i class="fas fa-receipt"></i>
                    <p>Reçus</p>
                  </a>
                </li>

                <li class="nav-item">
                  <a href="divers.php" class="nav-link">
                    <i class="fas fa-money-bill-wave-alt"></i>
                    <p>Sorties diverses</p>
                  </a>
                </li>
              </ul>
            </li>

            <li class="nav-item">
              <a href="#" class="nav-link">
                <i class="nav-icon fas fa-door-open"></i>
                <p>
                  Gestion des sorties
                  <i class="fas fa-angle-left right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="demandes.php" class="nav-link">
                    <i class="fas fa-list nav-icon"></i>
                    <p>Liste des demandes</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="demande_attente.php" class="nav-link">
                    <i class="fas fa-check-circle nav-icon"></i>
                    <p>Demandes en attente</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="demande_valide.php" class="nav-link">
                    <i class="fas fa-check-circle nav-icon"></i>
                    <p>Demandes validées</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="divers.php" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Divers</p>
                  </a>
                </li>
              </ul>
            </li>

            <li class="nav-header"><strong>TRANSACTIONS</strong></li>
            <li class="nav-item">
              <a href="approvisionnement.php" class="nav-link">
                <i class="fas fa-truck-loading"></i>
                <p>
                  Approvisionnement
                  <span class="badge badge-info right">2</span>
                </p>
              </a>
            </li>

            <li class="nav-item">
              <a href="paiements.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'paiements.php' ? 'active' : '' ?>">
                <i class="nav-icon fas fa-money-bill"></i>
                <p>Paiements de tickets et bordereaux</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="paiements_demande.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'paiements_demande.php' ? 'active' : '' ?>">
                <i class="nav-icon fas fa-money-bill"></i>
                <p>Paiements demandes</p>
              </a>
            </li>
            
            <li class="nav-item">
              <a href="recus.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'recus.php' ? 'active' : '' ?>">
                <i class="nav-icon fas fa-receipt"></i>
                <p>Reçus des paiements</p>
              </a>
            </li>

            <li class="nav-item">
              <a href="recus_demandes.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'recus_demandes.php' ? 'active' : '' ?>">
                <i class="nav-icon fas fa-receipt"></i>
                <p>Reçus des demandes</p>
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
                  <h3><?php echo $ticket_total['nb_ticket_tt'];   ?>
                </h3>
                <p>Nombre ticket Total</strong></p>

                </div>
              </div>
            </div>
            
            <!-- ./col -->
            <div class="col-lg-3 col-6">
              <!-- small box -->
              <div class="small-box bg-danger">
                <div class="inner">
                <h3><?php echo $ticket_non_valide['nb_ticket_nv'];?>

               </h3>
               <p>Nombre de tickets en <strong>attente</strong></p>
                </div>
              </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-6">
              <!-- small box -->
              <div class="small-box bg-warning">
                <div class="inner">
                <h3><?php echo $ticket_valide['nb_ticket_nv'];?>
                </h3>
                <p>Total tickets <strong>validés</strong></p>

                </div>
                 </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-6">
              <!-- small box -->
              <div class="small-box bg-danger">
                <div class="inner">
                 <h3><?php echo $ticket_paye['nb_ticket_paye'];?>
                </h3>
                <p>Nombre de ticket <strong>VALIDES et non payés</strong></p>
                </div>
              </div>
            </div>
            <!-- ./col -->
          </div>
          <!-- /.row -->
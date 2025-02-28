<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="col-md-3 left_col">
    <div class="left_col scroll-view">
        <div class="navbar nav_title" style="border: 0;">
            <a href="index.php" class="site_title"><i class="fa fa-paw"></i> <span>UNIPALM</span></a>
        </div>

        <div class="clearfix"></div>

        <!-- menu profile quick info -->
        <div class="profile clearfix">
            <div class="profile_pic">
                <img src="images/img.jpg" alt="..." class="img-circle profile_img">
            </div>
            <div class="profile_info">
                <span>Bienvenue,</span>
                <h2><?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Utilisateur'; ?></h2>
            </div>
        </div>
        <!-- /menu profile quick info -->

        <br />

        <!-- sidebar menu -->
        <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
            <div class="menu_section">
                <h3>General</h3>
                <ul class="nav side-menu">
                    <li><a href="index.php"><i class="fa fa-home"></i> Tableau de bord </a></li>
                    <li><a><i class="fa fa-ticket"></i> Tickets <span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">
                            <li><a href="tickets.php">Liste des tickets</a></li>
                            <li><a href="tickets_valides.php">Tickets validés</a></li>
                            <li><a href="tickets_rejetes.php">Tickets rejetés</a></li>
                        </ul>
                    </li>
                    <li><a><i class="fa fa-truck"></i> Véhicules <span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">
                            <li><a href="vehicules.php">Liste des véhicules</a></li>
                            <li><a href="vehicules_maintenance.php">En maintenance</a></li>
                        </ul>
                    </li>
                    <li><a href="utilisateurs.php"><i class="fa fa-users"></i> Utilisateurs </a></li>
                    <li><a><i class="fa fa-bar-chart"></i> Rapports <span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">
                            <li><a href="rapport_journalier.php">Rapport journalier</a></li>
                            <li><a href="rapport_mensuel.php">Rapport mensuel</a></li>
                            <li><a href="rapport_annuel.php">Rapport annuel</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
        <!-- /sidebar menu -->
    </div>
</div>

<!-- top navigation -->
<div class="top_nav">
    <div class="nav_menu">
        <div class="nav toggle">
            <a id="menu_toggle"><i class="fa fa-bars"></i></a>
        </div>
        <nav class="nav navbar-nav">
            <ul class=" navbar-right">
                <li class="nav-item dropdown open" style="padding-left: 15px;">
                    <a href="javascript:;" class="user-profile dropdown-toggle" aria-haspopup="true" id="navbarDropdown" data-toggle="dropdown" aria-expanded="false">
                        <img src="images/img.jpg" alt=""><?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Utilisateur'; ?>
                    </a>
                    <div class="dropdown-menu dropdown-usermenu pull-right" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="profile.php"> Profile</a>
                        <a class="dropdown-item" href="logout.php"><i class="fa fa-sign-out pull-right"></i> Déconnexion</a>
                    </div>
                </li>
            </ul>
        </nav>
    </div>
</div>
<!-- /top navigation -->

<script>
$(document).ready(function() {
    // Gestion du clic sur les menus
    $('.side-menu li a').on('click', function(e) {
        if($(this).next('ul').length) {
            e.preventDefault();
            // Ferme les autres menus
            $('.side-menu li a').not(this).next('ul').slideUp(300);
            $('.side-menu li a').not(this).find('.fa-chevron-down').removeClass('rotate');
            
            // Toggle le menu actuel
            $(this).next('ul').slideToggle(300);
            $(this).find('.fa-chevron-down').toggleClass('rotate');
        }
    });
});
</script>

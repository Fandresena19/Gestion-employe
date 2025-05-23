<?php
session_start();

include('bd.php');

if (!isset($_SESSION['Matricule_emp'])) {
  header('location:../../index.php');
  exit();
}

$id_user = $_SESSION['Matricule_emp'];

$sql = "SELECT nom_emp, prenom_emp FROM employer_login WHERE matricule_emp = :id_user";
$stmt = $bdd->prepare($sql);
$stmt->execute(['id_user' => $id_user]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Count unread notifications
$sql_notif = "SELECT COUNT(*) as unread_count FROM notifications 
WHERE matricule_emp = :id_user AND statut_notif = 'non lu'";
$stmt_notif = $bdd->prepare($sql_notif);
$stmt_notif->execute(['id_user' => $id_user]);
$notif_count = $stmt_notif->fetch(PDO::FETCH_ASSOC)['unread_count'];

// Afficher le message de succès s'il existe
if (isset($_SESSION['success'])) {
  echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
  unset($_SESSION['success']); // Supprimer le message après affichage
}

// Afficher le message d'erreur s'il existe
if (isset($_SESSION['error'])) {
  echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
  unset($_SESSION['error']); // Supprimer le message après affichage
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../../bootstrap4/css/bootstrap.min.css">
  <link rel="stylesheet" href="../../bootstrap4/boxicons-2.1.4/css/boxicons.min.css">
  <!-- <link rel="stylesheet" href="../../css/style_nav.css">
  <link rel="stylesheet" href="../../css/style_profil.css">
  <link rel="stylesheet" href="../../css/modal.css"> -->
  <link rel="stylesheet" href="../../css/css_complet.css">
  <title><?= $user['nom_emp'] . " " . $user['prenom_emp']; ?></title>

</head>

<body id="body_profil">

  <div class="container-fluid">
    <form action="../traitement/edition_emp.php" method="post" enctype="multipart/form-data">
      <div id="container">

        <!-- Sidebar -->
        <nav class="sidebar" id="side_nav">
          <header>
            <div class="image-text">
              <span class="image">
                <img src="../../Icone/Logo.png" alt="">
              </span>

              <div class="text header-text">
                <span class="employe">Employé</span>
              </div>
            </div>

            <i class="bx bx-chevron-right toggle"></i>
          </header>

          <div class="menu-bar">
            <div class="menu">
              <ul class="list-unstyled nav-links">
                <a href="./pageemp.php">
                  <li class="active"><img src="../../Icone/user.png" alt="" width="25px" class="icone"><span>Profil</span></li>
                </a>
                <a href="./mes_conge.php">
                  <li><img src="../../Icone/leave.png" alt="" width="20px" class="icone"><span>Congé</span></li>
                </a>
                <a href="./mes_permission.php">
                  <li><img src="../../Icone/permission.png" alt="" width="20px" class="icone"><span>Permission</span></li>
                </a>
                <a href="../vue/mes_timesheet.php">
                  <li><img src="../../Icone/timesheet.png" alt="" width="20px" class="icone"><span>Timesheet</span></li>
                </a>
                <a href="./historique.php">
                  <li><img src="../../Icone/historique.png" alt="" width="20px" class="icone"><span>Historique</span></li>
                </a>

                <a href="../vue/notif.php">
                  <li>
                    <div class="notification-badge">
                      <img src="../../Icone/notif.png" alt="" width="20px" class="icone">
                      <?php if ($notif_count > 0): ?>
                        <span class="badge"><?= $notif_count ?></span>
                      <?php endif; ?>
                    </div>
                    <span>Notifications</span>
                  </li>
                </a>

                <a href="#">
                  <li onclick="document.getElementById('id01').style.display='block'"><img src="../../Icone/logout.png" alt="" width="-25px" height="20px" class="icone"><span>Deconnexion</span></li>
                </a><br>

              </ul>
            </div>
          </div>
        </nav>
        <!-- /Sidebar -->

        <div id="id01" class="modal">
          <span onclick="document.getElementById('id01').style.display='none'" class="close">×</span>
          <form class="modal-content" action="">
            <div class="container">
              <h1>Deconnexion</h1>
              <p>Voulez-vous vraiment se deconnecter?</p>

              <div class="clearfix">
                <a href="../../other/logout.php"><button type="button" onclick="document.getElementById('id01').style.display='none'" class="deletebtn">Oui</button></a>
                <button type="button" onclick="document.getElementById('id01').style.display='none'" class="cancelbtn">Non</button>
              </div>
            </div>
          </form>
        </div>


        <div class="dashboard" id="Main">
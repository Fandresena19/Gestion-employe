<?php
session_start();

include('bd.php');

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../../bootstrap4/css/bootstrap.min.css">
  <link rel="stylesheet" href="../../bootstrap4/boxicons-2.1.4/css/boxicons.min.css">
  <link rel="stylesheet" href="../../css/style_nav.css">
  <link rel="stylesheet" href="../../css/conge.css">
  <link rel="stylesheet" href="../../css/modif_emp.css">
  <link rel="stylesheet" href="../../css/modal.css">
  <title>Responsable</title>

</head>

<body>

  <div class="container-fluid">
    <div id="container">

      <nav class="sidebar" id="side_nav">
        <header>
          <div class="image-text">
            <span class="image">
              <img src="../../Icone/Logo.png" alt="">
            </span>

            <div class="text header-text">
              <span class="resplonsable">Responsable</span>
            </div>
          </div>

          <i class="bx bx-chevron-right toggle"></i>
        </header>

        <div class="menu-bar">
          <div class="menu">

            <ul class="list-unstyled nav-links">
              <a href="../vue/pageresponsable.php">
                <li><img src="../../Icone/home.png" alt="" width="25px" class="icone"><span>Profil</span></li>
              </a>
              <a href="../vue/liste_employe.php">
                <li><img src="../../Icone/user.png" alt="" width="25px" class="icone"><span>Employés</span></li>
              </a>
              <a href="../vue/liste_conge.php">
                <li><img src="../../Icone/leave.png" alt="" width="20px" class="icone"><span>Congé</span></li>
              </a>
              <a href="../vue/liste_permission.php">
                <li class=""><img src="../../Icone/permission.png" alt="" width="20px" class="icone"><span>Permission</span></li>
              </a>
              <a href="../vue/detail_timesheet.php">
                <li><img src="../../Icone/timesheet.png" alt="" width="20px" class="icone"><span>Timesheet</span></li>
              </a>
              <a href="../vue/notifications_responsable.php">
                <li><img src="../../Icone/notif.png" alt="" width="20px" class="icone"><span>Timesheet</span></li>
              </a>
              <a href="#">
                <li onclick="document.getElementById('id01').style.display='block'"><img src="../../Icone/logout.png" alt="" width="-25px" height="20px" class="icone"><span>Deconnexion</span></li>
              </a><br>

            </ul>
          </div>
        </div>
      </nav>

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
<?php
session_start();
include('bd.php');

if (!isset($_SESSION['matricule_emp'])) {
  header('location:../../index.php');
  exit();
}

$id_user = $_SESSION['matricule_emp'];

$sql = "SELECT nom_emp, prenom_emp FROM employer_login WHERE matricule_emp = :id_user";
$stmt = $bdd->prepare($sql);
$stmt -> execute([':id_user => $id_user']);


?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../../bootstrap4/css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="../../bootstrap4/boxicons-2.1.4/css/boxicons.min.css">
  <link rel="stylesheet" href="../../css/style_nav.css">
  <link rel="stylesheet" href="../../css/conge.css">
  <link rel="stylesheet" href="../../css/modal.css">
  <link rel="stylesheet" href="../../css/modif_emp.css">
  <title>Employé</title>

  <style>
    tbody tr:nth-child(even) {
      background-color: rgb(91, 91, 91);
    }

    .conge-summary {
      background-color: #6a6363bf;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 20px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .conge-summary h4 {
      color: #e0e0e0ce;
      margin-bottom: 10px;
    }

    .conge-detail {
      display: flex;
      justify-content: space-around;
      flex-wrap: wrap;
    }

    .conge-item {
      text-align: center;
      padding: 8px;
      margin: 5px;
      min-width: 150px;
      border-radius: 5px;
    }

    .conge-item.taken {
      background-color: #f0ad4e;
      max-height: 50px;
    }

    .conge-item.remaining {
      background-color: #5cb85c;
      max-height: 50px;
      color: white;
    }

    .conge-item.quota {
      background-color: #5bc0de;
      max-height: 50px;
      color: white;
    }
  </style>
</head>

<body>

  <div class="container-fluid" id="container">

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
            <a href="../vue/pageemp.php">
              <li><img src="../../Icone/user.png" alt="" width="25px" class="icone"><span>Profil</span></li>
            </a>
            <a href="../vue/mes_conge.php">
              <li><img src="../../Icone/leave.png" alt="" width="20px" class="icone"><span>Congé</span></li>
            </a>
            <a href="../vue/mes_permission.php">
              <li><img src="../../Icone/permission.png" alt="" width="20px" class="icone"><span>Permission</span></li>
            </a>
            <a href="../vue/mes_timesheet.php">
              <li><img src="../../Icone/timesheet.png" alt="" width="20px" class="icone"><span>Timesheet</span></li>
            </a>
            <a href="../vue/historique.php">
              <li><img src="../../Icone/historique.png" alt="" width="20px" class="icone"><span>Historique</span></li>
            </a>
            <a href="../vue/notif.php">
              <li><img src="../../Icone/notif.png" alt="" width="20px" class="icone"><span>Notifications</span></li>
            </a>
            <a href="#">
              <li onclick="document.getElementById('id01').style.display='block'">
                <img src="../../Icone/logout.png" alt="" width="-25px" height="20px" class="icone"><span>Deconnexion</span>
              </li>
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
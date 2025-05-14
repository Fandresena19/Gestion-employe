<?php
  session_start();
  include('../other/head.php');
  $donnees = $bdd->query('SELECT * from employer_login e join conge c on e.matricule_emp=c.matricule_emp ORDER BY c.date_demande DESC ');

  if (!isset($_SESSION['Matricule_resp'])) {
    header('location:../index.php');
    exit();
  }

  ?>
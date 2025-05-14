<?php
  include('../other/head.php');
  $matricule_emp = $_SESSION['Matricule_emp'];


  $sql = "SELECT * FROM conge c JOIN employer_login e on c.matricule_emp=e.matricule_emp
 WHERE e.matricule_emp= :matricule_emp AND c.statut_conge = 'Validé' ORDER BY c.date_demande ASC ";
  $stmt = $bdd->prepare($sql);
  $stmt->execute(['matricule_emp' => $matricule_emp]);

  if (!isset($_SESSION['Matricule_emp'])) {
    header('location:../index.php');
    exit();
  }
  ?>
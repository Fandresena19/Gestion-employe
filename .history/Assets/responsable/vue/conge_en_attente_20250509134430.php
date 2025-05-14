<style>
  tbody tr:nth-child(even) {
    background-color: rgb(91, 91, 91);
  }
</style>

<?php
  session_start();
  include('bd.php');
  $bdd = connect();
  $donnees = $bdd->query('select * from employer_login e join conge c on e.matricule_emp=c.matricule_emp where c.statut_conge = "En attente"');

  if (!isset($_SESSION['Matricule_resp'])) {
    header('location:../index.php');
    exit();
  }

  ?>
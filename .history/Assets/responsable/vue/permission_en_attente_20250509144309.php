<style>
  tbody tr:nth-child(even) {
    background-color: rgb(91, 91, 91);
  }
</style>

<?php
include('../other/head.php');
  $donnees = $bdd->query('select * from employer_login e join permission p on e.matricule_emp=p.matricule_emp where p.Statut_permission = "En attente"');

  ?>

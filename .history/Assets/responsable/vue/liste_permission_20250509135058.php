<?php
  include('../other/head.php');
  $donnees = $bdd->query('select * from employer_login e join permission p on e.matricule_emp=p.matricule_emp ');

  ?>
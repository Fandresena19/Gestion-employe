<?php

  function connect () {
    $bdd = new PDO ('mysql:host=localhost;dbname=gestion_conge','root','');

    return $bdd;
  }

?>
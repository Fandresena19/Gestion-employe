<?php
include('../other/bd.php');

$bdd->query("DELETE FROM conge WHERE id_conge =" .$_GET['id']);

header('location:../vue/liste_conge.php');
?>
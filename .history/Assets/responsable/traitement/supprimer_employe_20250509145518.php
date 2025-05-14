<?php
include('./bd.php');
$bdd=connect();

$bdd->query("DELETE FROM employer_login WHERE matricule_emp=" .$_GET['id']);

$bdd->query("DELETE FROM obtenir WHERE matricule_emp=" .$_GET['id']);

header('location:liste_employe.php');

?>
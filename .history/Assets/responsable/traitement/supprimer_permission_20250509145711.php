<?php
include('../other/bd.php');

$bdd->query("DELETE FROM permission where id_permission =" .$_GET['id']);

header('location:../vue/liste_permission.php');
?>
<?php
include('../other/head.php');

$employes = $bdd->query('select *from employer_login');
$employe = $_SESSION['Matricule_emp'];

$permission = $bdd->query("SELECT * FROM permission");

$date = new DateTime('now', new DateTimeZone('GMT+3'));

?>
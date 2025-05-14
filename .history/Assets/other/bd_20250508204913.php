<?php

$servername = "localhost";
$dbname = "gestion_stock";
$username = "root";
$password = '';

try{
    $bdd = new PDO("mysql:host=$servername;dbname=$dbname" ,$username , $password);
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    return $bdd;
}catch(PDOException $e){
    die('Erreur de connexion' . $e->getMessage());
}


?>
<?php
session_start();
include ('../other/bd.php');


$matricule = $_POST['im'];
$nom = $_POST['nom'];
$prenom = $_POST['prenom'];
$dateEmb = $_POST['date_emb'];
$phone = $_POST['telephone'];
$service = $_POST['serv'];
$mail = $_POST['mail'];
$mdp = $_POST['mdp'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
$Employe = ('INSERT INTO employer_login (matricule_emp, nom_emp,prenom_emp, date_embauche,telephone,id_service,mail_emp,mdp_emp)
 values (? , ?, ?, ?, ?, ?, ?, ?)');
 $stmt = $bdd->prepare($Employe);
 $stmt->execute([
    $matricule,
    $nom,
    $prenom,
    $dateEmb,
    $phone,
    $service,
    $mail,
    $mdp
 ]);


$bdd->query('INSERT INTO obtenir (matricule_emp,id_grade,date_obtention_grade) 
value ('.$_POST['im'].','.$_POST['grade'].',"'.$_POST['dateG'].'")');

header('location: ../vue/ajout_employe.php');

}else{
    echo 'Erreur ';
}

?>
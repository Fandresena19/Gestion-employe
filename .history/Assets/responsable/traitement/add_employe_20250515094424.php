<?php
include ('../other/bd.php');


$bdd->query('insert into employer_login (matricule_emp, nom_emp,prenom_emp, date_embauche,telephone,role,id_service,mail_emp,mdp_emp)
 values ('.$_POST['im'].',"'.$_POST['nom'].'","'.$_POST['prenom'].'","'.$_POST['date_emb'].'","'.$_POST['telephone'].'","'.$_POST['role'].'",'.$_POST['serv'].',
 "'.$_POST['mail'].'", "'.$_POST['mdp'].'")');
 


$bdd->query('insert into obtenir (matricule_emp,id_grade,date_obtention_grade) 
value ('.$_POST['im'].','.$_POST['grade'].',"'.$_POST['dateG'].'")');

header('location: ../vue/ajout_employe.php')


?>
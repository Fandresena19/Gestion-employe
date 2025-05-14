<?php
session_start();

include('bd.php');
$bdd = connect();


if(!empty($_POST['mail']) and !empty($_POST['mdp']) and !empty($_POST['user']))
  {
    if($_POST['user']=='resp')
      {
        $users = $bdd->query('select * from responsable where mail_resp= "'.$_POST['mail'].'"
         and mdp_resp= "'.$_POST['mdp'].'"');

        if($users-> rowCount()>0){
          $user = $users -> fetch();
          $_SESSION['Matricule_resp'] = $user['Matricule_resp'];
          $_SESSION['Nom_resp'] = $user['Nom_resp'];
          $_SESSION['Prenom_resp'] = $user['Prenom_resp'];
          $_SESSION['Mail_resp'] = $user['Mail_resp'];
          header('location:responsable/pageresponsable.php?Matricule='.$_SESSION['Matricule_resp']);
          // header('location:./responsable/profil.php?Matricule='.$_SESSION['Matricule_resp']);
        
        }else{
          header("Location: ../index.php?error=Information incorrecte");
		      exit();
        };
      }else{
        $users = $bdd->query('select * from employer_login where mail_emp= "'.$_POST['mail'].'"
         and mdp_emp= "'.$_POST['mdp'].'"');

        if($users-> rowCount()>0){
          $user = $users -> fetch();
          $_SESSION['Matricule_emp'] = $user['matricule_emp'];
          $_SESSION['Nom_emp'] = $user['Nom_emp'];
          $_SESSION['Prenom_emp'] = $user['Prenom_emp'];
          $_SESSION['Mail_emp'] = $user['Mail_emp'];
          header("location:employe/pageemp.php?Matricule=".$_SESSION["Matricule_emp"]);
        
      }else{
        header("Location: index.php?error=Information incorrecte");
		    exit();
      };
  };
}else{
  header("Location: index.php");
	exit();
};

?>
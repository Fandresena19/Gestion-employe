<?php
session_start();
include('bd.php');
$bdd = connect();

$user_id = $_SESSION['Matricule_resp'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $matricule = $_POST['im'];
  $nom = $_POST['nom'];
  $prenom = $_POST['prenom'];
  $date_embauche = $_POST['date_emb'];
  $role = $_POST['role'];
  $id_service = $_POST['serv'];
  $id_grade = $_POST['grade'];
  $date_grade = $_POST['dateG'];
  $current_password = $_POST['current_password'];

  $verify_mpd = $bdd->prepare("SELECT mdp_resp FROM responsable
  WHERE Matricule_resp = ?");
  $verify_mpd->execute([$user_id]);
  $mdp = $verify_mpd->fetch();

  if($mdp && $current_password === $mdp['mdp_resp']){

    $query = "UPDATE employer_login e JOIN obtenir o ON
    e.matricule_emp = o.matricule_emp
     SET 
    e.nom_emp = :nom,
    e.prenom_emp = :prenom,
    e.date_embauche = :date_embauche,
    e.role = :role,
    e.id_service = :id_service,
    o.id_grade = :id_grade,
    o.date_obtention_grade = :dateG
    WHERE e.matricule_emp = :matricule";

$params = [
      ':nom' => $nom,
      ':prenom' => $prenom,
      ':date_embauche' => $date_embauche,
      ':role' => $role,
      ':id_service' => $id_service,
      ':id_grade' => $id_grade,
      ':dateG' => $date_grade,
      ':matricule' => $matricule
    ];

    $stmt = $bdd->prepare($query);
    $stmt->execute($params);

    $_SESSION['success_message'] = 'Employé mis à jour avec succès !';
    header('location:liste_employe.php');
    exit;
  }elseif(empty($current_password)){
    $_SESSION['error_message'] = 'Vous devez entrer votre mot de passe pour confirmer la modification';
    header('location:liste_employe.php');
  }else{
    $_SESSION['error_message'] = 'Mot de passe actuel incorrect';
    header('location:liste_employe.php');
  }



//   $bdd->query('update employer_login set nom_emp="' . $_POST['nom'] . '",
// prenom_emp="' . $_POST['prenom'] . '",date_embauche="' . $_POST['date_emb'] . '",
// role="' . $_POST['role'] . '",
// id_service=' . $_POST['serv'] . ' where matricule_emp=' . $_POST['im']);

//   $bdd->query('update obtenir set id_grade=' . $_POST['grade'] . ',
// date_obtention_grade="' . $_POST['dateG'] . '" where matricule_emp=' . $_POST['im']);
}
?>

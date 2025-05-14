<?php
session_start();
include('../other/bd.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  
  $user_id = $_SESSION['Matricule_resp'];
  $nom = $_POST['nom'];
  $prenom = $_POST['prenom'];
  $email = $_POST['mail'];
  $current_password = $_POST['current_password'];
  $new_password = $_POST['new_password'];
  $confirm_password = $_POST['confirm_password'];

  $verify_mpd = $bdd->prepare("SELECT mdp_resp FROM responsable
  WHERE Matricule_resp = ?");
  $verify_mpd->execute([$user_id]);
  $mdp = $verify_mpd->fetch();


  if ($mdp && $current_password === $mdp['mdp_resp'])  {

    $sql = "UPDATE responsable SET
          nom_resp = :nom,
          prenom_resp = :prenom,
          mail_resp = :mail
          WHERE Matricule_resp = :matricule";

    $params = [
      ':nom' => $nom,
      ':prenom' => $prenom,
      ':mail' => $email,
      ':matricule' => $user_id
    ];

    if (!empty($new_password) && $new_password === $confirm_password) {
      $modif_mdp = "UPDATE responsable SET mdp_resp = :new_password WHERE Matricule_resp = :matricule";
      $modif_motdp = [
        ':new_password' => $new_password,
        ':matricule' => $user_id
      ];
    }

    try {
      $stmt = $bdd->prepare($sql);
      $stmt->execute($params);

      if(!empty($new_password) && $new_password === $confirm_password){
        $stmnt = $bdd->prepare($modif_mdp);
        $stmnt->execute($modif_motdp);
      }

      $_SESSION['success_message'] = 'Responsable mis à jour avec succès !';
      header('Location: ../vue/pageresponsable.php');
      exit;
    } catch (PDOException $e) {
      //  Gestion des erreurs SQL;
      $_SESSION['error_message'] = 'Erreur lors de la mise à jour: ' . $e->getMessage();
      header('Location: ../vue/pageresponsable.php');
      exit;
    }

  }elseif (empty($current_password)) {
    $_SESSION['error_message'] = 'Vous devez entrer votre mot de passe pour confirmer la modification';
    header('location: ../vue/pageresponsable.php');
  } else {
    $_SESSION['error_message'] = 'Mot de passe actuel incorrect';
    header('Location: ../vue/pageresponsable.php');
    exit;
  }
}

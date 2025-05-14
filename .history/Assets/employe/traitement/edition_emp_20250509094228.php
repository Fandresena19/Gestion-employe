<?php
include('../other/bd.php');
session_start();


if ($_SERVER['REQUEST_METHOD'] === "POST") {

  // Récupération des données du formulaire
  $matricule = $_SESSION['Matricule_emp'];
  $nom = $_POST['nom'];
  $prenom = $_POST['prenom'];
  $email = $_POST['mail'];
  $role = $_POST['role'];
  $telephone = $_POST['telephone'];
  $current_password = $_POST['current_password'];
  $new_password = $_POST['new_password'];
  $confirm_password = $_POST['confirm_password'];

  $verif_mdp = $bdd->prepare("SELECT mdp_emp FROM employer_login WHERE matricule_emp = ?");
  $verif_mdp->execute([$matricule]);
  $mdp = $verif_mdp->fetch();


  if ($mdp && $current_password === $mdp['mdp_emp']) {


    if (isset($_FILES['avatar']) and !empty($_FILES['avatar']['name'])) {
      $tailleMax = 10870509;
      $extensionValides = array('jpg', 'jpeg', 'gif', 'png');

      if ($_FILES['avatar']['size'] <= $tailleMax) {
        $extensionUpload = strtolower(substr(strrchr($_FILES['avatar']['name'], '.'), 1));

        if (in_array($extensionUpload, $extensionValides)) {
          $chemin = "../uploads/" . 'image' . " " . "$prenom" . "." . $extensionUpload;
          $resultat = move_uploaded_file($_FILES['avatar']['tmp_name'], $chemin);
          if ($resultat) {
            $updateAvatar = $bdd->prepare('UPDATE employer_login SET profil = :avatar WHERE matricule_emp = :matricule');
            $updateAvatar->execute([
              'avatar' => $chemin,
              'matricule' => $matricule
            ]);
          } else {
            echo 'Erreur durant importation';
          }
        } else {
          echo 'Format non reconnu';
        }
      } else {
        echo 'Pas plus de 2Mo';
      }
    }

    // Mise à jour des informations dans la base de données
    $query = "UPDATE employer_login SET 
    nom_emp = :nom, 
    prenom_emp = :prenom,
    mail_emp = :mail, 
    role = :role,
    telephone = :phone 
    WHERE matricule_emp = :matricule";

    $params = [
      ':nom' => $nom,
      ':prenom' => $prenom,
      ':mail' => $email,
      ':role' => $role,
      ':phone' => $telephone,
      ':matricule' => $matricule,
    ];


    if (!empty($new_password) && $new_password === $confirm_password) {
      $modif_mdp = "UPDATE employer_login SET mdp_emp = :new_password WHERE matricule_emp = :matricule";
      $modif_motdp = [
        ':new_password' => $new_password,
        ':matricule' => $matricule
      ];
    }

    try {
      $stmt = $bdd->prepare($query);
      $stmt->execute($params);

      if(!empty($new_password) && $new_password === $confirm_password){
        $stmnt = $bdd->prepare($modif_mdp);
        $stmnt->execute($modif_motdp);
      }


      $_SESSION['success_message'] = 'Employé mis à jour avec succès !';
      header('Location: ./pageemp.php');
      exit;
    } catch (PDOException $e) {
      //  Gestion des erreurs SQL;
      $_SESSION['error_message'] = 'Erreur lors de la mise à jour: ' . $e->getMessage();
      header('Location: pageemp.php');
      exit;
    }
  } elseif (empty($current_password)) {
    $_SESSION['error_message'] = 'Vous devez entrer votre mot de passe pour confirmer la modification';
    header('location: pageemp.php');
  } else {
    $_SESSION['error_message'] = 'Mot de passe actuel incorrect';
    header('Location: ../vue/pageemp.php');
    exit;
  }
}

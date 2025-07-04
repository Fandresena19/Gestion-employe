<?php
include('../other/head_profil.php');

// Afficher le message de succès s'il existe
if (isset($_SESSION['success_message'])) {
  echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
  unset($_SESSION['success_message']); // Supprimer le message après affichage
}

// Afficher le message d'erreur s'il existe
if (isset($_SESSION['error_message'])) {
  echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
  unset($_SESSION['error_message']); // Supprimer le message après affichage
}

$user_id = $_SESSION['Matricule_resp'];

$stmt = $bdd->prepare("SELECT * FROM responsable r JOIN service s ON r.id_service=s.id_service WHERE r.Matricule_resp = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$profile_picture = '../../uploads/default.jpg';

if (!$user) {
  echo "Utilisateur non trouvé";
  exit();
}
?>

<h2 class="head-text">Profil Responsable</h2>

<div class="contenu_profil">

  <div class="contenu1_profil">
    <h3>Modifier responsable</h3>

    <div class="sous-contenu">
      <div class="ligne1">
        <input type="text" value="<?= 'Matricule = ' . $user['Matricule_resp'] ?>" class="input_mat" disabled />
        <input type="text" value="<?= strtoupper($user['nom_resp']) ?>" name="nom" class="input2" oninput="this.value = this.value.toUpperCase();" />
        <input type="text" name="prenom" value="<?= ucfirst(strtolower($user['prenom_resp'])) ?>" class="input2"
          oninput="this.value = this.value.charAt(0).toUpperCase() + this.value.slice(1).toLowerCase();" />
      </div><br><br>

      <div class="ligne2">
        <input type="mail" name="mail" value="<?= $user['mail_resp'] ?>" class="input3" />
      </div><br><br>

      <div class="ligne3plus">
        <div>
          <span><input type="password" name="current_password"
              placeholder="Votre mot de passe" id="mdp" />
            <i class="bx bx-hide" id="icone"></i> </span>
        </div>

        <div>
          <span><input type="password" name="new_password"
              placeholder="Nouveau mot de passe" id="mdp1" />
            <i class="bx bx-hide" id="icone"></i> </span>
        </div>

        <div>
          <span><input type="password" name="confirm_password"
              placeholder="confirmer nouveau mot de passe" id="mdp2" />
            <i class="bx bx-hide" id="icone"></i> </span>
        </div>

      </div><br />

      <button type="submit" class="bouton_confirmer">Confirmer modification</button>
    </div>
  </div>

  <div class="contenu2_profil">
    <div class="photo">
      <div class="cadre_photo" align="center">
        <img src="<?= $profile_picture; ?>" alt="" width="150" class="photo_de_profil">
        <h4><?php echo $user['nom_resp'] . '&nbsp;'  . $user['prenom_resp']; ?></h4>
      </div>
    </div>

    <div class="detail">

      <table class="tableau_profil">

        <tr><br>
          <td>Matricule </td>
          <td> <?= ':' . '&nbsp' . $user['Matricule_resp'] ?></td>
        </tr>
        <tr>
          <td>Nom </td>
          <td><?php echo ':' . '&nbsp' . strtoupper($user['nom_resp']) ?></td>
        </tr>
        <tr>
          <td>Prénom</td>
          <td><?php echo ':' . '&nbsp' . $user['prenom_resp']; ?></td>
        </tr>
        <tr>
          <td>Mail </td>
          <td><?php echo ':' . '&nbsp' . $user['mail_resp'] ?></td>
        </tr>
        <tr>
          <td>Service </td>
          <td><?php echo ':' . '&nbsp' . $user['nom_service']; ?></td>
        </tr>

      </table>

    </div>
  </div>

  <?php
  include('../other/foot.php');
  ?>
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

$user_id = $_SESSION['Matricule_emp'];
$stmt = $bdd->prepare("SELECT * FROM employer_login e JOIN service s ON 
e.id_service=s.id_service JOIN obtenir o on o.matricule_emp=e.matricule_emp
JOIN grade g ON g.id_grade=o.id_grade WHERE e.Matricule_emp = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$grades = $bdd->query('select *from grade');

// $conge = $bdd->query("SELECT * FROM conge WHERE Matricule_emp= $user_id");
// $permission = $bdd->query("SELECT * FROM permission WHERE Matricule_emp = $user_id");

$default_profile_picture = '../uploads/default.jpg';
$profile_picture = isset($user['profil']) ? $user['profil'] : $default_profile_picture;

$req_notif = $bdd->query("SELECT * FROM notifications WHERE Matricule_emp = '$user_id' ORDER BY date_notif DESC");

?>

          <h2 class="head-text">Profil Employé</h2>

          <div class="contenu">


            <div class="contenu1">
              <h3>Modifier employé</h3>

              <div class="sous-contenu">
                <div class="ligne1">
                  <input type="text" value="<?= 'Matricule = ' . $user['matricule_emp'] ?>" name="im" class="input_mat" disabled />
                  <input type="text" placeholder="<?= strtoupper($user['nom_emp']) ?>" name="nom" class="input2" oninput="this.value = this.value.toUpperCase();" />
                  <input type="text" name="prenom"
                    placeholder="<?= ucfirst(strtolower($user['prenom_emp'])) ?>"
                    class="input2"
                    oninput="this.value = this.value.charAt(0).toUpperCase() + this.value.slice(1).toLowerCase();">

                </div><br><br />

                <i class="ligne2">
                  <input type="mail" name="mail" placeholder="<?= $user['mail_emp'] ?>" class="input3" />
                  <input type="text" name="role" placeholder="<?= $user['role'] ?>" class="input4" />
                  <input type="text" name="telephone" placeholder="<?= $user['telephone'] ?>" class="input4" />
              </div><br><br />

              <div class="ligne3">
                <input type="file" name="avatar" />
              </div><br><br />

              <div class="ligne3plus">
                <div>
                  <span><input type="password" name="current_password" placeholder="Votre mot de passe" id="mdp" />
                    <i class="bx bx-hide" id="icone"></i> </span>
                </div>

                <div>
                  <span><input type="password" name="new_password" placeholder="Nouveau mot de passe" id="mdp" />
                    <i class="bx bx-hide" id="icone"></i> </span>
                </div>

                <div>
                  <span><input type="password" name="confirm_password" placeholder="confirmer nouveau mot de passe" id="mdp" />
                    <i class="bx bx-hide" id="icone"></i> </span>
                </div>

              </div>

              <!-- <div class="ligne4">
                <span><input type="password" name="confirm_password" placeholder="Entrer votre Mot de passe pour confirmer" class="input3" id="mdp" />
                  <i class="bx bx-hide" id="icone"></i></span>
              </div> -->

              <button type="submit" class="bouton_confirmer">Confirmer modification</button>
            </div>


            <div class="contenu2">
              <div class="photo">
                <div class="cadre_photo" align="center">

                  <?php
                  echo "<img src='" . $profile_picture . "' alt='Image' style='max-width:150px; border: 2px solid white; margin: 10px; border-radius: 20px;'>"
                  ?>

                  <h4><?php echo $user['nom_emp'] . '&nbsp;' . $user['prenom_emp']; ?></h4>
                </div>
              </div>

              <div class="detail">

                <table>

                  <tr><br>
                    <td>Matricule </td>
                    <td> <?= ':' . '&nbsp' . $user['matricule_emp'] ?></td>
                  </tr>
                  <tr>
                    <td>Nom </td>
                    <td><?php echo ':' . '&nbsp' . strtoupper($user['nom_emp']) ?></td>
                  </tr>
                  <tr>
                    <td>Prénom</td>
                    <td><?php echo ':' . '&nbsp' . $user['prenom_emp']; ?></td>
                  </tr>
                  <tr>
                    <td>Mail </td>
                    <td><?php echo ':' . '&nbsp' . $user['mail_emp'] ?></td>
                  </tr>
                  <tr>
                    <td>Date embauche </td>
                    <td><?php echo ':' . '&nbsp' . date('d/m/Y ', strtotime($user['date_embauche'])) ?></td>
                  </tr>
                  <tr>
                    <td>Grade </td>
                    <td><?php echo ':' . '&nbsp' . $user['nom_grade']; ?></td>
                  </tr>


                </table>

              </div>
            </div>
          </div>

        </div>

      </div>
  </div>

  </form>

  <?php
  include('../other/foot.php');
  ?>
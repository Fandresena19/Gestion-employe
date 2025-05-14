<div class="dashboard" id="Main">
        <h2 class="head-text">Profil Responsable</h2>

        <div class="contenu">

          <div class="contenu1">
            <h3>Modifier responsable</h3>

            <div class="sous-contenu">
              <div class="ligne1">
                <input type="text" value="<?= 'Matricule = ' . $user['Matricule_resp'] ?>" class="input_mat" disabled />
                <input type="text" placeholder="<?= strtoupper($user['nom_resp']) ?>" name="nom" class="input2" oninput="this.value = this.value.toUpperCase();" />
                <input type="text" name="prenom" placeholder="<?=ucfirst( strtolower($user['prenom_resp'])) ?>" class="input2" 
                oninput="this.value = this.value.charAt(0).toUpperCase() + this.value.slice(1).toLowerCase();"/>
              </div><br><br>

              <div class="ligne2">
                <input type="mail" name="mail" placeholder="<?= $user['mail_resp'] ?>" class="input3" />
              </div><br><br>

              <div class="ligne3plus">
                <div>
                  <span><input type="password" name="current_password" placeholder="Votre mot de passe" id="mdp"/>
                    <i class="bx bx-hide" id="icone"></i> </span>
                </div>

                <div>
                  <span><input type="password" name="new_password" placeholder="Nouveau mot de passe" id="mdp"/>
                    <i class="bx bx-hide" id="icone"></i> </span>
                </div>

                <div>
                  <span><input type="password" name="confirm_password" placeholder="confirmer nouveau mot de passe" id="mdp"/>
                    <i class="bx bx-hide" id="icone"></i> </span>
                </div>
                
              </div><br/>

              <!-- <div class="ligne4">
                <span><input type="password" placeholder="Entrer votre Mot de passe pour confirmer" class="input3" id="mdp" />
                  <i class="bx bx-hide" id="icone"></i></span>
              </div> -->

              <button type="submit" class="bouton_confirmer">Confirmer modification</button>
            </div>
          </div>       

          <div class="contenu2">
            <div class="photo">
              <div class="cadre_photo" align="center">
                <img src="<?= $profile_picture; ?>" alt="" width="150" class="photo_de_profil">
                <h4><?php echo $user['nom_resp'] . '&nbsp;'  . $user['prenom_resp']; ?></h4>
              </div>
            </div>

            <div class="detail">

              <table>

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
                <tr>
                  <td>A propos</td>
                  <td>
                    <?php
                    echo ':' . '&nbsp' . $user['nom_resp'] . '&nbsp' . $user['prenom_resp'] . " est un responsable dans la service de " . $user['nom_service']
                    ?>
                  </td>
                </tr>

              </table>

            </div>
          </div>
        </div>

      </div>
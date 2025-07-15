<style>
    tbody tr:nth-child(even) {
      background-color: rgb(91, 91, 91);
    }
  </style>

<?php
  include('../other/head.php');
  $donnees = $bdd->query('select * from employer_login e join permission p on e.matricule_emp=p.matricule_emp where p.Statut_permission = "Validé" ');

  ?>

<div class="annotation">
        <h2>Absent</h2>
      </div>

      <div class="contenu">
        <header>
          <h4>Permission Validée</h4>

          <div class="bouton">
            <a href="./liste_permission.php"><button type="submit">Retour</button></a>
          </div>
        </header><br />

        <table class="">

          <thead>
            <tr>
              <td>Permission N°</td>
              <td>Nom complet</td>
              <td>Date debut</td>
              <td>Date Fin</td>
              <td>Durée absence</td>
              <td>Motif</td>
            </tr>
          </thead>
          <tbody>
            <?php
            while ($data = $donnees->fetch()) {

              echo '<tr>
                <td>' . $data['id_permission'] . '</td>
                <td>' . $data['nom_emp'] . ' ' . $data['prenom_emp'] . '</td>
                
                <td>' . date('d/m/Y H:i:s', strtotime($data['date_debut_per'])). '</td>
                <td>' . date('d/m/Y H:i:s', strtotime($data['date_fin_per'])) . '</td>
                <td>' . $data['duree_jour_per'] . ' jours ' . $data['duree_heure_per'] . ' heures</td>
                <td>' . $data['motif_per'] . '</td>
                </tr>';
            }
            ?>
          </tbody>
        </table>
      </div>

      <?php
      include('../other/foot.php')
      ?>
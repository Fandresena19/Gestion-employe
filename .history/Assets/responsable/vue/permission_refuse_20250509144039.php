<style>
    tbody tr:nth-child(even) {
      background-color: rgb(91, 91, 91);
    }
  </style>

<?php
include('../other/head.php');

  $donnees = $bdd->query('select * from employer_login e join permission p on e.matricule_emp=p.matricule_emp where p.Statut_permission = "Refusé" ');
  ?>

<div class="annotation">
        <h2>Permission réfusée</h2>
      </div>

      <div class="contenu">
        <header>
          <h4>Permission réfusée</h4>
          <div class="bouton">
            <a href="./liste_permission.php"><button type="submit">Retour</button></a>
          </div>

        </header><br>
        <table class="">

          <thead>
            <tr>
              <td>Matricule</td>
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
                <td>' . $data['matricule_emp'] . '</td>
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
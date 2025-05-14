<?php
  include('../other/head.php');
  $donnees = $bdd->query('select * from employer_login e join conge c on e.matricule_emp=c.matricule_emp where c.statut_conge = "Refuse"');

  if (!isset($_SESSION['Matricule_resp'])) {
    header('location:../index.php');
    exit();
  }

  ?>

<style>
    tbody tr:nth-child(even) {
      background-color: rgb(91, 91, 91);
    }
  </style>

<div class="annotation">
        <h2>Congé refusé</h2>
      </div>

      <div class="contenu">
        <header>
          <h4>Congé réfusé</h4>


          <div class="bouton">
            <a href="./liste_conge.php"><button type="submit">Retour</button></a>
          </div>

        </header><br />

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
            while ($data = $donnees->fetch(PDO::FETCH_ASSOC)) {
              //Convertir les chaines de caractere en objet DateTime
              $dateDebut = new DateTime($data['date_debut']);
              $dateFin = new DateTime($data['date_fin']);
              //Calcule interval
              $interval = date_diff($dateDebut, $dateFin);
              $dureeAbsence = $interval->days + 1;

              echo '<tr>
                <td>' . $data['matricule_emp'] . '</td>
                <td>' . $data['nom_emp'] . ' ' . $data['prenom_emp'] . '</td>
                
                <td>' . date('d/m/Y H:i:s', strtotime($data['date_debut'])) . '</td>
                <td>' . date('d/m/Y H:i:s', strtotime($data['date_fin'])) . '</td>
                <td>' . $dureeAbsence . ' jours </td>
                <td>' . $data['motif'] . '</td>
                </tr>';
            }
            ?>
          </tbody>
        </table>
      </div>
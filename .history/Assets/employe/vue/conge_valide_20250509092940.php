<?php
  include('../other/head.php');
  $matricule_emp = $_SESSION['Matricule_emp'];


  $sql = "SELECT * FROM conge c JOIN employer_login e on c.matricule_emp=e.matricule_emp
 WHERE e.matricule_emp= :matricule_emp AND c.statut_conge = 'Validé' ORDER BY c.date_demande ASC ";
  $stmt = $bdd->prepare($sql);
  $stmt->execute(['matricule_emp' => $matricule_emp]);

  ?>

<div class="annotation">
        <h2>Congé</h2>
      </div>

      <div class="contenu">
        <table class="">

          <thead>

            <th>id congé</th>
            <th>Matricule</th>
            <th>Nom complet</th>
            <th>Date demande</th>
            <th>Date debut</th>
            <th>Date fin</th>
            <th>Durée absence</th>
            <th>Motif</th>

          </thead>
          <tbody>

            <?php
            $quotaCongeAnnuel = 30;
            $congePrise = 0;
            $resteConge = $quotaCongeAnnuel;
            $anneeEnCours = date('Y');
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
              //Convertir les chaines de caractere en objet DateTime
              $dateDebut = new DateTime($row['date_debut']);
              $dateFin = new DateTime($row['date_fin']);
              //Calcule interval
              $interval = date_diff($dateDebut, $dateFin);

              //Calcule durée de l'absence
              $dureeAbsenceJ = $interval->days;
              $dureeAbsenceH = $interval->h;

              //converir heure en jours
              $dureeAbsenceenJours = $dureeAbsenceJ + ($dureeAbsenceH / 24);

              //Mose à jour du nombre de jours de congé pris
              $congePrise += $dureeAbsenceenJours;

              $resteConge -= $dureeAbsenceenJours;

              $anneConge = date('Y', strtotime($row['date_debut']));
              if ($anneConge !== $anneeEnCours) {
                $resteConge += $quotaCongeAnnuel;
                $anneeEnCours = $anneConge;
              }

              if (!function_exists('afficherDureeEnJoursEtHeure')) {
                function afficherDureeEnJoursEtHeure($jours)
                {
                  $heures = ($jours - floor($jours)) * 24; //Calculer les heures restants
                  $jours = floor($jours); //obtient la partie entière des jours

                  return $jours . ' jours ' . $heures . ' heures ';
                }
              }


              echo '
              
              <tr>
                <td>' . $row['id_conge'] . '</td>
                <td>' . $row['matricule_emp'] . '</td>
                <td>' . $row['nom_emp'] . ' ' . $row['prenom_emp'] . '</td>
                
                <td>' . date('d/m/Y H:i:s', strtotime($row['date_demande'])) . '</td>
                <td>' . date('d/m/Y H:i:s', strtotime($row['date_debut']))  . '</td>
                <td>' . date('d/m/Y H:i:s', strtotime($row['date_fin'])) . '</td>
                <td>' . $dureeAbsenceJ . ' jours ' . $dureeAbsenceH . ' heures</td>
                <td>' . $row['motif'] . '</td>
                </tr>';
            }
            echo '
          <header class="conge_valide">';
            if (!empty($congePrise) and !empty($resteConge)) {

              echo '
              <h4>Totale congé prise cette année : ' . afficherDureeEnJoursEtHeure($congePrise) . '<span>
             
              </br>
  
               Reste congé cette année : ' . afficherDureeEnJoursEtHeure($resteConge) . '</span></h4>';
            } else {
              echo '<h4>Pas de congé prise </h4>';
            }


            echo '<div class="bouton">
            <a href="./mes_conge.php"><button type="submit">Retour</button></a>
            </div>';

            echo '</header> <br/>';
            ?>
          </tbody>
        </table>
      </div>
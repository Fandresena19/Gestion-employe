<?php
  include('../other/head.php');

  $matricule_emp = $_SESSION['Matricule_emp'];

  $sql = "SELECT * FROM employer_login e JOIN permission p ON e.matricule_emp=p.matricule_emp
   WHERE p.matricule_emp = :matricule_emp AND p.Statut_permission = 'Refusé' ORDER BY p.date_demande_per ASC";
  $stmt = $bdd->prepare($sql);
  $stmt->execute(['matricule_emp' => $matricule_emp]);

  if (!isset($_SESSION['Matricule_emp'])) {
    header('location:../index.php');
    exit();
  }

  ?>


<div class="annotation">
        <h2>Permission refusée</h2>
      </div>

      <div class="contenu">

        <table class="">

          <thead>

            <th>id</th>
            <th>Matricule</th>
            <th>Nom complet</th>
            <th>Date demande</th>
            <th>Date debut</th>
            <th>Date fin</th>
            <th>Durée</th>
            <th>Motif</th>
            <th>Note</th>

          </thead>
          <tbody>
            <?php
            $anneeEnCours = date('Y');
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
              $PermissionJour = $row['duree_jour_per'];
              $PermissionHeures = $row['duree_heure_per'];
              //convertisser en heures jours
              $total_heures = ($PermissionJour * 24) + $PermissionHeures;
              //Calcule nombre de jours et heures
              $jours = floor($total_heures / 24);
              $heures = $total_heures % 24;

              echo '<tr>
                    <td>' . $row['id_permission'] . '</td>
                    <td>' . $row['matricule_emp'] . '</td>
                    <td>' . $row['nom_emp'] . ' ' . $row['prenom_emp'] . '</td>
                    
                    <td>' . date('d/m/Y H:i:s', strtotime($row['date_demande_per'])) . '</td>
                    <td>' . date('d/m/Y H:i:s', strtotime($row['date_debut_per'])) . '</td>
                    <td>' . date('d/m/Y H:i:s', strtotime($row['date_fin_per'])) . '</td>
                    <td>' . $row['duree_jour_per'] . ' jours ' . $row['duree_heure_per'] . ' heures</td>
                    <td>' . $row['motif_per'] . '</td>';
              echo "<td>";
              if ($row['Statut_permission'] == "Validé") {
                echo 'Validé';
              } elseif ($row['Statut_permission'] == "Refusé") {
                echo 'Non validé';
              } else {
                echo 'En attente';
              }
              echo "</td>";
              echo "</tr>";
            }

            echo '
            <header>';
            echo '<h4 align="center">Premission réfuée</h4>';
            echo '<div class="bouton">
              <a href="./mes_permission.php"><button type="submit">Retour</button></a>
              </div>';

            echo '</header> <br/>';
            ?>
          </tbody>
        </table>


      </div>

      <?php
      include('../other/footer.php');
      ?>
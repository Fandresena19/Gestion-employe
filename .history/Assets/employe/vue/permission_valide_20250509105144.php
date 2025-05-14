<div class="annotation">
    <h2>Permission</h2>
  </div>

  <div class="contenu">
    <table>
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
        // Reset statement to re-fetch for table display
        $stmt = $bdd->prepare($sql);
        $stmt->execute(['matricule_emp' => $matricule_emp]);
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
          $PermissionJour = $row['duree_jour_per'];
          $PermissionHeures = $row['duree_heure_per'];
          
          echo '<tr>';
          echo '<td>' . $row['id_permission'] . '</td>';
          echo '<td>' . $row['matricule_emp'] . '</td>';
          echo '<td>' . $row['nom_emp'] . ' ' . $row['prenom_emp'] . '</td>';
          echo '<td>' . date('d/m/Y H:i:s', strtotime($row['date_demande_per'])) . '</td>';
          echo '<td>' . date('d/m/Y H:i:s', strtotime($row['date_debut_per'])) . '</td>';
          echo '<td>' . date('d/m/Y H:i:s', strtotime($row['date_fin_per'])) . '</td>';
          echo '<td>' . $row['duree_jour_per'] . ' jours ' . $row['duree_heure_per'] . ' heures</td>';
          echo '<td>' . $row['motif_per'] . '</td>';
          echo '<td>';
          
          if ($row['Statut_permission'] == "Validé") {
            echo 'Validé';
          } elseif ($row['Statut_permission'] == "Refusé") {
            echo 'Non validé';
          } else {
            echo 'En attente';
          }
          
          echo '</td></tr>';
        }
        ?>
      </tbody>
    </table>

    <?php
    // Only display total if permissions exist
    if ($permission_exists) {
      echo '<header>';
      echo '<h4 align="center">Permission prise cette année = ' . $total_jours . ' Jours et ' . $total_heures . ' Heures</h4>';
      echo '<div class="bouton">
              <a href="./mes_permission.php"><button type="submit">Retour</button></a>
            </div>';
      echo '</header> <br/>';
    } else {
      echo '<header>';
      echo '<h4 align="center">Aucune permission validée trouvée</h4>';
      echo '<div class="bouton">
              <a href="./mes_permission.php"><button type="submit">Retour</button></a>
            </div>';
      echo '</header> <br/>';
    }
    ?>
  </div>
  </div>
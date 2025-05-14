<div class="annotation">
        <h2>Congé</h2>
      </div>

      <!-- Ajout du résumé des congés -->
      <div class="conge-summary">
        <h4>Récapitulatif des congés pour l'année <?php echo $annee_courante; ?></h4>
        <div class="conge-detail">
          <div class="conge-item quota">
            <strong>Quota annuel</strong>
            <p><?php echo $quotaCongeAnnuel; ?> jours</p>
          </div>
          <div class="conge-item taken">
            <strong>Congés pris</strong>
            <p><?php echo $total_jours; ?> jours et <?php echo $total_heures; ?> heures</p>
          </div>
          <div class="conge-item remaining">
            <strong>Reste disponible</strong>
            <p><?php echo $reste_jours; ?> jours et <?php echo $reste_heures; ?> heures</p>
          </div>
        </div>
      </div>

      <div class="contenu">

        <header>
          <div class="bouton">
            <a href="./conge_valide.php"><button type="submit">Voir congé validé</button></a>
            <a href="./conge_refuse.php"><button type="submit">Voir congé refusé</button></a>
          </div>
        </header> <br>

        <table class="">
          <thead>
            <th>Congé N°</th>
            <th>Matricule</th>
            <th>Nom complet</th>
            <th>Date demande</th>
            <th>Date debut</th>
            <th>Date fin</th>
            <th>Durée absence</th>
            <th>Motif</th>
            <th>Note</th>
          </thead>
          <tbody>
            <?php
            // Reset statement pour la table d'affichage
            $stmt = $bdd->prepare($sql);
            $stmt->execute(['matricule_emp' => $matricule_emp]);
            
            $has_rows = false;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
              $has_rows = true;
              echo "<tr>";
              echo "<td>" . $row['id_conge'] . "</td>";
              echo "<td>" . $row['matricule_emp'] . "</td>";
              echo "<td>" . $row['nom_emp'] . ' ' . $row['prenom_emp'] . "</td>";
              echo "<td>" . date('d/m/Y H:i:s', strtotime($row['date_demande']))  . "</td>";
              echo "<td>" . date('d/m/Y H:i:s', strtotime($row['date_debut'])) . "</td>";
              echo "<td>" . date('d/m/Y H:i:s', strtotime($row['date_fin'])) . "</td>";
              echo "<td>" . $row['duree_jours_conge'] . " Jours " . $row['duree_heure_conge'] . " heures</td>";
              echo "<td>" . $row['motif'] . "</td>";
              echo "<td>";
              if ($row['statut_conge'] == "Validé") {
                echo 'Validé';
              } elseif ($row['statut_conge'] == "Refusé") {
                echo 'Non validé';
              } else {
                echo 'En traitement';
              }
              echo "</td>";
              echo "</tr>";
            }
            
            if (!$has_rows) {
              echo '<tr><td colspan="9" class="text-center">Aucun congé trouvé</td></tr>';
            }
            ?>
          </tbody>
        </table>
        <div class="Ajout_conge">
          <a href="./conge.php"><button type="submit">Ajouter Congé</button></a>
        </div>
      </div>

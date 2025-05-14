<?
include ('../other/head.php');
?>

<div class="annotation">
  <h2>Permission</h2>
</div>

<!-- Ajout du résumé des permissions -->
<div class="permission-summary">
  <h4>Récapitulatif des permissions pour l'année <?php echo $annee_courante; ?></h4>
  <div class="permission-detail">
    <div class="permission-item quota">
      <strong>Quota annuel</strong>
      <p><?php echo $quotaPermissionAnnuel; ?> jours</p>
    </div>
    <div class="permission-item taken">
      <strong>Permissions prises</strong>
      <p><?php echo $total_jours; ?> jours et <?php echo $total_heures; ?> heures</p>
    </div>
    <div class="permission-item remaining">
      <strong>Reste disponible</strong>
      <p><?php echo $reste_jours; ?> jours et <?php echo $reste_heures; ?> heures</p>
    </div>
  </div>
</div>

<div class="contenu">

  <header>
    <div class="bouton">
      <a href="./permission_valide.php"><button type="submit">Permission Validée</button></a>
      <a href="./permission_refuse.php"><button type="submit">Permission refusée</button></a>
    </div>
  </header><br>

  <table class="table_conge">
    <thead>
      <th>Permission N°</th>
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
      // Reset statement pour la table d'affichage
      $sql = "SELECT * FROM employer_login e JOIN permission p ON e.matricule_emp=p.matricule_emp
            WHERE p.matricule_emp = :matricule_emp ORDER BY p.date_demande_per ASC";
      $stmt = $bdd->prepare($sql);
      $stmt->execute(['matricule_emp' => $matricule_emp]);

      $has_rows = false;
      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $has_rows = true;
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
        echo '</td>';
        echo '</tr>';
      }

      if (!$has_rows) {
        echo '<tr><td colspan="9" class="text-center">Aucune permission trouvée</td></tr>';
      }
      ?>
    </tbody>
  </table>

  <div class="Ajout_conge" id="Annuler">
    <a href="./permission.php"><button type="submit">Ajouter Permission</button></a>
  </div>

</div>
<style>
  tbody tr:nth-child(even) {
    background-color: rgb(91, 91, 91);
  }

  .permission-summary {
    background-color: #6a6363bf;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
  }

  .permission-summary h4 {
    color: #e0e0e0ce;
    margin-bottom: 10px;
  }

  .permission-detail {
    display: flex;
    justify-content: space-around;
    flex-wrap: wrap;
  }

  .permission-item {
    text-align: center;
    padding: 10px;
    margin: 5px;
    min-width: 150px;
    border-radius: 5px;
  }

  .permission-item.taken {
    background-color: #f0ad4e;
    max-height: 50px;
  }

  .permission-item.remaining {
    background-color: #5cb85c;
    max-height: 50px;
    color: white;
  }

  .permission-item.quota {
    background-color: #5bc0de;
    max-height: 50px;
    color: white;
  }
</style>

<?php
include('../other/foot.php');
?>
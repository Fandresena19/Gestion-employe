<?php
  session_start();
  include('../other/head.php');
  
  if (!isset($_SESSION['Matricule_emp'])) {
    header('location:../index.php');
    exit();
  }
  
  $matricule_emp = $_SESSION['Matricule_emp'];
  
  // Récupérer les congés
  $sql = "SELECT * FROM conge c JOIN employer_login e on c.matricule_emp=e.matricule_emp
    WHERE e.matricule_emp= :matricule_emp ORDER BY c.date_demande ASC";
  $stmt = $bdd->prepare($sql);
  $stmt->execute(['matricule_emp' => $matricule_emp]);
  
  // Initialiser les variables pour le calcul des congés
  $total_jours = 0;
  $total_heures = 0;
  $conge_exists = false;
  $annee_courante = date('Y');
  
  // Quota annuel de congé
  $quotaCongeAnnuel = 30;
  
  // Calculer les congés validés pour l'année en cours
  $sql_valides = "SELECT * FROM conge 
                WHERE matricule_emp = :matricule_emp 
                AND statut_conge = 'Validé' 
                AND YEAR(date_debut) = :annee_en_cours";
  $stmt_valides = $bdd->prepare($sql_valides);
  $stmt_valides->execute([
      'matricule_emp' => $matricule_emp,
      'annee_en_cours' => $annee_courante
  ]);
  
  // Calculer le total des jours de congé déjà pris
  while ($row = $stmt_valides->fetch(PDO::FETCH_ASSOC)) {
      $conge_exists = true;
      $debutConge = new DateTime($row['date_debut']);
      $finConge = new DateTime($row['date_fin']);
      $intervalConge = $debutConge->diff($finConge);
      
      $joursConge = $intervalConge->days;
      $heuresConge = $intervalConge->h;
      
      // Mettre à jour le cumul des congés pris
      $total_heures_conge = ($joursConge * 24) + $heuresConge;
      
      // Accumulate total days and hours
      $total_jours += floor($total_heures_conge / 24);
      $total_heures += $total_heures_conge % 24;
  }
  
  // Normaliser les heures (convertir les heures excédentaires en jours)
  if ($total_heures >= 24) {
    $total_jours += floor($total_heures / 24);
    $total_heures = $total_heures % 24;
  }
  
  // Calculer le reste de congé disponible
  $total_pris_en_jours = $total_jours + ($total_heures / 24);
  $reste_conge = $quotaCongeAnnuel - $total_pris_en_jours;
  $reste_jours = floor($reste_conge);
  $reste_heures = round(($reste_conge - $reste_jours) * 24);
  ?>




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

<?php
include('../other/foot.php');
?>
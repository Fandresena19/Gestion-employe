<?php

include('../other/head.php');

// Afficher le message de succès s'il existe
if (isset($_SESSION['success_message'])) {
  echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
  unset($_SESSION['success_message']); // Supprimer le message après affichage
}

// Afficher le message d'erreur s'il existe
if (isset($_SESSION['error_message'])) {
  echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
  unset($_SESSION['error_message']); // Supprimer le message après affichage
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

  <table class="table_conge">
    <thead>
      <th>Congé N°</th>
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
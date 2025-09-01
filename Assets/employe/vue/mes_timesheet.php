<?php
include('../other/head.php');

$matricule_emp = $_SESSION['Matricule_emp'];

// Afficher le message de succès s'il existe
if (isset($_SESSION['success'])) {
  echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
  unset($_SESSION['success']); // Supprimer le message après affichage
}

// Afficher le message d'erreur s'il existe
if (isset($_SESSION['error'])) {
  echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
  unset($_SESSION['error']); // Supprimer le message après affichage
}

// Récupération de la date actuelle
$currentDate = new DateTime();
$mois_courante = $currentDate->format('F Y');

// Filtre par défaut (mois courant)
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'current_month';
$custom_start = isset($_GET['start']) ? $_GET['start'] : '';
$custom_end = isset($_GET['end']) ? $_GET['end'] : '';

// Message d'erreur pour les dates personnalisées non remplies
$error_message = '';
if ($filter == 'custom' && (empty($custom_start) || empty($custom_end))) {
  $error_message = "Veuillez remplir les deux dates pour le filtre personnalisé.";
  // Redirection vers le filtre par défaut si les dates ne sont pas spécifiées
  $filter = 'current_month';
}

// Préparation de la clause WHERE en fonction du filtre
$where_clause = " WHERE t.matricule_emp = :matricule_emp";
$params = ['matricule_emp' => $matricule_emp];

if ($filter == 'current_month') {
  $where_clause .= " AND MONTH(date_tache) = MONTH(CURRENT_DATE()) AND YEAR(date_tache) = YEAR(CURRENT_DATE())";
  $period_text = "du mois courant";
} elseif ($filter == 'last_month') {
  $where_clause .= " AND MONTH(date_tache) = MONTH(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH)) 
                     AND YEAR(date_tache) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))";
  $period_text = "du mois dernier";
} elseif ($filter == 'last_week') {
  $where_clause .= " AND YEARWEEK(date_tache, 1) = YEARWEEK(DATE_SUB(CURRENT_DATE(), INTERVAL 1 WEEK), 1)";
  $period_text = "de la semaine dernière";
} elseif ($filter == 'custom' && !empty($custom_start) && !empty($custom_end)) {
  $where_clause .= " AND date_tache BETWEEN :start_date AND :end_date";
  $params['start_date'] = $custom_start;
  $params['end_date'] = $custom_end;
  $period_text = "du " . date('d/m/Y', strtotime($custom_start)) . " au " . date('d/m/Y', strtotime($custom_end));
}

// Récupération des statistiques
$sql_stats = "SELECT COUNT(*) AS nb_taches, SUM(duree_tache) AS total_heures 
             FROM timesheet t $where_clause";
$stmt_stats = $bdd->prepare($sql_stats);
$stmt_stats->execute($params);
$stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);

$nb_taches = $stats['nb_taches'] ?: 0;
$total_heures = $stats['total_heures'] ?: 0;
?>

<div class="annotation">
  <h2>Timesheet</h2>
</div>

<!-- Affichage du message d'erreur s'il existe -->
<?php if (!empty($error_message)): ?>
<div class="error-message">
  <?php echo $error_message; ?>
</div>
<?php endif; ?>

<!-- Résumé des timesheets -->
<div class="permission-summary">
  <h4>Récapitulatif des tâches <?php echo $period_text; ?></h4>
  <div class="permission-detail">
    <div class="permission-item taken">
      <strong>Heures de travail</strong>
      <p><?php echo $total_heures; ?> heures</p>
    </div>
    
    <div class="permission-item remaining">
      <strong>Nombre de tâches</strong>
      <p><?php echo $nb_taches; ?> tâches</p>
    </div>
  </div>
</div>

<div class="contenu">
  <header>
    <div class="bouton">
      <a href="?filter=last_month"><button type="submit">Mois dernier</button></a>
      <a href="?filter=last_week"><button type="submit">Semaine dernière</button></a>
      <a href="?filter=current_month"><button type="submit">Mois courant</button></a>
    </div>
  </header>

  <!-- Filtre personnalisé -->
  <div class="custom-filter">
    <form method="GET" action="">
      <input type="hidden" name="filter" value="custom">
      <label for="start">Du:</label>
      <input type="date" id="start" name="start" value="<?php echo $custom_start; ?>" required>
      <label for="end">Au:</label>
      <input type="date" id="end" name="end" value="<?php echo $custom_end; ?>" required>
      <button type="submit">Filtrer</button>
    </form>
  </div>
  <br>

  <table class="table_conge">
    <thead>
      <th>Timesheet N°</th>
      <th>Matricule</th>
      <th>Nom complet</th>
      <th>Date travail</th>
      <th>Tâche</th>
      <th>Durée (heures)</th>
      <th>Client</th>
      <th>Mission</th>
      <th>Description</th>
      <th>Note</th>
      <th>Actions</th>
    </thead>
    <tbody>
      <?php
      // Requête pour afficher les timesheet de l'employé
      $sql = "SELECT t.*, e.nom_emp, e.prenom_emp 
              FROM timesheet t 
              JOIN employer_login e ON t.matricule_emp = e.matricule_emp
              $where_clause
              ORDER BY t.id_timesheet DESC";

      $stmt = $bdd->prepare($sql);
      $stmt->execute($params);

      $has_rows = false;
      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $has_rows = true;
        echo '<tr>';
        echo '<td>' . $row['id_timesheet'] . '</td>';
        echo '<td>' . $row['matricule_emp'] . '</td>';
        echo '<td>' . $row['nom_emp'] . ' ' . $row['prenom_emp'] . '</td>';
        echo '<td>' . date('d/m/Y', strtotime($row['date_tache'])) . '</td>';
        echo '<td>' . htmlspecialchars($row['tache']) . '</td>';
        echo '<td>' . $row['duree_tache'] . '</td>';
        echo '<td>' . htmlspecialchars($row['client']) . '</td>';
        echo '<td>' . htmlspecialchars($row['mission']) . '</td>';
        echo '<td>' . htmlspecialchars($row['description_tache']) . '</td>';
        echo '<td>' . htmlspecialchars($row['note']) . '</td>';

        echo '<td class="actions-cell">';
        echo '<a href="./timesheet.php?edit=' . $row['id_timesheet'] . '" class="btn-modifier" title="Modifier cette tâche">';
        echo '<i class="bx bx-edit"></i>';
        echo '</a>';
        echo '</td>';
        echo '</tr>';
      }

      if (!$has_rows) {
        echo '<tr><td colspan="11" class="text-center">Aucune tâche trouvée pour cette période</td></tr>';
      }
      ?>
    </tbody>
  </table>

  <div class="Ajout_conge" id="Annuler">
    <a href="./timesheet.php"><button type="submit">Ajouter une tâche</button></a>
  </div>
</div>

<style>

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
    max-height: 55px;
    color: #333;
  }

  .permission-item.remaining {
    background-color: #5cb85c;
    max-height: 55px;
    color: white;
  }

  .error-message {
    background-color: #f8d7da;
    color: #721c24;
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 5px;
    text-align: center;
    font-weight: bold;
  }

  .custom-filter {
    margin-top: 15px;
    padding: 10px;
    background-color: #55555580;
    border-radius: 5px;
  }

  .custom-filter input[type="date"] {
    padding: 5px;
    border-radius: 3px;
    border: 1px solid #ccc;
    margin: 0 10px;
  }

  .custom-filter button {
    padding: 5px 15px;
    border-radius: 3px;
    background-color: #337ab7;
    color: white;
    border: none;
    cursor: pointer;
  }

  /* Styles pour les actions */
  .actions-cell {
    text-align: center;
    padding: 8px;
  }

  .btn-modifier {
    display: inline-block;
    padding: 6px 12px;
    color: orange !important;
    text-decoration: none;
    border-radius: 4px;
    font-size: 18px;
    transition: background-color 0.3s ease;
  }

  .btn-modifier:hover {
    text-decoration: none;
  }

  .btn-modifier i {
    margin-right: 4px;
    color: orange !important;
  }

  /* Responsive design pour le tableau */
  @media (max-width: 768px) {
    .table_conge {
      font-size: 12px;
    }
    
    .btn-modifier {
      padding: 4px 8px;
      font-size: 10px;
    }
  }

  /* Ajout d'une largeur fixe pour la colonne actions */
  .table_conge th:last-child,
  .table_conge td:last-child {
    width: 100px;
    min-width: 100px;
  }
</style>

<?php
include('../other/foot.php');
?>
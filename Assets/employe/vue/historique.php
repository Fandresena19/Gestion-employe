<?php
include('../other/head.php');

$Matricule_emp = $_SESSION['Matricule_emp'];

// Récupération de la date actuelle
$currentDate = new DateTime();
$annee_courante = $currentDate->format('Y');

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
$where_clause = " WHERE Matricule_emp = :matricule_emp";
$params = ['matricule_emp' => $Matricule_emp];

if ($filter == 'current_month') {
    $where_clause .= " AND MONTH(date_demande) = MONTH(CURRENT_DATE()) AND YEAR(date_demande) = YEAR(CURRENT_DATE())";
    $period_text = "du mois courant";
} elseif ($filter == 'last_month') {
    $where_clause .= " AND MONTH(date_demande) = MONTH(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH)) 
                     AND YEAR(date_demande) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))";
    $period_text = "du mois dernier";
} elseif ($filter == 'last_week') {
    $where_clause .= " AND YEARWEEK(date_demande, 1) = YEARWEEK(DATE_SUB(CURRENT_DATE(), INTERVAL 1 WEEK), 1)";
    $period_text = "de la semaine dernière";
} elseif ($filter == 'custom' && !empty($custom_start) && !empty($custom_end)) {
    $where_clause .= " AND date_demande BETWEEN :start_date AND :end_date";
    $params['start_date'] = $custom_start;
    $params['end_date'] = $custom_end;
    $period_text = "du " . date('d/m/Y', strtotime($custom_start)) . " au " . date('d/m/Y', strtotime($custom_end));
} elseif ($filter == 'all') {
    $period_text = "complet";
}
?>

<div class="contenu">
  <header>
    <h4>Historique <?php echo $period_text; ?></h4>
  </header><br>

  <?php if (!empty($error_message)): ?>
    <div class="error-message">
        <?php echo $error_message; ?>
    </div>
  <?php endif; ?>

  <div class="navigation">
    <a href="?filter=last_month"><button type="submit">Mois dernier</button></a>
    <a href="?filter=last_week"><button type="submit">Semaine dernière</button></a>
    <a href="?filter=current_month"><button type="submit">Mois courant</button></a>
    <a href="?filter=all"><button type="submit">Tout</button></a>
  </div>

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

  <table class="table_historique">
    <thead>
      <tr>
        <th>Date demande</th>
        <th>Type</th>
        <th>Motif / Tâche</th>
        <th>Statut</th>
      </tr>
    </thead>
    <?php
    // Construction de la requête SQL en utilisant les clauses de filtrage
    $sql_conge = "SELECT date_demande, 'Congé' AS type_demande, motif, statut_conge AS statut 
                FROM conge $where_clause";
    
    $sql_permission = "SELECT date_demande_per AS date_demande, 'Permission' AS type_demande, 
                      motif_per AS motif, statut_permission AS statut 
                      FROM permission WHERE Matricule_emp = :matricule_emp";
    
    $sql_timesheet = "SELECT date_tache AS date_demande, 'Timesheet' AS type_demande, 
                     tache AS motif, note AS statut 
                     FROM timesheet WHERE Matricule_emp = :matricule_emp";
    
    // Appliquer les filtres aux requêtes permission et timesheet si ce n'est pas 'all'
    if ($filter != 'all') {
        if ($filter == 'current_month') {
            $sql_permission .= " AND MONTH(date_demande_per) = MONTH(CURRENT_DATE()) AND YEAR(date_demande_per) = YEAR(CURRENT_DATE())";
            $sql_timesheet .= " AND MONTH(date_tache) = MONTH(CURRENT_DATE()) AND YEAR(date_tache) = YEAR(CURRENT_DATE())";
        } elseif ($filter == 'last_month') {
            $sql_permission .= " AND MONTH(date_demande_per) = MONTH(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH)) 
                              AND YEAR(date_demande_per) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))";
            $sql_timesheet .= " AND MONTH(date_tache) = MONTH(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH)) 
                            AND YEAR(date_tache) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))";
        } elseif ($filter == 'last_week') {
            $sql_permission .= " AND YEARWEEK(date_demande_per, 1) = YEARWEEK(DATE_SUB(CURRENT_DATE(), INTERVAL 1 WEEK), 1)";
            $sql_timesheet .= " AND YEARWEEK(date_tache, 1) = YEARWEEK(DATE_SUB(CURRENT_DATE(), INTERVAL 1 WEEK), 1)";
        } elseif ($filter == 'custom' && !empty($custom_start) && !empty($custom_end)) {
            $sql_permission .= " AND date_demande_per BETWEEN :start_date AND :end_date";
            $sql_timesheet .= " AND date_tache BETWEEN :start_date AND :end_date";
        }
    }
    
    // Union des trois requêtes avec tri par date décroissante
    $sql_complet = "($sql_conge) UNION ALL ($sql_permission) UNION ALL ($sql_timesheet) ORDER BY date_demande DESC";
    
    $stmt = $bdd->prepare($sql_complet);
    $stmt->execute($params);
    
    echo "<tbody>";
    $has_rows = false;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $has_rows = true;
        echo "<tr>";
        echo "<td>" . date('d/m/Y H:i:s', strtotime($row['date_demande'])) . "</td>";
        echo "<td>" . $row['type_demande'] . "</td>";
        echo "<td>" . $row['motif'] . "</td>";
        
        // Affichage du statut avec des couleurs différentes selon le type
        $status_class = '';
        if ($row['statut'] == 'Validé') {
            $status_class = 'status-valid';
        } elseif ($row['statut'] == 'Refusé' || $row['statut'] == 'Non validé') {
            $status_class = 'status-refused';
        } elseif ($row['statut'] == 'En traitement') {
            $status_class = 'status-pending';
        }
        
        echo "<td class='$status_class'>" . $row['statut'] . "</td>";
        echo "</tr>";
    }
    
    if (!$has_rows) {
        echo '<tr><td colspan="4" class="text-center">Aucune demande trouvée pour cette période</td></tr>';
    }
    echo "</tbody>";
    ?>
  </table>
</div>

<style>

  
  .status-valid {
    color: #5cb85c;
    font-weight: bold;
  }
  
  .status-refused {
    color: #d9534f;
    font-weight: bold;
  }
  
  .status-pending {
    color: #f0ad4e;
    font-weight: bold;
  }
  
</style>
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
$where_clause = " WHERE p.matricule_emp = :matricule_emp";
$params = ['matricule_emp' => $matricule_emp];

if ($filter == 'current_month') {
  $where_clause .= " AND MONTH(date_demande_per) = MONTH(CURRENT_DATE()) AND YEAR(date_demande_per) = YEAR(CURRENT_DATE())";
  $period_text = "du mois courant";
} elseif ($filter == 'last_month') {
  $where_clause .= " AND MONTH(date_demande_per) = MONTH(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH)) 
                     AND YEAR(date_demande_per) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))";
  $period_text = "du mois dernier";
} elseif ($filter == 'last_week') {
  $where_clause .= " AND YEARWEEK(date_demande_per, 1) = YEARWEEK(DATE_SUB(CURRENT_DATE(), INTERVAL 1 WEEK), 1)";
  $period_text = "de la semaine dernière";
} elseif ($filter == 'custom' && !empty($custom_start) && !empty($custom_end)) {
  $where_clause .= " AND date_demande_per BETWEEN :start_date AND :end_date";
  $params['start_date'] = $custom_start;
  $params['end_date'] = $custom_end;
  $period_text = "du " . date('d/m/Y', strtotime($custom_start)) . " au " . date('d/m/Y', strtotime($custom_end));
}

// Quota annuel de permission (à récupérer depuis la base de données si nécessaire)
$quotaPermissionAnnuel = 10; // Quota fixé à 10 jours (exemple)

// Récupérer toutes les permissions validées de l'employé pour l'année en cours
$sql_validees = "SELECT * FROM permission
                  WHERE matricule_emp = :matricule_emp
                  AND Statut_permission = 'Validé'
                  AND YEAR(date_debut_per) = :annee_en_cours";
$stmt_validees = $bdd->prepare($sql_validees);
$stmt_validees->execute([
  'matricule_emp' => $matricule_emp,
  'annee_en_cours' => $annee_courante
]);

// Initialize variables
$total_jours = 0;
$total_heures = 0;

// Calculer le total des jours de permission déjà pris
while ($row = $stmt_validees->fetch(PDO::FETCH_ASSOC)) {
  $PermissionJour = $row['duree_jour_per'];
  $PermissionHeures = $row['duree_heure_per'];

  // Convert to total hours
  $total_heures_permission = ($PermissionJour * 24) + $PermissionHeures;

  // Accumulate total days and hours
  $total_jours += floor($total_heures_permission / 24);
  $total_heures += $total_heures_permission % 24;
}

// Normalize hours (convert excess hours to days)
if ($total_heures >= 24) {
  $total_jours += floor($total_heures / 24);
  $total_heures = $total_heures % 24;
}

// Calculate remaining permissions
$total_pris_en_jours = $total_jours + ($total_heures / 24);
$reste_permission = $quotaPermissionAnnuel - $total_pris_en_jours;
$reste_jours = floor($reste_permission);
$reste_heures = round(($reste_permission - $reste_jours) * 24);

// Récupérer les statistiques générales (pour la période filtrée)
$sql_stats = "SELECT COUNT(*) AS nb_permissions, 
             SUM(CASE WHEN Statut_permission = 'Validé' THEN 1 ELSE 0 END) AS nb_validees,
             SUM(CASE WHEN Statut_permission = 'Refusé' THEN 1 ELSE 0 END) AS nb_refusees
             FROM permission p $where_clause";
$stmt_stats = $bdd->prepare($sql_stats);
$stmt_stats->execute($params);
$stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);

$nb_permissions = $stats['nb_permissions'] ?: 0;
$nb_validees = $stats['nb_validees'] ?: 0;
$nb_refusees = $stats['nb_refusees'] ?: 0;
?>

<div class="annotation">
  <h2>Permissions</h2>
</div>

<?php if (!empty($error_message)): ?>
  <div class="error-message">
    <?php echo $error_message; ?>
  </div>
<?php endif; ?>

<div class="permission-summary">
  <h4>Récapitulatif des permissions <?php echo $period_text; ?></h4>
  <div class="permission-detail">
    <div class="permission-item quota">
      <strong>Quota annuel</strong>
      <p><?php echo $quotaPermissionAnnuel; ?> jours</p>
    </div>
    <div class="permission-item taken">
      <strong>Permissions prises (cette année)</strong>
      <p><?php echo $total_jours; ?> jours et <?php echo $total_heures; ?> heures</p>
    </div>
    <div class="permission-item remaining">
      <strong>Reste disponible</strong>
      <p><?php echo $reste_jours; ?> jours et <?php echo $reste_heures; ?> heures</p>
    </div>
    <div class="permission-item total">
      <strong>Total Permissions (cette période)</strong>
      <p><?php echo $nb_permissions; ?></p>
    </div>
    <!-- <div class="permission-item validees">
            <strong>Permissions Validées</strong>
            <p><?php //echo $nb_validees; 
                ?></p>
        </div>
        <div class="permission-item refusees">
            <strong>Permissions Refusées</strong>
            <p><?php //echo $nb_refusees; 
                ?></p>
        </div> -->
  </div>
</div>

<div class="contenu">
  <header>
    <div class="bouton" style="float: left;">
      <a href="./permission_valide.php"><button>Permission validée</button></a>
      <a href="./permission_refuse.php"><button>Permission réfusée</button></a>
    </div>
  </header><br>

  <div class="navigation">
    <a href="?filter=last_month"><button type="submit">Mois dernier</button></a>
    <a href="?filter=last_week"><button type="submit">Semaine dernière</button></a>
    <a href="?filter=current_month"><button type="submit">Mois courant</button></a>
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

  <table class="table_conge">
    <thead>
      <th>Permission N°</th>
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
      // Requête pour afficher les permissions de l'employé
      $sql = "SELECT p.*, e.nom_emp, e.prenom_emp 
                    FROM permission p 
                    JOIN employer_login e ON p.matricule_emp = e.matricule_emp
                    $where_clause
                    ORDER BY p.date_demande_per DESC";

      $stmt = $bdd->prepare($sql);
      $stmt->execute($params);

      $has_rows = false;
      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $has_rows = true;
        echo '<tr>';
        echo '<td>' . $row['id_permission'] . '</td>';
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
        echo '<tr><td colspan="9" class="text-center">Aucune permission trouvée pour cette période</td></tr>';
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
    height: 50px;
    border-radius: 5px;
  }

  @media(max-width: 768px) {
    .permission-item {
      min-width: 150px;
      height: auto;
    }

  }

  .permission-item.quota {
    background-color: #5bc0de;
    color: white;
  }

  .permission-item.taken {
    background-color: #f0ad4e;
    color: #333;
  }

  .permission-item.remaining {
    background-color: #5cb85c;
    color: white;
  }

  .permission-item.total {
    background-color: #428bca;
    color: white;
  }

  .permission-item.validees {
    background-color: #337ab7;
    color: white;
  }

  .permission-item.refusees {
    background-color: #d9534f;
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
</style>

<?php
include('../other/foot.php');
?>
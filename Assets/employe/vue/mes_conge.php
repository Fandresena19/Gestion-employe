<?php

include('../other/head.php');

$matricule_emp = $_SESSION['Matricule_emp'];

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
$where_clause = " WHERE c.matricule_emp = :matricule_emp";
$params = ['matricule_emp' => $matricule_emp];

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
}

// Quota annuel de congé (à récupérer depuis la base de données si nécessaire)
$quotaCongeAnnuel = 30; // Quota fixé à 30 jours

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

// Initialize variables
$total_jours = 0;
$total_heures = 0;

// Calculer le total des jours de congé déjà pris
while ($row = $stmt_valides->fetch(PDO::FETCH_ASSOC)) {
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

// Récupérer les statistiques générales (pour la période filtrée)
$sql_stats = "SELECT COUNT(*) AS nb_conges, 
             SUM(CASE WHEN statut_conge = 'Validé' THEN 1 ELSE 0 END) AS nb_valides,
             SUM(CASE WHEN statut_conge = 'Refusé' THEN 1 ELSE 0 END) AS nb_refuses
             FROM conge c $where_clause";
$stmt_stats = $bdd->prepare($sql_stats);
$stmt_stats->execute($params);
$stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);

$nb_conges = $stats['nb_conges'] ?: 0;
$nb_valides = $stats['nb_valides'] ?: 0;
$nb_refuses = $stats['nb_refuses'] ?: 0;
?>

<div class="annotation">
    <h2>Congé</h2>
</div>

<?php if (!empty($error_message)): ?>
    <div class="error-message">
        <?php echo $error_message; ?>
    </div>
<?php endif; ?>

<div class="conge-summary">
    <h4>Récapitulatif des congés <?php echo $period_text; ?></h4>
    <div class="conge-detail">
        <div class="conge-item quota">
            <strong>Quota annuel</strong>
            <p><?php echo $quotaCongeAnnuel; ?> jours</p>
        </div>
        <div class="conge-item taken">
            <strong>Congés pris (cette année)</strong>
            <p><?php echo $total_jours; ?> jours et <?php echo $total_heures; ?> heures</p>
        </div>
        <div class="conge-item remaining">
            <strong>Reste disponible</strong>
            <p><?php echo $reste_jours; ?> jours et <?php echo $reste_heures; ?> heures</p>
        </div>
        <div class="conge-item total">
            <strong>Total Congés (cette période)</strong>
            <p><?php echo $nb_conges; ?> Congés</p>
        </div>
        <!-- <div class="conge-item valides">
            <strong>Congés Validés</strong>
            <p>
                <?php // echo $nb_valides; 
                ?>
            </p>
        </div>
        <div class="conge-item refuses">
            <strong>Congés Refusés</strong>
            <p>
                <?php // echo $nb_refuses; 
                ?>
            </p>
        </div> -->
    </div>
</div>

<div class="contenu">
    <header>
        <div class="bouton">
            <a href="./conge_valide.php"><button type="submit">Congé validé</button></a>
            <a href="./conge_refuse.php"><button type="submit">Congé refusé</button></a>
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
            // Requête pour afficher les congés de l'employé
            $sql = "SELECT c.*, e.nom_emp, e.prenom_emp 
                    FROM conge c 
                    JOIN employer_login e ON c.matricule_emp = e.matricule_emp
                    $where_clause
                    ORDER BY c.date_demande DESC";

            $stmt = $bdd->prepare($sql);
            $stmt->execute($params);

            $has_rows = false;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $has_rows = true;
                echo "<tr>";
                echo "<td>" . $row['id_conge'] . "</td>";
                echo "<td>" . $row['nom_emp'] . ' ' . $row['prenom_emp'] . "</td>";
                echo "<td>" . date('d/m/Y H:i:s', strtotime($row['date_demande'])) . "</td>";
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
                echo '<tr><td colspan="9" class="text-center">Aucun congé trouvé pour cette période</td></tr>';
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
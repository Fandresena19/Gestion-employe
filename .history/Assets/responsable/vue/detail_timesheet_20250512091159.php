<?php
include('../other/head.php');

// Récupération du mois et de l'année actuels ou des paramètres passés dans l'URL
$moisActuel = isset($_GET['mois']) ? intval($_GET['mois']) : intval(date('m'));
$anneeActuelle = isset($_GET['annee']) ? intval($_GET['annee']) : intval(date('Y'));

// Vérification de la validité du mois
if ($moisActuel < 1) {
  $moisActuel = 12;
  $anneeActuelle--;
} elseif ($moisActuel > 12) {
  $moisActuel = 1;
  $anneeActuelle++;
}

// Date du premier jour du mois
$premierJourDuMois = $anneeActuelle . '-' . str_pad($moisActuel, 2, '0', STR_PAD_LEFT) . '-01';
// Date du dernier jour du mois
$dernierJourDuMois = date('Y-m-t', strtotime($premierJourDuMois));

// Noms des mois pour l'affichage
$nomsMois = [
  1 => 'Janvier',
  2 => 'Février',
  3 => 'Mars',
  4 => 'Avril',
  5 => 'Mai',
  6 => 'Juin',
  7 => 'Juillet',
  8 => 'Août',
  9 => 'Septembre',
  10 => 'Octobre',
  11 => 'Novembre',
  12 => 'Décembre'
];

// Calcul du mois précédent et suivant pour la navigation
$moisPrecedent = $moisActuel - 1;
$anneePrecedente = $anneeActuelle;
if ($moisPrecedent < 1) {
  $moisPrecedent = 12;
  $anneePrecedente--;
}

$moisSuivant = $moisActuel + 1;
$anneeSuivante = $anneeActuelle;
if ($moisSuivant > 12) {
  $moisSuivant = 1;
  $anneeSuivante++;
}

// Vérifier si l'utilisateur est un responsable
$estResponsable = false;
if (isset($_SESSION['id_user']) && isset($_SESSION['role'])) {
  $estResponsable = ($_SESSION['role'] === 'Responsable');
}

// D'abord récupérer tous les employés
$requeteEmployes = $bdd->query('SELECT matricule_emp, nom_emp, prenom_emp FROM employer_login ORDER BY nom_emp');

// Tableau pour stocker les informations des employés
$employesInfos = [];

// Initialiser les données pour tous les employés
while ($employe = $requeteEmployes->fetch(PDO::FETCH_ASSOC)) {
  $matricule = $employe['matricule_emp'];

  $employesInfos[$matricule] = [
    'nom_complet' => $employe['nom_emp'] . ' ' . $employe['prenom_emp'],
    'total_heures' => 0,
    'nb_taches' => 0,
    'taches' => []
  ];
}

// Requête pour récupérer toutes les tâches du mois pour tous les employés
$requeteTaches = $bdd->prepare('
    SELECT t.*, e.nom_emp, e.prenom_emp
    FROM timesheet t
    JOIN employer_login e ON e.matricule_emp = t.matricule_emp
    WHERE t.date_tache BETWEEN :debut AND :fin
    ORDER BY e.matricule_emp, t.date_tache DESC
');

$requeteTaches->execute([
  'debut' => $premierJourDuMois,
  'fin' => $dernierJourDuMois
]);

// Compléter les informations des employés avec leurs tâches
while ($tache = $requeteTaches->fetch(PDO::FETCH_ASSOC)) {
  $matricule = $tache['matricule_emp'];

  if (isset($employesInfos[$matricule])) {
    $tacheInfo = [
      'id_timesheet' => $tache['id_timesheet'],
      'tache' => $tache['tache'],
      'date_tache' => $tache['date_tache'],
      'duree_tache' => $tache['duree_tache'],
      'client' => $tache['client'],
      'description_tache' => $tache['description_tache'],
      'note' => $tache['note']
    ];

    $employesInfos[$matricule]['taches'][] = $tacheInfo;
    $employesInfos[$matricule]['total_heures'] += intval($tache['duree_tache']);
    $employesInfos[$matricule]['nb_taches']++;
  }
}

// Comptage des tâches pour chaque employé dans le mois sélectionné
foreach ($employesInfos as $matricule => &$employe) {
  $stmt = $bdd->prepare('
        SELECT COUNT(*) AS total, SUM(duree_tache) AS heures_totales 
        FROM timesheet 
        WHERE matricule_emp = :matricule AND date_tache BETWEEN :debut AND :fin
    ');
  $stmt->execute([
    'matricule' => $matricule,
    'debut' => $premierJourDuMois,
    'fin' => $dernierJourDuMois
  ]);
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  $employe['nb_taches'] = $result['total'] ?: 0;
  $employe['total_heures'] = $result['heures_totales'] ?: 0;
}
unset($employe); // Détruire la référence à la dernière valeur
?>

<style>
  html,
  body {
    height: 100%;
    margin: 0;
    overflow: hidden;
  }

  .contenu {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    margin-top: -2% !important;
    overflow-y: auto;
  }

  .contenu header {
    flex-shrink: 0;
  }

  .navigation {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    background-color: rgba(255, 255, 255, 0.1);
    padding: 10px;
    border-radius: 5px;
  }

  .navigation button{
    background-color: #6A2C82;
    border: none;
    padding: 5px;
  }

  .mois-actuel {
    font-size: 1.2em;
    font-weight: bold;
    color: white;
  }

  .nav-buttons {
    display: flex;
    gap: 10px;
  }

  .scrollbar {
    max-height: 70vh;
    overflow-y: auto;
    padding-right: 5px;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
  }

  th,
  td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
    color: white !important;
  }

  tr:hover {
    background-color: transparent !important;
  }

  .recap-header {
    background-color: rgb(154, 144, 144);
    font-weight: bold;
  }

  .tache-row {
    background-color: rgba(100, 149, 237, 0.7);
  }

  .no-tache {
    background-color: rgba(180, 180, 180, 0.5);
    font-style: italic;
  }

  .bouton-retour {
    margin-right: 10px;
  }

  .header-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
  }

  .tache-detail {
    margin-bottom: 5px;
  }

  .tache-description {
    max-width: 300px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
</style>

<div class="annotation">
  <h2>Timesheet</h2>
</div><br>

<div class="contenu">
  <header>
    <div class="header-actions">
      <h4>Détails des Tâches</h4>
      <div class="bouton bouton-retour">
        <a href="./liste_timesheet.php"><button type="submit">Retour</button></a>
      </div>
    </div>
  </header> <br />

  <div class="navigation">
    <a href="?mois=<?php echo $moisPrecedent; ?>&annee=<?php echo $anneePrecedente; ?>">
      <button type="button">&lt; Mois précédent</button>
    </a>
    <div class="mois-actuel">
      <?php echo $nomsMois[$moisActuel] . ' ' . $anneeActuelle; ?>
    </div>
    <a href="?mois=<?php echo $moisSuivant; ?>&annee=<?php echo $anneeSuivante; ?>">
      <button type="button">Mois suivant &gt;</button>
    </a>
  </div>

  <div class="scrollbar">
    <table>
      <thead>
        <tr>
          <th>Nom Complet</th>
          <th>Nombre de Tâches</th>
          <th>Total Heures</th>
          <th>Moyenne d'Heures/Tâche</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($employesInfos as $matricule => $employe): ?>
          <tr class="recap-header">
            <td><?php echo $employe['nom_complet']; ?></td>
            <td><?php echo $employe['nb_taches']; ?></td>
            <td><?php echo $employe['total_heures']; ?> heures</td>
            <td>
              <?php
              echo $employe['nb_taches'] > 0
                ? round($employe['total_heures'] / $employe['nb_taches'], 1) . ' heures'
                : '0 heure';
              ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <?php foreach ($employesInfos as $matricule => $employe): ?>
      <table>
        <thead>
          <tr>
            <th colspan="6">Détails des Tâches - <?php echo $employe['nom_complet']; ?></th>
          </tr>
          <tr>
            <th>Tâche N°</th>
            <th>Date</th>
            <th>Durée (heures)</th>
            <th>Client</th>
            <th>Tâche</th>
            <th>Description</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($employe['taches'])): ?>
            <tr class="no-tache">
              <td colspan="6" style="text-align: center;">Aucune tâche enregistrée pour ce mois</td>
            </tr>
          <?php else: ?>
            <?php foreach ($employe['taches'] as $tache): ?>
              <tr class="tache-row">
                <td><?php echo $tache['id_timesheet']; ?></td>
                <td><?php echo date('d/m/Y', strtotime($tache['date_tache'])); ?></td>
                <td><?php echo $tache['duree_tache']; ?></td>
                <td><?php echo $tache['client']; ?></td>
                <td><?php echo $tache['tache']; ?></td>
                <td class="tache-description" title="<?php echo htmlspecialchars($tache['description_tache']); ?>">
                  <?php echo htmlspecialchars($tache['description_tache']); ?>
                </td>
              </tr>
              <?php if (!empty($tache['note'])): ?>
                <tr>
                  <td colspan="6" class="tache-detail">
                    <strong>Note:</strong> <?php echo htmlspecialchars($tache['note']); ?>
                  </td>
                </tr>
              <?php endif; ?>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    <?php endforeach; ?>
  </div>
</div>

<?php
include('../other/foot.php');
?>
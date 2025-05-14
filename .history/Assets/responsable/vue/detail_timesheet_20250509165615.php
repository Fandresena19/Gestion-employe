<?php
include('../other/head.php');

// Initialiser les variables pour la navigation dans le temps
$periode = isset($_GET['periode']) ? $_GET['periode'] : 'semaine_courante';
$mois = isset($_GET['mois']) ? intval($_GET['mois']) : intval(date('m'));
$annee = isset($_GET['annee']) ? intval($_GET['annee']) : intval(date('Y'));
$semaine = isset($_GET['semaine']) ? intval($_GET['semaine']) : intval(date('W'));

// Vérifier si l'utilisateur connecté est un responsable
$estResponsable = false;
if (isset($_SESSION['role_emp']) && $_SESSION['role_emp'] === 'Admin') {
    $estResponsable = true;
}

// Déterminer les dates de début et de fin selon la période sélectionnée
switch ($periode) {
    case 'mois_precedent':
        $mois = $mois - 1;
        if ($mois < 1) {
            $mois = 12;
            $annee--;
        }
        $dateDebut = "$annee-$mois-01";
        $dateFin = date('Y-m-t', strtotime($dateDebut));
        break;
    case 'semaine_precedente':
        $timestamp = strtotime($annee . "W" . $semaine . "1") - 7 * 24 * 3600;
        $semaine = date('W', $timestamp);
        $annee = date('Y', $timestamp);
        $dateDebut = date('Y-m-d', strtotime($annee . "W" . $semaine . "1"));
        $dateFin = date('Y-m-d', strtotime($dateDebut . "+6 days"));
        break;
    case 'semaine_courante':
        $dateDebut = date('Y-m-d', strtotime('monday this week'));
        $dateFin = date('Y-m-d', strtotime('sunday this week'));
        break;
    case 'mois_courant':
    default:
        $dateDebut = date('Y-m-01');
        $dateFin = date('Y-m-t');
        break;
}

// Formater les dates pour l'affichage
$dateDebutFormatee = date('d/m/Y', strtotime($dateDebut));
$dateFinFormatee = date('d/m/Y', strtotime($dateFin));

// Requête pour récupérer tous les employés
$requeteEmployes = $bdd->query('
    SELECT matricule_emp, nom_emp, prenom_emp
    FROM employer_login
    ORDER BY nom_emp, prenom_emp
');

// Tableau pour stocker les informations des employés
$employesInfos = [];

// Charger tous les employés d'abord
while ($employe = $requeteEmployes->fetch(PDO::FETCH_ASSOC)) {
    $matricule = $employe['matricule_emp'];
    $employesInfos[$matricule] = [
        'nom_complet' => $employe['nom_emp'] . ' ' . $employe['prenom_emp'],
        'heures_totales' => 0,
        'timesheets' => []
    ];
}

// Requête pour récupérer les timesheet dans la période spécifiée
$requeteTimesheets = $bdd->prepare('
    SELECT t.*, e.nom_emp, e.prenom_emp
    FROM timesheet t
    JOIN employer_login e ON t.matricule_emp = e.matricule_emp
    WHERE t.date_tache BETWEEN :dateDebut AND :dateFin
    ORDER BY e.nom_emp, e.prenom_emp, t.date_tache
');
$requeteTimesheets->execute([
    'dateDebut' => $dateDebut,
    'dateFin' => $dateFin
]);

// Récupérer et associer les timesheet aux employés
while ($data = $requeteTimesheets->fetch(PDO::FETCH_ASSOC)) {
    $matricule = $data['matricule_emp'];

    $timesheet = [
        'id_timesheet' => $data['id_timesheet'],
        'tache' => $data['tache'],
        'date_tache' => $data['date_tache'],
        'duree_tache' => $data['duree_tache'],
        'client' => $data['client'],
        'description_tache' => $data['description_tache'],
        'note' => $data['note']
    ];

    // Mettre à jour le total des heures
    $employesInfos[$matricule]['heures_totales'] += $data['duree_tache'];

    // Ajouter la timesheet
    $employesInfos[$matricule]['timesheets'][] = $timesheet;
}

// Fonction pour formater la durée en heures et minutes
function afficherDureeEnHeuresEtMinutes($duree) {
    $heures = floor($duree);
    $minutes = round(($duree - $heures) * 60);
    
    return $heures . 'h ' . ($minutes < 10 ? '0' . $minutes : $minutes) . 'min';
}

// Fonction pour générer les liens de navigation
function genererLienNavigation($periode, $mois, $annee, $semaine) {
    $moisPrecedent = $mois - 1;
    $anneeMoisPrecedent = $annee;
    if ($moisPrecedent < 1) {
        $moisPrecedent = 12;
        $anneeMoisPrecedent--;
    }

    $moisSuivant = $mois + 1;
    $anneeMoisSuivant = $annee;
    if ($moisSuivant > 12) {
        $moisSuivant = 1;
        $anneeMoisSuivant++;
    }

    // Pour les semaines
    $timestampSemainePrecedente = strtotime($annee . "W" . $semaine . "1") - 7 * 24 * 3600;
    $semainePrecedente = date('W', $timestampSemainePrecedente);
    $anneeSemainePrecedente = date('Y', $timestampSemainePrecedente);
    
    $timestampSemaineSuivante = strtotime($annee . "W" . $semaine . "1") + 7 * 24 * 3600;
    $semaineSuivante = date('W', $timestampSemaineSuivante);
    $anneeSemaineSuivante = date('Y', $timestampSemaineSuivante);

    $liens = [
        'mois_precedent' => "?periode=mois_precedent&mois=$moisPrecedent&annee=$anneeMoisPrecedent",
        'mois_courant' => "?periode=mois_courant",
        'mois_suivant' => "?periode=mois_suivant&mois=$moisSuivant&annee=$anneeMoisSuivant",
        'semaine_precedente' => "?periode=semaine_precedente&semaine=$semainePrecedente&annee=$anneeSemainePrecedente",
        'semaine_courante' => "?periode=semaine_courante",
        'semaine_suivante' => "?periode=semaine_suivante&semaine=$semaineSuivante&annee=$anneeSemaineSuivante"
    ];

    return $liens;
}

$liens = genererLienNavigation($periode, $mois, $annee, $semaine);
?>

<div class="annotation">
  <h2>Feuille de Temps</h2>
</div><br>

<div class="contenu">
  <header>
    <h4>Feuille de Temps - <?php echo "$dateDebutFormatee au $dateFinFormatee"; ?></h4>
    
    <div class="navigation">
      <div class="periode-selector">
        <a href="<?php echo $liens['mois_precedent']; ?>" class="btn-nav <?php echo ($periode == 'mois_precedent') ? 'active' : ''; ?>">Mois Précédent</a>
        <a href="<?php echo $liens['mois_courant']; ?>" class="btn-nav <?php echo ($periode == 'mois_courant') ? 'active' : ''; ?>">Mois Courant</a>
        <a href="<?php echo $liens['semaine_precedente']; ?>" class="btn-nav <?php echo ($periode == 'semaine_precedente') ? 'active' : ''; ?>">Semaine Précédente</a>
        <a href="<?php echo $liens['semaine_courante']; ?>" class="btn-nav <?php echo ($periode == 'semaine_courante') ? 'active' : ''; ?>">Semaine Courante</a>
      </div>
      
      <div class="bouton">
        <?php if (!$estResponsable): ?>
          <a href="./create_timesheet.php"><button type="button">Ajouter</button></a>
        <?php endif; ?>
        <a href="../dashboard.php"><button type="submit">Retour</button></a>
      </div>
    </div>
  </header> <br />

  <div class="scrollbar">

    <table class="summary-table">
      <thead>
        <tr>
          <th>Nom Complet</th>
          <th>Heures Totales</th>
          <th>Nombre de Tâches</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($employesInfos as $matricule => $employe): ?>
          <?php if (!empty($employe['timesheets'])): ?>
            <tr class="solde-header">
              <td><?php echo $employe['nom_complet']; ?></td>
              <td><?php echo afficherDureeEnHeuresEtMinutes($employe['heures_totales']); ?></td>
              <td><?php echo count($employe['timesheets']); ?></td>
            </tr>
          <?php endif; ?>
        <?php endforeach; ?>
      </tbody>
    </table>

    <?php foreach ($employesInfos as $matricule => $employe): ?>
      <?php if (!empty($employe['timesheets'])): ?>
        <table class="detail-table">
          <thead>
            <tr>
              <th colspan="<?php echo $estResponsable ? '4' : '7'; ?>">Détails des Tâches - <?php echo $employe['nom_complet']; ?></th>
            </tr>
            <tr>
              <?php if (!$estResponsable): ?>
                <th>ID</th>
                <th>Date</th>
              <?php endif; ?>
              <th>Tâche</th>
              <th>Client</th>
              <?php if (!$estResponsable): ?>
                <th>Durée</th>
              <?php endif; ?>
              <th>Description</th>
              <?php if (!$estResponsable): ?>
                <th>Note</th>
              <?php endif; ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($employe['timesheets'] as $timesheet): ?>
              <tr>
                <?php if (!$estResponsable): ?>
                  <td><?php echo $timesheet['id_timesheet']; ?></td>
                  <td><?php echo date('d/m/Y', strtotime($timesheet['date_tache'])); ?></td>
                <?php endif; ?>
                <td><?php echo $timesheet['tache']; ?></td>
                <td><?php echo $timesheet['client']; ?></td>
                <?php if (!$estResponsable): ?>
                  <td><?php echo afficherDureeEnHeuresEtMinutes($timesheet['duree_tache']); ?></td>
                <?php endif; ?>
                <td><?php echo $timesheet['description_tache']; ?></td>
                <?php if (!$estResponsable): ?>
                  <td><?php echo $timesheet['note']; ?></td>
                <?php endif; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    <?php endforeach; ?>

    <?php
    // Vérifier s'il n'y a aucune donnée pour la période
    $aucuneDonnee = true;
    foreach ($employesInfos as $employe) {
      if (!empty($employe['timesheets'])) {
        $aucuneDonnee = false;
        break;
      }
    }
    
    if ($aucuneDonnee): 
    ?>
    <div class="no-data">
      <p>Aucune donnée disponible pour cette période</p>
    </div>
    <?php endif; ?>

  </div>
</div>

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

  th, td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
    color: white !important;
  }

  tr:hover {
    background-color: transparent !important;
  }

  .summary-table .solde-header {
    background-color: rgb(154, 144, 144);
    font-weight: bold;
  }

  .detail-table thead tr:first-child th {
    background-color: #444;
    font-size: 1.1em;
  }

  .detail-table thead tr:nth-child(2) th {
    background-color: #555;
  }

  .navigation {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
  }

  .periode-selector {
    display: flex;
    gap: 10px;
  }

  .btn-nav {
    padding: 8px 15px;
    background-color: #666;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    font-size: 14px;
  }

  .btn-nav:hover {
    background-color: #777;
  }

  .btn-nav.active {
    background-color: #444;
    font-weight: bold;
  }

  .no-data {
    text-align: center;
    padding: 30px;
    background-color: rgba(255, 255, 255, 0.1);
    border-radius: 5px;
    color: white;
  }
</style>

<script src="../js/Sidebar.js"></script>

<?php
include('../other/foot.php');
?>
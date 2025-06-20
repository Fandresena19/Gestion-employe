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

// Récupération des paramètres de filtrage
$filtreStatut = isset($_GET['statut']) ? $_GET['statut'] : 'tous';
$filtreEmploye = isset($_GET['employe']) ? $_GET['employe'] : 'tous';

// Quota annuel de permission
$quotaPermissionAnnuel = 10;

// D'abord récupérer tous les employés
$requeteEmployes = $bdd->query('SELECT matricule_emp, nom_emp, prenom_emp FROM employer_login ORDER BY nom_emp');

// Tableau pour stocker les informations des employés
$employesInfos = [];
$listeEmployes = []; // Pour le filtre

// Initialiser les données pour tous les employés
while ($employe = $requeteEmployes->fetch(PDO::FETCH_ASSOC)) {
  $matricule = $employe['matricule_emp'];
  $nomComplet = $employe['nom_emp'] . ' ' . $employe['prenom_emp'];
  
  $listeEmployes[$matricule] = $nomComplet;

  $employesInfos[$matricule] = [
    'nom_complet' => $nomComplet,
    'solde_initial' => $quotaPermissionAnnuel,
    'solde_actuel' => $quotaPermissionAnnuel,
    'permissions' => [],
    'nb_permissions_valides' => 0
  ];
}

// Construction de la requête de base
$sql = '
    SELECT e.matricule_emp, p.*
    FROM permission p
    JOIN employer_login e ON e.matricule_emp = p.matricule_emp
    WHERE 1=1
';

$params = [];

// Ajouter le filtre par date si navigue par mois
$sql .= ' AND p.date_debut_per BETWEEN :debut AND :fin';
$params['debut'] = $premierJourDuMois;
$params['fin'] = $dernierJourDuMois;

// Ajouter le filtre par statut si nécessaire
if ($filtreStatut !== 'tous') {
  $sql .= ' AND p.Statut_permission = :statut';
  $params['statut'] = $filtreStatut;
}

// Ajouter le filtre par employé si nécessaire
if ($filtreEmploye !== 'tous') {
  $sql .= ' AND e.matricule_emp = :matricule';
  $params['matricule'] = $filtreEmploye;
}

$sql .= ' ORDER BY e.matricule_emp, p.date_debut_per';

// Préparer et exécuter la requête
$requetePermissions = $bdd->prepare($sql);
$requetePermissions->execute($params);

// Fonction pour calculer la durée de permission en jours
function calculerDureePermission($dateDebut, $dateFin)
{
  if (!$dateDebut || !$dateFin) return 0;

  $debut = new DateTime($dateDebut);
  $fin = new DateTime($dateFin);
  $interval = date_diff($debut, $fin);

  // Calcul des jours et heures
  $dureeJours = $interval->days;
  $dureeHeures = $interval->h;

  return $dureeJours + ($dureeHeures / 24);
}

// Fonction pour formater la durée
function afficherDureeEnJoursEtHeure($jours)
{
  $heures = ($jours - floor($jours)) * 24;
  $jours = floor($jours);

  return $jours . ' jours ' . round($heures) . ' heures';
}

// Compléter les informations des employés avec leurs permissions
while ($data = $requetePermissions->fetch(PDO::FETCH_ASSOC)) {
  $matricule = $data['matricule_emp'];

  if (isset($employesInfos[$matricule])) {
    $dureePermission = calculerDureePermission($data['date_debut_per'], $data['date_fin_per']);

    $permission = [
      'id_permission' => $data['id_permission'],
      'date_demande' => $data['date_demande_per'],
      'date_debut' => $data['date_debut_per'],
      'date_fin' => $data['date_fin_per'],
      'duree_jour' => $data['duree_jour_per'],
      'duree_heure' => $data['duree_heure_per'],
      'duree' => $dureePermission,
      'motif' => $data['motif_per'],
      'statut' => $data['Statut_permission']
    ];

    // Mettre à jour le solde uniquement pour les permissions validées
    if ($data['Statut_permission'] == 'Validé') {
      $employesInfos[$matricule]['solde_actuel'] -= $dureePermission;
      $employesInfos[$matricule]['nb_permissions_valides']++;
    }

    $employesInfos[$matricule]['permissions'][] = $permission;
  }
}

// Comptage des permissions validées pour chaque employé pour l'année entière
foreach ($employesInfos as $matricule => &$employe) {
  $stmt = $bdd->prepare('
    SELECT COUNT(*) AS total, SUM(duree_jour_per + duree_heure_per/24) AS jours_totaux
    FROM permission 
    WHERE matricule_emp = :matricule AND Statut_permission = "Validé"
    AND YEAR(date_debut_per) = :annee
  ');
  $stmt->execute([
    'matricule' => $matricule,
    'annee' => $anneeActuelle
  ]);
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  $employe['nb_permissions_valides'] = $result['total'] ?: 0;
  $employe['jours_pris'] = $result['jours_totaux'] ?: 0;
  $employe['solde_actuel'] = $quotaPermissionAnnuel - $employe['jours_pris'];
}
unset($employe); // Détruire la référence à la dernière valeur

// Filtrer les employés à afficher si un filtre est appliqué
if ($filtreEmploye !== 'tous') {
  $employesInfosFiltres = [];
  if (isset($employesInfos[$filtreEmploye])) {
    $employesInfosFiltres[$filtreEmploye] = $employesInfos[$filtreEmploye];
  }
  $employesInfos = $employesInfosFiltres;
}
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
    height: 90vh !important;
    margin-bottom: 20% !important;
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

  .navigation button {
    background-color: #6A2C82;
    border: none;
    padding: 5px 10px;
    border-radius: 5px;
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

  .filters {
    background-color: rgba(255, 255, 255, 0.1);
    padding: 5px;
    border-radius: 5px;
    margin-bottom: 10px;
    display: flex !important;
    flex-wrap: wrap;
    gap: 10px;
  }

  .filter-group {
    display: flex;
    align-items: center;
    gap: 5px;
  }

  .filter-group label {
    color: white;
    font-weight: bold;
  }

  .filter-group select {
    padding: 5px;
    border-radius: 3px;
    border: 1px solid #ddd;
    background-color: rgba(255, 255, 255, 0.8);
  }

  .filter-button {
    background-color: #6A2C82;
    color: white;
    border: none;
    padding: 5px 15px;
    border-radius: 5px;
    cursor: pointer;
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
  }

  tr:hover {
    background-color: transparent !important;
  }

  .solde-header {
    background-color: rgb(154, 144, 144);
    font-weight: bold;
  }

  .permission-valide {
    background-color: rgba(95, 255, 95, 0.7);
  }

  .permission-attente {
    background-color: rgba(245, 166, 93, 0.75);
  }

  .permission-refuse {
    background-color: rgba(251, 98, 98, 0.77);
  }

  .no-permission {
    background-color: rgba(180, 180, 180, 0.5);
    font-style: italic;
  }

  .header-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
  }

  .legende {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-bottom: 15px;
    background-color: rgba(0, 0, 0, 0.2);
    padding: 10px;
    border-radius: 5px;
  }

  .legende-item {
    display: flex;
    align-items: center;
    gap: 5px;
  }

  .legende-couleur {
    width: 20px;
    height: 20px;
    border-radius: 3px;
  }

  .legende-texte {
    color: white;
  }
</style>



<div class="annotation">
  <h2>Permission</h2>
</div><br>


<div class="contenu">
  <header>
    <div class="header-actions">
      <h4>Détails des Permissions</h4>
      <div class="bouton">
        <a href="./liste_permission.php"><button type="submit">Retour</button></a>
      </div>
    </div>
  </header> <br />

  <div class="navigation">
    <a href="?mois=<?php echo $moisPrecedent; ?>&annee=<?php echo $anneePrecedente; ?>&statut=<?php echo $filtreStatut; ?>&employe=<?php echo $filtreEmploye; ?>">
      <button type="button">&lt; Mois précédent</button>
    </a>
    <div class="mois-actuel">
      <?php echo $nomsMois[$moisActuel] . ' ' . $anneeActuelle; ?>
    </div>
    <a href="?mois=<?php echo $moisSuivant; ?>&annee=<?php echo $anneeSuivante; ?>&statut=<?php echo $filtreStatut; ?>&employe=<?php echo $filtreEmploye; ?>">
      <button type="button">Mois suivant &gt;</button>
    </a>
  </div>

  <div class="filters">
    <form method="GET" action="" id="filtre-form">
      <input type="hidden" name="mois" value="<?php echo $moisActuel; ?>">
      <input type="hidden" name="annee" value="<?php echo $anneeActuelle; ?>">
      
<div class="filtre">
<div class="filter-group">
        <label for="statut">Statut:</label>
        <select name="statut" id="statut" onchange="document.getElementById('filtre-form').submit()">
          <option value="tous" <?php echo $filtreStatut === 'tous' ? 'selected' : ''; ?>>Tous les statuts</option>
          <option value="Validé" <?php echo $filtreStatut === 'Validé' ? 'selected' : ''; ?>>Validé</option>
          <option value="En attente" <?php echo $filtreStatut === 'En attente' ? 'selected' : ''; ?>>En attente</option>
          <option value="Refusé" <?php echo $filtreStatut === 'Refusé' ? 'selected' : ''; ?>>Refusé</option>
        </select>
      </div>

      <div class="filter-group">
        <label for="employe">Employé:</label>
        <select name="employe" id="employe" onchange="document.getElementById('filtre-form').submit()">
          <option value="tous" <?php echo $filtreEmploye === 'tous' ? 'selected' : ''; ?>>Tous les employés</option>
          <?php foreach ($listeEmployes as $matricule => $nom): ?>
            <option value="<?php echo $matricule; ?>" <?php echo $filtreEmploye === $matricule ? 'selected' : ''; ?>>
              <?php echo $nom; ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
</div>
    </form>
  </div>

  <div class="scrollbar">

    <table>
      <thead>
        <tr>
          <th>Nom Complet</th>
          <th>Solde Initial</th>
          <th>Solde Actuel</th>
          <th>Nombre Permissions Validées (<?php echo $anneeActuelle; ?>)</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($employesInfos as $matricule => $employe): ?>
          <tr class="solde-header">
            <td><?php echo $employe['nom_complet']; ?></td>
            <td><?php echo afficherDureeEnJoursEtHeure($employe['solde_initial']); ?></td>
            <td><?php echo afficherDureeEnJoursEtHeure($employe['solde_actuel']); ?></td>
            <td><?php echo $employe['nb_permissions_valides']; ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <?php foreach ($employesInfos as $matricule => $employe): ?>
      <table>
        <thead>
          <tr>
            <th colspan="7">Détails des Permissions - <?php echo $employe['nom_complet']; ?> (<?php echo $nomsMois[$moisActuel] . ' ' . $anneeActuelle; ?>)</th>
          </tr>
          <tr>
            <th>Permission N°</th>
            <th>Date Demande</th>
            <th>Date Début</th>
            <th>Date Fin</th>
            <th>Durée</th>
            <th>Motif</th>
            <th>Statut</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($employe['permissions'])): ?>
            <tr class="no-permission">
              <td colspan="7" style="text-align: center;">Aucune permission pour ce mois</td>
            </tr>
          <?php else: ?>
            <?php foreach ($employe['permissions'] as $permission): ?>
              <tr class="<?php
                          echo $permission['statut'] == 'Validé' ? 'permission-valide' : ($permission['statut'] == 'En attente' ? 'permission-attente' : 'permission-refuse');
                          ?>">
                <td><?php echo $permission['id_permission']; ?></td>
                <td><?php echo date('d/m/Y H:i:s', strtotime($permission['date_demande'])); ?></td>
                <td><?php echo date('d/m/Y H:i:s', strtotime($permission['date_debut'])); ?></td>
                <td><?php echo date('d/m/Y H:i:s', strtotime($permission['date_fin'])); ?></td>
                <td><?php echo $permission['duree_jour'] . ' jours ' . $permission['duree_heure'] . ' heures'; ?></td>
                <td><?php echo $permission['motif']; ?></td>
                <td><?php echo $permission['statut']; ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    <?php endforeach; ?>

  </div>
</div>

<style>
  .filtre{
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
  }
  /* Specific styles for select elements to make background darker */
  select {
    background-color: #2c2c2c !important;
    color: white !important;
    max-width: 150px;
    appearance: none !important; /* Remove default browser styling */
    background-image: linear-gradient(45deg, transparent 50%, #fff 50%), linear-gradient(135deg, #fff 50%, transparent 50%)  !important;
    background-position: calc(100% - 20px) calc(1em + 2px), calc(100% - 15px) calc(1em + 2px) !important;
    background-size: 5px 5px, 5px 5px !important;
    background-repeat: no-repeat !important;
  }

  select:focus {
    background-color: #1e1e1e !important;
    outline: none !important;
    box-shadow: 0 0 5px rgba(81, 203, 238, 1) !important;
  }

</style>
<?php
include('../other/foot.php');
?>
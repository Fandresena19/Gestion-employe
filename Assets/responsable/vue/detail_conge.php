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

// Quota annuel de congé
$quotaCongeAnnuel = 30;

// Requête pour récupérer tous les employés
$requeteEmployes = $bdd->query('
    SELECT matricule_emp, nom_emp, prenom_emp
    FROM employer_login
    ORDER BY nom_emp, prenom_emp
');

// Tableau pour stocker les informations des employés
$employesInfos = [];
$listeEmployes = []; // Pour le filtre

// Charger tous les employés d'abord
while ($employe = $requeteEmployes->fetch(PDO::FETCH_ASSOC)) {
  $matricule = $employe['matricule_emp'];
  $nomComplet = $employe['nom_emp'] . ' ' . $employe['prenom_emp'];
  
  $listeEmployes[$matricule] = $nomComplet;
  
  $employesInfos[$matricule] = [
    'nom_complet' => $nomComplet,
    'solde_initial' => $quotaCongeAnnuel,
    'solde_actuel' => $quotaCongeAnnuel,
    'conges' => [],
    'nb_conges_valides' => 0
  ];
}

// Construction de la requête de base
$sql = '
    SELECT c.*, e.nom_emp, e.prenom_emp 
    FROM conge c
    JOIN employer_login e ON c.matricule_emp = e.matricule_emp
    WHERE 1=1
';

$params = [];

// Ajouter le filtre par date si navigue par mois
$sql .= ' AND c.date_debut BETWEEN :debut AND :fin';
$params['debut'] = $premierJourDuMois;
$params['fin'] = $dernierJourDuMois;

// Ajouter le filtre par statut si nécessaire
if ($filtreStatut !== 'tous') {
  $sql .= ' AND c.statut_conge = :statut';
  $params['statut'] = $filtreStatut;
}

// Ajouter le filtre par employé si nécessaire
if ($filtreEmploye !== 'tous') {
  $sql .= ' AND c.matricule_emp = :matricule';
  $params['matricule'] = $filtreEmploye;
}

$sql .= ' ORDER BY e.nom_emp, e.prenom_emp, c.date_debut';

// Préparer et exécuter la requête
$requeteConges = $bdd->prepare($sql);
$requeteConges->execute($params);

// Fonction pour calculer la durée de congé en jours
function calculerDureeConge($dateDebut, $dateFin)
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

// Récupérer et associer les congés aux employés
while ($data = $requeteConges->fetch(PDO::FETCH_ASSOC)) {
  $matricule = $data['matricule_emp'];

  if (isset($employesInfos[$matricule])) {
    $dureeConge = calculerDureeConge($data['date_debut'], $data['date_fin']);

    $conge = [
      'id_conge' => $data['id_conge'],
      'date_demande' => $data['date_demande'],
      'date_debut' => $data['date_debut'],
      'date_fin' => $data['date_fin'],
      'duree' => $dureeConge,
      'statut' => $data['statut_conge']
    ];

    // Mettre à jour le solde uniquement pour les congés validés
    if ($data['statut_conge'] == 'Validé') {
      $employesInfos[$matricule]['nb_conges_valides']++;
    }

    $employesInfos[$matricule]['conges'][] = $conge;
  }
}

// Calcul du solde actuel pour chaque employé pour l'année entière
foreach ($employesInfos as $matricule => &$employe) {
  $stmt = $bdd->prepare('
    SELECT COUNT(*) AS total, SUM(TIMESTAMPDIFF(DAY, date_debut, date_fin) + TIMESTAMPDIFF(HOUR, date_debut, date_fin) % 24 / 24) AS jours_totaux
    FROM conge 
    WHERE matricule_emp = :matricule AND statut_conge = "Validé"
    AND YEAR(date_debut) = :annee
  ');
  $stmt->execute([
    'matricule' => $matricule,
    'annee' => $anneeActuelle
  ]);
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  $employe['nb_conges_valides_annee'] = $result['total'] ?: 0;
  $employe['jours_pris'] = $result['jours_totaux'] ?: 0;
  $employe['solde_actuel'] = $quotaCongeAnnuel - $employe['jours_pris'];
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

  .solde-header {
    background-color: rgb(154, 144, 144);
    font-weight: bold;
  }

  .conge-valide {
    background-color: rgba(95, 255, 95, 0.7);
  }

  .conge-attente {
    background-color: rgba(245, 166, 93, 0.75);
  }

  .conge-refuse {
    background-color: rgba(251, 98, 98, 0.77);
  }

  .no-conge {
    background-color: rgba(180, 180, 180, 0.5);
    font-style: italic;
  }

  .header-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
  }

</style>

<div class="annotation">
  <h2>Congé</h2>
</div><br>

<div class="contenu">
  <header>
    <div class="header-actions">
      <h4>Détails des Congés</h4>
      <div class="bouton">
        <a href="./liste_conge.php"><button type="submit">Retour</button></a>
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
          <th>Nombre Congés Validés (<?php echo $anneeActuelle; ?>)</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($employesInfos as $matricule => $employe): ?>
          <tr class="solde-header">
            <td><?php echo $employe['nom_complet']; ?></td>
            <td><?php echo afficherDureeEnJoursEtHeure($employe['solde_initial']); ?></td>
            <td><?php echo afficherDureeEnJoursEtHeure($employe['solde_actuel']); ?></td>
            <td><?php echo $employe['nb_conges_valides_annee']; ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <?php foreach ($employesInfos as $matricule => $employe): ?>
      <table>
        <thead>
          <tr>
            <th colspan="6">Détails des Congés - <?php echo $employe['nom_complet']; ?> (<?php echo $nomsMois[$moisActuel] . ' ' . $anneeActuelle; ?>)</th>
          </tr>
          <tr>
            <th>Congé N°</th>
            <th>Date Demande</th>
            <th>Date Début</th>
            <th>Date Fin</th>
            <th>Durée</th>
            <th>Statut</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($employe['conges'])): ?>
            <tr class="no-conge">
              <td colspan="6" style="text-align: center;">Aucun congé pour ce mois</td>
            </tr>
          <?php else: ?>
            <?php foreach ($employe['conges'] as $conge): ?>
              <tr class="<?php
                          echo $conge['statut'] == 'Validé' ? 'conge-valide' : ($conge['statut'] == 'En attente' ? 'conge-attente' : 'conge-refuse');
                          ?>">
                <td><?php echo $conge['id_conge']; ?></td>
                <td><?php echo date('d/m/Y H:i:s', strtotime($conge['date_demande'])); ?></td>
                <td><?php echo date('d/m/Y H:i:s', strtotime($conge['date_debut'])); ?></td>
                <td><?php echo date('d/m/Y H:i:s', strtotime($conge['date_fin'])); ?></td>
                <td><?php echo afficherDureeEnJoursEtHeure($conge['duree']); ?></td>
                <td><?php echo $conge['statut']; ?></td>
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
<script src="../js/Sidebar.js"></script>

<?php
include('../other/foot.php');
?>
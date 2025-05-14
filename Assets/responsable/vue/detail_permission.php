<?php
include('../other/head.php');

// Quota annuel de permission
$quotaPermissionAnnuel = 10;

// D'abord récupérer tous les employés
$requeteEmployes = $bdd->query('SELECT matricule_emp, nom_emp, prenom_emp FROM employer_login ORDER BY nom_emp');

// Tableau pour stocker les informations des employés
$employesInfos = [];

// Initialiser les données pour tous les employés
while ($employe = $requeteEmployes->fetch(PDO::FETCH_ASSOC)) {
  $matricule = $employe['matricule_emp'];

  $employesInfos[$matricule] = [
    'nom_complet' => $employe['nom_emp'] . ' ' . $employe['prenom_emp'],
    'solde_initial' => $quotaPermissionAnnuel,
    'solde_actuel' => $quotaPermissionAnnuel,
    'permissions' => [],
    'nb_permissions_valides' => 0
  ];
}

// Requête pour récupérer toutes les permissions
$requetePermissions = $bdd->query('
    SELECT e.matricule_emp, p.*
    FROM permission p
    JOIN employer_login e ON e.matricule_emp = p.matricule_emp
    ORDER BY e.matricule_emp, p.date_debut_per
');

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

// Comptage des permissions validées pour chaque employé
foreach ($employesInfos as $matricule => &$employe) {
  $stmt = $bdd->prepare('
    SELECT COUNT(*) AS total 
    FROM permission 
    WHERE matricule_emp = :matricule AND Statut_permission = "Validé"
  ');
  $stmt->execute(['matricule' => $matricule]);
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  $employe['nb_permissions_valides'] = $result['total'];
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
    /* Permet au contenu de grandir */
    display: flex;
    flex-direction: column;
    margin-top: -2% !important;
    overflow-y: auto;
    /* Cache tout débordement */
  }

  .contenu header {
    flex-shrink: 0;
    /* Empêche l'en-tête de rétrécir */
  }

  .scrollbar {
    max-height: 70vh;
    overflow-y: auto;
  }

  .scrollbar {
    max-height: 70vh;
    /* 70% de la hauteur de la fenêtre */
    overflow-y: auto;
    /* Permet le défilement vertical */
    padding-right: 5px;
    /* Espace pour la barre de défilement */
  }

  table {
    width: 100%;
    border-collapse: collapse;
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
</style>



<div class="annotation">
  <h2>Permission</h2>
</div><br>


<div class="contenu">
  <header>
    <h4>Détails des Permissions</h4>

    <div class="bouton">
      <a href="./liste_permission.php"><button type="submit">Retour</button></a>
    </div>
  </header> <br />

  <div class="scrollbar">

    <table>
      <thead>
        <tr>
          <th>Nom Complet</th>
          <th>Solde Initial</th>
          <th>Solde Actuel</th>
          <th>Nombre Permissions Validées</th>
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
            <th colspan="6">Détails des Permissions - <?php echo $employe['nom_complet']; ?></th>
          </tr>
          <tr>
            <th>Permission N°</th>
            <th>Date Demande</th>
            <th>Date Début</th>
            <th>Date Fin</th>
            <th>Durée</th>
            <th>Motif</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($employe['permissions'])): ?>
            <tr class="no-permission">
              <td colspan="6" style="text-align: center;">Aucune permission demandée</td>
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
              </tr>
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
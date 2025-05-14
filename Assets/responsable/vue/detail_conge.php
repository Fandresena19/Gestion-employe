<?php
include('../other/head.php');

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

// Charger tous les employés d'abord
while ($employe = $requeteEmployes->fetch(PDO::FETCH_ASSOC)) {
  $matricule = $employe['matricule_emp'];
  $employesInfos[$matricule] = [
    'nom_complet' => $employe['nom_emp'] . ' ' . $employe['prenom_emp'],
    'solde_initial' => $quotaCongeAnnuel,
    'solde_actuel' => $quotaCongeAnnuel,
    'conges' => [],
    'nb_conges_valides' => 0
  ];
}

// Requête pour récupérer tous les congés
$requeteConges = $bdd->query('
    SELECT c.*, e.nom_emp, e.prenom_emp 
    FROM conge c
    JOIN employer_login e ON c.matricule_emp = e.matricule_emp
    ORDER BY e.nom_emp, e.prenom_emp, c.date_debut
');

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
    $employesInfos[$matricule]['solde_actuel'] -= $dureeConge;
    $employesInfos[$matricule]['nb_conges_valides']++;
  }

  $employesInfos[$matricule]['conges'][] = $conge;
}
?>


<div class="annotation">
  <h2>Congé</h2>
</div><br>


<div class="contenu">
  <header>
    <h4>Congé</h4>

    <div class="bouton">
      <a href="./liste_conge.php"><button type="submit">Retour</button></a>
    </div>
  </header> <br />

  <div class="scrollbar">

    <table>
      <thead>
        <tr>
          <th>Nom Complet</th>
          <th>Solde Initial</th>
          <th>Solde Actuel</th>
          <th>Nombre Congés Validés</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($employesInfos as $matricule => $employe): ?>
          <tr class="solde-header">
            <td><?php echo $employe['nom_complet']; ?></td>
            <td><?php echo afficherDureeEnJoursEtHeure($employe['solde_initial']); ?></td>
            <td><?php echo afficherDureeEnJoursEtHeure($employe['solde_actuel']); ?></td>
            <td><?php echo $employe['nb_conges_valides']; ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <?php foreach ($employesInfos as $matricule => $employe): ?>
      <table>
        <thead>
          <tr>
            <th colspan="5">Détails des Congés - <?php echo $employe['nom_complet']; ?></th>
          </tr>
          <tr>
            <th>Congé N°</th>
            <th>Date Demande</th>
            <th>Date Début</th>
            <th>Date Fin</th>
            <th>Durée</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($employe['conges'])): ?>
            <tr>
              <td colspan="5" style="text-align: center;">Aucun congé demandé</td>
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
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    <?php endforeach; ?>

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

  .conge-valide {
    background-color: rgba(95, 255, 95, 0.7);
  }

  .conge-attente {
    background-color: rgba(245, 166, 93, 0.75);
  }

  .conge-refuse {
    background-color: rgba(251, 98, 98, 0.77);
  }
</style>

<script src="../js/Sidebar.js"></script>

<script>
  function confirmDelete(event, url) {
    event.preventDefault(); // Empêche le lien de se comporter comme un lien normal

    // Affiche le modal
    document.getElementById('confirmationModal').style.display = 'block';

    // Gestion de la confirmation
    document.getElementById('confirmDelete').onclick = function() {
      window.location.href = url; // Redirige vers l'URL de suppression
    };

    // Gestion de l'annulation
    document.getElementById('cancelDelete').onclick = function() {
      document.getElementById('confirmationModal').style.display = 'none'; // Cache le modal
    };
  }
</script>
<?php
include('../other/foot.php');
?>
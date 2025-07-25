<?php
include('../other/head.php');

$matricule_emp = $_SESSION['Matricule_emp'];

// Vérifier si on est en mode modification
$edit_mode = false;
$edit_data = null;
$edit_id = null;

if (isset($_GET['edit']) && !empty($_GET['edit'])) {
  $edit_id = intval($_GET['edit']);
  $edit_mode = true;

  // Récupérer les données de la tâche à modifier
  $sql_edit = "SELECT * FROM timesheet WHERE id_timesheet = :id AND matricule_emp = :matricule_emp";
  $stmt_edit = $bdd->prepare($sql_edit);
  $stmt_edit->execute([
    'id' => $edit_id,
    'matricule_emp' => $matricule_emp
  ]);
  $edit_data = $stmt_edit->fetch(PDO::FETCH_ASSOC);

  // Si la tâche n'existe pas ou n'appartient pas à l'utilisateur
  if (!$edit_data) {
    $_SESSION['error'] = "Tâche introuvable ou accès non autorisé.";
    header('Location: ./mes_timesheet.php');
    exit();
  }
}

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

// Récupérer la liste des clients pour le dropdown
$sql_clients = "SELECT DISTINCT client FROM timesheet ORDER BY client";
$stmt_clients = $bdd->prepare($sql_clients);
$stmt_clients->execute();
$clients = $stmt_clients->fetchAll(PDO::FETCH_ASSOC);

// Récupérer la liste des tâches existantes
$sql_taches = "SELECT DISTINCT tache FROM timesheet ORDER BY tache";
$stmt_taches = $bdd->prepare($sql_taches);
$stmt_taches->execute();
$taches = $stmt_taches->fetchAll(PDO::FETCH_ASSOC);

// Récupérer la liste des missions existantes
$sql_missions = "SELECT DISTINCT mission FROM timesheet ORDER BY mission";
$stmt_missions = $bdd->prepare($sql_missions);
$stmt_missions->execute();
$missions = $stmt_missions->fetchAll(PDO::FETCH_ASSOC);

$sql_heure = "SELECT DISTINCT duree_tache FROM timesheet ORDER BY duree_tache DESC";
$stmt_heure = $bdd->prepare($sql_heure);
$stmt_heure->execute();
$heures = $stmt_heure->fetchAll(PDO::FETCH_ASSOC);

// Définir les cycles prédéfinies
$taches_predefinies = [
  'IMMOBILISATIONS',
  'STOCK',
  'VENTE - CLIENTS',
  'TRESORERIE',
  'CAPITAUX PROPRES',
  'EMPRUNTS',
  'ACHATS - FOURNISSEURS',
  'AUTRES CREANCES ET DETTE',
  'PERSONNEL',
  'FISCALITE',
  'PROVISION',
  'AUTRE PRODUITS ET AUTRES CHARGES',
  'BOUCLAGE'
];

// Définir les missions prédéfinies
$missions_predefinies = [
  'AUDIT',
  'COMMISSARIAT AUX COMPTES',
  'TENUE DE COMPTABILITE',
  'MISSION DE COMPILATION',
  'ASSISTANCE FISCALE',
  'CONGE',
  'PERMISSION',
  'DISPONIBLE',
  'FORMATION'
];
?>

<div class="annotation">
  <h2><?php echo $edit_mode ? 'Modifier une tâche' : 'Ajouter une tâche'; ?></h2>
</div>

<div class="contenu">
  <header>
    <h4><?php echo $edit_mode ? 'Modification tâche' : 'Ajout tâche'; ?></h4>

    <div class="bouton">
      <a href="./mes_timesheet.php"><button>Retour</button></a>
    </div>
  </header><br>

  <form action="<?php echo $edit_mode ? '../traitement/modifier_tache.php' : '../traitement/ajout_tache.php'; ?>" method="post">
    <input type="hidden" name="matricule_emp" value="<?php echo $matricule_emp; ?>">
    <?php if ($edit_mode): ?>
      <input type="hidden" name="id_timesheet" value="<?php echo $edit_id; ?>">
    <?php endif; ?>

    <div class="form-group">
      <label for="date_tache">Date de la tâche:</label>
      <input type="date" id="date_tache" name="date_tache" class="form-control"
        value="<?php echo $edit_mode ? $edit_data['date_tache'] : date('Y-m-d'); ?>"
        <?php echo $edit_mode ? '' : 'required'; ?>>
    </div>

    <div class="form-group">
      <label for="tache">Cycles:</label>
      <div class="tache-input-container">
        <select id="tache_select" class="form-control" onchange="handleTacheSelection()">
          <option value="">Sélectionnez une cycle ou ajoutez-en un nouveau</option>
          <?php 
          // Fusionner les tâches prédéfinies avec celles de la base de données
          $all_taches = array_unique(array_merge($taches_predefinies, array_column($taches, 'tache')));
          sort($all_taches);
          foreach ($all_taches as $tache): ?>
            <option value="<?php echo htmlspecialchars($tache); ?>"
              <?php echo ($edit_mode && $edit_data['tache'] == $tache) ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($tache); ?>
            </option>
          <?php endforeach; ?>
          <option value="nouvelle">+ Ajouter une nouvelle tâche</option>
        </select>
        <input type="text" id="tache" name="tache" class="form-control"
          style="<?php echo ($edit_mode && !in_array($edit_data['tache'], $all_taches)) ? 'display:block;' : 'display:none;'; ?>"
          placeholder="Nom de la tâche"
          value="<?php echo $edit_mode ? htmlspecialchars($edit_data['tache']) : ''; ?>">
      </div>
    </div>

    <div class="form-group">
      <label for="mission">Mission:</label>
      <div class="mission-input-container">
        <select id="mission_select" class="form-control" onchange="handleMissionSelection()">
          <option value="">Sélectionnez une mission ou ajoutez-en une nouvelle</option>
          <?php 
          // Fusionner les missions prédéfinies avec celles de la base de données
          $all_missions = array_unique(array_merge($missions_predefinies, array_column($missions, 'mission')));
          sort($all_missions);
          foreach ($all_missions as $mission): ?>
            <option value="<?php echo htmlspecialchars($mission); ?>"
              <?php echo ($edit_mode && $edit_data['mission'] == $mission) ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($mission); ?>
            </option>
          <?php endforeach; ?>
          <option value="nouvelle">+ Ajouter une nouvelle mission</option>
        </select>
        <input type="text" id="mission" name="mission" class="form-control"
          style="<?php echo ($edit_mode && !in_array($edit_data['mission'], $all_missions)) ? 'display:block;' : 'display:none;'; ?>"
          placeholder="Mission effectuée"
          value="<?php echo $edit_mode ? htmlspecialchars($edit_data['mission']) : ''; ?>">
      </div>
    </div>

    <div class="form-group">
      <label for="description_tache">Description:</label>
      <textarea id="description_tache" name="description_tache" class="form-control" rows="3"
        placeholder="Description détaillée de la tâche"><?php echo $edit_mode ? htmlspecialchars($edit_data['description_tache']) : ''; ?></textarea>
    </div>

    <div class="form-group">
      <label for="duree_tache">Durée (heures):</label>
      <div class="heure-input-container">
        <select id="duree_select" class="form-control" onchange="handleHeureSelection()">
          <option value="">Selectionnez heure ou ajoutez-en</option>
          <?php foreach ($heures as $heure): ?>
            <option value="<?php echo htmlspecialchars($heure['duree_tache']); ?>"
              <?php echo ($edit_mode && $edit_data['duree_tache'] == $heure['duree_tache']) ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($heure['duree_tache']); ?> heures
            </option>
          <?php endforeach; ?>
          <option value="nouveau">+ Ajouter heures</option>
        </select>
        <input type="number" id="duree" name="duree_tache" class="form-control"
          style="<?php echo ($edit_mode && !in_array($edit_data['duree_tache'], array_column($heures, 'duree_tache'))) ? 'display:block;' : 'display:none;'; ?>"
          placeholder="Ajouter heures" required
          value="<?php echo $edit_mode ? $edit_data['duree_tache'] : '1'; ?>">
        <small>Entrez la durée en heures </small>
      </div>
    </div>

    <div class="form-group">
      <label for="client">Client:</label>
      <div class="client-input-container">
        <select id="client_select" class="form-control" onchange="handleClientSelection()">
          <option value="">Sélectionnez un client ou ajoutez-en un nouveau</option>
          <?php foreach ($clients as $client): ?>
            <option value="<?php echo htmlspecialchars($client['client']); ?>"
              <?php echo ($edit_mode && $edit_data['client'] == $client['client']) ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($client['client']); ?>
            </option>
          <?php endforeach; ?>
          <option value="new">+ Ajouter un nouveau client</option>
        </select>
        <input type="text" id="client" name="client" class="form-control"
          style="<?php echo ($edit_mode && !in_array($edit_data['client'], array_column($clients, 'client'))) ? 'display:block;' : 'display:none;'; ?>"
          placeholder="Nom du client"
          value="<?php echo $edit_mode ? htmlspecialchars($edit_data['client']) : ''; ?>">
      </div>
    </div>

    <div class="form-group">
      <label for="note">Note additionnelle:</label>
      <textarea id="note" name="note" class="form-control" rows="2"
        placeholder="Notes additionnelles (optionnel)"><?php echo $edit_mode ? htmlspecialchars($edit_data['note']) : ''; ?></textarea>
    </div>

    <div class="form-group">
      <button type="submit" class="bouton_confirmer">
        <?php echo $edit_mode ? 'Modifier' : 'Ajouter'; ?>
      </button><br>
      <?php if ($edit_mode): ?>
        <a href="./mes_timesheet.php" class="bouton_annuler">Annuler</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<script>
  function handleTacheSelection() {
    var select = document.getElementById('tache_select');
    var input = document.getElementById('tache');

    if (select.value === 'nouvelle') {
      input.style.display = 'block';
      input.value = '';
      input.required = true;
    } else {
      input.style.display = 'none';
      input.value = select.value;
      input.required = false;
    }
  }

  function handleMissionSelection() {
    var select = document.getElementById('mission_select');
    var input = document.getElementById('mission');

    if (select.value === 'nouvelle') {
      input.style.display = 'block';
      input.value = '';
      input.required = true;
    } else {
      input.style.display = 'none';
      input.value = select.value;
      input.required = false;
    }
  }

  function handleClientSelection() {
    var select = document.getElementById('client_select');
    var input = document.getElementById('client');

    if (select.value === 'new') {
      input.style.display = 'block';
      input.value = '';
      input.required = true;
    } else {
      input.style.display = 'none';
      input.value = select.value;
      input.required = false;
    }
  }

  function handleHeureSelection() {
    var select = document.getElementById('duree_select');
    var input = document.getElementById('duree');

    if (select.value === 'nouveau') {
      input.style.display = 'block';
      input.value = '';
      input.required = true;
    } else {
      input.style.display = 'none';
      input.value = select.value;
      input.required = false;
    }
  }

  // Initialiser l'état des champs au chargement de la page
  document.addEventListener('DOMContentLoaded', function() {
    // Initialiser le champ tâche
    var tacheSelect = document.getElementById('tache_select');
    var tacheInput = document.getElementById('tache');

    if (tacheSelect.value && tacheSelect.value !== 'nouvelle') {
      tacheInput.value = tacheSelect.value;
      tacheInput.style.display = 'none';
    } else if (tacheInput.value && tacheSelect.value === '') {
      // Si on a une valeur dans l'input mais pas dans le select (tâche personnalisée)
      tacheSelect.value = 'nouvelle';
      tacheInput.style.display = 'block';
    }

    // Initialiser le champ mission
    var missionSelect = document.getElementById('mission_select');
    var missionInput = document.getElementById('mission');

    if (missionSelect.value && missionSelect.value !== 'nouvelle') {
      missionInput.value = missionSelect.value;
      missionInput.style.display = 'none';
    } else if (missionInput.value && missionSelect.value === '') {
      // Si on a une valeur dans l'input mais pas dans le select (mission personnalisée)
      missionSelect.value = 'nouvelle';
      missionInput.style.display = 'block';
    }

    // Initialiser le champ client
    var clientSelect = document.getElementById('client_select');
    var clientInput = document.getElementById('client');

    if (clientSelect.value && clientSelect.value !== 'new') {
      clientInput.value = clientSelect.value;
      clientInput.style.display = 'none';
    } else if (clientInput.value && clientSelect.value === '') {
      // Si on a une valeur dans l'input mais pas dans le select (client personnalisé)
      clientSelect.value = 'new';
      clientInput.style.display = 'block';
    }

    // Initialiser le champ durée
    var dureeSelect = document.getElementById('duree_select');
    var dureeInput = document.getElementById('duree');

    if (dureeSelect.value && dureeSelect.value !== 'nouveau') {
      dureeInput.value = dureeSelect.value;
      dureeInput.style.display = 'none';
    } else if (dureeInput.value && dureeSelect.value === '') {
      // Si on a une valeur dans l'input mais pas dans le select (durée personnalisée)
      dureeSelect.value = 'nouveau';
      dureeInput.style.display = 'block';
    }
  });
</script>

<style>
  label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
  }

  /* Specific styles for select elements to make background darker */
  select.form-control {
    background-color: #2c2c2c !important;
    color: white !important;
    appearance: none !important;
    /* Remove default browser styling */
    background-image: linear-gradient(45deg, transparent 50%, #fff 50%), linear-gradient(135deg, #fff 50%, transparent 50%) !important;
    background-position: calc(100% - 20px) calc(1em + 2px), calc(100% - 15px) calc(1em + 2px) !important;
    background-size: 5px 5px, 5px 5px !important;
    background-repeat: no-repeat !important;
  }

  select.form-control:focus {
    background-color: #1e1e1e !important;
    outline: none !important;
    box-shadow: 0 0 5px rgba(81, 203, 238, 1) !important;
  }

  .form-control {
    width: 100%;
    padding: 10px;
    border: 1px solid #555;
    border-radius: 4px;
    background-color: #4a4a4a;
    color: #fff;
  }

  textarea.form-control {
    resize: vertical;
  }

  .client-input-container,
  .tache-input-container,
  .mission-input-container,
  .heure-input-container {
    display: flex;
    flex-direction: column;
  }

  .form-buttons {
    display: flex;
    justify-content: space-between;
    margin-top: 30px;
  }

  small {
    color: #aaa;
    font-size: 0.8em;
  }

  .bouton_annuler {
    display: flex;
    justify-content: center;
    width: 80%;
    padding: 10px 20px;
    background-color: #d9534f;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    margin: auto;
    transition: background-color 0.3s ease;
  }

  .bouton_annuler:hover {
    background-color: #c9302c;
    text-decoration: none;
    color: white;
  }

  .form-group {
    margin-bottom: 20px;
  }
</style>

<?php
include('../other/foot.php');
?>
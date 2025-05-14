<?php
include('../other/head.php');

$matricule_emp = $_SESSION['Matricule_emp'];

// Récupérer la liste des clients pour le dropdown
$sql_clients = "SELECT DISTINCT client FROM timesheet ORDER BY client";
$stmt_clients = $bdd->prepare($sql_clients);
$stmt_clients->execute();
$clients = $stmt_clients->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="annotation">
  <h2>Ajouter une tâche</h2>
</div>

<div class="contenu">
  <header>

    <div class="bouton">
      <h4>Ajout timesheet</h4>

      <div class="bouton">
        <a href="./mes_timesheet.php" class="btn-cancel">Annuler</a>
      </div>
    </div>

  </header>
  <form action="../traitement/ajout_tache.php" method="post">
    <input type="hidden" name="matricule_emp" value="<?php echo $matricule_emp; ?>">

    <div class="form-group">
      <label for="date_tache">Date de la tâche:</label>
      <input type="date" id="date_tache" name="date_tache" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
    </div>

    <div class="form-group">
      <label for="tache">Tâche effectuée:</label>
      <input type="text" id="tache" name="tache" class="form-control" required placeholder="Nom de la tâche">
    </div>

    <div class="form-group">
      <label for="duree_tache">Durée (heures):</label>
      <input type="number" id="duree_tache" name="duree_tache" class="form-control" required min="0.5" step="0.5" value="1">
      <small>Entrez la durée en heures (ex: 1.5 pour 1h30)</small>
    </div>

    <div class="form-group">
      <label for="client">Client:</label>
      <div class="client-input-container">
        <select id="client_select" class="form-control" onchange="handleClientSelection()">
          <option value="">Sélectionnez un client ou ajoutez-en un nouveau</option>
          <?php foreach ($clients as $client): ?>
            <option value="<?php echo htmlspecialchars($client['client']); ?>"><?php echo htmlspecialchars($client['client']); ?></option>
          <?php endforeach; ?>
          <option value="new">+ Ajouter un nouveau client</option>
        </select>
        <input type="text" id="client" name="client" class="form-control" style="display:none;" placeholder="Nom du client">
      </div>
    </div>

    <div class="form-group">
      <label for="description_tache">Description:</label>
      <textarea id="description_tache" name="description_tache" class="form-control" rows="3" placeholder="Description détaillée de la tâche"></textarea>
    </div>

    <div class="form-group">
      <label for="note">Note additionnelle:</label>
      <textarea id="note" name="note" class="form-control" rows="2" placeholder="Notes additionnelles (optionnel)"></textarea>
    </div>

    <div class="form-buttons">
      <button type="submit" class="btn-submit">Enregistrer la tâche</button>
    </div>
  </form>
</div>

<script>
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

  // Initialiser l'état du champ client
  document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('client').value = document.getElementById('client_select').value;
  });
</script>

<style>
  .contenu {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    background-color: #3a3a3a;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
  }

  .form-group {
    margin-bottom: 20px;
  }

  label {
    display: block;
    margin-bottom: 5px;
    color: #e0e0e0;
    font-weight: bold;
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

  .client-input-container {
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
</style>

<?php
include('../other/foot.php');
?>
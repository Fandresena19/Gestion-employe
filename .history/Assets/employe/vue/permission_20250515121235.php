<?php
include('../other/head.php');

$employe = $_SESSION['Matricule_emp'];

$employes = $bdd->query('select * from employer_login WHERE matricule_emp ="'.$employe.'"');
$matricule = $bdd->query('SELECT matricule FROM employer_login WHERE matricule_emp ='.$employe) ->fetch(PDO::FETCH_ASSOC);
$permission = $bdd->query("SELECT * FROM permission");

$date = new DateTime('now', new DateTimeZone('GMT+3'));

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

/// Récupérer le dernier matricule
$result = $bdd->query("SELECT id_conge FROM conge ORDER BY id_conge DESC LIMIT 1");

// Vérifier si un matricule existe déjà
$last_id = ($result->rowCount() > 0) ? intval($result->fetch()['id_conge']) + 1 : 1;
?>

<div class="annotation">
  <h2>Ajout permission</h2>
</div>

<div class="contenu">
  <header>
    <h4>Ajout permission</h4>

    <div class="bouton">
      <a href="./mes_permission.php"><button>Retour</button></a>
    </div>
  </header><br>

  <form action="../traitement/add_permission.php" method="post">
    <div class="form-group">
      <label>L'interessé</label>
      <input type="hidden" name="emp" class="form-control" value="<?php echo $employe ?>" readonly>
      <input type="text" class="form-control" value="<?= $matricule['matricule'] ?>" readonly>
    </div>

    <div class="form-group">
      <label for="">reference</label>
      <input name="ref" type="text" class="form-control" required />
    </div>

    <div class="form-group">
      <label>Motif de la demande</label>
      <textarea name="motif" class="form-control" required> </textarea>
    </div>

    <div class="form-group">
      <label>Date de la demande</label>
      <input type="datetime-local" name="dateDem" class="form-control" value="<?= $date->format('Y-m-d\TH:i'); ?>" min="<?= date('Y-m-d\TH:i'); ?>" readonly />
    </div>

    <div class="form-group">
      <label>Date debut</label>
      <input type="datetime-local" name="dateD" class="form-control" required />
    </div>

    <div class="form-group">
      <label>Date fin</label>
      <input type="datetime-local" name="dateF" class="form-control" required />
    </div></br>

    <div class="form-group">
      <button type="submit" class="bouton_confirmer">Demander permission</button>
    </div>
  </form>
</div>

<?php
include('../other/foot.php');
?>
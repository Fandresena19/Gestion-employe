<?php
include('../other/head.php');

$employes = $bdd->query('select *from employer_login');
$employe = $_SESSION['Matricule_emp'];

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
      <input type="text" name="emp" class="form-control" value="<?php echo $employe ?>" readonly>
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
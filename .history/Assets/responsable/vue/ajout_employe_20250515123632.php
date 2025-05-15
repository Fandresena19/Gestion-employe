<?php
include('../other/head.php');

$services = $bdd->query('select * from service');
$grades = $bdd->query('select * from grade');

// Récupérer le dernier matricule
$result = $bdd->query("SELECT matricule_emp FROM employer_login ORDER BY id DESC LIMIT 1");

if ($result->rowCount() > 0) {
    $lastMatricule = $result->fetch()['matricule_emp']; // ex: EMP004
    $num = intval(substr($lastMatricule, 3));           // extrait "004" → 4
    $newMatricule = 'EMP' . str_pad($num + 1, 3, '0', STR_PAD_LEFT); // EMP005
} else {
    $newMatricule = 'EMP001';
}


?>

<form action="../traitement/add_employe.php" method="post">
  <div class="annotation">
    <h2>Ajout Employé</h2>
  </div>

  <div class="contenu">
    <header>
      <h4>Ajout Employé</h4>

      <div class="bouton">
        <a href="./liste_employe.php"><button>Annuler</button></a>
        <a href=""><button type="submit"> Enregistrer</button></a>
      </div>
    </header><br>

    <div class="form-group">
      <label>Service</label>
      <select name="serv" class="form-control" required>
        <?php
        while ($data = $services->fetch()) {
          echo '<option value="' . $data['id_service'] . '">' . $data['nom_service'] . '</option>';
        }
        ?>
      </select>
    </div>
    <div class="form-group">
      <label>Matricule</label>
      <input type="hidden" name="im" class="form-control" value="<?=$newMatricule ?>" readonly required>
      <input type="text"  class="form-control" value="<?= $newMatricule ?>" readonly required>
    </div>

    <div class="form-group">
      <label>Nom</label>
      <input type="text" name="nom" class="form-control" required>
    </div>

    <div class="form-group">
      <label>Prénom</label>
      <input type="text" name="prenom" class="form-control" required>
    </div>


    <div class="form-group">
      <label>Date d'embauche</label>
      <input type="date" name="date_emb" class="form-control" required>
    </div>


    <div class="form-group">
      <label>Rôle</label>
      <input type="text" name="role" class="form-control" required>
    </div>


    <div class="form-group">
      <label>Téléphone</label>
      <input type="text" name="telephone" class="form-control" required>
    </div>

    <div class="form-group">
      <label>Grade</label>
      <select name="grade" class="form-control" required>
        <?php
        while ($toto = $grades->fetch()) {
          echo '<option value="' . $toto['id_grade'] . '">' . $toto['nom_grade'] . '</option>';
        }
        ?>
      </select>
    </div>

    <div class="form-group">
      <label>Date obtention grade</label>
      <input type="date" name="dateG" class="form-control" required>
    </div>

    <div class="form-group">
      <label>Email</label>
      <input type="mail" name="mail" class="form-control" required>
    </div>

    <div class="form-group">
      <label> Nouveau mot de passe</label>
      <input type="password" name="mdp" class="form-control" required>
    </div><br>

    <div class="form-group">
      <button type="submit" class="bouton_confirmer">Enregistrer</button>
    </div>

  </div>
</form>
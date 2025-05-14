<?php
include('../other/head.php');

$employes = $bdd->query('SELECT * FROM employer_login e JOIN service s ON s.id_service=e.id_service JOIN obtenir o
 ON e.matricule_emp = o.matricule_emp JOIN grade g on g.id_grade=o.id_grade WHERE e.matricule_emp=' . $_GET['id']);

$emp = $employes->fetch();
$services = $bdd->query('select *from service');
$grades = $bdd->query('select *from grade');

?>

<div class="annotation">
  <h2>Modification Employé</h2>
</div>

<div class="contenu">
  <header>
    <h4>Modification Employé</h4>

    <form action="" method="post">
      <div class="bouton">
        <a href="./liste_employe.php"><button>Annuler</button></a>
        <a href=""><button type="submit"> Enregistrer</button></a>
      </div>
    </form>
  </header>

  <div class="form-group">
    <label>Service</label>
    <select name="serv" class="form-control" required>
      <?php
      echo '<option value="' . $emp['id_service'] . '">' . $emp['nom_service'] . '</option>';
      while ($data = $services->fetch()) {
        echo '<option value="' . $data['id_service'] . '">' . $data['nom_service'] . '</option>';
      }
      ?>
    </select>
  </div>
  <div class="form-group">

    <label for="Matricule">Matricule</label>
    <input type="text" value="<?php echo $emp['matricule_emp'] ?>" class="form-control" disabled>
    <input type="hidden" value="<?php echo $emp['matricule_emp'] ?>" name="im" class="form-control">
  </div>

  <div class="form-group">
    <label>Nom</label>
    <input type="text" value="<?php echo $emp['nom_emp'] ?>" name="nom" class="form-control" required>
  </div>

  <div class="form-group">
    <label>Prénom</label>
    <input type="text" value="<?php echo $emp['prenom_emp'] ?>" name="prenom" class="form-control" required>
  </div>

  <div class="form-group">
    <label>Date d'embauche</label>
    <input type="date" value="<?php echo $emp['date_embauche'] ?>" name="date_emb" class="form-control" required>
  </div>


  <div class="form-group">
    <label>Rôle</label>
    <input type="text" value="<?php echo $emp['role'] ?>" name="role" class="form-control" required>
  </div>

  <div class="form-group">
    <label>Grade</label>
    <select name="grade" class="form-control" required>
      <?php
      echo '<option value="' . $emp['id_grade'] . '">' . $emp['nom_grade'] . '</option>';

      while ($toto = $grades->fetch()) {
        echo '<option value="' . $toto['id_grade'] . '">' . $toto['nom_grade'] . '</option>';
      }
      ?>
    </select>
  </div>
  <div class="form-group">
    <label>Date obtention grade</label>
    <input type="date" value="<?php echo $emp['date_obtention_grade'] ?>" name="dateG" class="form-control" required>
  </div> <br>

  <div class="form-group">
    <label> Entrer votre mot de passe pour confirmer la modification</label>
    <span style="display: flex;"><input type="password" name="current_password" placeholder="Votre mot de passe" class="form-control" id="mdp" style="position: relative;" />
      <i class="bx bx-hide" id="icone" style="font-size: 20px;margin-left: -10%; margin-top: 2%;"></i> </span>
  </div>
</div>

<!-- <div class="form-group " id="Annuler">
            <a href="./liste_employe.php"><button>Annuler</button></a>
            <button type="submit" class="">Enregistrer</button>
          </div> -->
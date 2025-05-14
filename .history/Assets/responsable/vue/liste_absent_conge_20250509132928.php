<?php
  include('../other/head.php');
  $donnees = $bdd->query('select * from employer_login e join conge c on e.matricule_emp=c.matricule_emp where c.statut_conge= "Valide" ORDER BY c.date_demande DESC ');

  if (!isset($_SESSION['Matricule_resp'])) {
    header('location:../index.php');
    exit();
  }

  ?>

<div class="annotation">
  <h2>Absent</h2>
</div>

<div class="contenu">
  <header>
    <h4>Congé validé</h4>

    <div class="bouton">
      <a href="./liste_conge.php"><button type="submit">Retour</button></a>
    </div>

  </header><br />

  <table class="">

    <thead>
      <th>Congé N°</th>
      <th>Matricule</th>
      <th>Nom complet</th>
      <th>Date debut</th>
      <th>Date Fin</th>
      <th>Durée absence</th>
      <th>Motif</th>
    </thead>
    <tbody>
      <?php
      while ($data = $donnees->fetch(PDO::FETCH_ASSOC)) {

        //Convertir les chaines de caractere en objet DateTime
        $dateDebut = new DateTime($data['date_debut']);
        $dateFin = new DateTime($data['date_fin']);
        //Calcule interval
        $interval = date_diff($dateDebut, $dateFin);
        $dureeAbsence = $interval->days;

        echo '<tr>
                <td>' . $data['id_conge'] . '</td>
                <td>' . $data['matricule_emp'] . '</td>
                <td>' . $data['nom_emp'] . ' ' . $data['prenom_emp'] . '</td>
                
                <td>' . date('d/m/Y H:i:s', strtotime($data['date_debut'])) . '</td>
                <td>' . date('d/m/Y H:i:s', strtotime($data['date_fin'])) . '</td>
                <td>' . $dureeAbsence . ' jours </td>
                <td>' . $data['motif'] . '</td>
                
                </tr>';
      }
      ?>
    </tbody>
  </table>
</div>

<?php
include('../other/foot.php');
?>
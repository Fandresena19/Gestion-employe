<?php
include('../other/head.php');
$matricule_emp = $_SESSION['Matricule_emp'];

$sql = "SELECT * FROM conge c JOIN employer_login e on c.matricule_emp=e.matricule_emp
 WHERE e.matricule_emp= :matricule_emp AND c.statut_conge = 'Refusé' ORDER BY c.date_demande ASC ";
$stmt = $bdd->prepare($sql);
$stmt->execute(['matricule_emp' => $matricule_emp]);

?>

<div class="annotation">
  <h2>Congé refusé</h2>
</div>

<div class="contenu">


  <table class="">

    <thead>
      <tr>
        <td>id congé</td>
        <td>Matricule</td>
        <td>Nom complet</td>
        <td>Date demande</td>
        <td>Date debut</td>
        <td>Date fin</td>
        <td>Motif</td>
      </tr>
    </thead>
    <tbody>
      <?php

      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {


        echo '
              
              <tr>
                <td>' . $row['id_conge'] . '</td>
                <td>' . $row['matricule_emp'] . '</td>
                <td>' . $row['nom_emp'] . ' ' . $row['prenom_emp'] . '</td>
                
                <td>' . date('d/m/Y H:i:s', strtotime($row['date_demande'])) . '</td>
                <td>' . date('d/m/Y H:i:s', strtotime($row['date_debut']))  . '</td>
                <td>' . date('d/m/Y H:i:s', strtotime($row['date_fin'])) . '</td>
                <td>' . $row['motif'] . '</td>';
        echo '
                </tr>';
      }

      echo '
            <header>';
      echo '<h4 align="center">Congé réfusé</h4>';
      echo '<div class="bouton">
              <a href="./mes_conge.php"><button type="submit">Retour</button></a>
              </div>';

      echo '</header> <br/>';

      ?>
    </tbody>
  </table>
</div>


<?php
include('../other/foot.php');
?>
<?php
include('../other/head.php');

$Matricule_emp = $_SESSION['Matricule_emp'];
?>

<div class="contenu">
  <header>
    <h4>Historique</h4>
  </header><br>

  <table class="">

    <thead>
      <tr>
        <td>Date demande</td>
        <td>Type demande</td>
        <td>Motif</td>
        <td>Statut</td>
      </tr>
    </thead>
    <?php
    $stmt = $bdd->query("SELECT date_demande, 'Congé' AS type_demande, motif,statut_conge AS statut FROM conge WHERE Matricule_emp= $Matricule_emp 
                      UNION ALL SELECT date_demande_per AS date_demande, 'Permission' AS type_demande, motif_per AS motif, statut_permission AS statut FROM permission
                      WHERE Matricule_emp= $Matricule_emp ORDER BY date_demande DESC");
    echo "<tbody>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      echo "<tr>";
      echo "<td style='text-align: right'>" . $row['date_demande'] . "</td>";
      echo "<td>" . $row['type_demande'] . "</td>";
      echo "<td>" . $row['motif'] . "</td>";
      echo "<td>" . $row['statut'] . "</td>";
      echo "</tr>";
    }
    echo "</tbody>";
    echo "</table>";

    // ... (fermeture de la connexion)
    ?>

</div>

<style>
    #confirmationModal {
      display: none;
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background-color: #1B0E20;
      padding: 20px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
      font-size: 12px;
    }

    #confirmationModal button {
      float: right;
      width: 20%;
      padding-left: 10px;
      padding-right: 10px;
      padding-top: 5px;
      padding-bottom: 5px;
      margin-left: 10px;
      border-radius: 5px;
    }

    #confirmDelete {
      background-color: red;
      border: none;
    }

    #cancelDelete {
      background-color: whitesmoke;
      border: none;
      color: #1B0E20;
    }

    tbody tr:nth-child(even) {
      background-color: rgb(91, 91, 91);
    }
  </style>
<?php
include('../other/head.php');
$donnees = $bdd->query('SELECT * from employer_login e join conge c on e.matricule_emp=c.matricule_emp ORDER BY c.date_demande DESC Limit 10');
?>

<div class="annotation">
  <h2>Congé</h2>
</div><br>


<div class="contenu">
  <header>
    <h4>Congé</h4>

    <div class="bouton">
      <a href="./liste_absent_conge.php"><button type="submit">Voir employé en Congé</button></a>
      <a href="./conge_en_attente.php"><button type="submit">Voir Conge en attente</button></a>
      <a href="./conge_refuse.php"><button type="submit">Voir Congé refusé</button></a>
    </div>
  </header> <br />

  <table class="">

    <thead>
      <th>Congé N°</th>
      <th>Nom complet</th>
      <th>Date demande</th>
      <th>Date debut</th>
      <th>Date fin</th>
      <th>Durée absence</th>
      <th>Statut</th>
      <th>Validation</th>
    </thead>
    <tbody>
      <?php

      $quotaCongeAnnuel = 30;
      $congePrise = 0;
      $resteConge = $quotaCongeAnnuel;
      $anneeEnCours = date('Y');

      while ($data = $donnees->fetch(PDO::FETCH_ASSOC)) {
        //Convertir les chaines de caractere en objet DateTime
        $dateDebut = new DateTime($data['date_debut']);
        $dateFin = new DateTime($data['date_fin']);
        //Calcule interval
        $interval = date_diff($dateDebut, $dateFin);

        //Calcule durée de l'absence
        $dureeAbsenceJ = $interval->days;
        $dureeAbsenceH = $interval->h;


        echo '<tr>

                <td>' . $data['id_conge'] . '</td>
                <td>' . $data['nom_emp'] . ' ' . $data['prenom_emp'] . '</td>
                
                <td>' . date('d/m/Y H:i:s', strtotime($data['date_demande'])) . '</td>
                <td>' . date('d/m/Y H:i:s', strtotime($data['date_debut'])) . '</td>
                <td>' . date('d/m/Y H:i:s', strtotime($data['date_fin'])) . '</td>
                <td>' . $data['duree_jours_conge'] . ' Jours ' . $data['duree_heure_conge'] . ' heures</td>
                <td>' . $data['statut_conge'] . '</td>
                <td>
                    
                <div style="padding: 0;display:flex; justify-content:center;">
                
                    <a href="valider_conge.php?id=' . $data['id_conge'] . '" id="confirm" onclick="confirmValidate(event)" >
                        <i class="bx bx-chevrons-right" style="text-decoration:none; color:black; font-size:20px"></i>
                    </a>&nbsp;&nbsp;&nbsp;&nbsp;';
      ?>
        <?php echo '                  
                        <a href="../traitement/supprimer_conge.php?id=' . $data['id_conge'] . '" id="confirm" onClick="confirmDelete(event, this.href)">
                        <i class="bx bx-trash" style="color:red;font-size:20px"></i>
                    </a>
                </div>                    
                </td>
                </tr>';
        ?>
        <!-- Modal Structure -->
        <div id="confirmationModal">
          <p>Êtes-vous sûr de vouloir supprimer cet élément ?</p>
          <button id="confirmDelete">Oui</button>
          <button id="cancelDelete">Non</button>
        </div>
      <?php
      }
      ?>
    </tbody>
  </table>

  <div class="Ajout_conge">
    <a href="./detail_conge.php"><button type="submit">Liste complet de congé</button></a>
  </div>
</div>

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
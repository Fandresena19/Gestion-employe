<?php
  include('../other/head.php');
  $donnees = $bdd->query('select * from employer_login e join permission p on e.matricule_emp=p.matricule_emp ');

  ?>

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

<div class="annotation">
        <h2>Permission</h2>
      </div><br>


      <div class="contenu">
        <header>
          <h4>Permission</h4>

          <div class="bouton">
            <a href="./liste_absent_perm.php"><button type="submit">Voir employé en permission</button></a>
            <a href="./permission_en_attente.php"><button type="submit">Voir permission en attente</button></a>
            <a href="./permission_refuse.php"><button type="submit">Voir permission refusé</button></a>
          </div>
        </header><br />

        <table class="">

          <thead>
            <th>id</th>
            <th>Matricule</th>
            <th>Nom complet</th>
            <th>Date demande</th>
            <th>Date debut</th>
            <th>Date fin</th>
            <th>Validation</th>
          </thead>
          <tbody>

            <?php
            while ($data = $donnees->fetch()) {

              echo '<tr>
                <td>' . $data['id_permission'] . '</td>
                <td>' . $data['matricule_emp'] . '</td>
                <td>' . $data['nom_emp'] . ' ' . $data['prenom_emp'] . '</td>
                
                <td>' . date('d/m/Y H:i:s', strtotime($data['date_demande_per'])) . '</td>
                <td>' . date('d/m/Y H:i:s', strtotime($data['date_debut_per'])) . '</td>
                <td>' . date('d/m/Y H:i:s', strtotime($data['date_fin_per'])) . '</td>
                <td>
                  <div style="padding: 0;display:flex; justify-content:center;">
                    <a href="valider_permission.php?id=' . $data['id_permission'] . '" id="confirm" onclick="confirmValidate(event)" >
                        <i class="bx bx-chevrons-right" style="text-decoration:none; color:black; font-size:20px"></i>
                    </a>&nbsp;&nbsp;&nbsp;&nbsp;

                    <a href="supprimer_permission.php?id=' . $data['id_permission'] . '" id="confirm" onClick="confirmDelete(event, this.href)">
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
          <a href="./detail_permission.php"><button type="submit">Liste complet des permissions</button></a>
        </div>
      </div>
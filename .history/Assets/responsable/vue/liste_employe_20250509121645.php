<div class="annotation">
        <h2>Employés</h2>
      </div>
      
      <div class="contenu">
        <header>
          <h4>Liste Employés</h4>

          <div class="bouton">
            <a href="./ajoute_employe.php"><button type="submit">Ajouter Employer</button></a>
          </div>
        </header><br/>
        
      <table class="">
        <thead>
            <th>Matricule</th>
            <th>Nom complet</th>
            <th>Date d'embauche</th>
            <th>Rôle</th>
            <th>Service</th>
            <th>Téléphone</th>
            <th>Action</th>
        </thead>
        <tbody>
          <?php
          while ($data = $donnees->fetch()) {

            echo '<tr>
 					<td>' . $data['matricule_emp'] . '</td>
				 	 <td>' . $data['nom_emp'] . ' ' . $data['prenom_emp'] . '</td>
				 	 
				 	 <td>' . $data['date_embauche'] . '</td>
				 	 <td>' . $data['role'] . '</td>
				 	 <td>' . $data['nom_service'] . '</td>
				 	 <td>' . $data['telephone'] . '</td>
				 	 <td>';
            echo'
				 	 		<a href="modif_employe.php?id=' . $data['matricule_emp'] . '"><i class="bx bx-edit" style="color:orange;font-size:20px"></i></a>&nbsp;&nbsp;&nbsp;&nbsp';

            echo'
							<a href="supprimer_employe.php?id=' . $data['matricule_emp'] . '" id="confirm" onClick="confirmDelete(event, this.href)"><i class="bx bx-trash" style="color:red;font-size:20px"></i></a>';

            echo'
              </td>
				 	 </tr>';
          }
          ?>
                          <!-- Modal Structure -->
                          <div id="confirmationModal">
                  <p>Êtes-vous sûr de vouloir supprimer cet élément ?</p>
                  <button id="confirmDelete">Oui</button>
                  <button id="cancelDelete">Non</button>
                </div>
        </tbody>

      </table>

      </div>

<?php
include('../other/foot.php')
?>
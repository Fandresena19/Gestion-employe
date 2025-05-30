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

                /* MESSAGE ALERT ET SUCCESS */
                .alert {
      padding: 15px;
      margin: auto;
      width: calc(50% - 0px);
      border: 1px solid transparent;
      border-radius: 4px;
    }

    .alert-success {
      color: #3c763d;
      background-color: #b4d5a6;
      border-color: #d6e9c6;
      text-align: center;
    }

    .alert-danger {
      color: #a94442;
      background-color: #f2dede;
      border-color: #ebccd1;
      text-align: center;
    }
  </style>
<?php
include('../other/head.php');

$donnees = $bdd->query('select * from employer_login e join obtenir o on o.matricule_emp=e.matricule_emp join service s
 on s.id_service=e.id_service join grade g on g.id_grade=o.id_grade');

   // Afficher le message de succès s'il existe
if (isset($_SESSION['success_message'])) {
  echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
  unset($_SESSION['success_message']);
}

// Afficher le message d'erreur s'il existe
if (isset($_SESSION['error_message'])) {
  echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
  unset($_SESSION['error_message']);
}

?>


<div class="annotation">
  <h2>Employés</h2>
</div>

<div class="contenu">
  <header>
    <h4>Liste Employés</h4>

    <div class="bouton">
      <a href="./ajout_employe.php"><button type="submit">Ajouter Employer</button></a>
    </div>
  </header><br />

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
 					<td>' . $data['matricule'] . '</td>
				 	 <td>' . $data['nom_emp'] . ' ' . $data['prenom_emp'] . '</td>
				 	 
				 	 <td>' . $data['date_embauche'] . '</td>
				 	 <td>' . $data['role'] . '</td>
				 	 <td>' . $data['nom_service'] . '</td>
				 	 <td>' . $data['telephone'] . '</td>
				 	 <td>';
        echo '
				 	 		<a href="modif_employe.php?id=' . $data['matricule_emp'] . '"><i class="bx bx-edit" style="color:orange;font-size:20px"></i></a>&nbsp;&nbsp;&nbsp;&nbsp';

        echo '
							<a href="../traitement/supprimer_employe.php?id=' . $data['matricule_emp'] . '" id="confirm" onClick="confirmDelete(event, this.href)"><i class="bx bx-trash" style="color:red;font-size:20px"></i></a>';

        echo '
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
include('../other/foot.php')
?>
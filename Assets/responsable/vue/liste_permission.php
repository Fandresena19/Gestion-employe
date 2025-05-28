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

<?php
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

//Recupération de la date actuelle
$date_actuelle = new DateTime();
$annee_courante = $date_actuelle->format('Y');

// Filtre par défaut (mois courant)
$filtrer = isset($_GET['filtrer']) ? $_GET['filtrer'] : 'mois_courant';
$debut_consom = isset($_GET['debut']) ? $_GET['debut'] : '';
$fin_consom = isset($_GET['fin']) ? $_GET['fin'] : '';

// Message d'erreur pour les dates personnalisées non remplies
$error_message = '';
if ($filtrer == 'consom' && (empty($debut_consom) || empty($fin_consom))){
  $error_message = "veuillez remplir les deux dates pour le filtre personnalisé.";
  $filtrer = 'mois_courant';
}

//Préparation de la clause WHERE en fonction du filtre
$where_clause ="WHERE 1=1";
$params = [];

if ($filtrer == 'mois_courant'){
  $where_clause .= " AND MONTH(p.date_demande_per) = YEAR(CURRENT_DATE())";
  $period_text = "du mois courant";
}elseif($filtrer == 'mois_dernier'){
  $where_clause .= " AND MONTH(p.date_demande_per) = MONTH(CURRENT_DATE(), INTERVAL 1 MONTH))
                    AND YEAR(p.date_demande_per) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))";   
  $period_text = "du mois dernier"; 
}elseif ($filtrer == 'semaine_derniere'){
  $where_clause .= " AND YEADWEEK(p.date_demande_per, 1) = YEARWEEK(DATE_SUB(CURRENT_DATE(), INTERVAL 1 WEEK), 1)";
  $period_text = "de la semaine dernière";
}elseif($filtrer == 'annee_courante'){
  $where_clause .= " AND YEAR(p.date_demande_per) = YEAR(CURRENT_DATE())";
  $period_text = "de l'année courante";
}elseif ($filtrer == 'consom' && !empty($debut_consom) && !empty($fin_consom)){
  $where_clause .= " AND p.date_demande_per BETWEEN :debut_date AND :fin_date";
  $params = [
    ':debut_date' => $debut_consom,
    ':fin_date' => $fin_consom
  ];
  $period_text = "du " .date('d/m/Y',strtotime($debut_consom)) 
  . " au " .date('d/m/Y', strtotime($fin_consom));
}else{
  $period_text = "de tous les permissions";
}

//Récupérer les statistiques générales (pour la période filtréee)
$sql_stats = "SELECT COUNT(*) AS nb_permission,
      SUM(CASE WHEN p.Statut_permission = 'Valide' THEN 1 ELSE 0 END) AS nb_valides, 
      SUM(CASE WHEN p.Statut_permission = 'Refuse' THEN 1 ELSE 0 END) AS nb_refuses,
      SUM(CASE WHEN p.Statut_permission = 'En attente' THEN 1 ELSE 0 END) AS nb_en_attente
      FROM permission p
      JOIN employer_login e ON p.matricule_emp = e.matricule_emp
      $where_clause";
$donnees_stats = $bdd->prepare($sql_stats);
$donnees_stats->execute($params);
$stats = $donnees_stats->fetch(PDO::FETCH_ASSOC);

$nb_permission = $stats['nb_permission'] ?: 0;
$nb_valides = $stats['nb_valides'] ?: 0;
$nb_refuses = $stats['nb_refuses'] ?: 0;
$nb_en_attente = $stats['nb_en_attente'] ?: 0;

//calculer les totaux des jours de permission pour la période
$sql_total_jours = "SELECT 
                    SUM(p.duree_jour_per) AS total_jours,
                    SUM(p.duree_heure_per) AS total_heures
                    FROM permission p
                    JOIN employer_login e ON p.matricule_emp = e.matricule_emp
                    $where_clause AND p.Statut_permission = 'Valide'";
$stmt_total = $bdd->prepare($sql_total_jours);
$stmt_total->execute($params);
$totaux = $stmt_total->fetch(PDO::FETCH_ASSOC);

$total_jours_periode = $totaux['total_jours'] ?: 0;
$total_heures_periode = $totaux['total_heures'] ?: 0;

//Préparer la requête principale avec limite si pas de filtre spécifique
$limit_clause = '';
if($filtrer == 'mois_courant' && empty($_GET['show_all'])){
  $limit_clause = "LIMIT 10"; // Afficher les 10 derniers permissions
}

$donnees = $bdd->prepare("SELECT p.*, e.nom_emp, e.prenom_emp
                        FROM permission p
                        JOIN employer_login e ON p.matricule_emp = e.matricule_emp
                        $where_clause
                        ORDER BY p.date_demande_per DESC
                        $limit_clause");
$donnees->execute($params);
?>

<div class="annotation">
  <h2>Permission</h2>
</div><br>

<?php if (!empty($error_message)): ?>
    <div class="error-message">
        <?php echo $error_message; ?>
    </div>
<?php endif; ?>

<div class="conge-summary">
    <h4>Statistiques des congés <?php echo $period_text; ?></h4>
    <div class="conge-detail">
        <div class="conge-item total">
            <strong>Total Congés</strong>
            <p><?php echo $nb_permission; ?> demandes</p>
        </div>
        <div class="conge-item quota">
            <strong>Congés Validés</strong>
            <p><?php echo $nb_valides; ?> demandes</p>
        </div>
        <div class="conge-item taken">
            <strong>Congés Refusés</strong>
            <p><?php echo $nb_refuses; ?> demandes</p>
        </div>
        <div class="conge-item remaining">
            <strong>En Attente</strong>
            <p><?php echo $nb_en_attente; ?> demandes</p>
        </div>
        <div class="conge-item total">
            <strong>Jours Validés</strong>
            <p><?php echo $total_jours_periode; ?> jours, <?php echo $total_heures_periode; ?> heures</p>
        </div>
    </div>
</div>

<div class="contenu">
  <header>
    <h4>Permission</h4>

    <div class="bouton">
      <a href="./liste_absent_perm.php"><button type="submit">Voir employé en permission</button></a>
      <a href="./permission_en_attente.php"><button type="submit">Voir permission en attente</button></a>
      <a href="./permission_refuse.php"><button type="submit">Voir permission refusé</button></a>
    </div>
  </header><br />

  <div class="navigation">
    <a href="?filtrer=last_month"><button type="submit">Mois dernier</button></a>
    <a href="?filtrer=last_week"><button type="submit">Semaine dernière</button></a>
    <a href="?filtrer=current_month"><button type="submit">Mois courant</button></a>
    <a href="?filtrer=current_year"><button type="submit">Année courante</button></a>
    <a href="?filtrer=all"><button type="submit">Tous les permissions</button></a>
  </div>

  <table class="">

    <thead>
      <th>Permission N°</th>
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
                <td>' . $data['nom_emp'] . ' ' . $data['prenom_emp'] . '</td>
                
                <td>' . date('d/m/Y H:i:s', strtotime($data['date_demande_per'])) . '</td>
                <td>' . date('d/m/Y H:i:s', strtotime($data['date_debut_per'])) . '</td>
                <td>' . date('d/m/Y H:i:s', strtotime($data['date_fin_per'])) . '</td>
                <td>
                  <div style="padding: 0;display:flex; justify-content:center;">
                    <a href="valider_permission.php?id=' . $data['id_permission'] . '" id="confirm" onclick="confirmValidate(event)" >
                        <i class="bx bx-chevrons-right" style="text-decoration:none; color:black; font-size:20px"></i>
                    </a>&nbsp;&nbsp;&nbsp;&nbsp;

                    <a href="../traitement/supprimer_permission.php?id=' . $data['id_permission'] . '" id="confirm" onClick="confirmDelete(event, this.href)">
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

  <?php include('../other/foot.php');?>
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
      width: 30%;
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
</style>

<?php
include('../other/head.php');

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

// Récupération de la date actuelle
$currentDate = new DateTime();
$annee_courante = $currentDate->format('Y');

// Filtre par défaut (mois courant)
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'current_month';
$custom_start = isset($_GET['start']) ? $_GET['start'] : '';
$custom_end = isset($_GET['end']) ? $_GET['end'] : '';

// Message d'erreur pour les dates personnalisées non remplies
$error_message = '';
if ($filter == 'custom' && (empty($custom_start) || empty($custom_end))) {
    $error_message = "Veuillez remplir les deux dates pour le filtre personnalisé.";
    $filter = 'current_month';
}

// Préparation de la clause WHERE en fonction du filtre
$where_clause = " WHERE 1=1";
$params = [];

if ($filter == 'current_month') {
    $where_clause .= " AND MONTH(c.date_demande) = MONTH(CURRENT_DATE()) AND YEAR(c.date_demande) = YEAR(CURRENT_DATE())";
    $period_text = "du mois courant";
} elseif ($filter == 'last_month') {
    $where_clause .= " AND MONTH(c.date_demande) = MONTH(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH)) 
                     AND YEAR(c.date_demande) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))";
    $period_text = "du mois dernier";
} elseif ($filter == 'last_week') {
    $where_clause .= " AND YEARWEEK(c.date_demande, 1) = YEARWEEK(DATE_SUB(CURRENT_DATE(), INTERVAL 1 WEEK), 1)";
    $period_text = "de la semaine dernière";
} elseif ($filter == 'current_year') {
    $where_clause .= " AND YEAR(c.date_demande) = YEAR(CURRENT_DATE())";
    $period_text = "de l'année courante";
} elseif ($filter == 'custom' && !empty($custom_start) && !empty($custom_end)) {
    $where_clause .= " AND c.date_demande BETWEEN :start_date AND :end_date";
    $params['start_date'] = $custom_start;
    $params['end_date'] = $custom_end;
    $period_text = "du " . date('d/m/Y', strtotime($custom_start))
     . " au " . date('d/m/Y', strtotime($custom_end));
} else {
    $period_text = "de tous les congés";
}

// Récupérer les statistiques générales (pour la période filtrée)
$sql_stats = "SELECT COUNT(*) AS nb_conges, 
             SUM(CASE WHEN c.statut_conge = 'Validé' THEN 1 ELSE 0 END) AS nb_valides,
             SUM(CASE WHEN c.statut_conge = 'Refusé' THEN 1 ELSE 0 END) AS nb_refuses,
             SUM(CASE WHEN c.statut_conge = 'En attente' THEN 1 ELSE 0 END) AS nb_attente
             FROM conge c
             JOIN employer_login e ON c.matricule_emp = e.matricule_emp 
             $where_clause";
$stmt_stats = $bdd->prepare($sql_stats);
$stmt_stats->execute($params);
$stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);

$nb_conges = $stats['nb_conges'] ?: 0;
$nb_valides = $stats['nb_valides'] ?: 0;
$nb_refuses = $stats['nb_refuses'] ?: 0;
$nb_attente = $stats['nb_attente'] ?: 0;

// Calculer les totaux des jours de congé pour la période
$sql_total_jours = "SELECT 
                    SUM(c.duree_jours_conge) AS total_jours,
                    SUM(c.duree_heure_conge) AS total_heures
                    FROM conge c
                    JOIN employer_login e ON c.matricule_emp = e.matricule_emp 
                    $where_clause AND c.statut_conge = 'Validé'";
$stmt_total = $bdd->prepare($sql_total_jours);
$stmt_total->execute($params);
$totaux = $stmt_total->fetch(PDO::FETCH_ASSOC);

$total_jours_periode = $totaux['total_jours'] ?: 0;
$total_heures_periode = $totaux['total_heures'] ?: 0;

// Préparer la requête principale avec limite si pas de filtre spécifique
$limit_clause = "";
if ($filter == 'current_month' && empty($_GET['show_all'])) {
    $limit_clause = " LIMIT 10";
}

$donnees = $bdd->prepare("SELECT c.*, e.nom_emp, e.prenom_emp 
                         FROM conge c 
                         JOIN employer_login e ON c.matricule_emp = e.matricule_emp 
                         $where_clause 
                         ORDER BY c.date_demande DESC 
                         $limit_clause");
$donnees->execute($params);
?>

<div class="annotation">
  <h2>Congé</h2>
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
            <p><?php echo $nb_conges; ?> demandes</p>
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
            <p><?php echo $nb_attente; ?> demandes</p>
        </div>
        <div class="conge-item total">
            <strong>Jours Validés</strong>
            <p><?php echo $total_jours_periode; ?> jours, <?php echo $total_heures_periode; ?> heures</p>
        </div>
    </div>
</div>

<div class="contenu">
  <header>
    <h4>Congé</h4>

    <div class="bouton">
      <a href="./liste_absent_conge.php"><button >Voir employé en Congé</button></a>
      <a href="./conge_en_attente.php"><button >Voir Conge en attente</button></a>
      <a href="./conge_refuse.php"><button >Voir Congé refusé</button></a>
    </div>
  </header> <br />

  <div class="navigation">
    <a href="?filter=last_month"><button type="submit">Mois dernier</button></a>
    <a href="?filter=last_week"><button type="submit">Semaine dernière</button></a>
    <a href="?filter=current_month"><button type="submit">Mois courant</button></a>
    <a href="?filter=current_year"><button type="submit">Année courante</button></a>
    <a href="?filter=all"><button type="submit">Tous les congés</button></a>
  </div>

  <div class="custom-filter">
    <form method="GET" action="">
      <input type="hidden" name="filter" value="custom">
      <label for="start">Du:</label>
      <input type="date" id="start" name="start" value="<?php echo $custom_start; ?>" required>
      <label for="end">Au:</label>
      <input type="date" id="end" name="end" value="<?php echo $custom_end; ?>" required>
      <button type="submit">Filtrer</button>
    </form>
  </div>

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
      $has_rows = false;

      while ($data = $donnees->fetch(PDO::FETCH_ASSOC)) {
        $has_rows = true;
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
                    </a>&nbsp;&nbsp;&nbsp;&nbsp;
                    <a href="../traitement/supprimer_conge.php?id=' . $data['id_conge'] . '" id="confirm" onClick="confirmDelete(event, this.href)">
                        <i class="bx bx-trash" style="color:red;font-size:20px"></i>
                    </a>
                </div>                    
                </td>
                </tr>';
      }

      if (!$has_rows) {
        echo '<tr><td colspan="8" class="text-center">Aucun congé trouvé pour cette période</td></tr>';
      }
      ?>
    </tbody>
  </table>

  <!-- Modal Structure -->
  <div id="confirmationModal">
    <p>Êtes-vous sûr de vouloir supprimer cet élément ?</p>
    <button id="confirmDelete">Oui</button>
    <button id="cancelDelete">Non</button>
  </div>

  <div class="Ajout_conge">
    <a href="./detail_conge.php"><button type="submit">Liste complet de congé</button></a>
    <?php if ($filter == 'current_month' && empty($_GET['show_all'])): ?>
      <a href="?filter=current_month&show_all=1"><button type="submit">Voir tous les congés du mois</button></a>
    <?php endif; ?>
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

    function confirmValidate(event) {
      // Vous pouvez ajouter une confirmation pour la validation si nécessaire
      return true;
    }
</script>

<?php
if (isset($_SESSION['Matricule_resp'])) {
  require_once('../traitement/debut.php');

  $date_du_jour = date('Y-m-d');

  // 📧 TRAITEMENT DES CONGÉS (seulement ceux pas encore envoyés)
  $sql = "SELECT c.id_conge, c.date_debut, c.date_fin, e.nom_emp AS nom_emp, e.prenom_emp AS prenom_emp
          FROM conge c
          JOIN employer_login e ON c.matricule_emp = e.matricule_emp
          WHERE c.date_debut = ? AND c.statut_conge = 'Validé' AND c.email_envoye = 0";
  $stmt = $bdd->prepare($sql);
  $stmt->execute([$date_du_jour]);
  $conges = $stmt->fetchAll(PDO::FETCH_ASSOC);

  if ($conges) {
    foreach ($conges as $conge) {
      $nom = $conge['nom_emp'];
      $prenom = $conge['prenom_emp'];
      $date_debut = $conge['date_debut'];
      $nom_complet = $nom . ' ' . $prenom;

      // Envoi de l'email
      $email_envoye = envoyerEmail($nom_complet, $date_debut, 'Congé');
      
      if ($email_envoye) {
        // Mettre à jour le statut de l'email envoyé pour ce congé spécifique
        $update_sql = "UPDATE conge SET email_envoye = 1 WHERE id_conge = ?";
        $update_stmt = $bdd->prepare($update_sql);
        $update_stmt->execute([$conge['id_conge']]);
        
        if ($update_stmt->rowCount() > 0) {
          echo "Email envoyé et statut mis à jour avec succès pour " . $nom_complet . "<br>";
        } else {
          echo "Email envoyé mais échec de la mise à jour du statut pour " . $nom_complet . "<br>";
        }
      } else {
        echo "Échec de l'envoi de l'email pour " . $nom_complet . "<br>";
      }
    }
  } else {
    // Pas de message si aucun congé à traiter
  }
}

include('../other/foot.php');
?>
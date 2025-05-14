<?php
include('../other/head.php');

// Récupérer l'ID du timesheet à afficher
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: liste_timesheet.php');
    exit;
}

$id = intval($_GET['id']);

// Récupérer les informations du timesheet
$requete = $bdd->prepare('
    SELECT t.*, e.nom_emp, e.prenom_emp
    FROM timesheet t
    JOIN employer_login e ON t.matricule_emp = e.matricule_emp
    WHERE t.id_timesheet = :id
');
$requete->execute(['id' => $id]);

// Vérifier si le timesheet existe
if ($requete->rowCount() === 0) {
    header('Location: liste_timesheet.php');
    exit;
}

$timesheet = $requete->fetch(PDO::FETCH_ASSOC);

// Fonction pour formater la durée en heures et minutes
function afficherDureeEnHeuresEtMinutes($duree) {
    $heures = floor($duree);
    $minutes = round(($duree - $heures) * 60);
    
    return $heures . 'h ' . ($minutes < 10 ? '0' . $minutes : $minutes) . 'min';
}
?>

<div class="annotation">
  <h2>Détail de la Feuille de Temps</h2>
</div><br>

<div class="contenu">
  <header>
    <h4>Détails de la Tâche #<?php echo $timesheet['id_timesheet']; ?></h4>

    <div class="bouton">
      <?php if (!$estResponsable): ?>
        <a href="edit_timesheet.php?id=<?php echo $timesheet['id_timesheet']; ?>"><button type="button">Modifier</button></a>
      <?php endif; ?>
      <a href="liste_timesheet.php"><button type="button">Retour à la liste</button></a>
    </div>
  </header><br />

  <div class="timesheet-details">
    <div class="info-section">
      <div class="info-group">
        <label>Employé :</label>
        <div class="info-value"><?php echo $timesheet['nom_emp'] . ' ' . $timesheet['prenom_emp']; ?></div>
      </div>
      
      <div class="info-group">
        <label>Tâche :</label>
        <div class="info-value"><?php echo $timesheet['tache']; ?></div>
      </div>
      
      <div class="info-group">
        <label>Client :</label>
        <div class="info-value"><?php echo $timesheet['client']; ?></div>
      </div>
      
      <?php if (!$estResponsable): ?>
        <div class="info-group">
          <label>Date :</label>
          <div class="info-value"><?php echo date('d/m/Y', strtotime($timesheet['date_tache'])); ?></div>
        </div>
        
        <div class="info-group">
          <label>Durée :</label>
          <div class="info-value"><?php echo afficherDureeEnHeuresEtMinutes($timesheet['duree_tache']); ?></div>
        </div>
      <?php endif; ?>
    </div>
    
    <div class="description-section">
      <h5>Description de la tâche</h5>
      <div class="description-content">
        <?php echo nl2br($timesheet['description_tache']) ?: 'Aucune description fournie'; ?>
      </div>
    </div>
    
    <?php if (!$estResponsable && $timesheet['note']): ?>
      <div class="note-section">
        <h5>Note</h5>
        <div class="note-content">
          <?php echo nl2br($timesheet['note']); ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<style>
  html,
  body {
    height: 100%;
    margin: 0;
    overflow: hidden;
  }

  .contenu {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    margin-top: -2% !important;
    overflow-y: auto;
    padding: 0 20px;
  }

  .contenu header {
    flex-shrink: 0;
  }

  .timesheet-details {
    background-color: rgba(50, 50, 50, 0.7);
    border-radius: 5px;
    padding: 20px;
    max-width: 800px;
    margin: 0 auto;
  }

  .info-section {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
  }

  .info-group {
    margin-bottom: 10px;
  }

  .info-group label {
    display: block;
    font-weight: bold;
    color: #ddd;
    margin-bottom: 5px;
  }

  .info-value {
    color: white;
    background-color: rgba(70, 70, 70, 0.7);
    padding: 8px;
    border-radius: 4px;
  }

  .description-section, .note-section {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #555;
  }

  .description-section h5, .note-section h5 {
    margin-top: 0;
    color: #ddd;
    font-size: 16px;
  }

  .description-content, .note-content {
    color: white;
    background-color: rgba(70, 70, 70, 0.7);
    padding: 15px;
    border-radius: 4px;
    min-height: 100px;
    white-space: pre-wrap;
  }
</style>

<script src="../js/Sidebar.js"></script>

<?php
include('../other/foot.php');
?>
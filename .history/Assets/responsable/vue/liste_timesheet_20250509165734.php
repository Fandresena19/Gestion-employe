<?php
include('../other/head.php');

// Vérifier si l'utilisateur connecté est un responsable
$estResponsable = false;
if (isset($_SESSION['role_emp']) && $_SESSION['role_emp'] === 'Admin') {
    $estResponsable = true;
}

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Filtre par employé
$filtreEmploye = isset($_GET['employe']) ? intval($_GET['employe']) : 0;

// Requête pour compter le nombre total de timesheet
$whereClause = $filtreEmploye > 0 ? "WHERE t.matricule_emp = $filtreEmploye" : "";
$requeteCount = $bdd->query("
    SELECT COUNT(*) as total
    FROM timesheet t
    $whereClause
");
$totalRows = $requeteCount->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalRows / $limit);

// Requête pour récupérer les timesheet avec pagination
$requeteSQL = "
    SELECT t.*, e.nom_emp, e.prenom_emp
    FROM timesheet t
    JOIN employer_login e ON t.matricule_emp = e.matricule_emp
    $whereClause
    ORDER BY t.date_tache DESC
    LIMIT $offset, $limit
";
$requeteTimesheets = $bdd->query($requeteSQL);

// Requête pour récupérer tous les employés pour le filtre
$requeteEmployes = $bdd->query('
    SELECT matricule_emp, nom_emp, prenom_emp
    FROM employer_login
    ORDER BY nom_emp, prenom_emp
');

// Fonction pour formater la durée en heures et minutes
function afficherDureeEnHeuresEtMinutes($duree) {
    $heures = floor($duree);
    $minutes = round(($duree - $heures) * 60);
    
    return $heures . 'h ' . ($minutes < 10 ? '0' . $minutes : $minutes) . 'min';
}

?>

<div class="annotation">
  <h2>Gestion des Feuilles de Temps</h2>
</div><br>

<div class="contenu">
  <header>
    <h4>Liste des Feuilles de Temps</h4>

    <div class="actions">
      <div class="filtre">
        <form action="" method="GET" id="filtreForm">
          <select name="employe" onchange="this.form.submit()">
            <option value="0">Tous les employés</option>
            <?php while ($employe = $requeteEmployes->fetch(PDO::FETCH_ASSOC)): ?>
              <option value="<?php echo $employe['matricule_emp']; ?>" <?php echo ($filtreEmploye == $employe['matricule_emp']) ? 'selected' : ''; ?>>
                <?php echo $employe['nom_emp'] . ' ' . $employe['prenom_emp']; ?>
              </option>
            <?php endwhile; ?>
          </select>
        </form>
      </div>
      
      <div class="bouton">
        <?php if (!$estResponsable): ?>
          <a href="./create_timesheet.php"><button type="button">Nouvelle feuille</button></a>
        <?php endif; ?>
        <a href="./detail_timesheet.php?periode=semaine_courante"><button type="button">Vue détaillée</button></a>
        <a href="../dashboard.php"><button type="button">Retour</button></a>
      </div>
    </div>
  </header><br />

  <div class="scrollbar">
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Employé</th>
          <th>Date</th>
          <th>Tâche</th>
          <th>Client</th>
          <th>Durée</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($requeteTimesheets->rowCount() > 0): ?>
          <?php while ($timesheet = $requeteTimesheets->fetch(PDO::FETCH_ASSOC)): ?>
            <tr>
              <td><?php echo $timesheet['id_timesheet']; ?></td>
              <td><?php echo $timesheet['nom_emp'] . ' ' . $timesheet['prenom_emp']; ?></td>
              <td><?php echo date('d/m/Y', strtotime($timesheet['date_tache'])); ?></td>
              <td><?php echo $timesheet['tache']; ?></td>
              <td><?php echo $timesheet['client']; ?></td>
              <td><?php echo afficherDureeEnHeuresEtMinutes($timesheet['duree_tache']); ?></td>
              <td class="actions-cell">
                <?php if (!$estResponsable): ?>
                  <a href="edit_timesheet.php?id=<?php echo $timesheet['id_timesheet']; ?>" class="btn-action edit" title="Modifier">
                    <i class="fas fa-edit"></i>
                  </a>
                  <a href="#" onclick="confirmDelete(event, 'delete_timesheet.php?id=<?php echo $timesheet['id_timesheet']; ?>')" class="btn-action delete" title="Supprimer">
                    <i class="fas fa-trash"></i>
                  </a>
                <?php endif; ?>
                <a href="view_timesheet.php?id=<?php echo $timesheet['id_timesheet']; ?>" class="btn-action view" title="Voir détails">
                  <i class="fas fa-eye"></i>
                </a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="7" class="no-data">Aucune feuille de temps trouvée.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>

    <?php if ($totalPages > 1): ?>
      <div class="pagination">
        <?php if ($page > 1): ?>
          <a href="?page=<?php echo ($page - 1); ?>&employe=<?php echo $filtreEmploye; ?>" class="page-link">Précédent</a>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <a href="?page=<?php echo $i; ?>&employe=<?php echo $filtreEmploye; ?>" class="page-link <?php echo ($i == $page) ? 'active' : ''; ?>">
            <?php echo $i; ?>
          </a>
        <?php endfor; ?>
        
        <?php if ($page < $totalPages): ?>
          <a href="?page=<?php echo ($page + 1); ?>&employe=<?php echo $filtreEmploye; ?>" class="page-link">Suivant</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Modal de confirmation pour la suppression -->
<div id="confirmationModal" class="modal">
  <div class="modal-content">
    <p>Êtes-vous sûr de vouloir supprimer cette feuille de temps ?</p>
    <div class="modal-actions">
      <button id="confirmDelete">Oui, supprimer</button>
      <button id="cancelDelete">Annuler</button>
    </div>
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
  }

  .contenu header {
    flex-shrink: 0;
  }

  <?php
  include('../other/foot.php');
  ?>
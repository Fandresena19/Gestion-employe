<?php
include('../other/head_profil.php');

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

$user_id = $_SESSION['Matricule_resp'];

$stmt = $bdd->prepare("SELECT * FROM responsable r JOIN service s ON r.id_service=s.id_service WHERE r.Matricule_resp = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$profile_picture = '../../uploads/default.jpg';

if (!$user) {
  echo "Utilisateur non trouvé";
  exit();
}

// Récupérer la date actuelle
$date_actuelle = date('Y-m-d');

// Récupérer les congés validés où la date d'aujourd'hui est comprise entre date_debut et date_fin
$sql_conges = "SELECT c.*, e.nom_emp, e.prenom_emp, e.id_service 
               FROM conge c 
               JOIN employer_login e ON c.matricule_emp = e.matricule_emp 
               WHERE c.statut_conge = 'Validé' 
               AND ? BETWEEN DATE(c.date_debut) AND DATE(c.date_fin)
               AND e.id_service = ?
               ORDER BY c.date_debut ASC";
$stmt_conges = $bdd->prepare($sql_conges);
$stmt_conges->execute([$date_actuelle, $user['id_service']]);
$conges_actuels = $stmt_conges->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les permissions validées où la date d'aujourd'hui est comprise entre date_debut_per et date_fin_per
$sql_permissions = "SELECT p.*, e.nom_emp, e.prenom_emp, e.id_service 
                    FROM permission p 
                    JOIN employer_login e ON p.matricule_emp = e.matricule_emp 
                    WHERE p.Statut_permission = 'Valide' 
                    AND ? BETWEEN DATE(p.date_debut_per) AND DATE(p.date_fin_per)
                    AND e.id_service = ?
                    ORDER BY p.date_debut_per ASC";
$stmt_permissions = $bdd->prepare($sql_permissions);
$stmt_permissions->execute([$date_actuelle, $user['id_service']]);
$permissions_actuelles = $stmt_permissions->fetchAll(PDO::FETCH_ASSOC);

// Compter le nombre total de notifications
$total_notifications = count($conges_actuels) + count($permissions_actuelles);
?>

<style>
  .notifications-container {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    margin: 20px 0;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.18);
    overflow: hidden;
    transition: all 0.3s ease;
  }

  .notifications-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px;
    color: white;
    cursor: pointer;
    user-select: none;
    transition: all 0.3s ease;
  }

  .notifications-header:hover {
    background: rgba(255, 255, 255, 0.1);
  }

  .notifications-header-left {
    display: flex;
    align-items: center;
  }

  .notifications-header h3 {
    margin: 0;
    font-size: 1.5em;
    font-weight: 600;
  }

  .notification-badges {
    background: #ff4757;
    color: white;
    border-radius: 50%;
    padding: 5px 10px;
    font-size: 0.8em;
    font-weight: bold;
    margin-left: 10px;
    min-width: 25px;
    text-align: center;
    animation: pulse 2s infinite;
  }

  @keyframes pulse {
    0% {
      transform: scale(1);
    }

    50% {
      transform: scale(1.1);
    }

    100% {
      transform: scale(1);
    }
  }

  .collapse-icon {
    font-size: 1.2em;
    transition: transform 0.3s ease;
    color: rgba(255, 255, 255, 0.8);
  }

  .collapse-icon.collapsed {
    transform: rotate(-90deg);
  }

  .notifications-content {
    padding: 0 20px 20px 20px;
    max-height: 1000px;
    overflow: hidden;
    transition: all 0.4s ease;
  }

  .notifications-content.collapsed {
    max-height: 0;
    padding: 0 20px;
  }

  .notification-items {
    background: rgba(255, 255, 255, 0.9);
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 10px;
    border-left: 4px solid;
    transition: all 0.3s ease;
    opacity: 1;
    transform: translateY(0);
  }

  .notification-items:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  }

  .notification-conge {
    border-left-color: #3742fa;
  }

  .notification-permission {
    border-left-color: #2ed573;
  }

  .notification-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .notification-text {
    flex: 1;
  }

  .notification-text h4 {
    margin: 0 0 5px 0;
    color: #2c3e50;
    font-size: 1.1em;
  }

  .notification-text p {
    margin: 0;
    color: rgb(65, 69, 69) !important;
    font-size: 0.9em;
  }

  .notification-text p strong {
    color: rgb(59, 58, 58) !important;
  }

  .notification-type {
    background: #ecf0f1;
    color: #2c3e50;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.8em;
    font-weight: 600;
    text-transform: uppercase;
  }

  .notification-conge .notification-type {
    background: #e3f2fd;
    color: #1976d2;
  }

  .notification-permission .notification-type {
    background: #e8f5e8;
    color: #388e3c;
  }

  .no-notifications {
    text-align: center;
    color: rgba(255, 255, 255, 0.8);
    padding: 40px;
    font-style: italic;
  }

  .notification-icon {
    font-size: 1.5em;
    margin-right: 10px;
  }

  .date-info {
    font-weight: 600;
    color: #34495e;
  }

  .motif-info {
    background: #f8f9fa;
    padding: 5px 8px;
    border-radius: 5px;
    font-size: 0.85em;
    margin-top: 5px;
    color: rgb(55, 55, 55) !important;
  }

  .motif-info strong {
    color: #2c3e50;
  }

  .collapse-hint {
    font-size: 0.8em;
    color: rgba(255, 255, 255, 0.7);
    margin-left: 10px;
    opacity: 0.7;
  }

  @media (max-width: 768px) {
    .notification-content {
      flex-direction: column;
      align-items: flex-start;
    }

    .notification-type {
      margin-top: 10px;
    }

    .notifications-header {
      flex-direction: column;
      align-items: flex-start;
    }

    .notifications-header-left {
      width: 100%;
      justify-content: space-between;
    }

    .collapse-hint {
      margin-left: 0;
      margin-top: 5px;
    }
  }

  /* Animation pour les notifications */
  @keyframes slideIn {
    from {
      opacity: 0;
      transform: translateY(20px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .notification-item {
    animation: slideIn 0.5s ease forwards;
  }
</style>

<h2 class="head-text">Profil Responsable</h2>

<!-- Section des notifications -->
<div class="notifications-container">
  <div class="notifications-header" onclick="toggleNotifications()">
    <div class="notifications-header-left">
      <i class="bx bx-bell notification-icon"></i>
      <h3>Notifications du jour</h3>
      <?php if ($total_notifications > 0): ?>
        <span class="notification-badges"><?php echo $total_notifications; ?></span>
      <?php endif; ?>
      <span class="collapse-hint">Cliquez pour afficher/masquer</span>
    </div>
    <i class="bx bx-chevron-down collapse-icon" id="collapseIcon"></i>
  </div>

  <div class="notifications-content" id="notificationsContent">
    <?php if ($total_notifications > 0): ?>
      <!-- Notifications de congés -->
      <?php foreach ($conges_actuels as $conge): ?>
        <div class="notification-items notification-conge">
          <div class="notification-content">
            <div class="notification-text">
              <p>
                <strong><?php echo $conge['nom_emp'] . ' ' . $conge['prenom_emp']; ?></strong> est en congé<br>
                <span class="date-info">
                  Du <?php echo date('d/m/Y', strtotime($conge['date_debut'])); ?>
                  au <?php echo date('d/m/Y', strtotime($conge['date_fin'])); ?>
                </span>
                <br>
                Durée: <?php echo $conge['duree_jours_conge']; ?> jours, <?php echo $conge['duree_heure_conge']; ?> heures
              </p>
              <?php if (!empty($conge['motif'])): ?>
                <div class="motif-info">
                  <strong>Motif:</strong> <?php echo $conge['motif']; ?>
                </div>
              <?php else: ?>
                <div class="motif-info">
                  <strong>Motif:</strong> Non spécifié
                </div>
              <?php endif; ?>
            </div>
            <div class="notification-type">Congé</div>
          </div>
        </div>
      <?php endforeach; ?>

      <!-- Notifications de permissions -->
      <?php foreach ($permissions_actuelles as $permission): ?>
        <div class="notification-items notification-permission">
          <div class="notification-content">
            <div class="notification-text">
              <p>
                <strong><?php echo $permission['nom_emp'] . ' ' . $permission['prenom_emp']; ?></strong> est en permission<br>
                <span class="date-info">
                  Du <?php echo date('d/m/Y H:i', strtotime($permission['date_debut_per'])); ?>
                  au <?php echo date('d/m/Y H:i', strtotime($permission['date_fin_per'])); ?>
                </span>
                <br>
                Durée: <?php echo $permission['duree_jour_per']; ?> jours, <?php echo $permission['duree_heure_per']; ?> heures
              </p>
              <?php if (!empty($permission['motif_per'])): ?>
                <div class="motif-info">
                  <strong>Motif:</strong> <?php echo $permission['motif_per']; ?>
                </div>
              <?php else: ?>
                <div class="motif-info">
                  <strong>Motif:</strong> Non spécifié
                </div>
              <?php endif; ?>
            </div>
            <div class="notification-type">Permission</div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="no-notifications">
        <i class="bx bx-info-circle" style="font-size: 3em; margin-bottom: 10px; color: rgba(255, 255, 255, 0.6);"></i>
        <p>Aucune notification pour aujourd'hui</p>
      </div>
    <?php endif; ?>
  </div>
</div>

<div class="contenu_profil">
  <form action="../traitement/edit_resp.php" method="post">
    <div class="contenu1_profil">
      <h3>Modifier responsable</h3>

      <div class="sous-contenu">
        <div class="ligne1">
          <input type="text" value="<?= 'Matricule = ' . $user['Matricule_resp'] ?>" class="input_mat" disabled />
          <input type="text" value="<?= strtoupper($user['nom_resp']) ?>" name="nom" class="input2" oninput="this.value = this.value.toUpperCase();" />
          <input type="text" name="prenom" value="<?= ucfirst(strtolower($user['prenom_resp'])) ?>" class="input2"
            oninput="this.value = this.value.charAt(0).toUpperCase() + this.value.slice(1).toLowerCase();" />
        </div><br><br>

        <div class="ligne2">
          <input type="mail" name="mail" value="<?= $user['mail_resp'] ?>" class="input3" />
        </div><br><br>

        <div class="ligne3plus">
          <div>
            <span><input type="password" name="current_password"
                placeholder="Votre mot de passe" id="mdp" />
              <i class="bx bx-hide" id="icone"></i> </span>
          </div>

          <div>
            <span><input type="password" name="new_password"
                placeholder="Nouveau mot de passe" id="mdp1" />
              <i class="bx bx-hide" id="icone"></i> </span>
          </div>

          <div>
            <span><input type="password" name="confirm_password"
                placeholder="confirmer nouveau mot de passe" id="mdp2" />
              <i class="bx bx-hide" id="icone"></i> </span>
          </div>
        </div><br />

        <button type="submit" class="bouton_confirmer">Confirmer modification</button>
      </div>
    </div>
  </form>

  <div class="contenu2_profil">
    <div class="photo">
      <div class="cadre_photo" align="center">
        <img src="<?= $profile_picture; ?>" alt="" width="150" class="photo_de_profil">
        <h4><?php echo $user['nom_resp'] . '&nbsp;'  . $user['prenom_resp']; ?></h4>
      </div>
    </div>

    <div class="detail">
      <table class="tableau_profil">
        <tr><br>
          <td>Matricule </td>
          <td> <?= ':' . '&nbsp' . $user['Matricule_resp'] ?></td>
        </tr>
        <tr>
          <td>Nom </td>
          <td><?php echo ':' . '&nbsp' . strtoupper($user['nom_resp']) ?></td>
        </tr>
        <tr>
          <td>Prénom</td>
          <td><?php echo ':' . '&nbsp' . $user['prenom_resp']; ?></td>
        </tr>
        <tr>
          <td>Mail </td>
          <td><?php echo ':' . '&nbsp' . $user['mail_resp'] ?></td>
        </tr>
        <tr>
          <td>Service </td>
          <td><?php echo ':' . '&nbsp' . $user['nom_service']; ?></td>
        </tr>
      </table>
    </div>
  </div>
</div>

<script>
  // État de la section des notifications (ouverte par défaut)
  let notificationsExpanded = true;

  // Fonction pour basculer l'état des notifications
  function toggleNotifications() {
    const content = document.getElementById('notificationsContent');
    const icon = document.getElementById('collapseIcon');

    notificationsExpanded = !notificationsExpanded;

    if (notificationsExpanded) {
      content.classList.remove('collapsed');
      icon.classList.remove('collapsed');
    } else {
      content.classList.add('collapsed');
      icon.classList.add('collapsed');
    }
  }

  // Animation d'entrée pour les notifications
  document.addEventListener('DOMContentLoaded', function() {
    const notifications = document.querySelectorAll('.notification-item');

    // Animer les notifications seulement si elles sont visibles
    if (notificationsExpanded) {
      notifications.forEach((notification, index) => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateY(20px)';

        setTimeout(() => {
          notification.style.transition = 'all 0.5s ease';
          notification.style.opacity = '1';
          notification.style.transform = 'translateY(0)';
        }, index * 100);
      });
    }
  });

  // Raccourci clavier pour basculer les notifications (Ctrl + N)
  document.addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.key === 'n') {
      e.preventDefault();
      toggleNotifications();
    }
  });
</script>

<?php
include('../other/foot.php');
?>
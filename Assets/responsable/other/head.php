<?php
session_start();
include('bd.php');

if (!isset($_SESSION['Matricule_resp'])) {
  header('location:../../index.php');
  exit();
}

// Afficher le message de succès s'il existe
if (isset($_SESSION['success'])) {
  echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
  unset($_SESSION['success']); // Supprimer le message après affichage
}

// Afficher le message d'erreur s'il existe
if (isset($_SESSION['error'])) {
  echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
  unset($_SESSION['error']); // Supprimer le message après affichage
}


// Récupérer les notifications pour l'utilisateur
$sql = "SELECT * FROM notifications_responsable ORDER BY date_notif_resp DESC";
$stmt = $bdd->prepare($sql);
$stmt->execute();
$Notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Compte notification non lue
$sql_notif = "SELECT COUNT(*) as unread_count FROM notifications_responsable 
WHERE statut_notif_resp = 'non lu'";
$stmt_notif = $bdd->prepare($sql_notif);
$stmt_notif->execute();
$notif_count = $stmt_notif->fetch(PDO::FETCH_ASSOC)['unread_count'];

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../../bootstrap4/css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="../../bootstrap4/boxicons-2.1.4/css/boxicons.min.css">
  
  <!-- CSS pour les thèmes -->
  <link rel="stylesheet" href="../../css/css_complet.css" id="theme-stylesheet">
  
  <title>Responsable</title>

  <style>
    /* Style pour le bouton de changement de thème */
    .theme-toggle {
      cursor: pointer;
      transition: all 0.3s ease;
      position: relative;
    }
    
    .theme-toggle:hover {
      transform: scale(1.05);
    }
    
    .theme-icon {
      width: 20px;
      height: 20px;
      transition: transform 0.3s ease;
    }
    
    .theme-toggle:hover .theme-icon {
      transform: rotate(20deg);
    }
    
    /* Animation pour la transition de thème */
    * {
      transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
    }
  </style>
</head>

<body id="body_sidebar">

  <div class="container-fluid">
   
      <div id="container">
        <!-- Content overlay for when notification dropdown is open -->
        <div class="content-overlay" id="content-overlay"></div>

        <!-- Sidebar -->
        <nav class="sidebar" id="side_nav">
          <header>
            <div class="image-text">
              <span class="image">
                <img src="../../Icone/Logo.png" alt="">
              </span>

              <div class="text header-text">
                <span class="employe">Employé</span>
              </div>
            </div>

            <i class="bx bx-chevron-right toggle"></i>
          </header>

          <div class="menu-bar">
            <div class="menu">
            <ul class="list-unstyled nav-links">
                <a href="../vue/pageresponsable.php">
                  <li class="active"><img src="../../Icone/home.png" alt="" width="25px" class="icone"><span>Profil</span></li>
                </a>
                <a href="../vue/liste_employe.php">
                  <li class=""><img src="../../Icone/user.png" alt="" width="25px" class="icone"><span>Employés</span></li>
                </a>
                <a href="../vue/liste_conge.php">
                  <li><img src="../../Icone/leave.png" alt="" width="20px" class="icone"><span>Congé</span></li>
                </a>
                <a href="../vue/liste_permission.php">
                  <li><img src="../../Icone/permission.png" alt="" width="20px" class="icone"><span>Permission</span></li>
                </a>
                <a href="../vue/detail_timesheet.php">
                  <li><img src="../../Icone/timesheet.png" alt="" width="20px" class="icone"><span>Timesheet</span></li>
                </a>

                <a href="javascript:void(0);" id="notification-toggle">
                  <li class="notification-container">
                    <div class="notification-badge">
                      <img src="../../Icone/notif.png" alt="" width="20px" class="icone">
                      <?php if ($notif_count > 0): ?>
                        <span class="badge"><?= $notif_count ?></span>
                      <?php endif; ?>
                    </div>
                    <span>Notifications</span>
                  </li>
                </a>

                <!-- Bouton pour changer de thème -->
                <a href="javascript:void(0);" class="theme-toggle" id="theme-toggle">
                  <li>
                    <img src="../../Icone/theme-light.png" alt="" class="icone theme-icon" id="theme-icon">
                    <span id="theme-text">Mode Clair</span>
                  </li>
                </a>

                <a href="#">
                  <li onclick="document.getElementById('id01').style.display='block'"><img src="../../Icone/logout.png" alt="" width="-25px" height="20px" class="icone"><span>Deconnexion</span></li>
                </a><br>

              </ul>
            </div>
          </div>
        </nav>
        <!-- /Sidebar -->

        <div id="id01" class="modal">
          <span onclick="document.getElementById('id01').style.display='none'" class="close">×</span>
          <form class="modal-content" action="">
            <div class="container">
              <h1>Deconnexion</h1>
              <p>Voulez-vous vraiment se deconnecter?</p>

              <div class="clearfix">
                <a href="../../other/logout.php"><button type="button" onclick="document.getElementById('id01').style.display='none'" class="deletebtn">Oui</button></a>
                <button type="button" onclick="document.getElementById('id01').style.display='none'" class="cancelbtn">Non</button>
              </div>
            </div>
          </form>
        </div>

    <!-- Central Notification Dropdown -->
    <div class="notification-dropdown" id="notification-dropdown">
      <div class="notification-header">
        <h3>Notifications</h3>
        <span class="options-icon close-notification" id="close-notification">&times;</span>
      </div>
      <div class="notification-tabs">
        <div class="notification-tab active" data-tab="all">Tous</div>
        <div class="notification-tab" data-tab="unread">Non lus (<?= $notif_count ?>)</div>
      </div>

      <?php if (count($Notifications) > 0): ?>
        <div class="notification-list">
          <?php foreach ($Notifications as $notification):
            // Déterminer la classe appropriée pour la notification
            $typeClass = '';
            if ($notification['Type'] == 'Validé') {
              $typeClass = 'success';
              $iconClass = 'bx bx-check';
            } elseif ($notification['Type'] == 'Refusé') {
              $typeClass = 'error';
              $iconClass = 'bx bx-x';
            } else {
              $typeClass = 'info';
              $iconClass = 'bx bx-bell';
            }

            // Classe pour non lu/lu
            $readClass = ($notification['Statut_notif_resp'] == 'non lu') ? 'non_lu' : 'lu';
          ?>
            <div class="notification-item <?= $readClass ?> <?= $typeClass ?>"
              data-id="<?= $notification['id_notification_resp'] ?>"
              data-genre="<?= htmlspecialchars($notification['Genre_notif'], ENT_QUOTES, 'UTF-8') ?>"
              data-message="<?= htmlspecialchars($notification['Message_resp'], ENT_QUOTES, 'UTF-8') ?>"
              data-date="<?= htmlspecialchars($notification['date_notif_resp'], ENT_QUOTES, 'UTF-8') ?>"
              data-type="<?= htmlspecialchars($notification['Type'], ENT_QUOTES, 'UTF-8') ?>">
              <div class="notification-icon">
                <i class="<?= $iconClass ?>"></i>
              </div>
              <div class="notification-content">
                <p class="notification-message"><strong><?= $notification['Genre_notif'] ?>:</strong> <?= $notification['Message_resp'] ?></p>
                <div class="notification-time"><?= $notification['date_notif_resp'] ?></div>
              </div>
              <?php if ($notification['Statut_notif_resp'] == 'non lu'): ?>
                <div class="notification-dot"></div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="notification-empty">
          <p>Aucune notification à afficher</p>
        </div>
      <?php endif; ?>

      <div class="see-all">
        <a href="../vue/notification_resp.php">Voir toutes les notifications</a>
      </div>
    </div>

    <!-- Notification Detail Modal -->
    <div id="notificationModal" class="modal">
      <div class="modal-content">
        <span class="close" id="close-modal">&times;</span>
        <h2 id="modalTitle"></h2>
        <p id="modalMessage"></p>
        <p id="modalDate" class="text-muted"></p>

        <div class="clearfix" id="notif">
          <a id="deleteLink" href="#"><button class="deletebtn">Supprimer</button></a>
          <button class="cancelbtn" id="close-modal-btn">Fermer</button>
        </div>
      </div>
    </div>

    <div class="dashboard" id="Main">

      <!-- Scripts -->
      <script>
        document.addEventListener('DOMContentLoaded', function() {
          // Éléments DOM pour les notifications
          const notificationToggle = document.getElementById('notification-toggle');
          const notificationDropdown = document.getElementById('notification-dropdown');
          const contentOverlay = document.getElementById('content-overlay');
          const closeNotification = document.getElementById('close-notification');
          const closeModal = document.getElementById('close-modal');
          const closeModalBtn = document.getElementById('close-modal-btn');
          const notificationModal = document.getElementById('notificationModal');
          const tabs = document.querySelectorAll('.notification-tab');
          const notificationItems = document.querySelectorAll('.notification-item');

          // Éléments DOM pour le changement de thème
          const themeToggle = document.getElementById('theme-toggle');
          const themeStylesheet = document.getElementById('theme-stylesheet');
          const themeIcon = document.getElementById('theme-icon');
          const themeText = document.getElementById('theme-text');

          // Initialiser le thème depuis localStorage ou défaut (sombre)
          function initializeTheme() {
            const savedTheme = localStorage.getItem('theme') || 'dark';
            setTheme(savedTheme);
          }

          // Fonction pour définir le thème
          function setTheme(theme) {
            if (theme === 'light') {
              themeStylesheet.href = '../../css/css_light.css';
              themeIcon.src = '../../Icone/theme-dark.png';
              themeText.textContent = 'Mode Sombre';
            } else {
              themeStylesheet.href = '../../css/css_complet.css';
              themeIcon.src = '../../Icone/theme-light.png';
              themeText.textContent = 'Mode Clair';
            }
            localStorage.setItem('theme', theme);
          }

          // Fonction pour basculer le thème
          function toggleTheme() {
            const currentTheme = localStorage.getItem('theme') || 'dark';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            setTheme(newTheme);
          }

          // Event listener pour le changement de thème
          themeToggle.addEventListener('click', function(e) {
            e.preventDefault();
            toggleTheme();
          });

          // Initialiser le thème au chargement
          initializeTheme();

          // Code existant pour les notifications...
          
          // Fonction pour ouvrir le dropdown de notifications
          function openNotificationDropdown() {
            notificationDropdown.style.display = 'block';
            contentOverlay.style.display = 'block';
            document.body.style.overflow = 'hidden';
          }

          // Fonction pour fermer le dropdown de notifications
          function closeNotificationDropdown() {
            notificationDropdown.style.display = 'none';
            contentOverlay.style.display = 'none';
            document.body.style.overflow = '';
          }

          // Toggle dropdown quand on clique sur l'icône de notification
          notificationToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (notificationDropdown.style.display === 'block') {
              closeNotificationDropdown();
            } else {
              openNotificationDropdown();
            }
          });

          // Fermer dropdown quand on clique sur l'overlay
          contentOverlay.addEventListener('click', closeNotificationDropdown);

          // Fermer dropdown quand on clique sur le X
          closeNotification.addEventListener('click', closeNotificationDropdown);

          // Fonctionnalité des onglets
          tabs.forEach(tab => {
            tab.addEventListener('click', function() {
              tabs.forEach(t => t.classList.remove('active'));
              this.classList.add('active');

              const tabType = this.getAttribute('data-tab');
              const items = document.querySelectorAll('.notification-item');

              items.forEach(item => {
                if (tabType === 'all') {
                  item.style.display = '';
                } else if (tabType === 'unread' && item.classList.contains('non_lu')) {
                  item.style.display = '';
                } else if (tabType === 'unread') {
                  item.style.display = 'none';
                }
              });
            });
          });

          // Afficher les détails de la notification et marquer comme lu
          notificationItems.forEach(item => {
            item.addEventListener('click', function() {
              const id = this.getAttribute('data-id');
              const genre = this.getAttribute('data-genre');
              const message = this.getAttribute('data-message');
              const date = this.getAttribute('data-date');
              const type = this.getAttribute('data-type');

              showNotificationDetails(id, genre, message, date, type);

              if (this.classList.contains('non_lu')) {
                markAsRead(id, this);
              }

              closeNotificationDropdown();
            });
          });

          // Fonction pour afficher les détails de notification
          function showNotificationDetails(id, genre, message, date, type) {
            document.getElementById('modalTitle').innerText = genre;
            document.getElementById('modalTitle').className = type === 'Validé' ? 'success' :
              (type === 'Refusé' ? 'error' : 'info');
            document.getElementById('modalMessage').innerText = message + ' (Verifiez votre liste ' + genre + ')';
            document.getElementById('modalDate').innerText = 'Date: ' + date;
            document.getElementById('deleteLink').href = '../traitement/delete_notification.php?id=' + id;

            notificationModal.style.display = 'block';
          }

          // Fermer le modal
          closeModal.addEventListener('click', function() {
            notificationModal.style.display = 'none';
          });

          closeModalBtn.addEventListener('click', function() {
            notificationModal.style.display = 'none';
          });

          // Fonction pour marquer une notification comme lue
          function markAsRead(id, element) {
            element.classList.remove('non_lu');
            element.classList.add('lu');

            const dot = element.querySelector('.notification-dot');
            if (dot) dot.remove();

            const badge = document.querySelector('.badge');
            if (badge) {
              const count = parseInt(badge.textContent) - 1;
              if (count > 0) {
                badge.textContent = count;
                const unreadTab = document.querySelector('.notification-tab[data-tab="unread"]');
                unreadTab.textContent = 'Non lus (' + count + ')';
              } else {
                badge.remove();
                const unreadTab = document.querySelector('.notification-tab[data-tab="unread"]');
                unreadTab.textContent = 'Non lus (0)';
              }
            }

            fetch('../traitement/mark_as_read.php?id=' + id)
              .then(response => {
                if (!response.ok) {
                  throw new Error('Erreur lors du marquage de la notification');
                }
                return response.text();
              })
              .catch(error => {
                console.error('Erreur:', error);
              });
          }

          // Fermer le modal si on clique en dehors
          window.addEventListener('click', function(event) {
            if (event.target === notificationModal) {
              notificationModal.style.display = 'none';
            }
          });
        });
      </script>
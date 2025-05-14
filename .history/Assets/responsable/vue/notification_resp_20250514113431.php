<?php
include('../other/head.php');

$sql = "SELECT * FROM notifications_responsable ORDER BY date_notif_resp DESC";

$stmt = $bdd->prepare($sql);
$stmt->execute();
$Notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql_count = "SELECT COUNT(*) FROM notifications_responsable 
WHERE Statut_notif_resp = 'non lu' ";
$statement = $bdd->prepare($sql_count);
$statement->execute();
$notification_count = $statement->fetchColumn();
?>
<style>
    /* Si ce n'est pas déjà dans votre fichier CSS */
    .text-muted {
      color: #888;
      font-size: 0.9em;
    }

    /* Pointer pour les notifications */
    .note {
      cursor: pointer;
      padding: 10px;
      margin-bottom: 5px;
      border-radius: 5px;
      position: relative;
    }

    .non_lu {
      position: relative;
      font-size: 12px;
      font-weight: bolder;
      background-color:rgba(0, 115, 255, 0.58);
      box-shadow:rgb(0, 221, 255) 0px 0px 15px;
    }

    .lu {
      background-color: rgba(129, 127, 127, 0.25);
    }

    #modalDate {
      text-align: right;
      margin-top: 20px;
    }

    #notif a button {
      float: left;
      display: flex;
      width: 20%;
      margin-right: 10px;
      padding: 10px;
      justify-content: center;
    }

    .success {
      border-left: 5px solid #28a745;
    }

    .error {
      border-left: 5px solid #dc3545;
    } 
    
    .note:hover {
      box-shadow: 0 0 5px rgba(0,0,0,0.2);
      transition: all 0.3s ease;
    }

    .exposant {
      background-color: #ff4d4d;
      color: white;
      border-radius: 70%;
      font-size: 20px;
      font-weight: bold;
      padding: 5px 10px;
    }

</style>

<div class="annotation">
  <h2>Notifications Responsable</h2>
</div>

<div class="contenu">
  <header>
    <h3>Notifications
      <span class="exposant"><?= $notification_count; ?></span>
    </h3>
  </header><br />

  <div id="notificationModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="document.getElementById('notificationModal').style.display='none'">&times;</span>
      <h2 id="modalTitle"></h2>
      <p id="modalMessage"></p>
      <p id="modalDate" class="text-muted"></p>

      <div class="clearfix" id="notif">
        <a id="deleteLink" href="#"><button class="deletebtn" onclick="document.getElementById('notificationModal').style.display='none'">Supprimer</button></a>
        <button class="cancelbtn" onclick="document.getElementById('notificationModal').style.display='none'">Fermer</button>
      </div>
    </div>
  </div>

  <div class="notifications-scrollable">
    <?php if (count($Notifications) > 0): ?>
      <div class="notif_contenu">
        <?php foreach ($Notifications as $notification):
          // Déterminer la classe appropriée pour la notification
          $typeClass = 'Nouveau';

          // Échapper correctement les données pour JavaScript
          $id = (int)$notification['id_notification_resp'];
          $genre = htmlspecialchars($notification['Genre_notif'], ENT_QUOTES, 'UTF-8');
          $message = htmlspecialchars($notification['Message_resp'], ENT_QUOTES, 'UTF-8');
          $date = $idnotification['date_notif_resp'] ;
          $type = htmlspecialchars($notification['Type'], ENT_QUOTES, 'UTF-8');

          // Classe pour non lu/lu
          $readClass = ($notification['Statut_notif_resp'] == 'non lu') ? 'non_lu' : 'lu';
        ?>

          <li class="note <?= $readClass ?> <?= $typeClass ?>"
            data-id="<?= $id ?>"
            data-genre="<?= $genre ?>"
            data-message="<?= $message ?>"
            data-date="<?= $date ?>"
            data-type="<?= $type ?>">
            <?php if ($notification['Statut_notif_resp'] == 'non lu'): ?>
              <span class="notification-badge"></span><br />
            <?php endif; ?>
            <strong><?= $genre ?>&nbsp;:</strong>
            <p><?= $message ?>
              <span class="date_notif"><?= $date ?></span>
            </p>
          </li>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p>Aucune notification à afficher</p>
    <?php endif; ?>
  </div>
</div>

<script>
  // Attacher les événements après le chargement complet du DOM
  document.addEventListener('DOMContentLoaded', function() {
    // Sélectionner toutes les notifications
    var notifications = document.querySelectorAll('.note');

    // Ajouter un écouteur d'événements à chaque notification
    notifications.forEach(function(notification) {
      notification.addEventListener('click', function() {
        // Récupérer les données à partir des attributs data-*
        var id = this.getAttribute('data-id');
        var genre = this.getAttribute('data-genre');
        var message = this.getAttribute('data-message');
        var date = this.getAttribute('data-date');
        var type = this.getAttribute('data-type');

        // Appeler la fonction pour afficher le modal
        showNotificationDetails(id, genre, message, date, type);

        // Si la notification était non lue, la marquer comme lue
        if (this.classList.contains('non_lu')) {
          markAsRead(id);
          this.classList.remove('non_lu');
          this.classList.add('lu');

          // Mettre à jour le compteur de notifications
          var counter = document.querySelector('h3 span');
          if (counter) {
            var count = parseInt(counter.textContent) - 1;
            counter.textContent = count > 0 ? count : '';
          }
        }
      });
    });
  });

  function showNotificationDetails(id, genre, message, date, type) {
    // Mettre à jour le contenu du modal
    document.getElementById('modalTitle').innerText = genre;
    document.getElementById('modalTitle').className = type === 'Nouveau' ? 'info' : (type === 'Urgent' ? 'error' : 'success');
    document.getElementById('modalMessage').innerText = message + ' (Verifiez votre ' + genre + ')';
    document.getElementById('modalDate').innerText = 'Date: ' + date;
    document.getElementById('deleteLink').href = '../traitement/delete_notification.php?id=' + id;

    // Afficher le modal
    document.getElementById('notificationModal').style.display = 'block';
  }

  function markAsRead(id) {
    // Utiliser fetch pour marquer la notification comme lue en arrière-plan
    fetch('../traitement/mark_as_read.php?id=' + id)
      .then(response => {
        if (!response.ok) {
          throw new Error('Erreur lors du marquage de la notification');
        }
        return response.text();
      })
      .then(data => {
        console.log('Notification marquée comme lue');
      })
      .catch(error => {
        console.error('Erreur:', error);
      });
  }

  // Supprimer le badge de notification bleu
  const badge = this.querySelector('.notification-badge');
  if (badge) {
    badge.remove();
  }
</script>

<?php
include('../other/foot.php');
?>
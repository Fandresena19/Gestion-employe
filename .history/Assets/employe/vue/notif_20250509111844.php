<?php
include('../other/head.php');

?>


<div class="annotation">
  <h2>Notifications</h2>
</div>

<div class="contenu">
  <header>
    <h3>Notifications
      <span><?= $notification_count; ?></span>
    </h3>
  </header><br />

  <div id="id01" class="modal">
    <span onclick="document.getElementById('id01').style.display='none'" class="close">×</span>
    <form class="modal-content" action="">
      <div class="container">
        <h1>Deconnexion</h1>
        <p>Voulez-vous vraiment se deconnecter?</p>

        <div class="clearfix">
          <a href="../logout.php"><button type="button" onclick="document.getElementById('id01').style.display='none'" class="deletebtn">Oui</button></a>
          <button type="button" onclick="document.getElementById('id01').style.display='none'" class="cancelbtn">Non</button>
        </div>
      </div>
    </form>
  </div>

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
          $typeClass = '';
          if ($notification['Type'] == 'Validé') {
            $typeClass = 'success';
          } elseif ($notification['Type'] == 'Refusé') {
            $typeClass = 'error';
          } else {
            $typeClass = 'info';
          }

          // Échapper correctement les données pour JavaScript
          $genre = htmlspecialchars($notification['Genre'], ENT_QUOTES, 'UTF-8');
          $message = htmlspecialchars($notification['Message'], ENT_QUOTES, 'UTF-8');
          $date = htmlspecialchars($notification['date_notif'], ENT_QUOTES, 'UTF-8');
          $id = (int)$notification['id_notification'];
          $type = htmlspecialchars($notification['Type'], ENT_QUOTES, 'UTF-8');

          // Classe pour non lu/lu
          $readClass = ($notification['Statut_notif'] == 'non lu') ? 'non_lu' : 'lu';
        ?>

          <li class="note <?= $readClass ?> <?= $typeClass ?>"
            data-id="<?= $id ?>"
            data-genre="<?= $genre ?>"
            data-message="<?= $message ?>"
            data-date="<?= $date ?>"
            data-type="<?= $type ?>">
            <?php if ($notification['Statut_notif'] == 'non lu'): ?>
              <span class="notification-badge"></span><br />
            <?php endif; ?>
            <strong><?= $genre ?>&nbsp;:</strong>
            <p><?= $message ?>&nbsp;(Verifiez votre&nbsp;<?= $genre ?>)
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
    document.getElementById('modalTitle').className = type === 'Validé' ? 'success' : (type === 'Refusé' ? 'error' : 'info');
    document.getElementById('modalMessage').innerText = message + ' (Verifiez votre ' + genre + ')';
    document.getElementById('modalDate').innerText = 'Date: ' + date;
    document.getElementById('deleteLink').href = './delete_notification.php?id=' + id;

    // Afficher le modal
    document.getElementById('notificationModal').style.display = 'block';
  }

  function markAsRead(id) {
    // Utiliser fetch pour marquer la notification comme lue en arrière-plan
    fetch('./mark_as_read.php?id=' + id)
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
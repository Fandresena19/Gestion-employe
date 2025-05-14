<div class="annotation">
  <h2>Ajout Congé</h2>
</div>

<div class="contenu">
  <header>
    <h4>Ajouter congé</h4>

    <div class="bouton">
      <a href="./mes_conge.php"><button>Retour</button></a>
    </div>
  </header><br>

  <form action="../traitement/add_conge.php" method="post">
    <div class="form-group">
      <label>L'interessé</label>
      <input type="text" name="emp" class="form-control" value="<?php echo $employe ?>" readonly>
    </div>

    <div class="form-group">
      <label for="">Reference</label>
      <input name="ref" type="text" class="form-control" required>
    </div>

    <div class="form-group">
      <label>Motif de la demande</label>
      <textarea name="motif" class="form-control" required> </textarea>
    </div>

    <div class="form-group">
      <label>Date de la demande</label>
      <input type="datetime-local" name="dateDem" class="form-control" value="<?php echo $date->format('Y-m-d\TH:i'); ?>" min="<?= date('Y-m-d\TH:i'); ?>" readonly />
    </div>

    <div class="form-group">
      <label>Date debut</label>
      <input type="datetime-local" name="dateD" class="form-control" id="date_debut" required>
    </div>
    <div class="form-group">
      <label>Date fin</label>
      <input type="datetime-local" name="dateF" class="form-control" id="date_fin" required>
    </div><br>

    <div class="form-group">
      <button type="submit" class="bouton_confirmer">Demander congé</button>
    </div>
  </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.querySelector('form');

      form.addEventListener('submit', function(e) {
        const dateDebut = document.getElementById('date_debut').value;
        const dateFin = document.getElementById('date_fin').value;

        if (!dateDebut || !dateFin) {
          return true; // Laissez la validation HTML5 gérer ce cas
        }

        // Vérification côté client avant envoi du formulaire
        const debutDate = new Date(dateDebut);
        const finDate = new Date(dateFin);

        if (debutDate >= finDate) {
          alert("La date de début doit être antérieure à la date de fin.");
          e.preventDefault();
          return false;
        }

        // Le reste de la validation sera géré côté serveur
        return true;
      });
    });
  </script>
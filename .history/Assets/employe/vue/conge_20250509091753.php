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

    <form action="add_conge.php" method="post">
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
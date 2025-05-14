<div class="annotation">
        <h2>Congé</h2>
      </div><br>


      <div class="contenu">
        <header>
          <h4>Congé</h4>

          <div class="bouton">
            <a href="./liste_conge.php"><button type="submit">Retour</button></a>
          </div>
        </header> <br />

        <div class="scrollbar">

        <table>
            <thead>
              <tr>
                <th>Nom Complet</th>
                <th>Solde Initial</th>
                <th>Solde Actuel</th>
                <th>Nombre Congés Validés</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($employesInfos as $matricule => $employe): ?>
                <tr class="solde-header">
                  <td><?php echo $employe['nom_complet']; ?></td>
                  <td><?php echo afficherDureeEnJoursEtHeure($employe['solde_initial']); ?></td>
                  <td><?php echo afficherDureeEnJoursEtHeure($employe['solde_actuel']); ?></td>
                  <td><?php echo $employe['nb_conges_valides']; ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>

          <?php foreach ($employesInfos as $matricule => $employe): ?>
            <table>
              <thead>
                <tr>
                  <th colspan="5">Détails des Congés - <?php echo $employe['nom_complet']; ?></th>
                </tr>
                <tr>
                  <th>Congé N°</th>
                  <th>Date Demande</th>
                  <th>Date Début</th>
                  <th>Date Fin</th>
                  <th>Durée</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($employe['conges'])): ?>
                  <tr>
                    <td colspan="5" style="text-align: center;">Aucun congé demandé</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($employe['conges'] as $conge): ?>
                    <tr class="<?php
                      echo $conge['statut'] == 'Validé' ? 'conge-valide' : 
                           ($conge['statut'] == 'En attente' ? 'conge-attente' : 'conge-refuse');
                    ?>">
                      <td><?php echo $conge['id_conge']; ?></td>
                      <td><?php echo date('d/m/Y H:i:s', strtotime($conge['date_demande'])); ?></td>
                      <td><?php echo date('d/m/Y H:i:s', strtotime($conge['date_debut'])); ?></td>
                      <td><?php echo date('d/m/Y H:i:s', strtotime($conge['date_fin'])); ?></td>
                      <td><?php echo afficherDureeEnJoursEtHeure($conge['duree']); ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          <?php endforeach; ?>

        </div>
      </div>
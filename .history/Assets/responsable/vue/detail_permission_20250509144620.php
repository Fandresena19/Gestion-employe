<div class="annotation">
        <h2>Permission</h2>
      </div><br>


      <div class="contenu">
        <header>
          <h4>Détails des Permissions</h4>

          <div class="bouton">
            <a href="./liste_permission.php"><button type="submit">Retour</button></a>
          </div>
        </header> <br />

        <div class="scrollbar">

        <table>
            <thead>
              <tr>
                <th>Nom Complet</th>
                <th>Solde Initial</th>
                <th>Solde Actuel</th>
                <th>Nombre Permissions Validées</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($employesInfos as $matricule => $employe): ?>
                <tr class="solde-header">
                  <td><?php echo $employe['nom_complet']; ?></td>
                  <td><?php echo afficherDureeEnJoursEtHeure($employe['solde_initial']); ?></td>
                  <td><?php echo afficherDureeEnJoursEtHeure($employe['solde_actuel']); ?></td>
                  <td><?php echo $employe['nb_permissions_valides']; ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>

          <?php foreach ($employesInfos as $matricule => $employe): ?>
              <table>
                <thead>
                  <tr>
                    <th colspan="6">Détails des Permissions - <?php echo $employe['nom_complet']; ?></th>
                  </tr>
                  <tr>
                    <th>Permission N°</th>
                    <th>Date Demande</th>
                    <th>Date Début</th>
                    <th>Date Fin</th>
                    <th>Durée</th>
                    <th>Motif</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($employe['permissions'])): ?>
                    <tr class="no-permission">
                      <td colspan="6" style="text-align: center;">Aucune permission demandée</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($employe['permissions'] as $permission): ?>
                      <tr class="<?php
                        echo $permission['statut'] == 'Validé' ? 'permission-valide' : 
                             ($permission['statut'] == 'En attente' ? 'permission-attente' : 'permission-refuse');
                      ?>">
                        <td><?php echo $permission['id_permission']; ?></td>
                        <td><?php echo date('d/m/Y H:i:s', strtotime($permission['date_demande'])); ?></td>
                        <td><?php echo date('d/m/Y H:i:s', strtotime($permission['date_debut'])); ?></td>
                        <td><?php echo date('d/m/Y H:i:s', strtotime($permission['date_fin'])); ?></td>
                        <td><?php echo $permission['duree_jour'] . ' jours ' . $permission['duree_heure'] . ' heures'; ?></td>
                        <td><?php echo $permission['motif']; ?></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
          <?php endforeach; ?>

        </div>
      </div>

      <?php
      include('../other/foot.php');
      ?>
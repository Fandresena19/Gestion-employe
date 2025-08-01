<?php
// Version simplifiée utilisant les fonctions PHP natives pour créer un fichier CSV/Excel

require_once '../other/bd.php';

// Récupération des paramètres
$moisActuel = isset($_GET['mois']) ? intval($_GET['mois']) : intval(date('m'));
$anneeActuelle = isset($_GET['annee']) ? intval($_GET['annee']) : intval(date('Y'));
$filtreEmploye = isset($_GET['employe']) ? $_GET['employe'] : 'tous';

// Vérification de la validité du mois
if ($moisActuel < 1) {
  $moisActuel = 12;
  $anneeActuelle--;
} elseif ($moisActuel > 12) {
  $moisActuel = 1;
  $anneeActuelle++;
}

// Date du premier jour du mois
$premierJourDuMois = $anneeActuelle . '-' . str_pad($moisActuel, 2, '0', STR_PAD_LEFT) . '-01';
// Date du dernier jour du mois
$dernierJourDuMois = date('Y-m-t', strtotime($premierJourDuMois));

// Noms des mois
$nomsMois = [
  1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
  5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
  9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
];

try {
    // Récupération des données (même logique que detail_timesheet.php)
    $requeteEmployes = $bdd->query('SELECT matricule_emp, nom_emp, prenom_emp FROM employer_login ORDER BY nom_emp');

    $listeEmployes = [];
    $employesInfos = [];

    while ($employe = $requeteEmployes->fetch(PDO::FETCH_ASSOC)) {
      $matricule = $employe['matricule_emp'];
      $nomComplet = $employe['nom_emp'] . ' ' . $employe['prenom_emp'];
      
      $listeEmployes[$matricule] = $nomComplet;
      $employesInfos[$matricule] = [
        'nom_complet' => $nomComplet,
        'total_heures' => 0,
        'nb_taches' => 0,
        'taches' => []
      ];
    }

    // Construire la requête pour récupérer les tâches
    $sql = '
        SELECT t.*, e.nom_emp, e.prenom_emp
        FROM timesheet t
        JOIN employer_login e ON e.matricule_emp = t.matricule_emp
        WHERE t.date_tache BETWEEN :debut AND :fin
    ';

    $params = [
      'debut' => $premierJourDuMois,
      'fin' => $dernierJourDuMois
    ];

    if ($filtreEmploye !== 'tous') {
      $sql .= ' AND t.matricule_emp = :matricule';
      $params['matricule'] = $filtreEmploye;
    }

    $sql .= ' ORDER BY e.matricule_emp, t.date_tache DESC';

    $requeteTaches = $bdd->prepare($sql);
    $requeteTaches->execute($params);

    while ($tache = $requeteTaches->fetch(PDO::FETCH_ASSOC)) {
      $matricule = $tache['matricule_emp'];

      if (isset($employesInfos[$matricule])) {
        $tacheInfo = [
          'id_timesheet' => $tache['id_timesheet'],
          'tache' => $tache['tache'],
          'date_tache' => $tache['date_tache'],
          'duree_tache' => $tache['duree_tache'],
          'client' => $tache['client'],
          'description_tache' => $tache['description_tache'],
          'note' => $tache['note']
        ];

        $employesInfos[$matricule]['taches'][] = $tacheInfo;
        $employesInfos[$matricule]['total_heures'] += intval($tache['duree_tache']);
        $employesInfos[$matricule]['nb_taches']++;
      }
    }

    // Filtrer les employés si nécessaire
    if ($filtreEmploye !== 'tous') {
      $employesInfosFiltres = [];
      if (isset($employesInfos[$filtreEmploye])) {
        $employesInfosFiltres[$filtreEmploye] = $employesInfos[$filtreEmploye];
      }
      $employesInfos = $employesInfosFiltres;
    }

    // Création du contenu Excel en HTML (Excel peut ouvrir du HTML avec des tableaux)
    $titreEmploye = ($filtreEmploye !== 'tous' && isset($listeEmployes[$filtreEmploye])) 
        ? ' - ' . $listeEmployes[$filtreEmploye] 
        : '';

    $filename = 'Timesheet_' . $nomsMois[$moisActuel] . '_' . $anneeActuelle . 
                ($filtreEmploye !== 'tous' && isset($listeEmployes[$filtreEmploye]) ? 
                '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $listeEmployes[$filtreEmploye]) : '') . '.xls';

    // Headers pour le téléchargement Excel
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    // Début du contenu HTML/Excel
    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">';
    echo '<head><meta charset="UTF-8"><title>Timesheet</title></head>';
    echo '<body>';

    // Titre principal
    echo '<table border="1" cellspacing="0" cellpadding="5">';
    echo '<tr><td colspan="6" style="background-color: #6A2C82; color: white; font-weight: bold; text-align: center; font-size: 16px;">';
    echo 'Timesheet - ' . $nomsMois[$moisActuel] . ' ' . $anneeActuelle . $titreEmploye;
    echo '</td></tr>';
    echo '</table><br>';

    // Tableau récapitulatif
    echo '<table border="1" cellspacing="0" cellpadding="5">';
    echo '<tr><td colspan="4" style="background-color: #9A9090; font-weight: bold; text-align: center; font-size: 14px;">RÉCAPITULATIF PAR EMPLOYÉ</td></tr>';
    echo '<tr style="background-color: #CCCCCC; font-weight: bold;">';
    echo '<td>Nom Complet</td><td>Nombre de Tâches</td><td>Total Heures</td><td>Moyenne d\'Heures/Tâche</td>';
    echo '</tr>';

    foreach ($employesInfos as $matricule => $employe) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($employe['nom_complet']) . '</td>';
        echo '<td>' . $employe['nb_taches'] . '</td>';
        echo '<td>' . $employe['total_heures'] . ' heures</td>';
        $moyenne = $employe['nb_taches'] > 0 
            ? round($employe['total_heures'] / $employe['nb_taches'], 1) . ' heures'
            : '0 heure';
        echo '<td>' . $moyenne . '</td>';
        echo '</tr>';
    }
    echo '</table><br><br>';

    // Détails des tâches pour chaque employé
    foreach ($employesInfos as $matricule => $employe) {
        echo '<table border="1" cellspacing="0" cellpadding="5">';
        echo '<tr><td colspan="6" style="background-color: #6495ED; color: white; font-weight: bold; text-align: center; font-size: 12px;">';
        echo 'DÉTAILS DES TÂCHES - ' . htmlspecialchars($employe['nom_complet']);
        echo '</td></tr>';
        
        echo '<tr style="background-color: #CCCCCC; font-weight: bold;">';
        echo '<td>Tâche N°</td><td>Date</td><td>Durée (heures)</td><td>Client</td><td>Tâche</td><td>Description</td>';
        echo '</tr>';
        
        if (empty($employe['taches'])) {
            echo '<tr><td colspan="6" style="text-align: center; font-style: italic; background-color: #F0F0F0;">';
            echo 'Aucune tâche enregistrée pour ce mois</td></tr>';
        } else {
            foreach ($employe['taches'] as $tache) {
                echo '<tr style="background-color: #E6F3FF;">';
                echo '<td>' . htmlspecialchars($tache['id_timesheet']) . '</td>';
                echo '<td>' . date('d/m/Y', strtotime($tache['date_tache'])) . '</td>';
                echo '<td>' . htmlspecialchars($tache['duree_tache']) . '</td>';
                echo '<td>' . htmlspecialchars($tache['client']) . '</td>';
                echo '<td>' . htmlspecialchars($tache['tache']) . '</td>';
                echo '<td>' . htmlspecialchars($tache['description_tache']) . '</td>';
                echo '</tr>';
                
                if (!empty($tache['note'])) {
                    echo '<tr><td colspan="6" style="background-color: #FFFACD; font-weight: bold;">';
                    echo 'Note: ' . htmlspecialchars($tache['note']);
                    echo '</td></tr>';
                }
            }
        }
        echo '</table><br><br>';
    }

    echo '</body></html>';

} catch (Exception $e) {
    error_log('Erreur lors de la génération du fichier Excel : ' . $e->getMessage());
    die('Erreur lors de la génération du fichier Excel : ' . $e->getMessage());
}

exit;
?>
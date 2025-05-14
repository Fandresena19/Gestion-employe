<?php
session_start();
include('../other/bd.php');

$matricule_emp = $_POST['emp'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  //Recuperation des dates du formulaire;
  $dateDebut = new DateTime($_POST['dateD']);
  $dateFin = new DateTime($_POST['dateF']);

  // Verfication que la date début est avant la date fin 
  if ($dateDebut >= $dateFin) {
    echo "
        <script>
            alert('Erreur: La date début doit être antérieur à la date fin');
            window.location.href = './add_permission';
        </script>
        ";
    exit();
  }

  //Calcule durée d'absence
  $interval = $dateDebut->diff($dateFin);
  $jourPermission = $interval->days;
  $heurePermission = $interval->h;

  //Convertir en jours pour la comparaison
  $dureeAbsenceDemandee = $jourPermission + ($heurePermission / 24);

  //Calculer le quota de congé annuel et le reste disponible
  $quotaPermissionAnnuel = 10; // Quota fixé à 10 jours
  $permissionPrise = 0;

  //Recupérer toutes les permissions validées de l'employé pour l'année en cours
  $sql = "SELECT * FROM permission
    WHERE matricule_emp = :matricule_emp
    AND Statut_permission = 'Validé'
    AND YEAR(date_debut_per) = :annee_en_cours";
  $stmt = $bdd->prepare($sql);
  $stmt->execute([
    'matricule_emp' => $_POST['emp'],
    'annee_en_cours' => date('Y', strtotime($_POST['dateD']))
  ]);

  //calculer le total des jours de permission déjà prise
  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $debutPermission = new DateTime($row['date_debut_per']);
    $finPermission = new DateTime($row['date_fin_per']);
    $intervalPermission = $debutPermission->diff($finPermission);

    $joursPermission = $intervalPermission->days;
    $heurePermission = $intervalPermission->h;

    //Convertir heures en jours
    $dureePermissionEnJours = $joursPermission + ($heurePermission / 24);

    //Mettre en jours le cumul des permissions déjà prises
    $permissionPrise += $dureePermissionEnJours;
  }

  //Calculer le reste de permission disponible
  $restePermission = $quotaPermissionAnnuel - $permissionPrise;

  //Calculer les jours et heures de permission
  $resteJours = floor($restePermission);
  $resteHeures = round(($restePermission - $resteJours) * 24);

  //Si la durée demandée est supérieur au reste disponible
  if ($dureeAbsenceDemandee > $restePermission) {
    echo "<script>
            alert('Erreur: Votre demande de permission de " . $jourPermission . " jours " .
      $heurePermission . ", reste de permission disponible " .
      $resteJours . " jours " . $resteHeures . " heures.');
            window.location.href = '../vue/permission.php'; 
        </script>";
    exit();
  }

  //Si tout est OK, insérer la demande dans la base de données
  $query = "INSERT INTO permission (matricule_emp, reference_perm, date_demande_per, date_debut_per, 
    date_fin_per, duree_jour_per, duree_heure_per, motif_per, Statut_permission)
    VALUES (:matricule_emp, :reference_perm, :date_demande_per, :date_debut_per, :date_fin_per, 
    :duree_jour_per, :duree_heure_per, :motif_per, :statut_permission)";

  $stmtn = $bdd->prepare($query);
  $result = $stmtn->execute([
    'matricule_emp' => $_POST['emp'],
    'reference_perm' => $_POST['ref'],
    'date_demande_per' => $_POST['dateDem'],
    'date_debut_per' => $_POST['dateD'],
    'date_fin_per' => $_POST['dateF'],
    'duree_jour_per' => $jourPermission,
    'duree_heure_per' => $heurePermission,
    'motif_per' => $_POST['motif'],
    'statut_permission' => 'En attente' // Statut par défaut
  ]);

  if ($result) {
    //calcule solde restant après demande
    $soldeApres = $restePermission - $dureeAbsenceDemandee;
    $soldeJours = floor($soldeApres);
    $soldeHeures = round(($soldeApres - $soldeJours) * 24);

    $emp = $bdd->query("SELECT * FROM employer_login WHERE matricule_emp = $matricule_emp")->fetch(PDO::FETCH_ASSOC);

    $notif = "INSERT INTO notifications_responsable 
    (matricule_emp, Genre_notif, Message_resp, type, date_notif_resp, statut_notif_resp)
        VALUES ( :matricule_emp, 'Nouvelle permission' , :message, 'Congé', :date_notif_resp, :Statut_notif_resp)";
    $stmt = $bdd->prepare($notif);
    $result_notif = $stmt->execute([
      'matricule_emp' => $matricule_emp,
      'message' => "L'employé " . $emp['nom_emp'] . " " . $emp['prenom_emp'] . " a soumis une demande de permission de " . $jourPermission. "
             jours" . $heurePermission . " Heures",
      'date_notif_resp' => $dateDem,
      'Statut_notif_resp' => 'non lu'
    ]);

    echo "<script>
            alert('Durée d\'absence: " . $jourPermission . " jours " . $heurePermission . " Heures');
            window.location.href = '../vue/mes_permission.php'; 
        </script>";
  } else {
    echo "<script>
        alert('Une erreur s\'est produite lors de la soumission de votre demande');
        window.location.href = '../vue/permission.php';
        </script>";
  }
  exit();
}

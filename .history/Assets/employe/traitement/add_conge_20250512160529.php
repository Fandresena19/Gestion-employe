<?php
session_start();
include('../other/bd.php');


$matricule_emp = $_POST['emp'];
$reference = $_POST['ref'];
$motif = $_POST['motif'];
$dateD = $_POST['dateD'];
$dateF = $_POST['dateF'];
$dateDem = $_POST['dateDem'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $dateDebut = new DateTime($dateD);
    $dateFin = new DateTime($dateF);

    // Vérifier que la date de début est avant la date de fin
    if ($dateDebut >= $dateFin) {
        echo "<script>
        alert('Erreur: La date de début doit être antérieure à la date de fin');
        window.location.href = 'conge.php'; 
        </script>";
        exit();
    }

    // Calculer la durée d'absence
    $interval = $dateDebut->diff($dateFin);
    $dureeAbsenceJ = $interval->days;
    $dureeAbsenceH = $interval->h;

    // Convertir en jours pour la comparaison
    $dureeAbsenceDemandee = $dureeAbsenceJ + ($dureeAbsenceH / 24);

    // Calculer le quota de congé annuel et le reste disponible
    $quotaCongeAnnuel = 30;
    $congePrise = 0;

    // Récupérer tous les congés validés de l'employé pour l'année en cours
    $sql = "SELECT * FROM conge 
            WHERE matricule_emp = :matricule_emp 
            AND statut_conge = 'Validé' 
            AND YEAR(date_debut) = :annee_en_cours";
    $stmt = $bdd->prepare($sql);
    $stmt->execute([
        'matricule_emp' => $matricule_emp,
        'annee_en_cours' => date('Y', strtotime($dateD))
    ]);

    // Calculer le total des jours de congé déjà pris
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $debutConge = new DateTime($row['date_debut']);
        $finConge = new DateTime($row['date_fin']);
        $intervalConge = $debutConge->diff($finConge);

        $joursConge = $intervalConge->days;
        $heuresConge = $intervalConge->h;

        // Convertir heures en jours
        $dureeCongeEnJours = $joursConge + ($heuresConge / 24);

        // Mettre à jour le cumul des congés pris
        $congePrise += $dureeCongeEnJours;
    }

    // Calculer le reste de congé disponible
    $resteConge = $quotaCongeAnnuel - $congePrise;

    // Calculer les jours et heures restantes
    $resteJours = floor($resteConge);
    $resteHeures = round(($resteConge - $resteJours) * 24);

    // Vérifier si la durée demandée est supérieure au reste disponible
    if ($dureeAbsenceDemandee > $resteConge) {
        echo "<script>
        alert('Erreur: Votre demande de congé de $dureeAbsenceJ jours 
        $dureeAbsenceH heures dépasse votre solde disponible qui est de
         $resteJours jours $resteHeures heures.');
        window.location.href = 'conge.php'; 
        </script>";
        exit();
    }

    // Si tout est OK, insérer la demande dans la base de données
    $query = "INSERT INTO conge (matricule_emp, reference, date_debut, date_fin,
     duree_jours_conge, duree_heure_conge, motif, date_demande, statut_conge) 
              VALUES (:matricule, :reference, :date_debut, :date_fin, :duree_jours, :duree_heures, :motif, :date_demande, 'En attente')";

    $stmt = $bdd->prepare($query);
    $result = $stmt->execute([
        'matricule' => $matricule_emp,
        'reference' => $reference,
        'date_debut' => $dateD,
        'date_fin' => $dateF,
        'duree_jours' => $dureeAbsenceJ,
        'duree_heures' => $dureeAbsenceH,
        'motif' => $motif,
        'date_demande' => $dateDem
    ]);

    if ($result) {
        // Calculer le solde restant après cette demande
        $soldeApres = $resteConge - $dureeAbsenceDemandee;
        $soldeJours = floor($soldeApres);
        $soldeHeures = round(($soldeApres - $soldeJours) * 24);

        echo "<script>
        alert('Votre demande de congé de $dureeAbsenceJ jours $dureeAbsenceH heures a été soumise avec succès. Il vous restera $soldeJours jours $soldeHeures heures de congé disponible si cette demande est validée.');
        window.location.href = '../vue/mes_conge.php'; 
        </script>";
    } else {
        echo "<script>
        alert('Une erreur s\'est produite lors de la soumission de votre demande.');
        window.location.href = 'conge.php'; 
        </script>";
    }

    exit();
}

// Supposons que ces variables sont déjà disponibles :
$sql = "SELECT Matricule_resp FROM responsable";
$stmt = $conn->query($sql);
$responsables = $stmt->fetchAll(PDO::FETCH_ASSOC);

$matricule_resp = ''; // à définir dynamiquement si nécessaire
$genre_notif = 'Demande de congé';
$message_resp = "L'employé $matricule_emp a soumis une demande de congé.";
$type = 'congé';
$date_notif = date('Y-m-d H:i:s');
$statut_notif = 'non lu';

// Préparation de la requête d'insertion
$insertNotif = $conn->prepare("INSERT INTO notifications_responsable 
(matricule_emp, matricule_resp, Genre_notif, Message_resp, TYPE, date_notif_resp, Statut_notif_resp)
 VALUES (?, ?, ?, ?, ?, ?, ?)");

// Boucle sur chaque responsable
foreach ($responsables as $resp) {
    $matricule_resp = $resp['matricule'];
    $insertNotif->execute([$matricule_emp, $matricule_resp, $genre_notif, $message_resp, $type, $date_notif, $statut_notif]);
}

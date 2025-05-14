<?php
session_start();
require_once('../other/bd.php');


// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Récupérer les données du formulaire
        $matricule_emp = $_POST['matricule_emp'];
        $date_tache = $_POST['date_tache'];
        $tache = htmlspecialchars($_POST['tache']);
        $duree_tache = floatval($_POST['duree_tache']);
        $mission = htmlspecialchars($_POST['mission']);
        $client = htmlspecialchars($_POST['client']);
        $description_tache = htmlspecialchars($_POST['description_tache']);
        $note = htmlspecialchars($_POST['note']);

        // Validation des données
        if (empty($tache) || empty($date_tache) || empty($duree_tache) || empty($mission)) {
            $_SESSION['error'] = "Veuillez remplir tous les champs obligatoires correctement.";
            header('Location: ../vue/timesheet.php');
            exit();
        }

        // Insérer la tâche dans la base de données
        $sql = "INSERT INTO timesheet (matricule_emp, tache, date_tache, duree_tache, client, mission, description_tache, note) 
                VALUES (:matricule_emp, :tache, :date_tache, :duree_tache, :client, :mission, :description_tache, :note)";

        $stmt = $bdd->prepare($sql);
        $stmt->execute([
            'matricule_emp' => $matricule_emp,
            'tache' => $tache,
            'date_tache' => $date_tache,
            'duree_tache' => $duree_tache,
            'mission' => $mission,
            'client' => $client,
            'description_tache' => $description_tache,
            'note' => $note
        ]);

        $emp = $bdd->query("SELECT * FROM employer_login 
        WHERE matricule_emp = $matricule_emp")->fetch(PDO::FETCH_ASSOC);

        $notif = "INSERT INTO notifications_responsable 
        (matricule_emp, Genre_notif, Message_resp, type, date_notif_resp, statut_notif_resp)
        VALUES (:matricule_emp, 'Nouvelle Timesheet' , :message, 'Timesheet', :date_notif_resp, :Statut_notif_resp)";
        $stmtn = $bdd->prepare($notif);
        $result_notif = $stmtn->execute([
            'matricule_emp' => $matricule_emp,
            'message' => "L'employé " . $emp['nom_emp'] . " " . $emp['prenom_emp'] . "a soumis une timesheet",
            'date_notif_resp' => $date_tache,
            'Statu_notif_resp' => 'non lu'
        ]);

        // Rediriger avec un message de succès
        $_SESSION['success'] = "La tâche a été enregistrée avec succès.";
        header('Location: ../vue/mes_timesheet.php');
        exit();
    } catch (PDOException $e) {
        // En cas d'erreur, rediriger avec un message d'erreur
        $_SESSION['error'] = "Erreur lors de l'enregistrement de la tâche: " . $e->getMessage();
        header('Location: ../vue/timesheet.php');
        exit();
    }
} else {
    // Si la page est accédée directement sans soumission de formulaire
    header('Location: ../vue/mes_timesheet.php');
    exit();
}

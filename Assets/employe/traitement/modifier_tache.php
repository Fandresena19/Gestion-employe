<?php
session_start();
require_once('../other/bd.php');

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        require_once './sendMail.php';
        
        // Récupérer les données du formulaire
        $id_timesheet = intval($_POST['id_timesheet']);
        $matricule_emp = $_POST['matricule_emp'];
        $date_tache = $_POST['date_tache'];
        $tache = htmlspecialchars($_POST['tache']);
        $duree_tache = floatval($_POST['duree_tache']);
        $mission = htmlspecialchars($_POST['mission']);
        $client = htmlspecialchars($_POST['client']);
        $description_tache = htmlspecialchars($_POST['description_tache']);
        $note = htmlspecialchars($_POST['note']);

        // Validation des données
        if (empty($tache) || empty($date_tache) || empty($duree_tache) || empty($mission) || empty($id_timesheet)) {
            $_SESSION['error'] = "Veuillez remplir tous les champs obligatoires correctement.";
            header('Location: ../vue/timesheet.php?edit=' . $id_timesheet);
            exit();
        }

        // Vérifier que la tâche appartient bien à l'utilisateur connecté
        $sql_check = "SELECT id_timesheet FROM timesheet WHERE id_timesheet = :id_timesheet AND matricule_emp = :matricule_emp";
        $stmt_check = $bdd->prepare($sql_check);
        $stmt_check->execute([
            'id_timesheet' => $id_timesheet,
            'matricule_emp' => $matricule_emp
        ]);

        if (!$stmt_check->fetch()) {
            $_SESSION['error'] = "Accès non autorisé à cette tâche.";
            header('Location: ../vue/mes_timesheet.php');
            exit();
        }

        // Mettre à jour la tâche dans la base de données
        $sql = "UPDATE timesheet SET 
                tache = :tache, 
                date_tache = :date_tache, 
                duree_tache = :duree_tache, 
                client = :client, 
                mission = :mission, 
                description_tache = :description_tache, 
                note = :note 
                WHERE id_timesheet = :id_timesheet AND matricule_emp = :matricule_emp";

        $stmt = $bdd->prepare($sql);
        $result = $stmt->execute([
            'tache' => $tache,
            'date_tache' => $date_tache,
            'duree_tache' => $duree_tache,
            'mission' => $mission,
            'client' => $client,
            'description_tache' => $description_tache,
            'note' => $note,
            'id_timesheet' => $id_timesheet,
            'matricule_emp' => $matricule_emp
        ]);

        if ($result) {
            // Récupérer les informations de l'employé
            $emp = $bdd->query("SELECT * FROM employer_login 
            WHERE matricule_emp = $matricule_emp")->fetch(PDO::FETCH_ASSOC);

            // Insérer une notification pour le responsable
            $notif = "INSERT INTO notifications_responsable 
            (matricule_emp, Genre_notif, Message_resp, type, date_notif_resp, statut_notif_resp)
            VALUES (:matricule_emp, 'Timesheet Modifié' , :message, 'Timesheet', :date_notif_resp, :Statut_notif_resp)";
            $stmtn = $bdd->prepare($notif);
            $result_notif = $stmtn->execute([
                'matricule_emp' => $matricule_emp,
                'message' => "L'employé " . $emp['nom_emp'] . " " . $emp['prenom_emp'] . " a modifié une tâche (ID: " . $id_timesheet . ")",
                'date_notif_resp' => date('Y-m-d H:i:s'),
                'Statut_notif_resp' => 'non lu'
            ]);

            // Récupérer le message de notification pour l'email
            $sql_message = "SELECT message_resp FROM notifications_responsable 
            WHERE matricule_emp = :matricule_emp AND type = 'Timesheet' ORDER BY date_notif_resp DESC LIMIT 1";
            $stm_mes = $bdd->prepare($sql_message);
            $stm_mes->execute(['matricule_emp' => $matricule_emp]);
            $message_data = $stm_mes->fetch(PDO::FETCH_ASSOC);

            if ($message_data) {
                EnvoiMail($mail, $message_data['message_resp']);
            }

            // Rediriger avec un message de succès
            $_SESSION['success'] = "La tâche a été modifiée avec succès.";
            header('Location: ../vue/mes_timesheet.php');
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de la modification de la tâche.";
            header('Location: ../vue/timesheet.php?edit=' . $id_timesheet);
            exit();
        }
    } catch (PDOException $e) {
        // En cas d'erreur, rediriger avec un message d'erreur
        $_SESSION['error'] = "Erreur lors de la modification de la tâche: " . $e->getMessage();
        header('Location: ../vue/timesheet.php?edit=' . $id_timesheet);
        exit();
    }
} else {
    // Si la page est accédée directement sans soumission de formulaire
    header('Location: ../vue/mes_timesheet.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../css/valider.css">
    <title>Confirmation congé</title>
</head>

<body>

    <form method="post">
        <input type="hidden" name="id_conge" value="<?php echo $_GET['id']; ?>">
        <textarea name="message" cols=62 rows="15" placeholder="Ajouter un message"></textarea><br>
        <div class="bouton" align="end">
            <button type="submit" name="valider">Valider</button>
            <button type="submit" name="refuser">Refuser</button>
        </div>
    </form>

    <?php
    session_start();
    include('../other/bd.php');
    
    
    if (isset($_POST['id_conge'])) {
        $id_conge = $_POST['id_conge'];
        $message = isset($_POST['message']) ? $_POST['message'] : '';

        //Recuperation Matricule_emp
        $req_emp = $bdd->query("SELECT Matricule_emp FROM conge WHERE id_conge = $id_conge");
        $Matricule_emp = $req_emp->fetchColumn();
        
        if (isset($_POST['valider'])) {
            $statut = 'Validé';
            $type = 'Validé'; //Type de notification (succès)
        } elseif (isset($_POST['refuser'])) {
            $statut = 'Refusé';
            $type = 'Refusé'; //Type de notification (erreur)
        }
        
        $sql = "UPDATE conge SET statut_conge = :statut, message = :message WHERE id_conge = :id_conge";
        $stmt = $bdd->prepare($sql);
        $stmt->execute(['statut' => $statut, 'message' => $message, 'id_conge' => $id_conge]);
        
        $dateconge = $bdd->query("SELECT date_debut FROM conge Where id_conge = $id_conge");

        if ($stmt->rowCount() > 0) {
            echo "Congé mis à jour";
            //insertion de la notification

            // Insertion de la notification
            $sql_notif = "INSERT INTO notifications (Genre,Matricule_emp, message, type) VALUES (:Genre,:Matricule_emp, :message, :type)";
            $stmt_notif = $bdd->prepare($sql_notif);
            $stmt_notif->execute([
                'Genre' => 'Congé',
                'Matricule_emp' => $Matricule_emp,
                'message' => "Votre Congé est " . $type . " avec cette remarque : \"" . $message . "\"",
                'type' => $type
            ]);

            if ($stmt_notif->rowCount() > 0) {
                echo "Notification ajoutée";
                require_once('../traitement/sendMail.php');
                $sql_mes = "SELECT c.date_debut, c.date_fin, n.Type, n.Message, e.mail_emp
                FROM conge c JOIN employer_login e ON e.Matricule_emp = c.Matricule_emp
                JOIN notifications n ON n.Matricule_emp = e.Matricule_emp WHERE c.id_conge = :id_conge AND Genre = 'Congé' ORDER BY date_notif DESC LIMIT 1";
                $stmt_mes = $bdd->prepare($sql_mes);
                $stmt_mes->execute(['id_conge' => $id_conge]);
                
                // Récupération du message
                $message_data = $stmt_mes->fetch(PDO::FETCH_ASSOC);
                if ($message_data) {
                    $email = $message_data['mail_emp'];
                    $date_debut = $message_data['date_debut'];
                    $date_fin = $message_data['date_fin'];
                    $type = $message_data['Type'];
                    $message = $message_data['Message'];
                
                    // Préparer le contenu de l'e-mail
                    $subject = "Notification de votre congé";
                    $body = "
                        <p>Bonjour,</p>
                        <p>Votre congé du <strong>$date_debut</strong> au <strong>$date_fin</strong> est <strong>$type</strong>.</p>
                        <p>$message</p>
                    ";
                
                    // Envoi de l’e-mail
                    EnvoiMail($mail, $subject, $body);
                    header('location:./liste_conge.php'); // Redirection vers la page de liste
                }
            } else {
                echo "Erreur lors de l'ajout de la notification";
                // Ajoutez ici un code de debug pour afficher l'erreur SQL
                $errorInfo = $stmt_notif->errorInfo();
                echo "Erreur SQL: " . $errorInfo[2];
            }

        } else {
            echo "Erreur lors de la mise à jour du congé.";
        }
    }

    ?>



</body>

</html>
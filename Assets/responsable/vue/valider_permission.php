1
3<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../css/valider.css">
    <title>Confirmation permission</title>
</head>

<body>

    <form method="post">
        <input type="hidden" name="id_permission" value="<?php echo $_GET['id']; ?>">
        <textarea name="message" cols=62 rows="15" placeholder="Ajouter un message"></textarea><br>
        <div class="bouton" align="end">
            <button type="submit" name="valider">Valider</button>
            <button type="submit" name="refuser">Refuser</button>
        </div>
    </form>

    <?php
    include('../other/bd.php');

    if (isset($_POST['id_permission'])) {
        $id_permission = $_POST['id_permission'];
        $message = isset($_POST['message']) ? $_POST['message'] : '';

        //Recuperation Matricule_emp
        $req_emp = $bdd->query("SELECT Matricule_emp FROM permission WHERE id_permission = $id_permission");
        $Matricule_emp = $req_emp->fetchColumn();

        if (isset($_POST['valider'])) {
            $statut = 'Validé';
            $type = 'Validée'; //Type de notification (succès)
        } elseif (isset($_POST['refuser'])) {
            $statut = 'Refusé';
            $type = 'Refusée'; //Type de notification (erreur)
        }

        $sql = "UPDATE permission SET Statut_permission = :statut, message = :message WHERE id_permission = :id_permission";
        $stmt = $bdd->prepare($sql);
        $stmt->execute(['statut' => $statut, 'message' => $message, 'id_permission' => $id_permission]);

        if ($stmt->rowCount() > 0) {
            echo "Permission mis à jour";
            //insertion de la notification

            // Insertion de la notification
            $sql_notif = "INSERT INTO notifications (Genre,Matricule_emp, Message, Type) VALUES (:Genre,:Matricule_emp, :message, :type)";
            $stmt_notif = $bdd->prepare($sql_notif);
            $stmt_notif->execute([
                'Genre' => "Permission",
                'Matricule_emp' => $Matricule_emp,
                'message' => "Votre Permission est " . $type . " avec cette remarque: \"" . $message . "\"",
                'type' => $type
            ]);

            if ($stmt_notif->rowCount() > 0) {
                echo "Notification ajoutée";
                require_once('../traitement/sendMail.php');
                $sql_mes = "SELECT p.date_debut_per, p.date_fin_per, n.Type, n.Message, e.mail_emp
                FROM permission p JOIN employer_login e ON e.Matricule_emp = p.Matricule_emp
                JOIN notifications n ON n.Matricule_emp = e.Matricule_emp WHERE p.id_permission = :id_permission AND Genre = 'Permission' ORDER BY date_notif DESC LIMIT 1";
                $stmt_mes = $bdd->prepare($sql_mes);
                $stmt_mes->execute(['id_permission' => $id_permission]);
                // Récupération du message
                $message_data = $stmt_mes->fetch(PDO::FETCH_ASSOC);
                if ($message_data) {
                    $email = $message_data['mail_emp'];
                    $date_debut = $message_data['date_debut_per'];
                    $date_fin = $message_data['date_fin_per'];
                    $type = $message_data['Type'];
                    $message = $message_data['Message'];
                
                    // Préparer le contenu de l'e-mail
                    $subject = "Notification de votre permission";
                    $body = "
                        <p>Bonjour,</p>
                        <p>Votre permission du <strong>$date_debut</strong> au <strong>$date_fin</strong> est <strong>$type</strong>.</p>
                        <p>$message</p>
                    ";
                
                    // Envoi de l’e-mail
                    EnvoiMail($email, $subject, $body);
            
                    header('location:./liste_permission.php'); // Redirection vers la page de liste
                }
            } else {
                echo "Erreur lors de l'ajout de la notification";
            }

            exit();
        } else {
            echo "Erreur lors de la mise à jour du permis9464sion.";
        }
    }

    ?>



</body>

</html>
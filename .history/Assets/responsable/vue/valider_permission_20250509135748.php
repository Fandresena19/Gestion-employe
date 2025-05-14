<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/valider.css">
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
            $type = 'Validé'; //Type de notification (succès)
        } elseif (isset($_POST['refuser'])) {
            $statut = 'Refusé';
            $type = 'Refusé'; //Type de notification (erreur)
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
                'message' => $message,
                'type' => $type
            ]);

            if ($stmt_notif->rowCount() > 0) {
                echo "Notification ajoutée";
            } else {
                echo "Erreur lors de l'ajout de la notification";
            }

            header('location:./liste_absent_perm.php'); // Redirection vers la page de liste
            exit();
        } else {
            echo "Erreur lors de la mise à jour du permis9464sion.";
        }
    }
    ?>



</body>

</html>
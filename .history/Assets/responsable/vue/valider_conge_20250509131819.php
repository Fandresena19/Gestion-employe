<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/valider.css">
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
    include('bd.php');
    $bdd = connect();

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
                'message' => $message,
                'type' => $type
            ]);

            if ($stmt_notif->rowCount() > 0) {
                echo "Notification ajoutée";
            } else {
                echo "Erreur lors de l'ajout de la notification";
                // Ajoutez ici un code de debug pour afficher l'erreur SQL
                $errorInfo = $stmt_notif->errorInfo();
                echo "Erreur SQL: " . $errorInfo[2];
            }

            header('location:./liste_absent_conge.php'); // Redirection vers la page de liste
            exit();
        } else {
            echo "Erreur lors de la mise à jour du congé.";
        }
    }
    ?>



</body>

</html>
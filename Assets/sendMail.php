<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

// Connexion à la base de données
$conn = new mysqli("localhost", "root", "", "gestion_rh");

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connexion échouée: " . $conn->connect_error);
}

// Récupérer l'email du responsable (adapte si plusieurs responsables)
$res = $conn->query("SELECT email FROM users WHERE role = 'responsable' LIMIT 1");
$responsable = $res->fetch_assoc();

if (!$responsable) {
    die("Aucun responsable trouvé.");
}

$email_responsable = $responsable['email'];

// Récupérer les notifications non envoyées (tu peux ajouter une colonne 'envoye' dans la table)
$sql = "SELECT message FROM notification_responsable WHERE envoyer = 0";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $messages = "";
    while ($row = $result->fetch_assoc()) {
        $messages .= "- " . $row['message'] . "<br>";
    }

    // Envoi d'email avec PHPMailer
    $mail = new PHPMailer(true);
    try {
        // Paramètres SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'tonemail@gmail.com'; // Remplace par ton adresse Gmail
        $mail->Password = 'ton_mot_de_passe_application'; // Mot de passe d'application Gmail
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Expéditeur et destinataire
        $mail->setFrom('tonemail@gmail.com', 'Système RH');
        $mail->addAddress($email_responsable);

        // Contenu
        $mail->isHTML(true);
        $mail->Subject = 'Notifications RH';
        $mail->Body = "<h3>Vous avez de nouvelles notifications :</h3><p>$messages</p>";

        $mail->send();

        // Marquer les notifications comme envoyées (optionnel)
        $conn->query("UPDATE notification_responsable SET envoyer = 1");

        echo "Email envoyé avec succès.";
    } catch (Exception $e) {
        echo "Erreur lors de l'envoi : {$mail->ErrorInfo}";
    }
} else {
    echo "Aucune nouvelle notification à envoyer.";
}

$conn->close();
?>

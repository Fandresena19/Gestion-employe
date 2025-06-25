<?php
//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../../PHPMailer/src/Exception.php';
require '../../PHPMailer/src/PHPMailer.php';
require '../../PHPMailer/src/SMTP.php';

function EnvoiMail($destinataire_email, $message) {
    try {
        //Create an instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);
        
        //Server settings
        $mail->SMTPDebug = 0;                      //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = 'fandresenaandrinirina@gmail.com';                     //SMTP username
        $mail->Password   = 'votre_mot_de_passe_app';                               //SMTP password (utilisez un mot de passe d'application)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        //Recipients
        $mail->CharSet = 'UTF-8'; // Définir l'encodage UTF-8
        $mail->setFrom('fandresenaandrinirina@gmail.com', 'MESSAGE APPLICATION DE GESTION EMPLOYE');
        $mail->addAddress($destinataire_email, 'Employé');     //Add a recipient

        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = 'Message de l\'application de gestion des employés';
        $mail->Body    = '<b>'.$message.'</b>';
        $mail->AltBody = strip_tags($message); // Version texte sans HTML

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Erreur d'envoi d'email: {$mail->ErrorInfo}");
        return false;
    }
}
?>
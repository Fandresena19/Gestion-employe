<?php
//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../../PHPMailer/src/Exception.php';
require '../../PHPMailer/src/PHPMailer.php';
require '../../PHPMailer/src/SMTP.php';

//Create an instance; passing `true` enables exceptions
$mail = new PHPMailer(true);

function EnvoiMail($mail, $message) {
    //Server settings
    $mail->SMTPDebug = 0;                      //Enable verbose debug output
    $mail->isSMTP();                                            //Send using SMTP
    $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
    $mail->Username   = 'fandresenaandrinirina@gmail.com';                     //SMTP username
    $mail->Password   = 'ydgsvnkyzkrxquyt';                               //SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
    $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

    //Recipients
    $mail->setFrom('fandresenaandrinirina@gmail.com', 'MESSAGE APPLICATION DE GESTION EMPLOYE');
    $mail->addAddress('fandresenaandry14@gmail.com', 'Responsable');     //Add a recipient

    //Content
    $mail->isHTML(true);                                  //Set email format to HTML
    $mail->CharSet = 'UTF-8'; // Définir l'encodage UTF-8
    $mail->Subject = 'Message de l\'application de gestion des employés';
    $mail->Body    = '<b>'.$message.'</b>';
    $mail->AltBody = $message;

    $mail->send();
    echo 'Message has been sent';
}
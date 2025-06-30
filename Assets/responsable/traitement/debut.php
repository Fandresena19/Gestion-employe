<?php
//Recuperation base de données
include_once('../../other/bd.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


require '../../phpmailer/src/PHPMailer.php';
require '../../phpmailer/src/SMTP.php';
require '../../phpmailer/src/Exception.php';


$date_du_jour = date('Y-m-d');



// Fonction d'envoi d'email 
function envoyerEmail($nom_emp, $date_debut, $type)
{
    $mail = new PHPMailer(true);
    try {
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'fandresenaandrinirina@gmail.com';
        $mail->Password   = 'ydgsvnkyzkrxquyt';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom('fandresenaandrinirina@gmail.com', 'MESSAGE APPLICATION DE GESTION EMPLOYE');
        $mail->addAddress('fandresenaandry14@gmail.com', 'Responsable');

        $mail->isHTML(true);
        $mail->Subject = "$type de $nom_emp commence aujourd'hui";
        $mail->Body = "
            Bonjour,<br><br>
            L'employé <strong>$nom_emp</strong> commence son <strong>$type</strong> aujourd'hui : <strong>$date_debut</strong>.<br><br>
            Cordialement,<br>
            L'application RH
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log l'erreur pour debugging
        error_log("Erreur PHPMailer: " . $e->getMessage());
        return false;
    }
}
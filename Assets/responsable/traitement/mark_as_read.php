<?php
include('../other/bd.php');

if(isset($_GET['id']))
$notification_id =$_GET['id'];

$notification_sql = "UPDATE notifications_responsable SET Statut_notif_resp = 'lu' WHERE id_notification_resp = :id";
$stmt = $bdd->prepare($notification_sql);
$stmt->execute(['id'=> $notification_id]);

header('location:../vue/notification_resp.php');

exit();

//Preparer et executé ma requête SQL

?>
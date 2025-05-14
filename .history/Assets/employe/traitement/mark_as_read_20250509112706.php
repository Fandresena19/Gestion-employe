<?php
include('bd.php');

if(isset($_GET['id']))
$notification_id =$_GET['id'];

$notification_sql = "UPDATE notifications SET Statut_notif = 'lu' WHERE id_notification = :id";
$stmt = $bdd->prepare($notification_sql);
$stmt->execute(['id'=> $notification_id]);

header('location:../vue/notif.php');

exit();

//Preparer et executé ma requête SQL

?>
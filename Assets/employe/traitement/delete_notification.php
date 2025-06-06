<?php
session_start();
include('../other/bd.php');

$user_id = $_SESSION['Matricule_emp'];
$notification_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Vérifier que la notification appartient bien à l'utilisateur
$sql = "SELECT COUNT(*) FROM notifications WHERE id_notification = :id AND Matricule_emp = :user_id";
$stmt = $bdd->prepare($sql);
$stmt->execute([
    'id' => $notification_id,
    'user_id' => $user_id
]);

if ($stmt->fetchColumn() > 0) {
    // Supprimer la notification
    $delete_sql = "DELETE FROM notifications WHERE id_notification = :id";
    $delete_stmt = $bdd->prepare($delete_sql);
    $delete_stmt->execute(['id' => $notification_id]);
    
    // Rediriger vers la page des notifications
    header('location: ../vue/notif.php');
    exit();
} else {
    // La notification n'existe pas ou n'appartient pas à l'utilisateur
    header('location: ../vue/notif.php');
    exit();
}
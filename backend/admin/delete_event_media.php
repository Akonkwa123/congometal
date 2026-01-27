<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkAuth();
if (!isAdmin()) header("Location: login.php");

$media_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;

if ($media_id > 0) {
    $result = $conn->query("SELECT media_path FROM event_media WHERE id = $media_id");
    if ($result && $row = $result->fetch_assoc()) {
        if (!empty($row['media_path'])) {
            deleteImage($row['media_path']);
        }
    }
    $conn->query("DELETE FROM event_media WHERE id = $media_id");
}

if ($event_id > 0) {
    header("Location: add_event.php?id=$event_id");
    exit();
}

header("Location: dashboard.php");
exit();
?>

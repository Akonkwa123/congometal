<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkAuth();
if (!isAdmin()) header("Location: login.php");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    $media_result = $conn->query("SELECT media_path FROM event_media WHERE event_id = $id");
    if ($media_result && $media_result->num_rows > 0) {
        while ($row = $media_result->fetch_assoc()) {
            if (!empty($row['media_path'])) {
                deleteImage($row['media_path']);
            }
        }
    }
    $conn->query("DELETE FROM events WHERE id = $id");
}

header("Location: dashboard.php");
exit();
?>

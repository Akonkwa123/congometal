<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkAuth();
if (!isAdmin()) {
    header("Location: login.php");
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id > 0) {
    $res = $conn->query("SELECT image_path FROM event_posters WHERE id=" . $id);
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        if (!empty($row['image_path'])) {
            deleteImage($row['image_path']);
        }
    }
    $conn->query("DELETE FROM event_posters WHERE id=" . $id);
}

header("Location: dashboard.php#event-posters");
exit();
?>

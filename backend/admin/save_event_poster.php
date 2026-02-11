<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkAuth();
if (!isAdmin()) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['poster_image']) && $_FILES['poster_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $upload_dir = UPLOAD_DIR . 'event_posters';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $upload_result = uploadImage($_FILES['poster_image'], 'event_posters');

        if (!is_array($upload_result)) {
            $title = escape_input($_POST['poster_title'] ?? '');
            $order = (int)($_POST['poster_order'] ?? 0);
            $path = escape_input($upload_result);
            $conn->query("INSERT INTO event_posters (title, image_path, order_position) VALUES ('$title', '$path', $order)");
        }
    }
}

header("Location: dashboard.php#event-posters");
exit();
?>

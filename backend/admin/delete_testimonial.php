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
    $conn->query("DELETE FROM testimonials WHERE id=" . $id);
}

header("Location: dashboard.php#testimonials");
exit();
?>

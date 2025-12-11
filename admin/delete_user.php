<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkAuth();
if (!isAdmin()) header("Location: login.php");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0 && $id !== $_SESSION['user_id']) {
    $sql = "DELETE FROM users WHERE id = $id";
    $conn->query($sql);
}

header("Location: dashboard.php");
exit();
?>

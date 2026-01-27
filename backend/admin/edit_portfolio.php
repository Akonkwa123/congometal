<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkAuth();
if (!isAdmin()) header("Location: login.php");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    $result = $conn->query("SELECT * FROM portfolio WHERE id = $id");
    if ($result && $result->num_rows > 0) {
        $_GET['id'] = $id;
        header("Location: add_portfolio.php?id=$id");
        exit();
    }
}

header("Location: dashboard.php");
exit();
?>

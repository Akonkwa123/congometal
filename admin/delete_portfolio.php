<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkAuth();
if (!isAdmin()) header("Location: login.php");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    // Récupérer l'image pour la supprimer
    $result = $conn->query("SELECT image FROM portfolio WHERE id = $id");
    if ($result && $result->num_rows > 0) {
        $portfolio = $result->fetch_assoc();
        if (!empty($portfolio['image'])) {
            deleteImage($portfolio['image']);
        }
    }

    $sql = "DELETE FROM portfolio WHERE id = $id";
    $conn->query($sql);
}

header("Location: dashboard.php");
exit();
?>

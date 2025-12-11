<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkAuth();
if (!isAdmin()) header("Location: login.php");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    // Récupérer l'image et le média pour les supprimer
    $result = $conn->query("SELECT image, media_path FROM testimonials WHERE id = $id");
    if ($result && $result->num_rows > 0) {
        $testimonial = $result->fetch_assoc();
        if (!empty($testimonial['image'])) {
            deleteImage($testimonial['image']);
        }
        if (!empty($testimonial['media_path'])) {
            deleteImage($testimonial['media_path']);
        }
    }

    $sql = "DELETE FROM testimonials WHERE id = $id";
    $conn->query($sql);
}

header("Location: dashboard.php");
exit();
?>

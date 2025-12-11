<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkAuth();
if (!isAdmin()) header("Location: login.php");


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Gestion du logo
    if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $upload_result = uploadImage($_FILES['company_logo'], 'general');
        if (is_array($upload_result) && isset($upload_result['error'])) {
            // Erreur upload, ignorer ou gérer l'affichage
        } else {
            // Supprimer l'ancienne image si différente
            $old = getSetting('company_logo', '');
            if ($old && $old !== $upload_result) {
                deleteImage($old);
            }
            updateSetting('company_logo', $upload_result);
        }
    }

    // Gestion de l'image À propos
    if (isset($_FILES['about_image']) && $_FILES['about_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $upload_result = uploadImage($_FILES['about_image'], 'about');
        if (is_array($upload_result) && isset($upload_result['error'])) {
            // Erreur upload, ignorer ou gérer l'affichage
        } else {
            // Supprimer l'ancienne image À propos si différente
            $old_about = getSetting('about_image', '');
            if ($old_about && $old_about !== $upload_result) {
                deleteImage($old_about);
            }
            updateSetting('about_image', $upload_result);
        }
    }

    // Gestion de l'image de fond du héro
    if (isset($_FILES['hero_background_image']) && $_FILES['hero_background_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $upload_result = uploadImage($_FILES['hero_background_image'], 'hero');
        if (is_array($upload_result) && isset($upload_result['error'])) {
            // Erreur upload, ignorer ou gérer l'affichage
        } else {
            // Supprimer l'ancienne image de fond du héro si différente
            $old_hero = getSetting('hero_background_image', '');
            if ($old_hero && $old_hero !== $upload_result) {
                deleteImage($old_hero);
            }
            updateSetting('hero_background_image', $upload_result);
        }
    }
    // Enregistrer tous les autres champs
    foreach ($_POST as $key => $value) {
        if ($key !== 'submit') {
            updateSetting($key, $value);
        }
    }
    header("Location: dashboard.php?success=1");
    exit();
}

header("Location: dashboard.php");
exit();
?>

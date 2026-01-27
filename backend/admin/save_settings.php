<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkAuth();
if (!isAdmin()) {
    header("Location: login.php");
    exit();
}

// Activer l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Vérifier si le dossier d'upload existe, sinon le créer
$uploadDirs = [
    UPLOAD_DIR . 'general',
    UPLOAD_DIR . 'about',
    UPLOAD_DIR . 'hero',
    UPLOAD_DIR . 'portfolio'
];

foreach ($uploadDirs as $dir) {
    if (!is_dir($dir)) {
        if (!mkdir($dir, 0777, true)) {
            die("Impossible de créer le répertoire : $dir");
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => ''];
    
    // Vérifier si le formulaire est souis avec enctype="multipart/form-data"
    if (empty($_FILES) && empty($_POST)) {
        $response['message'] = 'Le formulaire doit être soumis avec enctype="multipart/form-data"';
        echo json_encode($response);
        exit();
    }
    // Gestion du logo
    if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] !== UPLOAD_ERR_NO_FILE) {
        error_log("Tentative d'upload du logo");
        error_log("Fichier reçu : " . print_r($_FILES['company_logo'], true));
        
        $upload_result = uploadImage($_FILES['company_logo'], 'general');
        
        if (is_array($upload_result) && isset($upload_result['error'])) {
            $response['message'] = 'Erreur lors de l\'upload du logo : ' . $upload_result['error'];
            error_log("Erreur d'upload du logo : " . $upload_result['error']);
        } else {
            try {
                // Supprimer l'ancienne image si différente
                $old = getSetting('company_logo', '');
                if ($old && $old !== $upload_result) {
                    deleteImage($old);
                }
                updateSetting('company_logo', $upload_result);
                $response['success'] = true;
                $response['message'] = 'Paramètres mis à jour avec succès';
                $response['logo_path'] = UPLOAD_URL . ltrim($upload_result, '/');
            } catch (Exception $e) {
                $response['message'] = 'Erreur lors de la mise à jour du logo : ' . $e->getMessage();
                error_log("Exception lors de la mise à jour du logo : " . $e->getMessage());
            }
        }
    }

    // Gestion de l'image À propos
    if (isset($_FILES['about_image']) && $_FILES['about_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        error_log("Tentative d'upload de l'image À propos");
        error_log("Fichier reçu : " . print_r($_FILES['about_image'], true));
        
        $upload_result = uploadImage($_FILES['about_image'], 'about');
        
        if (is_array($upload_result) && isset($upload_result['error'])) {
            $response['message'] = 'Erreur lors de l\'upload de l\'image À propos : ' . $upload_result['error'];
            error_log("Erreur d'upload de l'image À propos : " . $upload_result['error']);
        } else {
            try {
                // Supprimer l'ancienne image À propos si différente
                $old_about = getSetting('about_image', '');
                if ($old_about && $old_about !== $upload_result) {
                    deleteImage($old_about);
                }
                updateSetting('about_image', $upload_result);
                $response['success'] = true;
                $response['message'] = 'Paramètres mis à jour avec succès';
                $response['about_image_path'] = UPLOAD_URL . ltrim($upload_result, '/');
            } catch (Exception $e) {
                $response['message'] = 'Erreur lors de la mise à jour de l\'image À propos : ' . $e->getMessage();
                error_log("Exception lors de la mise à jour de l'image À propos : " . $e->getMessage());
            }
        }
    }

    // Gestion de l'image de fond du héro
    if (isset($_FILES['hero_background_image']) && $_FILES['hero_background_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        error_log("Tentative d'upload de l'image de fond du héro");
        error_log("Fichier reçu : " . print_r($_FILES['hero_background_image'], true));
        
        $upload_result = uploadImage($_FILES['hero_background_image'], 'hero');
        
        if (is_array($upload_result) && isset($upload_result['error'])) {
            $response['message'] = 'Erreur lors de l\'upload de l\'image de fond du héro : ' . $upload_result['error'];
            error_log("Erreur d'upload de l'image de fond du héro : " . $upload_result['error']);
        } else {
            try {
                // Supprimer l'ancienne image de fond du héro si différente
                $old_hero = getSetting('hero_background_image', '');
                if ($old_hero && $old_hero !== $upload_result) {
                    deleteImage($old_hero);
                }
                updateSetting('hero_background_image', $upload_result);
                $response['success'] = true;
                $response['message'] = 'Paramètres mis à jour avec succès';
                $response['hero_image_path'] = UPLOAD_URL . ltrim($upload_result, '/');
            } catch (Exception $e) {
                $response['message'] = 'Erreur lors de la mise à jour de l\'image de fond du héro : ' . $e->getMessage();
                error_log("Exception lors de la mise à jour de l'image de fond du héro : " . $e->getMessage());
            }
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

<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Démarrer la session et vérifier l'authentification
checkAuth();
if (!isAdmin()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit();
}

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Nettoyer et valider les entrées
        $member_id = isset($_POST['member_id']) ? intval($_POST['member_id']) : 0;
        $name = trim($_POST['name'] ?? '');
        $position = trim($_POST['position'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $phone = trim($_POST['phone'] ?? '');
        $linkedin = trim($_POST['linkedin'] ?? '');
        $twitter = trim($_POST['twitter'] ?? '');
        $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
        $display_order = isset($_POST['display_order']) ? intval($_POST['display_order']) : 0;
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $existing_image = $_POST['existing_image'] ?? '';
        
        // Validation des champs obligatoires
        if (empty($name) || empty($position)) {
            throw new Exception('Le nom et le poste sont obligatoires');
        }
        
        // Gestion du téléchargement de l'image
        $image_path = $existing_image;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['image'];
            $upload_dir = '../admin/uploads/team/';
            
            // Créer le répertoire s'il n'existe pas
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Vérifier le type de fichier
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_info = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($file_info, $file['tmp_name']);
            
            if (!in_array($mime_type, $allowed_types)) {
                throw new Exception('Format de fichier non autorisé. Formats acceptés : JPG, PNG, GIF');
            }
            
            // Générer un nom de fichier unique
            $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid('member_') . '.' . $file_extension;
            $target_path = $upload_dir . $new_filename;
            
            // Déplacer le fichier téléchargé
            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                $image_path = 'admin/uploads/team/' . $new_filename;
                
                // Supprimer l'ancienne image si elle existe
                if (!empty($existing_image) && file_exists('../' . $existing_image)) {
                    unlink('../' . $existing_image);
                }
            } else {
                throw new Exception('Erreur lors du téléchargement de l\'image');
            }
        }
        
        // Préparer la requête SQL
        if ($member_id > 0) {
            // Mise à jour d'un membre existant
            $stmt = $conn->prepare("UPDATE team_members SET 
                name = ?, position = ?, description = ?, email = ?, phone = ?, 
                linkedin = ?, twitter = ?, category_id = ?, display_order = ?, 
                is_active = ?, image_path = ?, updated_at = NOW() 
                WHERE id = ?");
                
            $stmt->bind_param("sssssssiissi", 
                $name, $position, $description, $email, $phone, 
                $linkedin, $twitter, $category_id, $display_order, 
                $is_active, $image_path, $member_id
            );
        } else {
            // Nouveau membre
            $stmt = $conn->prepare("INSERT INTO team_members (
                name, position, description, email, phone, 
                linkedin, twitter, category_id, display_order, 
                is_active, image_path, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            
            $stmt->bind_param("sssssssiiss", 
                $name, $position, $description, $email, $phone, 
                $linkedin, $twitter, $category_id, $display_order, 
                $is_active, $image_path
            );
        }
        
        // Exécuter la requête
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Membre ' . ($member_id > 0 ? 'mis à jour' : 'ajouté') . ' avec succès';
            $response['id'] = $member_id > 0 ? $member_id : $conn->insert_id;
        } else {
            throw new Exception('Erreur lors de l\'enregistrement : ' . $conn->error);
        }
        
    } catch (Exception $e) {
        error_log('Erreur save_member.php : ' . $e->getMessage());
        $response['message'] = $e->getMessage();
    }
} else {
    $response['message'] = 'Méthode non autorisée';
}

// Renvoyer la réponse en JSON
header('Content-Type: application/json');
echo json_encode($response);
?>

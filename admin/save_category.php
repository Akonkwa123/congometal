<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkAuth();
if (!isAdmin()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit();
}

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $display_order = isset($_POST['display_order']) ? intval($_POST['display_order']) : 0;
    
    if (empty($name)) {
        $response['message'] = 'Le nom de la catégorie est obligatoire';
    } else {
        try {
            if ($id > 0) {
                // Mise à jour
                $stmt = $conn->prepare("UPDATE team_categories SET name = ?, description = ?, display_order = ?, updated_at = NOW() WHERE id = ?");
                $stmt->bind_param("ssii", $name, $description, $display_order, $id);
            } else {
                // Nouvelle catégorie
                $stmt = $conn->prepare("INSERT INTO team_categories (name, description, display_order, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
                $stmt->bind_param("ssi", $name, $description, $display_order);
            }
            
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Catégorie enregistrée avec succès';
                $response['id'] = $id > 0 ? $id : $conn->insert_id;
            } else {
                $response['message'] = 'Erreur lors de l\'enregistrement : ' . $conn->error;
            }
        } catch (Exception $e) {
            $response['message'] = 'Erreur : ' . $e->getMessage();
        }
    }
} else {
    $response['message'] = 'Méthode non autorisée';
}

header('Content-Type: application/json');
echo json_encode($response);
?>

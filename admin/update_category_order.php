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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['order'])) {
    $order = json_decode($_POST['order'], true);
    
    if (is_array($order) && !empty($order)) {
        try {
            $conn->begin_transaction();
            
            $stmt = $conn->prepare("UPDATE team_categories SET display_order = ? WHERE id = ?");
            
            foreach ($order as $item) {
                if (isset($item['id']) && isset($item['order'])) {
                    $id = intval($item['id']);
                    $display_order = intval($item['order']);
                    $stmt->bind_param("ii", $display_order, $id);
                    $stmt->execute();
                }
            }
            
            $conn->commit();
            $response['success'] = true;
            $response['message'] = 'Ordre des catégories mis à jour avec succès';
        } catch (Exception $e) {
            $conn->rollback();
            $response['message'] = 'Erreur lors de la mise à jour de l\'ordre : ' . $e->getMessage();
        }
    } else {
        $response['message'] = 'Données d\'ordre invalides';
    }
} else {
    $response['message'] = 'Aucune donnée d\'ordre reçue';
}

header('Content-Type: application/json');
echo json_encode($response);
?>

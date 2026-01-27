<?php
require_once __DIR__ . '/BaseController.php';

class PortfolioController extends BaseController {
    protected $tableName = 'portfolio';

    public function __construct($conn) {
        parent::__construct($conn);
    }

    // Récupérer tous les projets avec filtrage et tri
    public function getAll($filters = []) {
        $query = "SELECT p.*, c.name as category_name 
                 FROM " . $this->tableName . " p
                 LEFT JOIN portfolio_categories c ON p.category_id = c.id
                 WHERE 1=1";
        
        $params = [];
        $types = '';
        
        // Filtrage par catégorie
        if (!empty($filters['category_id'])) {
            $query .= " AND p.category_id = ?";
            $types .= 'i';
            $params[] = $filters['category_id'];
        }
        
        // Filtre de recherche
        if (!empty($filters['search'])) {
            $query .= " AND (p.title LIKE ? OR p.description LIKE ?)";
            $types .= 'ss';
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Filtre par statut
        if (isset($filters['is_active'])) {
            $query .= " AND p.is_active = ?";
            $types .= 'i';
            $params[] = (int)$filters['is_active'];
        }
        
        // Tri
        $orderBy = 'p.created_at DESC';
        if (!empty($filters['sort_by'])) {
            $sortOrder = strtoupper($filters['sort_order'] ?? 'ASC');
            $sortOrder = in_array($sortOrder, ['ASC', 'DESC']) ? $sortOrder : 'ASC';
            
            $allowedSortFields = ['title', 'created_at', 'display_order'];
            if (in_array($filters['sort_by'], $allowedSortFields)) {
                $orderBy = "p.{$filters['sort_by']} $sortOrder";
            }
        }
        $query .= " ORDER BY $orderBy";
        
        // Pagination
        if (isset($filters['limit']) || isset($filters['offset'])) {
            $limit = (int)($filters['limit'] ?? 10);
            $offset = (int)($filters['offset'] ?? 0);
            $query .= " LIMIT ? OFFSET ?";
            $types .= 'ii';
            $params[] = $limit;
            $params[] = $offset;
        }
        
        $stmt = $this->conn->prepare($query);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $projects = [];
        while ($row = $result->fetch_assoc()) {
            // Récupérer les images du projet
            $images = $this->getProjectImages($row['id']);
            $row['images'] = $images;
            $projects[] = $row;
        }
        
        // Compter le nombre total de résultats (pour la pagination)
        $total = $this->countProjects($filters);
        
        return [
            'data' => $projects,
            'total' => $total,
            'limit' => $filters['limit'] ?? null,
            'offset' => $filters['offset'] ?? 0
        ];
    }
    
    // Compter le nombre total de projets avec les mêmes filtres
    private function countProjects($filters) {
        $query = "SELECT COUNT(*) as total FROM " . $this->tableName . " p WHERE 1=1";
        $params = [];
        $types = '';
        
        if (!empty($filters['category_id'])) {
            $query .= " AND p.category_id = ?";
            $types .= 'i';
            $params[] = $filters['category_id'];
        }
        
        if (!empty($filters['search'])) {
            $query .= " AND (p.title LIKE ? OR p.description LIKE ?)";
            $types .= 'ss';
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (isset($filters['is_active'])) {
            $query .= " AND p.is_active = ?";
            $types .= 'i';
            $params[] = (int)$filters['is_active'];
        }
        
        $stmt = $this->conn->prepare($query);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['total'];
    }
    
    // Récupérer les images d'un projet
    private function getProjectImages($projectId) {
        $query = "SELECT * FROM portfolio_images WHERE project_id = ? ORDER BY display_order ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $projectId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $images = [];
        while ($row = $result->fetch_assoc()) {
            $images[] = $row;
        }
        
        return $images;
    }
    
    // Récupérer un projet par son ID
    public function getById($id) {
        $query = "SELECT p.*, c.name as category_name 
                 FROM " . $this->tableName . " p
                 LEFT JOIN portfolio_categories c ON p.category_id = c.id
                 WHERE p.id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $this->sendError('Projet non trouvé', 404);
        }
        
        $project = $result->fetch_assoc();
        
        // Récupérer les images du projet
        $images = $this->getProjectImages($id);
        $project['images'] = $images;
        
        return $project;
    }
    
    // Créer un nouveau projet
    public function create($data) {
        $this->validateToken();
        
        $required = ['title', 'description', 'category_id'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $this->sendError("Le champ $field est requis", 400);
            }
        }
        
        // Démarrer une transaction
        $this->conn->begin_transaction();
        
        try {
            // Insérer le projet
            $query = "INSERT INTO " . $this->tableName . " 
                     (title, description, category_id, client, project_date, project_url, display_order, is_active) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            
            $client = $data['client'] ?? null;
            $projectDate = $data['project_date'] ?? date('Y-m-d');
            $projectUrl = $data['project_url'] ?? null;
            $displayOrder = $data['display_order'] ?? 0;
            $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 1;
            
            $stmt->bind_param("ssisssii", 
                $data['title'],
                $data['description'],
                $data['category_id'],
                $client,
                $projectDate,
                $projectUrl,
                $displayOrder,
                $isActive
            );
            
            if (!$stmt->execute()) {
                throw new Exception('Erreur lors de la création du projet: ' . $stmt->error);
            }
            
            $projectId = $this->conn->insert_id;
            
            // Gérer les images si fournies
            if (!empty($data['images']) && is_array($data['images'])) {
                $this->saveProjectImages($projectId, $data['images']);
            }
            
            // Valider la transaction
            $this->conn->commit();
            
            return [
                'id' => $projectId,
                'message' => 'Projet créé avec succès'
            ];
            
        } catch (Exception $e) {
            // Annuler la transaction en cas d'erreur
            $this->conn->rollback();
            $this->sendError($e->getMessage(), 500);
        }
    }
    
    // Mettre à jour un projet
    public function update($id, $data) {
        $this->validateToken();
        
        // Vérifier si le projet existe
        $project = $this->getById($id);
        
        // Démarrer une transaction
        $this->conn->begin_transaction();
        
        try {
            // Mettre à jour le projet
            $query = "UPDATE " . $this->tableName . " SET ";
            $updates = [];
            $types = '';
            $values = [];
            
            $fields = [
                'title' => 's',
                'description' => 's',
                'category_id' => 'i',
                'client' => 's',
                'project_date' => 's',
                'project_url' => 's',
                'display_order' => 'i',
                'is_active' => 'i'
            ];
            
            foreach ($fields as $field => $type) {
                if (array_key_exists($field, $data)) {
                    $updates[] = "$field = ?";
                    $types .= $type;
                    $values[] = &$data[$field];
                }
            }
            
            if (!empty($updates)) {
                $query .= implode(', ', $updates) . " WHERE id = ?";
                $types .= 'i';
                $values[] = &$id;
                
                $stmt = $this->conn->prepare($query);
                
                // Utiliser call_user_func_array pour lier dynamiquement les paramètres
                $params = array_merge([$types], $values);
                call_user_func_array([$stmt, 'bind_param'], $this->refValues($params));
                
                if (!$stmt->execute()) {
                    throw new Exception('Erreur lors de la mise à jour du projet: ' . $stmt->error);
                }
            }
            
            // Mettre à jour les images si fournies
            if (isset($data['images']) && is_array($data['images'])) {
                // Supprimer les images existantes
                $this->deleteProjectImages($id);
                
                // Ajouter les nouvelles images
                if (!empty($data['images'])) {
                    $this->saveProjectImages($id, $data['images']);
                }
            }
            
            // Valider la transaction
            $this->conn->commit();
            
            return [
                'id' => $id,
                'message' => 'Projet mis à jour avec succès'
            ];
            
        } catch (Exception $e) {
            // Annuler la transaction en cas d'erreur
            $this->conn->rollback();
            $this->sendError($e->getMessage(), 500);
        }
    }
    
    // Supprimer un projet
    public function delete($id) {
        $this->validateToken();
        
        // Vérifier si le projet existe
        $this->getById($id);
        
        // Démarrer une transaction
        $this->conn->begin_transaction();
        
        try {
            // Supprimer les images du projet
            $this->deleteProjectImages($id);
            
            // Supprimer le projet
            $query = "DELETE FROM " . $this->tableName . " WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $id);
            
            if (!$stmt->execute()) {
                throw new Exception('Erreur lors de la suppression du projet: ' . $stmt->error);
            }
            
            // Valider la transaction
            $this->conn->commit();
            
            return ['message' => 'Projet supprimé avec succès'];
            
        } catch (Exception $e) {
            // Annuler la transaction en cas d'erreur
            $this->conn->rollback();
            $this->sendError($e->getMessage(), 500);
        }
    }
    
    // Sauvegarder les images d'un projet
    private function saveProjectImages($projectId, $images) {
        if (empty($images)) {
            return;
        }
        
        $query = "INSERT INTO portfolio_images (project_id, image_url, thumbnail_url, alt_text, display_order) 
                 VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        
        foreach ($images as $index => $image) {
            $imageUrl = $image['image_url'] ?? '';
            $thumbnailUrl = $image['thumbnail_url'] ?? '';
            $altText = $image['alt_text'] ?? '';
            $displayOrder = $image['display_order'] ?? $index;
            
            $stmt->bind_param("isssi", 
                $projectId,
                $imageUrl,
                $thumbnailUrl,
                $altText,
                $displayOrder
            );
            
            if (!$stmt->execute()) {
                throw new Exception('Erreur lors de l\'ajout des images: ' . $stmt->error);
            }
        }
    }
    
    // Supprimer toutes les images d'un projet
    private function deleteProjectImages($projectId) {
        $query = "DELETE FROM portfolio_images WHERE project_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $projectId);
        
        if (!$stmt->execute()) {
            throw new Exception('Erreur lors de la suppression des images: ' . $stmt->error);
        }
    }
}

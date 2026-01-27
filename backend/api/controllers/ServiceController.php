<?php
require_once __DIR__ . '/BaseController.php';

class ServiceController extends BaseController {
    protected $tableName = 'services';

    public function __construct($conn) {
        parent::__construct($conn);
    }

    // Récupérer tous les services
    public function getAll() {
        $query = "SELECT * FROM " . $this->tableName . " WHERE is_active = 1 ORDER BY display_order ASC";
        $result = $this->conn->query($query);
        
        $services = [];
        while ($row = $result->fetch_assoc()) {
            $services[] = $row;
        }
        
        return $services;
    }

    // Récupérer un service par son ID
    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->tableName . " WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $this->sendError('Service non trouvé', 404);
        }
        
        return $result->fetch_assoc();
    }

    // Créer un nouveau service
    public function create($data) {
        $this->validateToken();
        
        $required = ['title', 'description', 'icon'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $this->sendError("Le champ $field est requis", 400);
            }
        }

        $query = "INSERT INTO " . $this->tableName . " (title, description, icon, display_order, is_active) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        
        $display_order = $data['display_order'] ?? 0;
        $is_active = isset($data['is_active']) ? (int)$data['is_active'] : 1;
        
        $stmt->bind_param("sssii", 
            $data['title'], 
            $data['description'], 
            $data['icon'],
            $display_order,
            $is_active
        );
        
        if ($stmt->execute()) {
            return [
                'id' => $stmt->insert_id,
                'title' => $data['title'],
                'message' => 'Service créé avec succès'
            ];
        } else {
            $this->sendError('Erreur lors de la création du service: ' . $stmt->error, 500);
        }
    }

    // Mettre à jour un service
    public function update($id, $data) {
        $this->validateToken();
        
        // Vérifier si le service existe
        $service = $this->getById($id);
        
        $query = "UPDATE " . $this->tableName . " SET ";
        $updates = [];
        $types = '';
        $values = [];
        
        $fields = ['title', 'description', 'icon', 'display_order', 'is_active'];
        
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $types .= is_int($data[$field]) ? 'i' : 's';
                $values[] = &$data[$field];
            }
        }
        
        if (empty($updates)) {
            $this->sendError('Aucune donnée à mettre à jour', 400);
        }
        
        $query .= implode(', ', $updates) . " WHERE id = ?";
        $types .= 'i';
        $values[] = &$id;
        
        $stmt = $this->conn->prepare($query);
        
        // Utiliser call_user_func_array pour lier dynamiquement les paramètres
        $params = array_merge([$types], $values);
        call_user_func_array([$stmt, 'bind_param'], $this->refValues($params));
        
        if ($stmt->execute()) {
            return [
                'id' => $id,
                'message' => 'Service mis à jour avec succès'
            ];
        } else {
            $this->sendError('Erreur lors de la mise à jour du service: ' . $stmt->error, 500);
        }
    }

    // Supprimer un service
    public function delete($id) {
        $this->validateToken();
        
        // Vérifier si le service existe
        $this->getById($id);
        
        $query = "DELETE FROM " . $this->tableName . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            return ['message' => 'Service supprimé avec succès'];
        } else {
            $this->sendError('Erreur lors de la suppression du service: ' . $stmt->error, 500);
        }
    }
    
    // Fonction utilitaire pour lier les références
    private function refValues($arr) {
        $refs = [];
        foreach ($arr as $key => $value) {
            $refs[$key] = &$arr[$key];
        }
        return $refs;
    }
}

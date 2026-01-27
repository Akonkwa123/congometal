<?php
class BaseController {
    protected $conn;
    protected $tableName;
    protected $primaryKey = 'id';

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Méthode pour gérer les réponses d'erreur
    protected function sendError($message, $statusCode = 400) {
        http_response_code($statusCode);
        echo json_encode([
            'status' => 'error',
            'message' => $message
        ]);
        exit();
    }

    // Méthode pour valider le token JWT (à implémenter plus tard)
    protected function validateToken() {
        $headers = getallheaders();
        $token = null;

        if (isset($headers['Authorization'])) {
            $token = str_replace('Bearer ', '', $headers['Authorization']);
        }

        // Ici, vous pouvez ajouter la logique de validation du token
        // Pour l'instant, on retourne true pour les tests
        return true;
    }
}

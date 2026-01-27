<?php
// Configuration de l'API
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

// Gestion des requêtes OPTIONS (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Inclure la configuration de la base de données
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Fonction pour envoyer une réponse JSON
function sendResponse($data = null, $status = 200, $message = '') {
    http_response_code($status);
    echo json_encode([
        'status' => $status < 400 ? 'success' : 'error',
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

// Récupérer la méthode de la requête
$method = $_SERVER['REQUEST_METHOD'];

// Récupérer l'URL de la requête
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request_uri = str_replace('/congometal/backend/api', '', $request_uri);
$uri = explode('/', trim($request_uri, '/'));

// Récupérer les données de la requête
$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    $input = [];
}

// Fusionner les données de la requête avec $_POST
$data = array_merge($_POST, $input);
?>

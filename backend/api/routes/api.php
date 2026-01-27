<?php
// Inclure la configuration de l'API
require_once __DIR__ . '/../config/api_config.php';

// Inclure les contrÃ´leurs
require_once __DIR__ . '/../controllers/ServiceController.php';
require_once __DIR__ . '/../controllers/PortfolioController.php';

// CrÃ©er des instances des contrÃ´leurs
$serviceController = new ServiceController($conn);
$portfolioController = new PortfolioController($conn);

// RÃ©cupÃ©rer les paramÃ¨tres de requÃªte
$queryParams = [];
parse_str($_SERVER['QUERY_STRING'] ?? '', $queryParams);

// DÃ©finir les routes
$endpoint = $uri[0] ?? '';
$id = $uri[1] ?? null;
$action = $uri[2] ?? null;

// Fonction pour gÃ©rer les rÃ©ponses d'erreur
function handleError($message, $statusCode = 400) {
    sendResponse(null, $statusCode, $message);
}

// Router les requÃªtes
switch ($endpoint) {
    // Routes pour les services
    case 'services':
        switch ($method) {
            case 'GET':
                if ($id) {
                    $result = $serviceController->getById($id);
                    sendResponse($result);
                } else {
                    // Appliquer les filtres et le tri
                    $filters = array_intersect_key($queryParams, array_flip([
                        'search', 'is_active', 'sort_by', 'sort_order', 'limit', 'offset'
                    ]));
                    $result = $serviceController->getAll($filters);
                    sendResponse($result);
                }
                break;

            case 'POST':
                $result = $serviceController->create($data);
                sendResponse($result, 201, 'Service crÃ©Ã© avec succÃ¨s');
                break;

            case 'PUT':
                if (!$id) handleError('ID du service requis', 400);
                $result = $serviceController->update($id, $data);
                sendResponse($result, 200, 'Service mis Ã  jour avec succÃ¨s');
                break;

            case 'DELETE':
                if (!$id) handleError('ID du service requis', 400);
                $result = $serviceController->delete($id);
                sendResponse($result, 200, 'Service supprimÃ© avec succÃ¨s');
                break;

            default:
                handleError('MÃ©thode non autorisÃ©e', 405);
        }
        break;

    // Routes pour le portfolio
    case 'portfolio':
        switch ($method) {
            case 'GET':
                if ($id) {
                    // DÃ©tails d'un projet spÃ©cifique
                    $result = $portfolioController->getById($id);
                    sendResponse($result);
                } else {
                    // Liste des projets avec filtres
                    $filters = array_intersect_key($queryParams, array_flip([
                        'category_id', 'search', 'is_active', 'sort_by', 'sort_order', 'limit', 'offset'
                    ]));
                    $result = $portfolioController->getAll($filters);
                    sendResponse($result);
                }
                break;

            case 'POST':
                $result = $portfolioController->create($data);
                sendResponse($result, 201, 'Projet ajoutÃ© avec succÃ¨s');
                break;

            case 'PUT':
                if (!$id) handleError('ID du projet requis', 400);
                $result = $portfolioController->update($id, $data);
                sendResponse($result, 200, 'Projet mis Ã  jour avec succÃ¨s');
                break;

            case 'DELETE':
                if (!$id) handleError('ID du projet requis', 400);
                $result = $portfolioController->delete($id);
                sendResponse($result, 200, 'Projet supprimÃ© avec succÃ¨s');
                break;

            default:
                handleError('MÃ©thode non autorisÃ©e', 405);
        }
        break;

    // Route pour la page d'accueil (donnÃ©es agrÃ©gÃ©es)
    case 'home':
        if ($method !== 'GET') handleError('MÃ©thode non autorisÃ©e', 405);

        // RÃ©cupÃ©rer les services mis en avant
        $services = $serviceController->getAll([
            'is_active' => 1,
            'limit' => 6,
            'sort_by' => 'display_order',
            'sort_order' => 'ASC'
        ]);

        // RÃ©cupÃ©rer les projets rÃ©cents
        $projects = $portfolioController->getAll([
            'is_active' => 1,
            'limit' => 4,
            'sort_by' => 'created_at',
            'sort_order' => 'DESC'
        ]);

        // Retourner les donnÃ©es agrÃ©gÃ©es
        sendResponse([
            'services' => $services['data'],
            'featured_projects' => $projects['data']
        ]);
        break;

    // Route par dÃ©faut (documentation de l'API)
    default:
        $documentation = [
            'endpoints' => [
                'GET /api/services' => 'Liste des services',
                'GET /api/services/{id}' => 'DÃ©tails d\'un service',
                'POST /api/services' => 'CrÃ©er un service',
                'PUT /api/services/{id}' => 'Mettre Ã  jour un service',
                'DELETE /api/services/{id}' => 'Supprimer un service',

                'GET /api/portfolio' => 'Liste des projets',
                'GET /api/portfolio/{id}' => 'DÃ©tails d\'un projet',
                'POST /api/portfolio' => 'CrÃ©er un projet',
                'PUT /api/portfolio/{id}' => 'Mettre Ã  jour un projet',
                'DELETE /api/portfolio/{id}' => 'Supprimer un projet',

                'GET /api/home' => 'DonnÃ©es pour la page d\'accueil'
            ],
            'filters_common' => [
                'sort_by' => 'Champ de tri (dÃ©pend de l\'endpoint)',
                'sort_order' => 'Ordre de tri (ASC ou DESC)',
                'limit' => 'Nombre maximum de rÃ©sultats',
                'offset' => 'DÃ©calage pour la pagination'
            ],
            'filters_services' => [
                'search' => 'Recherche dans le titre et la description',
                'is_active' => 'Filtrer par statut (0 ou 1)'
            ],
            'filters_portfolio' => [
                'category_id' => 'Filtrer par catÃ©gorie',
                'search' => 'Recherche dans le titre et la description',
                'is_active' => 'Filtrer par statut (0 ou 1)'
            ]
        ];

        sendResponse($documentation, 200, 'Bienvenue sur l\'API de Congometal');
}
?>

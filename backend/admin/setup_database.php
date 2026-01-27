<?php
/**
 * Script de configuration de la base de données
 * 
 * Ce script crée la base de données si elle n'existe pas,
 * puis crée les tables nécessaires pour la gestion de l'équipe.
 * 
 * @package Admin
 * @version 1.0.0
 */

// Désactiver l'affichage des erreurs en production
// error_reporting(0);
// ini_set('display_errors', 0);

// Activer l'affichage des erreurs pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

// En-tête pour le type de contenu
header('Content-Type: text/html; charset=utf-8');

// Fonction pour afficher les messages de statut
function show_message($message, $type = 'info') {
    $class = '';
    switch ($type) {
        case 'success':
            $class = 'alert-success';
            break;
        case 'error':
            $class = 'alert-danger';
            break;
        case 'warning':
            $class = 'alert-warning';
            break;
        default:
            $class = 'alert-info';
    }
    echo "<div class='alert $class'>$message</div>\n";
}

// Fonction pour exécuter une requête SQL et afficher le résultat
function execute_query($conn, $sql, $success_message) {
    if ($conn->query($sql) === TRUE) {
        show_message($success_message, 'success');
        return true;
    } else {
        show_message("Erreur lors de l'exécution de la requête : " . $conn->error, 'error');
        return false;
    }
}

// Vérifier si le formulaire a été soumis
$action = $_POST['action'] ?? '';
$db_host = $_POST['db_host'] ?? 'localhost';
$db_user = $_POST['db_user'] ?? 'root';
$db_pass = $_POST['db_pass'] ?? '';
$db_name = $_POST['db_name'] ?? 'congometal';

// Si le formulaire a été soumis, essayer de se connecter à la base de données
if ($action === 'install') {
    try {
        // Établir une connexion sans sélectionner de base de données
        $conn = new mysqli($db_host, $db_user, $db_pass);
        
        // Vérifier la connexion
        if ($conn->connect_error) {
            throw new Exception("Échec de la connexion : " . $conn->connect_error);
        }
        
        // Créer la base de données si elle n'existe pas
        $sql = "CREATE DATABASE IF NOT EXISTS `$db_name` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        if (execute_query($conn, $sql, "Base de données '$db_name' créée avec succès ou déjà existante.")) {
            // Sélectionner la base de données
            $conn->select_db($db_name);
            
            // Créer la table des catégories d'équipe
            $sql = "CREATE TABLE IF NOT EXISTS `team_categories` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(100) NOT NULL,
                `description` text,
                `display_order` int(11) DEFAULT 0,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_display_order` (`display_order`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            if (execute_query($conn, $sql, "Table 'team_categories' créée avec succès.")) {
                // Insérer des catégories par défaut si la table est vide
                $result = $conn->query("SELECT COUNT(*) as count FROM `team_categories");
                $row = $result->fetch_assoc();
                
                if ($row['count'] == 0) {
                    $default_categories = [
                        ['name' => 'Direction', 'description' => 'Membres de la direction', 'order' => 1],
                        ['name' => 'Équipe Commerciale', 'description' => 'Service commercial', 'order' => 2],
                        ['name' => 'Équipe Technique', 'description' => 'Service technique', 'order' => 3],
                        ['name' => 'Support Client', 'description' => 'Service client et support', 'order' => 4]
                    ];
                    
                    $stmt = $conn->prepare("INSERT INTO `team_categories` (`name`, `description`, `display_order`) VALUES (?, ?, ?)");
                    $stmt->bind_param("ssi", $name, $description, $order);
                    
                    $count = 0;
                    foreach ($default_categories as $category) {
                        $name = $category['name'];
                        $description = $category['description'];
                        $order = $category['order'];
                        if ($stmt->execute()) {
                            $count++;
                        }
                    }
                    
                    if ($count > 0) {
                        show_message("$count catégories par défaut ajoutées avec succès.", 'success');
                    }
                    $stmt->close();
                }
                
                // Créer la table des membres d'équipe
                $sql = "CREATE TABLE IF NOT EXISTS `team_members` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `name` varchar(100) NOT NULL,
                    `position` varchar(100) NOT NULL,
                    `description` text,
                    `image_path` varchar(255) DEFAULT NULL,
                    `category_id` int(11) DEFAULT NULL,
                    `email` varchar(100) DEFAULT NULL,
                    `phone` varchar(20) DEFAULT NULL,
                    `linkedin` varchar(255) DEFAULT NULL,
                    `twitter` varchar(255) DEFAULT NULL,
                    `display_order` int(11) DEFAULT 0,
                    `is_active` tinyint(1) DEFAULT 1,
                    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    KEY `idx_category` (`category_id`),
                    KEY `idx_display_order` (`display_order`),
                    KEY `idx_is_active` (`is_active`),
                    CONSTRAINT `fk_member_category` FOREIGN KEY (`category_id`) REFERENCES `team_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
                
                if (execute_query($conn, $sql, "Table 'team_members' créée avec succès.")) {
                    // Créer le dossier d'upload s'il n'existe pas
                    $upload_dir = __DIR__ . '/uploads/team/';
                    if (!file_exists($upload_dir)) {
                        if (mkdir($upload_dir, 0755, true)) {
                            show_message("Dossier d'upload créé avec succès : $upload_dir", 'success');
                            // Créer un fichier .htaccess pour sécuriser le dossier
                            file_put_contents($upload_dir . '.htaccess', "Order deny,allow\nDeny from all");
                            file_put_contents($upload_dir . 'index.html', ''); // Fichier vide pour lister le dossier
                        } else {
                            show_message("Attention : Impossible de créer le dossier d'upload : $upload_dir. Veuillez le créer manuellement et définir les permissions à 755.", 'warning');
                        }
                    }
                    
                    // Mettre à jour le fichier de configuration
                    $config_file = __DIR__ . '/../includes/config.php';
                    if (file_exists($config_file)) {
                        $config_content = file_get_contents($config_file);
                        $config_content = preg_replace(
                            "/define\('DB_HOST',\s*'.*?'\)/i",
                            "define('DB_HOST', '" . addslashes($db_host) . "')",
                            $config_content
                        );
                        $config_content = preg_replace(
                            "/define\('DB_USER',\s*'.*?'\)/i",
                            "define('DB_USER', '" . addslashes($db_user) . "')",
                            $config_content
                        );
                        $config_content = preg_replace(
                            "/define\('DB_PASSWORD',\s*'.*?'\)/i",
                            "define('DB_PASSWORD', '" . addslashes($db_pass) . "')",
                            $config_content
                        );
                        $config_content = preg_replace(
                            "/define\('DB_NAME',\s*'.*?'\)/i",
                            "define('DB_NAME', '" . addslashes($db_name) . "')",
                            $config_content
                        );
                        
                        if (file_put_contents($config_file, $config_content) !== false) {
                            show_message("Fichier de configuration mis à jour avec succès.", 'success');
                        } else {
                            show_message("Attention : Impossible de mettre à jour le fichier de configuration. Veuillez le faire manuellement.", 'warning');
                        }
                    }
                    
                    show_message("<strong>Installation terminée avec succès !</strong>", 'success');
                    show_message("<a href='manage_team.php' class='btn btn-primary'>Accéder à la gestion de l'équipe</a>");
                }
            }
        }
        
        $conn->close();
        
    } catch (Exception $e) {
        show_message("Erreur : " . $e->getMessage(), 'error');
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - Gestion d'équipe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 800px;
            margin-top: 50px;
        }
        .alert {
            margin-top: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h1 class="h4 mb-0">Installation - Gestion d'équipe</h1>
            </div>
            <div class="card-body">
                <?php if (!isset($action) || $action !== 'install'): ?>
                    <p>Ce script va configurer la base de données nécessaire pour la gestion de l'équipe.</p>
                    <p>Veuillez vérifier que vous avez les droits d'administration sur la base de données.</p>
                    
                    <form method="post" action="">
                        <input type="hidden" name="action" value="install">
                        
                        <div class="form-group">
                            <label for="db_host">Serveur de base de données :</label>
                            <input type="text" class="form-control" id="db_host" name="db_host" value="<?php echo htmlspecialchars($db_host); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="db_name">Nom de la base de données :</label>
                            <input type="text" class="form-control" id="db_name" name="db_name" value="<?php echo htmlspecialchars($db_name); ?>" required>
                            <small class="form-text text-muted">La base de données sera créée si elle n'existe pas.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="db_user">Nom d'utilisateur MySQL :</label>
                            <input type="text" class="form-control" id="db_user" name="db_user" value="<?php echo htmlspecialchars($db_user); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="db_pass">Mot de passe MySQL :</label>
                            <input type="password" class="form-control" id="db_pass" name="db_pass" value="<?php echo htmlspecialchars($db_pass); ?>">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Installer</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

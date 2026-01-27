<?php
// Désactiver l'affichage des erreurs en production
// error_reporting(0);
// ini_set('display_errors', 0);

// Activer l'affichage des erreurs pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

// En-tête pour le type de contenu
header('Content-Type: text/html; charset=utf-8');

echo "<h2>Vérification de la base de données</h2>";

// Charger la configuration
try {
    require_once __DIR__ . '/../includes/config.php';
    echo "<p>✅ Fichier de configuration chargé avec succès.</p>";
} catch (Exception $e) {
    die("<p style='color:red;'>❌ Erreur lors du chargement du fichier de configuration : " . $e->getMessage() . "</p>");
}

// Vérifier la connexion à la base de données
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Échec de la connexion : " . $conn->connect_error);
    }
    
    echo "<p>✅ Connexion à la base de données réussie.</p>";
    
    // Vérifier les tables
    $tables = ['team_categories', 'team_members'];
    $missing_tables = [];
    
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            echo "<p>✅ Table '$table' existe.</p>";
            
            // Afficher la structure de la table
            echo "<details>";
            echo "<summary>Structure de la table '$table'</summary>";
            $structure = $conn->query("DESCRIBE `$table`");
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>Champ</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th><th>Extra</th></tr>";
            while ($row = $structure->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "</details>";
            
            // Afficher les données de la table
            $data = $conn->query("SELECT * FROM `$table` LIMIT 5");
            if ($data->num_rows > 0) {
                echo "<p>Données (premiers enregistrements) :</p>";
                echo "<table border='1' cellpadding='5'>";
                // En-têtes
                echo "<tr>";
                $fields = $data->fetch_fields();
                foreach ($fields as $field) {
                    echo "<th>" . htmlspecialchars($field->name) . "</th>";
                }
                echo "</tr>";
                // Données
                $data->data_seek(0);
                while ($row = $data->fetch_assoc()) {
                    echo "<tr>";
                    foreach ($row as $value) {
                        echo "<td>" . htmlspecialchars(substr($value ?? '', 0, 50)) . "</td>";
                    }
                    echo "</tr>";
                }
                echo "</table>";
                
                if ($data->num_rows >= 5) {
                    echo "<p>... et " . ($data->num_rows - 5) . " autres enregistrements.</p>";
                }
            } else {
                echo "<p>⚠️ La table '$table' est vide.</p>";
            }
            
        } else {
            echo "<p style='color:orange;'>⚠️ Table '$table' n'existe pas.</p>";
            $missing_tables[] = $table;
        }
    }
    
    if (!empty($missing_tables)) {
        echo "<div style='background:#fff3cd; padding:10px; margin:10px 0; border:1px solid #ffeeba;'>";
        echo "<h3>Tables manquantes détectées</h3>";
        echo "<p>Les tables suivantes sont manquantes : " . implode(', ', $missing_tables) . "</p>";
        echo "<p>Pour créer les tables manquantes, veuillez :</p>";
        echo "<ol>";
        echo "<li>Ouvrir phpMyAdmin</li>";
        echo "<li>Sélectionner la base de données '" . DB_NAME . "'</li>";
        echo "<li>Onglet 'SQL'</li>";
        echo "<li>Copier-coller le code SQL suivant :</li>";
        echo "</ol>";
        echo "<pre style='background:#f8f9fa; padding:10px; border:1px solid #ddd;'>";
        
        // Afficher le SQL pour créer les tables manquantes
        if (in_array('team_categories', $missing_tables)) {
            echo "-- Création de la table team_categories\n";
            echo "CREATE TABLE IF NOT EXISTS `team_categories` (\n";
            echo "  `id` int(11) NOT NULL AUTO_INCREMENT,\n";
            echo "  `name` varchar(100) NOT NULL,\n";
            echo "  `description` text DEFAULT NULL,\n";
            echo "  `display_order` int(11) DEFAULT 0,\n";
            echo "  `is_active` tinyint(1) DEFAULT 1,\n";
            echo "  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),\n";
            echo "  PRIMARY KEY (`id`),\n";
            echo "  KEY `idx_display_order` (`display_order`),\n";
            echo "  KEY `idx_is_active` (`is_active`)\n";
            echo ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";
            
            // Insérer des catégories par défaut
            echo "-- Catégories par défaut\n";
            echo "INSERT INTO `team_categories` (`name`, `description`, `display_order`) VALUES\n";
            echo "('Direction', 'Membres de la direction', 1),\n";
            echo "('Équipe Commerciale', 'Service commercial', 2),\n";
            echo "('Équipe Technique', 'Service technique', 3),\n";
            echo "('Support Client', 'Service client et support', 4);\n\n";
        }
        
        if (in_array('team_members', $missing_tables)) {
            echo "-- Création de la table team_members\n";
            echo "CREATE TABLE IF NOT EXISTS `team_members` (\n";
            echo "  `id` int(11) NOT NULL AUTO_INCREMENT,\n";
            echo "  `name` varchar(100) NOT NULL,\n";
            echo "  `position` varchar(100) NOT NULL,\n";
            echo "  `description` text DEFAULT NULL,\n";
            echo "  `image_path` varchar(255) DEFAULT NULL,\n";
            echo "  `category_id` int(11) DEFAULT NULL,\n";
            echo "  `email` varchar(100) DEFAULT NULL,\n";
            echo "  `phone` varchar(20) DEFAULT NULL,\n";
            echo "  `linkedin` varchar(255) DEFAULT NULL,\n";
            echo "  `twitter` varchar(255) DEFAULT NULL,\n";
            echo "  `display_order` int(11) DEFAULT 0,\n";
            echo "  `is_active` tinyint(1) DEFAULT 1,\n";
            echo "  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),\n";
            echo "  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),\n";
            echo "  PRIMARY KEY (`id`),\n";
            echo "  KEY `idx_category` (`category_id`),\n";
            echo "  KEY `idx_display_order` (`display_order`),\n";
            echo "  KEY `idx_is_active` (`is_active`),\n";
            echo "  CONSTRAINT `fk_member_category` FOREIGN KEY (`category_id`) REFERENCES `team_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE\n";
            echo ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";
        }
        
        echo "</pre>";
        echo "</div>";
    }
    
    // Vérifier les clés étrangères
    $fk_check = $conn->query("
        SELECT 
            TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
        FROM 
            INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
        WHERE 
            REFERENCED_TABLE_SCHEMA = '" . DB_NAME . "' AND
            REFERENCED_TABLE_NAME IS NOT NULL
    ");
    
    if ($fk_check->num_rows > 0) {
        echo "<h3>Vérification des clés étrangères</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Table</th><th>Colonne</th><th>Contrainte</th><th>Table référencée</th><th>Colonne référencée</th></tr>";
        while ($fk = $fk_check->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($fk['TABLE_NAME']) . "</td>";
            echo "<td>" . htmlspecialchars($fk['COLUMN_NAME']) . "</td>";
            echo "<td>" . htmlspecialchars($fk['CONSTRAINT_NAME']) . "</td>";
            echo "<td>" . htmlspecialchars($fk['REFERENCED_TABLE_NAME']) . "</td>";
            echo "<td>" . htmlspecialchars($fk['REFERENCED_COLUMN_NAME']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Erreur : " . $e->getMessage() . "</p>";
    
    // Afficher les informations de connexion (pour le débogage uniquement)
    echo "<div style='background:#f8d7da; padding:10px; margin:10px 0; border:1px solid #f5c6cb;'>";
    echo "<h3>Informations de débogage</h3>";
    echo "<p><strong>Hôte :</strong> " . htmlspecialchars(DB_HOST) . "</p>";
    echo "<p><strong>Utilisateur :</strong> " . htmlspecialchars(DB_USER) . "</p>";
    echo "<p><strong>Base de données :</strong> " . htmlspecialchars(DB_NAME) . "</p>";
    echo "</div>";
}

// Vérifier si le dossier d'upload existe
$upload_dir = __DIR__ . '/uploads/team/';
if (!file_exists($upload_dir)) {
    echo "<div style='background:#fff3cd; padding:10px; margin:10px 0; border:1px solid #ffeeba;'>";
    echo "<h3>Dossier d'upload manquant</h3>";
    echo "<p>Le dossier d'upload n'existe pas : <code>" . htmlspecialchars($upload_dir) . "</code></p>";
    echo "<p>Créez le dossier avec les permissions appropriées :</p>";
    echo "<pre>mkdir -p " . htmlspecialchars($upload_dir) . "
chmod 755 " . htmlspecialchars($upload_dir) . "
chown www-data:www-data " . htmlspecialchars($upload_dir) . "</pre>";
    echo "</div>";
} else {
    echo "<p>✅ Le dossier d'upload existe : <code>" . htmlspecialchars($upload_dir) . "</code></p>";
    
    // Vérifier les permissions
    if (!is_writable($upload_dir)) {
        echo "<p style='color:orange;'>⚠️ Le dossier d'upload n'est pas accessible en écriture. Exécutez : <code>chmod 755 " . htmlspecialchars($upload_dir) . "</code></p>";
    } else {
        echo "<p>✅ Le dossier d'upload est accessible en écriture.</p>";
    }
}

// Vérifier les extensions PHP nécessaires
$required_extensions = ['pdo_mysql', 'gd', 'fileinfo'];
$missing_extensions = [];

foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $missing_extensions[] = $ext;
    }
}

if (!empty($missing_extensions)) {
    echo "<div style='background:#f8d7da; padding:10px; margin:10px 0; border:1px solid #f5c6cb;'>";
    echo "<h3>Extensions PHP manquantes</h3>";
    echo "<p>Les extensions suivantes sont requises mais ne sont pas chargées : <strong>" . implode(', ', $missing_extensions) . "</strong></p>";
    echo "<p>Activez ces extensions dans votre fichier php.ini et redémarrez votre serveur web.</p>";
    echo "</div>";
} else {
    echo "<p>✅ Toutes les extensions PHP requises sont chargées.</p>";
}

// Vérifier la version de PHP
if (version_compare(PHP_VERSION, '7.4.0') < 0) {
    echo "<div style='background:#f8d7da; padding:10px; margin:10px 0; border:1px solid #f5c6cb;'>";
    echo "<h3>Version de PHP obsolète</h3>";
    echo "<p>Votre version de PHP est " . PHP_VERSION . ". Il est recommandé d'utiliser PHP 7.4 ou supérieur pour des raisons de performances et de sécurité.</p>";
    echo "</div>";
} else {
    echo "<p>✅ Version de PHP : " . PHP_VERSION . " (recommandée : 7.4+)";
    if (version_compare(PHP_VERSION, '8.0.0') >= 0) {
        echo " 🚀";
    }
    echo "</p>";
}

// Lien pour retourner à l'administration
echo "<p style='margin-top: 20px;'><a href='manage_team.php' class='btn btn-primary'>Retour à la gestion de l'équipe</a></p>";

// Supprimer ce fichier après utilisation (sécurité)
// unlink(__FILE__);
?>

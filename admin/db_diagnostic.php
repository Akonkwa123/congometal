<?php
// Activer l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Inclure la configuration
try {
    require_once __DIR__ . '/../includes/config.php';
    
    // Vérifier la connexion à la base de données
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    
    if ($conn->connect_error) {
        die("Erreur de connexion à la base de données : " . $conn->connect_error);
    }
    
    echo "<h2>Vérification de la base de données</h2>";
    
    // Vérifier les tables
    $tables = ['team_categories', 'team_members'];
    
    foreach ($tables as $table) {
        echo "<h3>Table : $table</h3>";
        
        // Vérifier si la table existe
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        
        if ($result->num_rows === 0) {
            echo "<p style='color: red;'>La table '$table' n'existe pas.</p>";
            continue;
        }
        
        echo "<p>✅ La table existe.</p>";
        
        // Afficher la structure de la table
        echo "<h4>Structure :</h4>";
        $structure = $conn->query("DESCRIBE `$table`");
        
        if ($structure) {
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>Champ</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th><th>Extra</th></tr>";
            
            while ($row = $structure->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
                echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        } else {
            echo "<p>Impossible de récupérer la structure de la table : " . $conn->error . "</p>";
        }
        
        // Afficher les données de la table
        echo "<h4>Données :</h4>";
        $data = $conn->query("SELECT * FROM `$table`");
        
        if ($data) {
            if ($data->num_rows > 0) {
                echo "<p>Nombre d'enregistrements : " . $data->num_rows . "</p>";
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
            } else {
                echo "<p>Aucune donnée dans la table.</p>";
            }
        } else {
            echo "<p>Erreur lors de la récupération des données : " . $conn->error . "</p>";
        }
        
        echo "<hr>";
    }
    
    // Tester la requête des membres avec leurs catégories
    echo "<h2>Test de la requête des membres avec leurs catégories</h2>";
    $query = "
        SELECT m.*, c.name as category_name 
        FROM team_members m
        LEFT JOIN team_categories c ON m.category_id = c.id
        ORDER BY c.display_order, m.display_order, m.name
    ";
    
    $result = $conn->query($query);
    
    if ($result) {
        if ($result->num_rows > 0) {
            echo "<p>✅ La requête a retourné " . $result->num_rows . " membres.</p>";
            
            // Afficher les premiers membres
            echo "<h4>Premiers membres :</h4>";
            echo "<table border='1' cellpadding='5'>";
            
            // En-têtes
            echo "<tr>";
            $fields = $result->fetch_fields();
            foreach ($fields as $field) {
                echo "<th>" . htmlspecialchars($field->name) . "</th>";
            }
            echo "</tr>";
            
            // Données (limitées aux 5 premiers)
            $result->data_seek(0);
            $count = 0;
            while ($row = $result->fetch_assoc() && $count < 5) {
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>" . htmlspecialchars(substr($value ?? '', 0, 30)) . "</td>";
                }
                echo "</tr>";
                $count++;
            }
            
            echo "</table>";
        } else {
            echo "<p>⚠️ La requête n'a retourné aucun résultat.</p>";
            
            // Vérifier si les tables sont vides
            $check_cats = $conn->query("SELECT COUNT(*) as count FROM team_categories");
            $check_members = $conn->query("SELECT COUNT(*) as count FROM team_members");
            
            if ($check_cats && $check_members) {
                $cats_count = $check_cats->fetch_assoc()['count'];
                $members_count = $check_members->fetch_assoc()['count'];
                
                echo "<p>Nombre de catégories : $cats_count</p>";
                echo "<p>Nombre de membres : $members_count</p>";
                
                if ($cats_count == 0) {
                    echo "<p style='color: orange;'>⚠️ Il n'y a aucune catégorie dans la base de données.</p>";
                    echo "<p>Exécutez cette requête pour ajouter des catégories par défaut :</p>";
                    echo "<pre>
                        INSERT INTO team_categories (name, description, display_order, is_active) VALUES 
                        ('Direction', 'Membres de la direction', 1, 1),
                        ('Équipe Commerciale', 'Service commercial', 2, 1),
                        ('Équipe Technique', 'Service technique', 3, 1),
                        ('Support Client', 'Service client et support', 4, 1);
                    </pre>";
                }
                
                if ($members_count == 0) {
                    echo "<p style='color: orange;'>⚠️ Il n'y a aucun membre dans la base de données.</p>";
                    echo "<p>Ajoutez des membres via l'interface d'administration.</p>";
                }
            }
        }
    } else {
        echo "<p style='color: red;'>❌ Erreur lors de l'exécution de la requête : " . $conn->error . "</p>";
        
        // Vérifier si les tables existent
        $tables = $conn->query("SHOW TABLES LIKE 'team_%'");
        if ($tables->num_rows === 0) {
            echo "<p style='color: red;'>❌ Aucune table d'équipe n'existe dans la base de données.</p>";
            echo "<p>Exécutez le script de création des tables : <a href='create_team_table.php'>create_team_table.php</a></p>";
        }
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<h2>Erreur</h2>";
    echo "<p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>";
    
    // Afficher les informations de connexion (pour le débogage)
    echo "<h3>Configuration actuelle :</h3>";
    echo "<pre>";
    echo "DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'non défini') . "\n";
    echo "DB_USER: " . (defined('DB_USER') ? DB_USER : 'non défini') . "\n";
    echo "DB_NAME: " . (defined('DB_NAME') ? DB_NAME : 'non défini') . "\n";
    echo "</pre>";
}
?>

<h2>Instructions</h2>
<ol>
    <li>Vérifiez que les tables existent et ont la bonne structure.</li>
    <li>Si les tables n'existent pas, exécutez <a href='create_team_table.php'>create_team_table.php</a> pour les créer.</li>
    <li>Si les tables sont vides, ajoutez des données de test.</li>
    <li>Vérifiez les erreurs PHP dans les logs du serveur.</li>
    <li>Assurez-vous que le dossier d'upload existe et a les bonnes permissions.</li>
</ol>

<p><a href='manage_team.php'>Retour à la gestion de l'équipe</a></p>

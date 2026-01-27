<?php
require_once '../includes/config.php';

// Vérifier les tables
echo "<h2>Vérification des tables</h2>";
$tables = $conn->query("SHOW TABLES");
if ($tables) {
    echo "<h3>Tables trouvées :</h3>";
    while ($row = $tables->fetch_array()) {
        echo "<p>" . $row[0] . "</p>";
        
        // Afficher les colonnes de chaque table
        $columns = $conn->query("SHOW COLUMNS FROM " . $row[0]);
        echo "<ul>";
        while ($col = $columns->fetch_assoc()) {
            echo "<li>" . $col['Field'] . " - " . $col['Type'] . "</li>";
        }
        echo "</ul>";
        
        // Compter les enregistrements
        $count = $conn->query("SELECT COUNT(*) as count FROM " . $row[0])->fetch_assoc();
        echo "<p>Nombre d'enregistrements : " . $count['count'] . "</p>";
    }
} else {
    echo "<p>Erreur lors de la récupération des tables : " . $conn->error . "</p>";
}

// Tester la requête des membres
$test_query = "
    SELECT m.*, c.name as category_name 
    FROM team_members m
    LEFT JOIN team_categories c ON m.category_id = c.id
    ORDER BY c.display_order, m.display_order, m.name
    LIMIT 5";

$result = $conn->query($test_query);

if ($result) {
    echo "<h3>Résultats de la requête test (5 premiers membres) :</h3>";
    echo "<pre>";
    while ($row = $result->fetch_assoc()) {
        print_r($row);
    }
    echo "</pre>";
} else {
    echo "<p>Erreur lors de la requête test : " . $conn->error . "</p>";
}

echo "<h3>Erreurs PHP :</h3>";
print_r(error_get_last());
?>

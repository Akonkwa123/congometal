<?php
require_once '../includes/config.php';

// Désactiver temporairement les contraintes de clé étrangère
$conn->query("SET FOREIGN_KEY_CHECKS=0");

// Supprimer les tables si elles existent déjà
$conn->query("DROP TABLE IF EXISTS team_members");
$conn->query("DROP TABLE IF EXISTS team_categories");

// Création de la table des catégories d'équipe
$sql_categories = "CREATE TABLE IF NOT EXISTS team_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// Création de la table des membres d'équipe
$sql_team_members = "CREATE TABLE IF NOT EXISTS team_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    position VARCHAR(100) NOT NULL,
    description TEXT,
    image_path VARCHAR(255),
    category_id INT,
    email VARCHAR(100),
    phone VARCHAR(20),
    linkedin VARCHAR(255),
    twitter VARCHAR(255),
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES team_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// Insertion des catégories par défaut
$default_categories = [
    ['Direction', 'L\'équipe de direction', 1],
    ['Technique', 'L\'équipe technique', 2],
    ['Commercial', 'L\'équipe commerciale', 3],
    ['Support', 'L\'équipe support', 4]
];

try {
    // Exécution des requêtes
    if (!$conn->query($sql_categories)) {
        throw new Exception("Erreur lors de la création de la table team_categories: " . $conn->error);
    }
    
    if (!$conn->query($sql_team_members)) {
        throw new Exception("Erreur lors de la création de la table team_members: " . $conn->error);
    }
    
    // Insertion des catégories par défaut
    $stmt = $conn->prepare("INSERT INTO team_categories (name, description, display_order) VALUES (?, ?, ?)");
    if (!$stmt) {
        throw new Exception("Erreur de préparation: " . $conn->error);
    }
    
    foreach ($default_categories as $category) {
        $stmt->bind_param("ssi", $category[0], $category[1], $category[2]);
        if (!$stmt->execute()) {
            throw new Exception("Erreur lors de l'insertion des catégories: " . $stmt->error);
        }
    }
    
    // Réactiver les contraintes de clé étrangère
    $conn->query("SET FOREIGN_KEY_CHECKS=1");
    
    echo "<div style='font-family: Arial, sans-serif; padding: 20px;'>";
    echo "<h2 style='color: #4CAF50;'>Installation réussie !</h2>";
    echo "<p>Les tables ont été créées avec succès.</p>";
    echo "<p>Catégories par défaut ajoutées :</p>";
    echo "<ul>";
    foreach ($default_categories as $cat) {
        echo "<li><strong>{$cat[0]}</strong> - {$cat[1]}</li>";
    }
    echo "</ul>";
    echo "<p><a href='manage_team.php' style='display: inline-block; background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin-top: 20px;'>Commencer à gérer l'équipe</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    // Réactiver les contraintes en cas d'erreur
    $conn->query("SET FOREIGN_KEY_CHECKS=1");
    die("<div style='font-family: Arial, sans-serif; padding: 20px; color: #f44336;'><h2>Erreur lors de l'installation</h2><p>" . $e->getMessage() . "</p></div>");
}
?>

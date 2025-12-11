<?php
require_once '../includes/config.php';

// Vérifier si les tables existent
$tables = $conn->query("SHOW TABLES LIKE 'team_%'");
if ($tables->num_rows === 0) {
    die("Les tables 'team_members' et 'team_categories' n'existent pas. Veuillez exécuter le script d'installation.");
}

// Vérifier les catégories
echo "<h2>Catégories existantes :</h2>";
$categories = $conn->query("SELECT * FROM team_categories");
if ($categories->num_rows > 0) {
    echo "<ul>";
    while ($cat = $categories->fetch_assoc()) {
        echo "<li>" . htmlspecialchars($cat['name']) . " (ID: " . $cat['id'] . ")</li>";
    }
    echo "</ul>";
} else {
    echo "<p>Aucune catégorie trouvée. Ajout de catégories par défaut...</p>";
    
    // Ajouter des catégories par défaut
    $default_categories = [
        ['Direction', 'L\'équipe de direction', 1],
        ['Technique', 'L\'équipe technique', 2],
        ['Commercial', 'L\'équipe commerciale', 3],
        ['Support', 'L\'équipe support', 4]
    ];
    
    $stmt = $conn->prepare("INSERT INTO team_categories (name, description, display_order) VALUES (?, ?, ?)");
    
    foreach ($default_categories as $category) {
        $stmt->bind_param("ssi", $category[0], $category[1], $category[2]);
        if ($stmt->execute()) {
            echo "<p>Ajout de la catégorie : " . htmlspecialchars($category[0]) . "</p>";
        } else {
            echo "<p>Erreur lors de l'ajout de la catégorie " . htmlspecialchars($category[0]) . ": " . $conn->error . "</p>";
        }
    }
}

// Vérifier les membres
echo "<h2>Membres de l'équipe :</h2>";
$members = $conn->query("SELECT * FROM team_members");
if ($members->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Nom</th><th>Poste</th><th>Email</th><th>Statut</th></tr>";
    while ($member = $members->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $member['id'] . "</td>";
        echo "<td>" . htmlspecialchars($member['name']) . "</td>";
        echo "<td>" . htmlspecialchars($member['position']) . "</td>";
        echo "<td>" . htmlspecialchars($member['email']) . "</td>";
        echo "<td>" . ($member['is_active'] ? 'Actif' : 'Inactif') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>Aucun membre trouvé dans la base de données.</p>";
    echo "<p><a href='?add'>Ajouter un nouveau membre</a></p>";
}

// Afficher les erreurs PHP
ini_set('display_errors', 1);
error_reporting(E_ALL);
?>

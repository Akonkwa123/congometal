<?php
require_once 'config.php';

// Création de la BDD si pas existante
$conn_temp = new mysqli(DB_HOST, DB_USER, DB_PASSWORD);
$conn_temp->query("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
$conn_temp->close();

// Connexion à la base finale
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$conn->set_charset("utf8");

// Table users
$sql_users = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql_users) or die("ERREUR USERS : " . $conn->error);

// Table pages
$sql_pages = "CREATE TABLE IF NOT EXISTS pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content LONGTEXT,
    meta_description VARCHAR(255),
    meta_keywords VARCHAR(255),
    status ENUM('published', 'draft') DEFAULT 'published',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
$conn->query($sql_pages) or die("ERREUR PAGES : " . $conn->error);

// Table services
$sql_services = "CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    icon VARCHAR(50),
    order_position INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
$conn->query($sql_services) or die("ERREUR SERVICES : " . $conn->error);

// Table portfolio
$sql_portfolio = "CREATE TABLE IF NOT EXISTS portfolio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    category VARCHAR(100),
    client VARCHAR(255),
    project_url VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX(status),
    INDEX(category)
)";
$conn->query($sql_portfolio) or die("ERREUR PORTFOLIO : " . $conn->error);

// Table events
$sql_events = "CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    event_date DATE,
    location VARCHAR(255),
    start_time TIME,
    end_time TIME,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX(status),
    INDEX(event_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
$conn->query($sql_events) or die("ERREUR EVENTS : " . $conn->error);

// Table event_media
$sql_event_media = "CREATE TABLE IF NOT EXISTS event_media (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    media_type ENUM('image','video','audio') NOT NULL,
    media_path VARCHAR(255) NOT NULL,
    media_title VARCHAR(255) DEFAULT NULL,
    media_caption TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX(event_id),
    CONSTRAINT fk_event_media_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
$conn->query($sql_event_media) or die("ERREUR EVENT_MEDIA : " . $conn->error);

// Table contacts
$sql_contacts = "CREATE TABLE IF NOT EXISTS contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(255),
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql_contacts) or die("ERREUR CONTACTS : " . $conn->error);

// Table settings
$sql_settings = "CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(255) UNIQUE NOT NULL,
    setting_value LONGTEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
$conn->query($sql_settings) or die("ERREUR SETTINGS : " . $conn->error);

// Table about_gallery
$sql_about_gallery = "CREATE TABLE IF NOT EXISTS about_gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    image_path VARCHAR(255) NOT NULL,
    image_alt VARCHAR(255),
    description TEXT,
    order_position INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX(order_position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
$conn->query($sql_about_gallery) or die("ERREUR ABOUT_GALLERY : " . $conn->error);

// Table team_categories
$sql_team_categories = "CREATE TABLE IF NOT EXISTS team_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    display_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_display_order (display_order),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
$conn->query($sql_team_categories) or die("ERREUR TEAM_CATEGORIES : " . $conn->error);

// Table team_members
$sql_team_members = "CREATE TABLE IF NOT EXISTS team_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    position VARCHAR(100) NOT NULL,
    description TEXT,
    image_path VARCHAR(255) DEFAULT NULL,
    category_id INT DEFAULT NULL,
    email VARCHAR(100) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    linkedin VARCHAR(255) DEFAULT NULL,
    twitter VARCHAR(255) DEFAULT NULL,
    display_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category_id),
    INDEX idx_display_order (display_order),
    INDEX idx_is_active (is_active),
    CONSTRAINT fk_member_category FOREIGN KEY (category_id) 
        REFERENCES team_categories(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
$conn->query($sql_team_members) or die("ERREUR TEAM_MEMBERS : " . $conn->error);

// Insérer des catégories par défaut si la table est vide
$check_categories = $conn->query("SELECT COUNT(*) as count FROM team_categories");
if ($check_categories && $check_categories->fetch_assoc()['count'] == 0) {
    $default_categories = [
        "('Direction', 'Membres de la direction', 1, 1)",
        "('Équipe Commerciale', 'Service commercial', 2, 1)",
        "('Équipe Technique', 'Service technique', 3, 1)",
        "('Support Client', 'Service client et support', 4, 1)"
    ];
    
    $sql_insert_cats = "INSERT INTO team_categories (name, description, display_order, is_active) 
                        VALUES " . implode(", ", $default_categories);
    $conn->query($sql_insert_cats) or die("ERREUR INSERT CATEGORIES : " . $conn->error);
    
    echo "Catégories par défaut ajoutées avec succès !<br>";
    
    // Vérifier si des membres existent déjà
    $check_members = $conn->query("SELECT COUNT(*) as count FROM team_members");
    if ($check_members && $check_members->fetch_assoc()['count'] == 0) {
        // Récupérer l'ID de la première catégorie (Direction)
        $result = $conn->query("SELECT id FROM team_categories ORDER BY id LIMIT 1");
        $first_category_id = $result ? $result->fetch_assoc()['id'] : 1;
        
        // Membre 1 (Direction)
        $description1 = "Avec plus de 15 ans d'expérience dans le secteur, Jean apporte une expertise stratégique à notre entreprise.";
        $conn->query("INSERT INTO team_members 
            (name, position, description, category_id, email, phone, linkedin, twitter, display_order, is_active) 
            VALUES (
                'Jean Dupont', 
                'Directeur Général', 
                '" . $conn->real_escape_string($description1) . "', 
                $first_category_id, 
                'jean.dupont@example.com', 
                '+33123456789', 
                'linkedin.com/in/jeandupont', 
                'jeandupont', 
                1, 
                1
            )");
        
        // Membre 2 (Commercial)
        $conn->query("INSERT INTO team_members 
            (name, position, description, category_id, email, phone, display_order, is_active) 
            VALUES (
                'Marie Martin', 
                'Responsable Commerciale', 
                'Marie est passionnée par le développement commercial et les relations clients.', 
                " . ($first_category_id + 1) . ", 
                'marie.martin@example.com', 
                '+33123456790', 
                1, 
                1
            )");
        
        // Membre 3 (Technique)
        $conn->query("INSERT INTO team_members 
            (name, position, description, category_id, email, linkedin, display_order, is_active) 
            VALUES (
                'Thomas Leroy', 
                'Chef de Projet Technique', 
                'Expert en gestion de projets techniques, Thomas assure le bon déroulement de nos opérations.', 
                " . ($first_category_id + 2) . ", 
                'thomas.leroy@example.com', 
                'linkedin.com/in/thomasleroy', 
                1, 
                1
            )");
        
        // Membre 4 (Support)
        $description4 = "Toujours à l'écoute, Sophie s'assure que chaque client reçoit une assistance de qualité.";
        $conn->query("INSERT INTO team_members 
            (name, position, description, category_id, email, phone, twitter, display_order, is_active) 
            VALUES (
                'Sophie Petit', 
                'Responsable Support Client', 
                '" . $conn->real_escape_string($description4) . "', 
                " . ($first_category_id + 3) . ", 
                'sophie.petit@example.com', 
                '+33123456791', 
                'sophiepetit', 
                1, 
                1
            )");
        
        echo "4 membres d'équipe ajoutés avec succès !<br>";
    }
}

echo "Toutes les tables ont été créées avec succès !";

// Créer le dossier d'upload s'il n'existe pas
$upload_dir = __DIR__ . '/../admin/uploads/team/';
if (!file_exists($upload_dir)) {
    if (mkdir($upload_dir, 0755, true)) {
        echo "Dossier d'upload créé avec succès : " . htmlspecialchars($upload_dir) . "<br>";
    } else {
        echo "<span style='color: red;'>Erreur lors de la création du dossier d'upload. Veuillez créer manuellement le dossier : " . 
             htmlspecialchars($upload_dir) . " et définir les permissions à 755</span><br>";
    }
}

?>

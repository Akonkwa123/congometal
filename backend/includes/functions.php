<?php
require_once __DIR__ . '/config.php';

// Fonction pour vérifier si un utilisateur est connecté
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Fonction pour vérifier si l'utilisateur est admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Fonction de redirection si non authentifié
function checkAuth() {
    if (!isLoggedIn()) {
        header("Location: " . ADMIN_URL . "/login.php");
        exit();
    }
}

// Fonction pour échapper les entrées
function escape_input($data) {
    global $conn;
    return $conn->real_escape_string(htmlspecialchars(strip_tags($data)));
}

// Fonction pour obtenir tous les services
function getServices($status = 'active') {
    global $conn;
    $status = escape_input($status);
    $sql = "SELECT * FROM services WHERE status = '$status' ORDER BY order_position ASC";
    return $conn->query($sql);
}

// Fonction pour obtenir tous les projets
function getPortfolio($limit = null) {
    global $conn;
    $sql = "SELECT * FROM portfolio WHERE status = 'active' ORDER BY created_at DESC";
    if ($limit) {
        $limit = intval($limit);
        $sql .= " LIMIT $limit";
    }
    return $conn->query($sql);
}

// Fonction pour obtenir les événements
function getEvents($status = 'active') {
    global $conn;
    $status = escape_input($status);
    $sql = "SELECT * FROM events WHERE status = '$status' ORDER BY event_date DESC, created_at DESC";
    return $conn->query($sql);
}

// Fonction pour obtenir les médias d'un événement
function getEventMedia($event_id) {
    global $conn;
    $event_id = intval($event_id);
    $media = [];
    $result = $conn->query("SELECT * FROM event_media WHERE event_id = $event_id ORDER BY created_at ASC");
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $media[] = $row;
        }
    }
    return $media;
}

// Fonction pour obtenir les paramètres du site
function getSetting($key, $default = '') {
    global $conn;
    $key = escape_input($key);
    $sql = "SELECT setting_value FROM settings WHERE setting_key = '$key'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['setting_value'];
    }
    return $default;
}

// Fonction pour mettre à jour les paramètres
function updateSetting($key, $value) {
    global $conn;
    $key = escape_input($key);
    $value = escape_input($value);
    
    $sql = "INSERT INTO settings (setting_key, setting_value) VALUES ('$key', '$value')
            ON DUPLICATE KEY UPDATE setting_value = '$value'";
    return $conn->query($sql);
}

// Créer la table des visites si elle n'existe pas
function ensureSiteVisitsTable() {
    global $conn;
    $sql = "CREATE TABLE IF NOT EXISTS site_visits (
        id INT AUTO_INCREMENT PRIMARY KEY,
        visit_date DATE NOT NULL,
        ip VARCHAR(64) NOT NULL,
        user_agent VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_visit (visit_date, ip),
        INDEX idx_visit_date (visit_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $conn->query($sql);
}

// Enregistrer une visite unique par IP et par jour
function logSiteVisit() {
    global $conn;
    if (empty($conn)) {
        return;
    }
    ensureSiteVisitsTable();
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    if ($ip === '') {
        return;
    }
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $date = date('Y-m-d');
    $ip = $conn->real_escape_string($ip);
    $ua = $conn->real_escape_string(substr($ua, 0, 255));
    $date = $conn->real_escape_string($date);

    $conn->query("INSERT IGNORE INTO site_visits (visit_date, ip, user_agent) VALUES ('$date', '$ip', '$ua')");
}

// Fonction pour télécharger une image
function uploadImage($file, $folder = 'general') {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'Erreur lors du téléchargement du fichier'];
    }
    
    // Vérifier la taille
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['error' => 'Le fichier est trop volumineux (max 5MB)'];
    }
    
    // Vérifier le type
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed_types)) {
        return ['error' => 'Type de fichier non autorisé'];
    }
    
    // Créer le dossier s'il n'existe pas
    $upload_path = UPLOAD_DIR . $folder . '/';
    if (!is_dir($upload_path)) {
        mkdir($upload_path, 0755, true);
    }
    
    // Générer un nom de fichier unique
    $filename = uniqid() . '_' . basename($file['name']);
    $filepath = $upload_path . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return $folder . '/' . $filename;
    }
    
    return ['error' => 'Erreur lors de la sauvegarde du fichier'];
}

// Fonction pour télécharger différents types de médias (image, video, audio, document)
function uploadMedia($file, $folder = 'general', $allowed_types = [], $max_size = MAX_FILE_SIZE) {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'Erreur lors du téléchargement du fichier'];
    }

    // Vérifier la taille
    if ($file['size'] > $max_size) {
        return ['error' => 'Le fichier est trop volumineux (max ' . ($max_size/1048576) . 'MB)'];
    }

    // Détecter le type MIME via finfo pour plus de fiabilité
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!empty($allowed_types) && !in_array($mime, $allowed_types)) {
        return ['error' => 'Type de fichier non autorisé: ' . $mime];
    }

    // Créer le dossier s'il n'existe pas
    $upload_path = UPLOAD_DIR . $folder . '/';
    if (!is_dir($upload_path)) {
        mkdir($upload_path, 0755, true);
    }

    // Générer un nom de fichier unique
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($file['name']));
    $filepath = $upload_path . $filename;

    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return $folder . '/' . $filename;
    }

    return ['error' => 'Erreur lors de la sauvegarde du fichier'];
}

// Fonction pour supprimer une image
function deleteImage($image_path) {
    $full_path = UPLOAD_DIR . $image_path;
    if (file_exists($full_path)) {
        return unlink($full_path);
    }
    return false;
}

// Fonction pour générer un slug
function generateSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    return $text;
}

// Trouver ou créer un utilisateur à partir du profil Google
function findOrCreateUserFromGoogle($profile) {
    global $conn;
    // $profile attend : ['id', 'email', 'name', 'picture']
    $email = escape_input($profile['email'] ?? '');
    if (empty($email)) return null;

    $sql = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }

    // Créer un utilisateur si absent. Par défaut rôle 'admin' pour accéder au panneau.
    $username = escape_input($profile['name'] ?? explode('@', $email)[0]);
    // Générer un mot de passe aléatoire (non utilisé pour OAuth)
    $random_password = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, 'admin', NOW())");
    if ($stmt) {
        $stmt->bind_param('sss', $username, $email, $random_password);
        if ($stmt->execute()) {
            $id = $stmt->insert_id;
            $stmt->close();
            $res = $conn->query("SELECT * FROM users WHERE id = $id");
            return $res->fetch_assoc();
        }
        $stmt->close();
    }

    return null;
}
?>

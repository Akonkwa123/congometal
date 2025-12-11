<?php
// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'congometal');

// Créer la connexion
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Erreur de connexion: " . $conn->connect_error);
}

// Définir le charset
$conn->set_charset("utf8");

// Variables globales
define('SITE_URL', 'http://localhost/congometal');
define('UPLOAD_DIR', __DIR__ . '/../admin/uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB

// Google OAuth2 - remplissez ces valeurs avec vos identifiants
// Créez des identifiants OAuth 2.0 dans Google Cloud Console
// Redirect URI example: http://localhost/congometal/admin/google_callback.php
define('GOOGLE_CLIENT_ID', 'YOUR_GOOGLE_CLIENT_ID');
define('GOOGLE_CLIENT_SECRET', 'YOUR_GOOGLE_CLIENT_SECRET');
define('GOOGLE_REDIRECT_URI', SITE_URL . '/admin/google_callback.php');

// Démarrer la session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

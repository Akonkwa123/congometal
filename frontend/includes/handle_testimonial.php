<?php
require_once 'config.php';
require_once 'functions.php';

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    $data = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
    // Assurer que la table existe (au cas où l'init n'a pas encore été rejouée)
    $sql_testimonials = "CREATE TABLE IF NOT EXISTS testimonials (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255),
        message TEXT NOT NULL,
        status ENUM('new', 'approved', 'rejected') DEFAULT 'new',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    if ($conn->query($sql_testimonials) !== TRUE) {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur serveur: ' . $conn->error
        ]);
        exit;
    }

    // Migration douce: s'assurer que toutes les colonnes existent
    $cols = [];
    $res = $conn->query("SHOW COLUMNS FROM testimonials");
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $cols[$r['Field']] = true;
        }
    }
    if (!isset($cols['email'])) {
        $conn->query("ALTER TABLE testimonials ADD COLUMN email VARCHAR(255) NULL AFTER name");
    }
    if (!isset($cols['status'])) {
        $conn->query("ALTER TABLE testimonials ADD COLUMN status ENUM('new','approved','rejected') DEFAULT 'new'");
    }
    if (!isset($cols['created_at'])) {
        $conn->query("ALTER TABLE testimonials ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
    }

    $statusCol = isset($cols['status']) ? 'status' : (isset($cols['statut']) ? 'statut' : 'status');
    $statusType = '';
    $res = $conn->query("SHOW COLUMNS FROM testimonials");
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            if ($r['Field'] === 'status' || $r['Field'] === 'statut') {
                $statusType = $r['Type'];
            }
        }
    }
    $statusMode = 'workflow';
    if ($statusType && stripos($statusType, 'active') !== false && stripos($statusType, 'inactive') !== false) {
        $statusMode = 'active_inactive';
    }
    $newStatusValue = $statusMode === 'active_inactive' ? 'inactive' : 'new';

    $name = escape_input($data['name'] ?? '');
    $email = escape_input($data['email'] ?? '');
    $message = escape_input($data['message'] ?? '');

    if (empty($name) || empty($message)) {
        echo json_encode([
            'success' => false,
            'message' => 'Veuillez remplir les champs obligatoires.'
        ]);
        exit;
    }

    $sql = "INSERT INTO testimonials (name, email, message, `$statusCol`)
            VALUES ('$name', '$email', '$message', '$newStatusValue')";

    if ($conn->query($sql) === TRUE) {
        echo json_encode([
            'success' => true,
            'message' => 'Merci ! Votre témoignage a été envoyé.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur serveur: ' . $conn->error
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée.'
    ]);
}
?>

<?php
require_once 'config.php';
require_once 'functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = escape_input($_POST['name'] ?? '');
    $email = escape_input($_POST['email'] ?? '');
    $phone = escape_input($_POST['phone'] ?? '');
    $subject = escape_input($_POST['subject'] ?? '');
    $message = escape_input($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        echo json_encode([
            'success' => false,
            'message' => 'Veuillez remplir tous les champs obligatoires.'
        ]);
        exit;
    }

    $sql = "INSERT INTO contacts (name, email, phone, subject, message) 
            VALUES ('$name', '$email', '$phone', '$subject', '$message')";

    if ($conn->query($sql) === TRUE) {
        echo json_encode([
            'success' => true,
            'message' => 'Votre message a été envoyé avec succès. Nous vous répondrons bientôt.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Une erreur est survenue. Veuillez réessayer.'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée.'
    ]);
}
?>

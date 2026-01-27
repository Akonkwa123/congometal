<?php
/**
 * Gestion des membres de l'équipe - Panneau d'administration
 */

// Démarrer la session de manière sécurisée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    session_regenerate_id(true);
}

// Inclure les fichiers nécessaires
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Vérification d'authentification et de droits d'administration
checkAuth();
if (!isAdmin()) {
    $_SESSION['error'] = 'Accès non autorisé';
    header("Location: login.php");
    exit();
}

// Définir les en-têtes de sécurité
header('Content-Type: text/html; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' https://cdnjs.cloudflare.com https://code.jquery.com https://cdn.jsdelivr.net; style-src \'self\' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net \'unsafe-inline\'; img-src \'self\' data: https:;');

// Vérifier si les tables existent de manière sécurisée
$tables_exist = false;
try {
    $tables = $conn->query("SHOW TABLES");
    $required_tables = ['team_categories', 'team_members'];
    $existing_tables = [];
    
    if ($tables) {
        while ($row = $tables->fetch_array()) {
            $existing_tables[] = $row[0];
        }
        $tables->free();
    }
    
    $tables_exist = count(array_intersect($required_tables, $existing_tables)) === count($required_tables);
    
    if (!$tables_exist && !isset($_GET['setup'])) {
        header("Location: create_team_table.php");
        exit();
    }
} catch (Exception $e) {
    error_log('Erreur de vérification des tables : ' . $e->getMessage());
    $error = 'Une erreur est survenue lors de la vérification des tables de la base de données.';
}

$message = '';
$error = '';

// Nettoyage des entrées
$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING) ?: '';
$member_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['default' => 0, 'min_range' => 0]]);

switch ($action) {
    case 'delete_member':
        if ($member_id) {
            try {
                $stmt = $conn->prepare("DELETE FROM team_members WHERE id = ?");
                $stmt->bind_param("i", $member_id);
                if ($stmt->execute()) {
                    $message = 'Membre supprimé avec succès';
                } else {
                    $error = 'Erreur lors de la suppression du membre';
                }
            } catch (Exception $e) {
                $error = 'Erreur : ' . $e->getMessage();
            }
        }
        break;
        
    case 'toggle_status':
        if ($member_id) {
            try {
                $stmt = $conn->prepare("UPDATE team_members SET is_active = !is_active WHERE id = ?");
                $stmt->bind_param("i", $member_id);
                if ($stmt->execute()) {
                    $message = 'Statut du membre mis à jour';
                } else {
                    $error = 'Erreur lors de la mise à jour du statut';
                }
            } catch (Exception $e) {
                $error = 'Erreur : ' . $e->getMessage();
            }
        }
        break;
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nettoyage et validation des entrées
    $name = trim(htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8'));
    $position = trim(htmlspecialchars($_POST['position'] ?? '', ENT_QUOTES, 'UTF-8'));
    $description = trim(htmlspecialchars($_POST['description'] ?? '', ENT_QUOTES, 'UTF-8'));
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL) ?: '';
    $phone = preg_replace('/[^0-9+\s-]/', '', $_POST['phone'] ?? '');
    $linkedin = filter_var(trim($_POST['linkedin'] ?? ''), FILTER_SANITIZE_URL);
    $twitter = filter_var(trim($_POST['twitter'] ?? ''), FILTER_SANITIZE_URL);
    $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT, ['options' => ['default' => 0, 'min_range' => 0]]);
    $display_order = filter_input(INPUT_POST, 'display_order', FILTER_VALIDATE_INT, ['options' => ['default' => 0, 'min_range' => 0]]);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $member_id = filter_input(INPUT_POST, 'member_id', FILTER_VALIDATE_INT, ['options' => ['default' => 0, 'min_range' => 0]]);
    
    // Validation des champs obligatoires
    $errors = [];
    if (empty($name)) {
        $errors[] = 'Le nom est obligatoire';
    }
    if (empty($position)) {
        $errors[] = 'Le poste est obligatoire';
    }
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'L\'adresse email n\'est pas valide';
    }
    
    // Gestion de l'upload de l'image
    $image_path = !empty($_POST['existing_image']) ? filter_var($_POST['existing_image'], FILTER_SANITIZE_STRING) : '';
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../admin/uploads/team/';
        if (!file_exists($upload_dir) && !mkdir($upload_dir, 0755, true) && !is_dir($upload_dir)) {
            $errors[] = 'Impossible de créer le répertoire de destination';
        } else {
            $file_info = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($file_info, $_FILES['image']['tmp_name']);
            $allowed_mime_types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
            
            if (!in_array($mime_type, array_keys($allowed_mime_types), true)) {
                $errors[] = 'Format de fichier non autorisé. Utilisez JPG, JPEG, PNG ou GIF.';
            } elseif ($_FILES['image']['size'] > 2 * 1024 * 1024) {
                $errors[] = 'La taille du fichier ne doit pas dépasser 2 Mo.';
            } else {
                $file_extension = $allowed_mime_types[$mime_type];
                $filename = uniqid('', true) . '.' . $file_extension;
                $target_path = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                    // Supprimer l'ancienne image si elle existe
                    if (!empty($image_path)) {
                        $old_image_path = __DIR__ . '/../' . ltrim($image_path, '/');
                        if (file_exists($old_image_path) && is_file($old_image_path)) {
                            @unlink($old_image_path);
                        }
                    }
                    $image_path = 'uploads/team/' . $filename;
                } else {
                    $errors[] = 'Erreur lors du téléchargement du fichier.';
                }
            }
            finfo_close($file_info);
        }
    }
    
    // Si pas d'erreurs, procéder à l'enregistrement
    if (empty($errors)) {
        try {
            if ($member_id > 0) {
                // Mise à jour
                $update_fields = [
                    'name' => $name,
                    'position' => $position,
                    'description' => $description,
                    'email' => $email,
                    'phone' => $phone,
                    'linkedin' => $linkedin,
                    'twitter' => $twitter,
                    'category_id' => $category_id,
                    'display_order' => $display_order,
                    'is_active' => $is_active
                ];
                
                $params = [];
                $types = '';
                $values = [];
                
                // Construction dynamique de la requête
                $sql = "UPDATE team_members SET ";
                
                foreach ($update_fields as $field => $value) {
                    $sql .= "$field = ?, ";
                    $values[] = $value;
                    $types .= is_int($value) ? 'i' : 's';
                }
                
                // Ajout du chemin de l'image si fourni
                if (!empty($image_path)) {
                    $sql .= "image_path = ?, ";
                    $values[] = $image_path;
                    $types .= 's';
                }
                
                // Suppression de la virgule finale et ajout de la clause WHERE
                $sql = rtrim($sql, ', ') . " WHERE id = ?";
                $values[] = $member_id;
                $types .= 'i';
                
                // Préparation et exécution de la requête
                $stmt = $conn->prepare($sql);
                $stmt->bind_param($types, ...$values);
            } else {
                // Nouveau membre
                $sql = "INSERT INTO team_members (
                            name, position, description, email, phone,
                            linkedin, twitter, category_id, display_order, is_active, image_path
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param(
                    "ssssssssiis",
                    $name, $position, $description, $email, $phone,
                    $linkedin, $twitter, $category_id, $display_order, $is_active, $image_path
                );
            }
            
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Membre ' . ($member_id > 0 ? 'mis à jour' : 'ajouté') . ' avec succès';
                header("Location: manage_team.php");
                exit();
            } else {
                throw new Exception('Erreur lors de l\'enregistrement : ' . $conn->error);
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            $error = 'Une erreur est survenue lors de l\'enregistrement. Veuillez réessayer.';
        }
    } else {
        $error = implode('<br>', $errors);
    }
}

// Récupération des données pour l'édition
$member = null;
if (isset($_GET['edit'])) {
    $edit_id = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
    if ($edit_id) {
        try {
            $stmt = $conn->prepare("SELECT * FROM team_members WHERE id = ?");
            $stmt->bind_param("i", $edit_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $member = $result->fetch_assoc();
            $stmt->close();
            
            if (!$member) {
                $_SESSION['error'] = 'Membre introuvable';
                header("Location: manage_team.php");
                exit();
            }
        } catch (Exception $e) {
            error_log('Erreur de récupération du membre : ' . $e->getMessage());
            $_SESSION['error'] = 'Erreur lors de la récupération des données du membre';
            header("Location: manage_team.php");
            exit();
        }
    } else {
        $_SESSION['error'] = 'Identifiant de membre invalide';
        header("Location: manage_team.php");
        exit();
    }
}

// Récupération des catégories avec gestion des erreurs
try {
    $categories = $conn->query("SELECT id, name, display_order, is_active FROM team_categories WHERE is_active = 1 ORDER BY display_order, name");
    if (!$categories) {
        throw new Exception('Erreur lors de la récupération des catégories : ' . $conn->error);
    }
    
    // Récupération des membres avec leurs catégories
    $members = $conn->query("
        SELECT m.*, c.name as category_name 
        FROM team_members m
        LEFT JOIN team_categories c ON m.category_id = c.id
        ORDER BY c.display_order, m.display_order, m.name
    ");
    
    if (!$members) {
        throw new Exception('Erreur lors de la récupération des membres : ' . $conn->error);
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    $error = 'Une erreur est survenue lors du chargement des données. Veuillez réessayer plus tard.';
    $categories = [];
    $members = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Gérer l'équipe - Administration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --secondary: #6b7280;
            --success: #22c55e;
            --danger: #dc2626;
            --warning: #fbbf24;
            --light: #f9fafb;
            --dark: #020617;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background:
                radial-gradient(circle at top left, rgba(37, 99, 235, 0.12), transparent 55%),
                radial-gradient(circle at bottom right, rgba(129, 140, 248, 0.12), transparent 55%),
                #f3f4f6;
            color: #111827;
            min-height: 100vh;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 260px;
            background: linear-gradient(145deg, #1e293b, #020617);
            color: #e5e7eb;
            padding: 1.75rem 0 2rem;
            position: fixed;
            inset-block: 0;
            box-shadow: 0 0 0 1px rgba(15, 23, 42, 0.7), 0 24px 60px rgba(15, 23, 42, 0.9);
            overflow-y: auto;
        }

        .sidebar h2 {
            padding: 0 1.75rem 0.5rem;
            margin-bottom: 1.8rem;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.16em;
            font-weight: 700;
            opacity: 0.9;
        }

        .sidebar ul {
            list-style: none;
            padding: 0 0.75rem;
        }

        .sidebar ul li + li {
            margin-top: 0.25rem;
        }

        .sidebar ul li a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1.1rem;
            margin: 0 0.75rem;
            border-radius: 999px;
            color: #e5e7eb;
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 500;
            transition: background-color 0.2s ease, color 0.2s ease, transform 0.15s ease, box-shadow 0.2s ease;
        }

        .sidebar ul li a i {
            font-size: 1.15rem;
        }

        .sidebar ul li a:hover,
        .sidebar ul li a.active {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.5), rgba(37, 99, 235, 0.8));
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.7);
            transform: translateY(-1px);
            color: #f9fafb;
        }

        .sidebar .logout {
            margin-top: 2rem;
            padding: 0 1.75rem;
        }

        .sidebar .logout a {
            display: block;
            text-align: center;
            padding: 0.65rem 1rem;
            border-radius: 999px;
            text-decoration: none;
            background: rgba(15, 23, 42, 0.4);
            color: #e5e7eb;
            font-size: 0.9rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            font-weight: 600;
        }

        .sidebar .logout a:hover {
            background: rgba(15, 23, 42, 0.7);
        }

        .main-content {
            margin-left: 260px;
            flex: 1;
            padding: 2rem 2.5rem 2.5rem;
        }

        header {
            background: linear-gradient(135deg, #ffffff, #eff6ff);
            padding: 1.25rem 1.75rem;
            border-radius: 16px;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.15);
            border: 1px solid rgba(148, 163, 184, 0.35);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            margin: 0;
            font-size: 1.35rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            background: linear-gradient(120deg, #2563eb, #6366f1);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .header-user {
            text-align: right;
        }

        .header-user p {
            margin: 0;
            font-size: 0.95rem;
            color: #6b7280;
        }

        .header-user strong {
            color: #111827;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 0 1.5rem;
        }

        .admin-page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.75rem;
        }

        .admin-page-title {
            margin: 0;
            font-size: 1.6rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            background: linear-gradient(120deg, #2563eb, #6366f1);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .admin-page-title i {
            color: #2563eb;
        }
        
        .card {
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 14px 35px rgba(15,23,42,0.08);
            margin-bottom: 20px;
            overflow: hidden;
            border: 1px solid rgba(148,163,184,0.28);
        }
        
        .card-header {
            background: linear-gradient(135deg, #2563eb, #1e40af);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            border-radius: 999px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
            text-decoration: none;
        }
        
        .btn i {
            margin-right: 5px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
        }
        
        .btn-danger {
            background: rgba(220,38,38,0.1);
            color: #b91c1c;
        }
        
        .btn-warning {
            background: rgba(234,179,8,0.1);
            color: #92400e;
        }
        
        .btn-success {
            background: rgba(22,163,74,0.1);
            color: #15803d;
        }
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th, .table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
            font-size: 0.92rem;
        }
        
        .table th {
            background-color: #f3f4ff;
            font-weight: 600;
        }
        
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-control {
            display: block;
            width: 100%;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
        .form-control:focus {
            color: #495057;
            background-color: #fff;
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        .custom-file {
            position: relative;
            display: inline-block;
            width: 100%;
            height: calc(1.5em + 0.75rem + 2px);
            margin-bottom: 0;
        }
        
        .custom-file-input {
            position: relative;
            z-index: 2;
            width: 100%;
            height: calc(1.5em + 0.75rem + 2px);
            margin: 0;
            opacity: 0;
        }
        
        .custom-file-label {
            position: absolute;
            top: 0;
            right: 0;
            left: 0;
            z-index: 1;
            height: calc(1.5em + 0.75rem + 2px);
            padding: 0.375rem 0.75rem;
            font-weight: 400;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
        }
        
        .custom-file-label::after {
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            z-index: 3;
            display: block;
            height: calc(1.5em + 0.75rem);
            padding: 0.375rem 0.75rem;
            line-height: 1.5;
            color: #495057;
            content: "Parcourir";
            background-color: #e9ecef;
            border-left: inherit;
            border-radius: 0 0.25rem 0.25rem 0;
        }
        
        .preview-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .preview-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
            text-align: center;
        }
        
        .preview-card h4 {
            margin-top: 0;
            color: #495057;
        }
        
        .preview-info {
            margin-top: 15px;
        }
        
        .preview-info h5 {
            margin: 10px 0 5px;
            color: #343a40;
        }
        
        .preview-info p {
            color: #6c757d;
            margin: 0;
        }
        
        .social-links {
            margin-top: 15px;
        }
        
        .social-links a {
            color: #6c757d;
            margin: 0 5px;
            font-size: 1.2rem;
            transition: color 0.3s;
        }
        
        .social-links a:hover {
            color: #4a6bdf;
            text-decoration: none;
        }
        
        .tabs {
            display: flex;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 20px;
        }
        
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            border: 1px solid transparent;
            border-bottom: none;
            border-radius: 999px 999px 0 0;
            margin-right: 5px;
            background-color: #e5e7eb;
            font-size: 0.9rem;
            color: #4b5563;
        }
        
        .tab.active {
            background-color: #ffffff;
            border-color: #e5e7eb #e5e7eb #ffffff;
            margin-bottom: -1px;
            color: #1f2937;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .img-preview {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
            margin-top: 10px;
        }

        @media (max-width: 991.98px) {
            .admin-container {
                flex-direction: column;
            }

            .sidebar {
                position: relative;
                width: 100%;
                inset-block: auto;
                height: auto;
                box-shadow: 0 10px 28px rgba(15, 23, 42, 0.7);
            }

            .main-content {
                margin-left: 0;
                padding: 1.75rem 1.25rem 2rem;
            }

            .container {
                padding: 0 0 1.25rem;
            }
        }

        @media (max-width: 768px) {
            header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }

            .admin-page-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .tabs {
                flex-wrap: wrap;
                row-gap: 0.5rem;
            }

            .tab {
                padding: 0.5rem 0.9rem;
                font-size: 0.85rem;
            }

            .sidebar ul li a {
                padding: 0.6rem 0.9rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>
    <button class="mobile-menu-toggle" id="mobileMenuToggle" type="button" aria-label="Ouvrir le menu" aria-expanded="false">
        <i class="bi bi-list"></i>
    </button>
    <div class="admin-container">
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="dashboard.php" class="nav-link"><i class="bi bi-speedometer2 me-2"></i><span>Tableau de bord</span></a></li>
                <li><a href="dashboard.php#settings" class="nav-link"><i class="bi bi-gear me-2"></i><span>Paramètres</span></a></li>
                <li><a href="add_about_gallery.php" class="nav-link"><i class="bi bi-images me-2"></i><span>Galerie À propos</span></a></li>
                <li><a href="manage_portfolio.php" class="nav-link"><i class="bi bi-briefcase me-2"></i><span>Gestion Portfolio</span></a></li>
                <li><a href="manage_team.php" class="nav-link active"><i class="bi bi-people me-2"></i><span>Gestion de l'équipe</span></a></li>
                <li><a href="dashboard.php#services" class="nav-link"><i class="bi bi-grid me-2"></i><span>Services</span></a></li>
                <li><a href="dashboard.php#portfolio" class="nav-link"><i class="bi bi-columns-gap me-2"></i><span>Portfolio</span></a></li>
                <li><a href="dashboard.php#contacts" class="nav-link"><i class="bi bi-envelope-open me-2"></i><span>Contacts</span></a></li>
                <li><a href="dashboard.php#users" class="nav-link"><i class="bi bi-person-badge me-2"></i><span>Utilisateurs</span></a></li>
            </ul>
            <div class="logout">
                <a href="logout.php"><i class="bi bi-box-arrow-right me-1"></i>Déconnexion</a>
            </div>
        </div>

        <div class="main-content">
            <header>
                <h1>Congometal - Admin</h1>
                <div class="header-user">
                    <p>Connecté en tant que: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></p>
                </div>
            </header>

            <div class="container">
                <div class="admin-page-header">
                    <h1 class="admin-page-title"><i class="fas fa-users-cog"></i><span>Gestion de l'équipe</span></h1>
                </div>

                <?php if (!empty($message)): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>
        
        <div class="tabs">
            <div class="tab active" data-tab="members">Membres</div>
            <div class="tab" data-tab="categories">Catégories</div>
            <?php if (isset($_GET['add']) || isset($_GET['edit'])): ?>
                <div class="tab" data-tab="add-member"><?php echo isset($_GET['edit']) ? 'Modifier' : 'Ajouter'; ?> un membre</div>
            <?php endif; ?>
        </div>
        
        <div id="members-tab" class="tab-content active">
            <div class="card">
                <div class="card-header">
                    <h3 style="margin: 0;">Liste des membres</h3>
                    <a href="?add" class="btn btn-success"><i class="fas fa-plus"></i> Ajouter un membre</a>
                </div>
                <div class="card-body">
                    <?php if ($members && $members->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Photo</th>
                                        <th>Nom</th>
                                        <th>Poste</th>
                                        <th>Catégorie</th>
                                        <th>Ordre</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $members->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <?php if (!empty($row['image_path'])): ?>
                                                    <img src="../<?php echo htmlspecialchars($row['image_path']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">
                                                <?php else: ?>
                                                    <div style="width: 50px; height: 50px; background-color: #e9ecef; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #6c757d; font-weight: bold; font-size: 1.2rem;">
                                                        <?php echo strtoupper(substr($row['name'], 0, 1)); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['position']); ?></td>
                                            <td><?php echo htmlspecialchars($row['category_name'] ?? 'Non catégorisé'); ?></td>
                                            <td><?php echo (int)$row['display_order']; ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $row['is_active'] ? 'success' : 'danger'; ?>">
                                                    <?php echo $row['is_active'] ? 'Actif' : 'Inactif'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="?edit=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="?action=toggle_status&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-<?php echo $row['is_active'] ? 'warning' : 'success'; ?>" title="<?php echo $row['is_active'] ? 'Désactiver' : 'Activer'; ?>">
                                                    <i class="fas fa-<?php echo $row['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                                                </a>
                                                <a href="?action=delete_member&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce membre ?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">Aucun membre n'a été trouvé.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div id="add-member-tab" class="tab-content" <?php echo $member || isset($_GET['add']) ? 'style="display: block;"' : ''; ?>>
            <div class="card">
                <div class="card-header">
                    <h3 style="margin: 0;"><?php echo $member ? 'Modifier le membre' : 'Ajouter un membre'; ?></h3>
                    <a href="?" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" id="memberForm">
                        <input type="hidden" name="member_id" value="<?php echo $member['id'] ?? ''; ?>">
                        <input type="hidden" name="existing_image" id="existing_image" value="<?php echo $member['image_path'] ?? ''; ?>">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Nom complet *</label>
                                    <input type="text" id="name" name="name" class="form-control" required 
                                           value="<?php echo htmlspecialchars($member['name'] ?? ''); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="position">Poste *</label>
                                    <input type="text" id="position" name="position" class="form-control" required
                                           value="<?php echo htmlspecialchars($member['position'] ?? ''); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" id="email" name="email" class="form-control"
                                           value="<?php echo htmlspecialchars($member['email'] ?? ''); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="phone">Téléphone</label>
                                    <input type="text" id="phone" name="phone" class="form-control"
                                           value="<?php echo htmlspecialchars($member['phone'] ?? ''); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="linkedin">LinkedIn</label>
                                    <input type="url" id="linkedin" name="linkedin" class="form-control"
                                           value="<?php echo htmlspecialchars($member['linkedin'] ?? ''); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="twitter">Twitter</label>
                                    <input type="url" id="twitter" name="twitter" class="form-control"
                                           value="<?php echo htmlspecialchars($member['twitter'] ?? ''); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="category_id">Catégorie</label>
                                    <select id="category_id" name="category_id" class="form-control">
                                        <option value="">Sélectionnez une catégorie</option>
                                        <?php if ($categories): ?>
                                            <?php while ($category = $categories->fetch_assoc()): ?>
                                                <option value="<?php echo $category['id']; ?>" 
                                                    <?php echo (isset($member['category_id']) && $member['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="display_order">Ordre d'affichage</label>
                                    <input type="number" id="display_order" name="display_order" class="form-control"
                                           value="<?php echo $member['display_order'] ?? '0'; ?>">
                                    <small class="text-muted">Définit l'ordre d'affichage des membres (du plus petit au plus grand)</small>
                                </div>
                                
                                <div class="form-group form-check">
                                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" 
                                           <?php echo !isset($member['is_active']) || $member['is_active'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_active">Membre actif</label>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="image">Photo</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="image" name="image" accept="image/*">
                                        <label class="custom-file-label" for="image">Choisir un fichier</label>
                                    </div>
                                    <div id="image_preview" class="mt-2">
                                        <?php if (!empty($member['image_path'])): ?>
                                            <img src="../<?php echo htmlspecialchars($member['image_path']); ?>" alt="Prévisualisation" class="img-preview">
                                        <?php endif; ?>
                                    </div>
                                    <small class="form-text text-muted">
                                        Formats acceptés : JPG, PNG, GIF (max 2 Mo)
                                    </small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea id="description" name="description" class="form-control" rows="5"><?php echo htmlspecialchars($member['description'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="preview-card">
                                    <h4>Aperçu de la fiche membre</h4>
                                    <div class="text-center">
                                        <img id="preview_avatar" src="<?php 
                                            echo !empty($member['image_path']) ? 
                                                '../' . htmlspecialchars($member['image_path']) : 
                                                'data:image/svg+xml;charset=UTF-8,' . rawurlencode('<svg width="200" height="200" xmlns="http://www.w3.org/2000/svg"><rect width="200" height="200" fill="#e9ecef"/><text x="100" y="110" font-family="Arial" font-size="80" text-anchor="middle" alignment-baseline="middle" fill="#6c757d">' . 
                                                (!empty($member['name']) ? strtoupper(substr($member['name'], 0, 1)) : '') . 
                                                '</text></svg>'); 
                                        ?>" alt="Avatar" class="preview-avatar">
                                        
                                        <div class="preview-info">
                                            <h5 id="preview_name"><?php echo htmlspecialchars($member['name'] ?? 'Nom du membre'); ?></h5>
                                            <p id="preview_position"><?php echo htmlspecialchars($member['position'] ?? 'Poste'); ?></p>
                                            <p id="preview_description" style="font-size: 0.9rem; color: #6c757d;">
                                                <?php 
                                                    $desc = !empty($member['description']) ? $member['description'] : 'Une brève description du membre apparaîtra ici.';
                                                    echo strlen($desc) > 100 ? substr($desc, 0, 100) . '...' : $desc;
                                                ?>
                                            </p>
                                            
                                            <div class="social-links">
                                                <?php if (!empty($member['email'])): ?>
                                                    <a href="mailto:<?php echo htmlspecialchars($member['email']); ?>" title="Email">
                                                        <i class="fas fa-envelope"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (!empty($member['linkedin'])): ?>
                                                    <a href="<?php echo htmlspecialchars($member['linkedin']); ?>" target="_blank" title="LinkedIn">
                                                        <i class="fab fa-linkedin"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (!empty($member['twitter'])): ?>
                                                    <a href="<?php echo htmlspecialchars($member['twitter']); ?>" target="_blank" title="Twitter">
                                                        <i class="fab fa-twitter"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-right mt-4">
                            <button type="reset" class="btn btn-secondary"><i class="fas fa-undo"></i> Réinitialiser</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?php echo $member ? 'Mettre à jour' : 'Ajouter'; ?> le membre
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Onglet Catégories -->
        <div id="categories-tab" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <h3 style="margin: 0;">Gestion des catégories</h3>
                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#categoryModal">
                        <i class="fas fa-plus"></i> Ajouter une catégorie
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="sortable-categories">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Nom</th>
                                    <th>Description</th>
                                    <th>Ordre</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $categories_result = $conn->query("SELECT * FROM team_categories ORDER BY display_order, name");
                                if ($categories_result && $categories_result->num_rows > 0):
                                    while ($category = $categories_result->fetch_assoc()):
                                ?>
                                    <tr data-id="<?php echo $category['id']; ?>" style="cursor: move;">
                                        <td><i class="fas fa-arrows-alt" style="color: #6c757d;"></i></td>
                                        <td><?php echo htmlspecialchars($category['name']); ?></td>
                                        <td><?php echo !empty($category['description']) ? htmlspecialchars($category['description']) : '-'; ?></td>
                                        <td><?php echo (int)$category['display_order']; ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $category['is_active'] ? 'success' : 'danger'; ?>">
                                                <?php echo $category['is_active'] ? 'Actif' : 'Inactif'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary edit-category" 
                                                    data-id="<?php echo $category['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($category['name']); ?>"
                                                    data-description="<?php echo htmlspecialchars($category['description']); ?>"
                                                    data-order="<?php echo (int)$category['display_order']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="?action=toggle_category&id=<?php echo $category['id']; ?>" 
                                               class="btn btn-sm btn-<?php echo $category['is_active'] ? 'warning' : 'success'; ?>">
                                                <i class="fas fa-<?php echo $category['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                                            </a>
                                            <a href="?action=delete_category&id=<?php echo $category['id']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ? Les membres associés ne seront pas supprimés mais n\\'auront plus de catégorie.');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php 
                                    endwhile;
                                else:
                                ?>
                                    <tr>
                                        <td colspan="6" class="text-center">Aucune catégorie trouvée</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Ajout/Modification Catégorie -->
    <div class="modal fade" id="categoryModal" tabindex="-1" role="dialog" aria-labelledby="categoryModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="categoryModalLabel">Ajouter une catégorie</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="categoryForm" method="POST" action="save_category.php">
                    <input type="hidden" name="category_id" id="category_id" value="">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="category_name">Nom de la catégorie *</label>
                            <input type="text" class="form-control" id="category_name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="category_description">Description</label>
                            <textarea class="form-control" id="category_description" name="description" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="category_order">Ordre d'affichage</label>
                            <input type="number" class="form-control" id="category_order" name="display_order" value="0" min="0">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- jQuery, Popper.js, Bootstrap JS, Select2 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.0/jquery-ui.min.js"></script>
    
    <script>
        // Gestion des onglets
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => {
                tab.addEventListener('click', (e) => {
                    e.preventDefault();
                    // Désactiver tous les onglets
                    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                    document.querySelectorAll('.tab-content').forEach(c => c.style.display = 'none');
                    
                    // Activer l'onglet cliqué
                    tab.classList.add('active');
                    const tabId = tab.getAttribute('data-tab');
                    document.getElementById(tabId + '-tab').style.display = 'block';
                    
                    // Mettre à jour l'URL sans recharger la page
                    history.pushState(null, '', '?tab=' + tabId);
                });
            });
            
            // Vérifier l'URL au chargement pour activer le bon onglet
            const urlParams = new URLSearchParams(window.location.search);
            const activeTab = urlParams.get('tab');
            if (activeTab) {
                const tabToActivate = document.querySelector(`.tab[data-tab="${activeTab}"]`);
                if (tabToActivate) {
                    tabToActivate.click();
                }
            }
            
            // Prévisualisation de l'image
            document.getElementById('image').addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const preview = document.getElementById('image_preview');
                        preview.innerHTML = `<img src="${e.target.result}" class="img-preview">`;
                        
                        // Mettre à jour la prévisualisation
                        document.getElementById('preview_avatar').src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });
            
            // Mise à jour en temps réel de la prévisualisation
            ['name', 'position', 'description'].forEach(field => {
                const element = document.getElementById(field);
                if (element) {
                    element.addEventListener('input', updatePreview);
                }
            });
            
            function updatePreview() {
                const name = document.getElementById('name').value || 'Nom du membre';
                const position = document.getElementById('position').value || 'Poste';
                const description = document.getElementById('description').value || 'Une brève description du membre apparaîtra ici.';
                
                document.getElementById('preview_name').textContent = name;
                document.getElementById('preview_position').textContent = position;
                document.getElementById('preview_description').textContent = 
                    description.length > 100 ? description.substring(0, 100) + '...' : description;
                    
                // Mettre à jour l'initiale dans l'avatar si pas d'image
                if (!document.getElementById('image').files[0] && !document.getElementById('existing_image').value) {
                    const initial = name.charAt(0).toUpperCase();
                    const svg = `data:image/svg+xml;charset=UTF-8,${encodeURIComponent(
                        `<svg width="200" height="200" xmlns="http://www.w3.org/2000/svg">
                            <rect width="200" height="200" fill="#e9ecef"/>
                            <text x="100" y="110" font-family="Arial" font-size="80" text-anchor="middle" 
                                  alignment-baseline="middle" fill="#6c757d">${initial}</text>
                        </svg>`
                    )}`;
                    
                    const previewAvatar = document.getElementById('preview_avatar');
                    if (previewAvatar) {
                        previewAvatar.src = svg;
                    }
                }
            }
            
            // Initialiser Select2
            // Vérifier que jQuery est chargé
            if (typeof jQuery == 'undefined') {
                console.error('jQuery n\'est pas chargé');
            } else {
                console.log('jQuery est chargé avec succès');
            }
            
            $(document).ready(function() {
                $('#category_id').select2({
                    placeholder: 'Sélectionnez une catégorie',
                    allowClear: true
                });
                
                // Vérifier que jQuery UI est chargé
                if (typeof jQuery.ui === 'undefined' || !jQuery.ui.sortable) {
                    console.error('jQuery UI Sortable n\'est pas chargé');
                    return;
                }
                
                // Gestion du tri des catégories
                $("#sortable-categories").sortable({
                    update: function(event, ui) {
                        const order = [];
                        $("#sortable-categories tr").each(function(index) {
                            order.push({
                                id: $(this).data('id'),
                                order: index + 1
                            });
                        });
                        
                        // Envoyer la nouvelle commande au serveur
                        $.post('update_category_order.php', { order: order });
                    }
                });
                
                // Gestion du modal des catégories
                $('.edit-category').click(function() {
                    const id = $(this).data('id');
                    const name = $(this).data('name');
                    const description = $(this).data('description');
                    const order = $(this).data('order');
                    
                    $('#category_id').val(id);
                    $('#category_name').val(name);
                    $('#category_description').val(description);
                    $('#category_order').val(order);
                    $('#categoryModalLabel').text('Modifier la catégorie');
                    
                    // Afficher le modal
                    $('#categoryModal').modal('show');
                });
                
                // Réinitialiser le formulaire de catégorie
                $('button[data-target="#categoryModal"]').click(function() {
                    $('#categoryForm')[0].reset();
                    $('#category_id').val('');
                    $('#categoryModalLabel').text('Ajouter une catégorie');
                });
                
                // Soumission du formulaire de catégorie
                $('#categoryForm').on('submit', function(e) {
                    e.preventDefault();
                    
                    $.ajax({
                        url: 'save_category.php',
                        type: 'POST',
                        data: $(this).serialize(),
                        success: function(response) {
                            // Recharger la page pour voir les changements
                            location.reload();
                        },
                        error: function(xhr, status, error) {
                            console.error('Erreur lors de la sauvegarde de la catégorie :', error);
                            alert('Erreur lors de la sauvegarde de la catégorie : ' + error);
                        }
                    });
                });
                
                // Gestion de la soumission du formulaire de membre
                $('#memberForm').on('submit', function(e) {
                    e.preventDefault();
                    
                    // Créer un objet FormData pour gérer les fichiers
                    var formData = new FormData(this);
                    
                    // Afficher un indicateur de chargement
                    var submitBtn = $(this).find('button[type="submit"]');
                    var originalText = submitBtn.html();
                    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Enregistrement...');
                    
                    $.ajax({
                        url: 'save_member.php',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            try {
                                var result = typeof response === 'string' ? JSON.parse(response) : response;
                                if (result.success) {
                                    // Rediriger vers la liste des membres avec un message de succès
                                    window.location.href = 'manage_team.php?success=' + encodeURIComponent(result.message || 'Membre enregistré avec succès');
                                } else {
                                    // Afficher l'erreur
                                    alert(result.message || 'Une erreur est survenue lors de l\'enregistrement du membre');
                                    submitBtn.prop('disabled', false).html(originalText);
                                }
                            } catch (e) {
                                console.error('Erreur de parsing de la réponse :', e);
                                alert('Réponse du serveur invalide');
                                submitBtn.prop('disabled', false).html(originalText);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Erreur AJAX :', status, error);
                            alert('Erreur lors de la communication avec le serveur : ' + error);
                            submitBtn.prop('disabled', false).html(originalText);
                        }
                    });
                });
            });
        });

        // Bouton flottant mobile pour la sidebar
        document.addEventListener('DOMContentLoaded', function () {
            var bodyEl = document.body;
            var mobileToggle = document.getElementById('mobileMenuToggle');
            var sidebarBackdrop = document.getElementById('sidebarBackdrop');

            function setToggleIcon(isOpen) {
                if (!mobileToggle) return;
                var icon = mobileToggle.querySelector('i');
                if (!icon) return;
                icon.classList.remove('bi-list', 'bi-x-lg');
                icon.classList.add(isOpen ? 'bi-x-lg' : 'bi-list');
                if (isOpen) {
                    mobileToggle.classList.add('is-open');
                } else {
                    mobileToggle.classList.remove('is-open');
                }
            }

            function closeSidebar() {
                bodyEl.classList.remove('sidebar-open');
                if (mobileToggle) {
                    mobileToggle.setAttribute('aria-expanded', 'false');
                }
                setToggleIcon(false);
            }

            function toggleSidebar() {
                var willOpen = !bodyEl.classList.contains('sidebar-open');
                bodyEl.classList.toggle('sidebar-open', willOpen);
                if (mobileToggle) {
                    mobileToggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
                }
                setToggleIcon(willOpen);
            }

            if (mobileToggle) {
                mobileToggle.addEventListener('click', function (e) {
                    e.preventDefault();
                    toggleSidebar();
                });
            }

            if (sidebarBackdrop) {
                sidebarBackdrop.addEventListener('click', function () {
                    closeSidebar();
                });
            }

            document.querySelectorAll('.sidebar .nav-link').forEach(function (link) {
                link.addEventListener('click', function () {
                    if (window.innerWidth <= 991.98) {
                        closeSidebar();
                    }
                });
            });
        });
    </script>
</body>
</html>
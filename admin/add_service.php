<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkAuth();
if (!isAdmin()) header("Location: login.php");

// Récupérer l'ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$service = null;

if ($id > 0) {
    $result = $conn->query("SELECT * FROM services WHERE id = $id");
    $service = $result->fetch_assoc();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = escape_input($_POST['title']);
    $description = escape_input($_POST['description']);
    $icon = escape_input($_POST['icon']);
    $status = escape_input($_POST['status']);
    $order_position = intval($_POST['order_position']);

    if (empty($title)) {
        $message = '<div class="alert alert-error">Veuillez remplir tous les champs requis.</div>';
    } else {
        if ($id > 0) {
            // Mise à jour
            $sql = "UPDATE services SET title='$title', description='$description', icon='$icon', status='$status', order_position=$order_position WHERE id=$id";
            $message = '<div class="alert alert-success">Service mis à jour avec succès.</div>';
        } else {
            // Insertion
            $sql = "INSERT INTO services (title, description, icon, status, order_position) VALUES ('$title', '$description', '$icon', '$status', $order_position)";
            $message = '<div class="alert alert-success">Service ajouté avec succès.</div>';
        }

        if ($conn->query($sql) === TRUE) {
            header("Location: dashboard.php");
            exit();
        } else {
            $message = '<div class="alert alert-error">Erreur: ' . $conn->error . '</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $id > 0 ? 'Éditer' : 'Ajouter'; ?> Service - Congometal Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; }
        .container { max-width: 600px; margin: 2rem auto; padding: 2rem; background: white; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h1 { margin-bottom: 1.5rem; color: #333; }
        .form-group { margin-bottom: 1.5rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: #333; }
        input, textarea, select { width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px; font-family: inherit; font-size: 1rem; }
        input:focus, textarea:focus, select:focus { outline: none; border-color: #667eea; box-shadow: 0 0 5px rgba(102,126,234,0.3); }
        textarea { resize: vertical; min-height: 150px; }
        button { background: #667eea; color: white; padding: 0.8rem 1.5rem; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
        button:hover { background: #764ba2; }
        .alert { padding: 1rem; margin-bottom: 1rem; border-radius: 5px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        a { color: #667eea; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo $id > 0 ? 'Éditer le service' : 'Ajouter un service'; ?></h1>
        <?php echo $message; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="title">Titre *</label>
                <input type="text" id="title" name="title" value="<?php echo $service ? htmlspecialchars($service['title']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"><?php echo $service ? htmlspecialchars($service['description']) : ''; ?></textarea>
            </div>
            <div class="form-group">
                <label for="icon">Icône (emoji ou unicode)</label>
                <input type="text" id="icon" name="icon" value="<?php echo $service ? htmlspecialchars($service['icon']) : '⚙️'; ?>" maxlength="10">
            </div>
            <div class="form-group">
                <label for="order_position">Position</label>
                <input type="number" id="order_position" name="order_position" value="<?php echo $service ? $service['order_position'] : '0'; ?>">
            </div>
            <div class="form-group">
                <label for="status">Statut</label>
                <select id="status" name="status">
                    <option value="active" <?php echo $service && $service['status'] === 'active' ? 'selected' : ''; ?>>Actif</option>
                    <option value="inactive" <?php echo $service && $service['status'] === 'inactive' ? 'selected' : ''; ?>>Inactif</option>
                </select>
            </div>
            <button type="submit">Enregistrer</button>
            <a href="dashboard.php" style="margin-left: 1rem;">Annuler</a>
        </form>
    </div>
</body>
</html>

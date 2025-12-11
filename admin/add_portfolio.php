<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkAuth();
if (!isAdmin()) header("Location: login.php");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$portfolio = null;

if ($id > 0) {
    $result = $conn->query("SELECT * FROM portfolio WHERE id = $id");
    $portfolio = $result->fetch_assoc();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = escape_input($_POST['title']);
    $description = escape_input($_POST['description']);
    $category = escape_input($_POST['category']);
    $client = escape_input($_POST['client']);
    $project_url = escape_input($_POST['project_url']);
    $status = escape_input($_POST['status']);
    $image = $portfolio['image'] ?? '';

    // Gestion de l'upload d'image
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $upload_result = uploadImage($_FILES['image'], 'portfolio');
        if (is_array($upload_result) && isset($upload_result['error'])) {
            $message = '<div class="alert alert-error">' . $upload_result['error'] . '</div>';
        } else {
            $image = $upload_result;
        }
    }

    if (empty($title)) {
        $message = '<div class="alert alert-error">Veuillez remplir tous les champs requis.</div>';
    } else {
        if ($id > 0) {
            $sql = "UPDATE portfolio SET title='$title', description='$description', category='$category', client='$client', project_url='$project_url', status='$status', image='$image' WHERE id=$id";
            $message = '<div class="alert alert-success">Projet mis à jour avec succès.</div>';
        } else {
            $sql = "INSERT INTO portfolio (title, description, category, client, project_url, status, image) VALUES ('$title', '$description', '$category', '$client', '$project_url', '$status', '$image')";
            $message = '<div class="alert alert-success">Projet ajouté avec succès.</div>';
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
    <title><?php echo $id > 0 ? 'Éditer' : 'Ajouter'; ?> Projet - Congometal Admin</title>
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
        .image-preview { max-width: 200px; margin: 1rem 0; }
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
        <h1><?php echo $id > 0 ? 'Éditer le projet' : 'Ajouter un projet'; ?></h1>
        <?php echo $message; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Titre *</label>
                <input type="text" id="title" name="title" value="<?php echo $portfolio ? htmlspecialchars($portfolio['title']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"><?php echo $portfolio ? htmlspecialchars($portfolio['description']) : ''; ?></textarea>
            </div>
            <div class="form-group">
                <label for="category">Catégorie</label>
                <input type="text" id="category" name="category" value="<?php echo $portfolio ? htmlspecialchars($portfolio['category']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="client">Client</label>
                <input type="text" id="client" name="client" value="<?php echo $portfolio ? htmlspecialchars($portfolio['client']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="project_url">URL du projet</label>
                <input type="url" id="project_url" name="project_url" value="<?php echo $portfolio ? htmlspecialchars($portfolio['project_url']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="image">Image</label>
                <input type="file" id="image" name="image" accept="image/*">
                <?php if ($portfolio && $portfolio['image']): ?>
                    <img src="<?php echo SITE_URL; ?>/admin/uploads/<?php echo $portfolio['image']; ?>" alt="Image" class="image-preview">
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="status">Statut</label>
                <select id="status" name="status">
                    <option value="active" <?php echo $portfolio && $portfolio['status'] === 'active' ? 'selected' : ''; ?>>Actif</option>
                    <option value="inactive" <?php echo $portfolio && $portfolio['status'] === 'inactive' ? 'selected' : ''; ?>>Inactif</option>
                </select>
            </div>
            <button type="submit">Enregistrer</button>
            <a href="dashboard.php" style="margin-left: 1rem;">Annuler</a>
        </form>
    </div>
</body>
</html>

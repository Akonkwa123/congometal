<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkAuth();
if (!isAdmin()) header("Location: login.php");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$testimonial = null;

if ($id > 0) {
    $result = $conn->query("SELECT * FROM testimonials WHERE id = $id");
    $testimonial = $result->fetch_assoc();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = escape_input($_POST['name']);
    $position = escape_input($_POST['position']);
    $company = escape_input($_POST['company']);
    $message_text = escape_input($_POST['message']);
    $rating = intval($_POST['rating']);
    $status = escape_input($_POST['status']);

    // Champs média
    $media_type = escape_input($_POST['media_type'] ?? '');
    $media_title = escape_input($_POST['media_title'] ?? '');
    $media_caption = escape_input($_POST['media_caption'] ?? '');

    $image = $testimonial['image'] ?? '';
    $media_path = $testimonial['media_path'] ?? null;

    // Gestion de l'upload de média (input name="media")
    if (isset($_FILES['media']) && $_FILES['media']['error'] !== UPLOAD_ERR_NO_FILE) {
        $allowed = [];
        if ($media_type === 'image') {
            $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
        } elseif ($media_type === 'video') {
            $allowed = ['video/mp4','video/webm','video/ogg'];
        } elseif ($media_type === 'audio') {
            $allowed = ['audio/mpeg','audio/ogg','audio/wav'];
        } elseif ($media_type === 'document') {
            $allowed = ['application/pdf','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        }

        $upload_result = uploadMedia($_FILES['media'], 'testimonials', $allowed);
        if (is_array($upload_result) && isset($upload_result['error'])) {
            $message = '<div class="alert alert-error">' . $upload_result['error'] . '</div>';
        } else {
            if (!empty($media_path)) deleteImage($media_path);
            $media_path = $upload_result;
        }
    }

    if (empty($name) || empty($message_text)) {
        $message = '<div class="alert alert-error">Veuillez remplir tous les champs requis.</div>';
    } else {
        if ($id > 0) {
            $sql = "UPDATE testimonials SET name='$name', position='$position', company='$company', message='$message_text', rating=$rating, status='$status', media_type='".$media_type."', media_path='".$media_path."', media_title='".$media_title."', media_caption='".$media_caption."' WHERE id=$id";
            $message = '<div class="alert alert-success">Témoignage mis à jour avec succès.</div>';
        } else {
            $sql = "INSERT INTO testimonials (name, position, company, message, rating, status, media_type, media_path, media_title, media_caption) VALUES ('$name', '$position', '$company', '$message_text', $rating, '$status', '".$media_type."', '".$media_path."', '".$media_title."', '".$media_caption."')";
            $message = '<div class="alert alert-success">Témoignage ajouté avec succès.</div>';
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
    <title><?php echo $id > 0 ? 'Éditer' : 'Ajouter'; ?> Témoignage - Congometal Admin</title>
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
        .image-preview { max-width: 200px; margin: 1rem 0; border-radius: 50%; }
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
        <h1><?php echo $id > 0 ? 'Éditer le témoignage' : 'Ajouter un témoignage'; ?></h1>
        <?php echo $message; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Nom *</label>
                <input type="text" id="name" name="name" value="<?php echo $testimonial ? htmlspecialchars($testimonial['name']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="position">Poste</label>
                <input type="text" id="position" name="position" value="<?php echo $testimonial ? htmlspecialchars($testimonial['position']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="company">Entreprise</label>
                <input type="text" id="company" name="company" value="<?php echo $testimonial ? htmlspecialchars($testimonial['company']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="message">Message *</label>
                <textarea id="message" name="message" required><?php echo $testimonial ? htmlspecialchars($testimonial['message']) : ''; ?></textarea>
            </div>
            <div class="form-group">
                <label for="rating">Note (1-5)</label>
                <select id="rating" name="rating">
                    <option value="5" <?php echo $testimonial && $testimonial['rating'] == 5 ? 'selected' : 'selected'; ?>>★★★★★ 5 étoiles</option>
                    <option value="4" <?php echo $testimonial && $testimonial['rating'] == 4 ? 'selected' : ''; ?>>★★★★☆ 4 étoiles</option>
                    <option value="3" <?php echo $testimonial && $testimonial['rating'] == 3 ? 'selected' : ''; ?>>★★★☆☆ 3 étoiles</option>
                    <option value="2" <?php echo $testimonial && $testimonial['rating'] == 2 ? 'selected' : ''; ?>>★★☆☆☆ 2 étoiles</option>
                    <option value="1" <?php echo $testimonial && $testimonial['rating'] == 1 ? 'selected' : ''; ?>>★☆☆☆☆ 1 étoile</option>
                </select>
            </div>
            <div class="form-group">
                <label for="media_type">Type de média</label>
                <select id="media_type" name="media_type">
                    <option value="">Aucun</option>
                    <option value="image" <?php echo $testimonial && $testimonial['media_type']==='image' ? 'selected' : ''; ?>>Image</option>
                    <option value="video" <?php echo $testimonial && $testimonial['media_type']==='video' ? 'selected' : ''; ?>>Vidéo</option>
                    <option value="audio" <?php echo $testimonial && $testimonial['media_type']==='audio' ? 'selected' : ''; ?>>Audio</option>
                    <option value="document" <?php echo $testimonial && $testimonial['media_type']==='document' ? 'selected' : ''; ?>>Document</option>
                </select>
            </div>

            <div class="form-group">
                <label for="media">Fichier média (image, vidéo, audio, document)</label>
                <input type="file" id="media" name="media" accept="image/*,video/*,audio/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
                <?php if ($testimonial && !empty($testimonial['media_path'])): ?>
                    <div style="margin-top:0.8rem;">
                        <?php if ($testimonial['media_type'] === 'image'): ?>
                            <img src="<?php echo SITE_URL; ?>/admin/uploads/<?php echo htmlspecialchars($testimonial['media_path']); ?>" alt="Media" class="image-preview">
                        <?php else: ?>
                            <a href="<?php echo SITE_URL; ?>/admin/uploads/<?php echo htmlspecialchars($testimonial['media_path']); ?>" target="_blank">Voir le média existant</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="media_title">Titre du média</label>
                <input type="text" id="media_title" name="media_title" value="<?php echo $testimonial ? htmlspecialchars($testimonial['media_title'] ?? '') : ''; ?>">
            </div>

            <div class="form-group">
                <label for="media_caption">Légende / description du média</label>
                <textarea id="media_caption" name="media_caption"><?php echo $testimonial ? htmlspecialchars($testimonial['media_caption'] ?? '') : ''; ?></textarea>
            </div>

            <div class="form-group">
                <label for="status">Statut</label>
                <select id="status" name="status">
                    <option value="active" <?php echo $testimonial && $testimonial['status'] === 'active' ? 'selected' : 'selected'; ?>>Actif</option>
                    <option value="inactive" <?php echo $testimonial && $testimonial['status'] === 'inactive' ? 'selected' : ''; ?>>Inactif</option>
                </select>
            </div>
            <button type="submit">Enregistrer</button>
            <a href="dashboard.php" style="margin-left: 1rem;">Annuler</a>
        </form>
    </div>
</body>
</html>

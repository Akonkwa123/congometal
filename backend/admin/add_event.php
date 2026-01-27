<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkAuth();
if (!isAdmin()) header("Location: login.php");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$event = null;
$event_media = [];
$message = '';

if ($id > 0) {
    $result = $conn->query("SELECT * FROM events WHERE id = $id");
    if ($result && $result->num_rows > 0) {
        $event = $result->fetch_assoc();
        $media_result = $conn->query("SELECT * FROM event_media WHERE event_id = $id ORDER BY created_at ASC");
        if ($media_result && $media_result->num_rows > 0) {
            while ($row = $media_result->fetch_assoc()) {
                $event_media[] = $row;
            }
        }
    } else {
        header("Location: dashboard.php");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = escape_input($_POST['title'] ?? '');
    $description = escape_input($_POST['description'] ?? '');
    $event_date = escape_input($_POST['event_date'] ?? '');
    $location = escape_input($_POST['location'] ?? '');
    $start_time = escape_input($_POST['start_time'] ?? '');
    $end_time = escape_input($_POST['end_time'] ?? '');
    $status = escape_input($_POST['status'] ?? 'active');

    if (empty($title)) {
        $message = '<div class="alert alert-error">Veuillez renseigner le titre de l\'evenement.</div>';
    } else {
        $event_date_sql = $event_date ? "'$event_date'" : "NULL";
        $start_time_sql = $start_time ? "'$start_time'" : "NULL";
        $end_time_sql = $end_time ? "'$end_time'" : "NULL";

        if ($id > 0) {
            $sql = "UPDATE events SET title='$title', description='$description', event_date=$event_date_sql, location='$location',
                    start_time=$start_time_sql, end_time=$end_time_sql, status='$status', updated_at=NOW() WHERE id=$id";
        } else {
            $sql = "INSERT INTO events (title, description, event_date, location, start_time, end_time, status, created_at)
                    VALUES ('$title', '$description', $event_date_sql, '$location', $start_time_sql, $end_time_sql, '$status', NOW())";
        }

        if ($conn->query($sql) === TRUE) {
            if ($id === 0) {
                $id = $conn->insert_id;
            }

            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'video/mp4', 'video/webm', 'video/ogg', 'audio/mpeg', 'audio/ogg', 'audio/wav'];
            if (isset($_FILES['media_files']) && !empty($_FILES['media_files']['name'][0])) {
                $files_count = count($_FILES['media_files']['name']);
                for ($i = 0; $i < $files_count; $i++) {
                    if ($_FILES['media_files']['error'][$i] === UPLOAD_ERR_NO_FILE) {
                        continue;
                    }
                    if ($_FILES['media_files']['error'][$i] !== UPLOAD_ERR_OK) {
                        $message = '<div class="alert alert-error">Erreur lors du telechargement d\'un fichier.</div>';
                        break;
                    }

                    $file = [
                        'name' => $_FILES['media_files']['name'][$i],
                        'type' => $_FILES['media_files']['type'][$i],
                        'tmp_name' => $_FILES['media_files']['tmp_name'][$i],
                        'error' => $_FILES['media_files']['error'][$i],
                        'size' => $_FILES['media_files']['size'][$i]
                    ];

                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $file['tmp_name']);
                    finfo_close($finfo);

                    if (strpos($mime, 'video/') === 0) {
                        $media_type = 'video';
                    } elseif (strpos($mime, 'audio/') === 0) {
                        $media_type = 'audio';
                    } else {
                        $media_type = 'image';
                    }

                    $upload_result = uploadMedia($file, 'events', $allowed, MAX_EVENT_MEDIA_SIZE);
                    if (is_array($upload_result) && isset($upload_result['error'])) {
                        $message = '<div class="alert alert-error">' . $upload_result['error'] . '</div>';
                        break;
                    }
                    $media_path = escape_input($upload_result);

                    $conn->query("INSERT INTO event_media (event_id, media_type, media_path) VALUES ($id, '$media_type', '$media_path')");
                }
            }

            if (empty($message)) {
                header("Location: dashboard.php");
                exit();
            }
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
    <title><?php echo $id > 0 ? 'Editer' : 'Ajouter'; ?> evenement - Congometal Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; }
        .container { max-width: 720px; margin: 2rem auto; padding: 2rem; background: white; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h1 { margin-bottom: 1.5rem; color: #333; }
        .form-group { margin-bottom: 1.5rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: #333; }
        input, textarea, select { width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px; font-family: inherit; font-size: 1rem; }
        input:focus, textarea:focus, select:focus { outline: none; border-color: #667eea; box-shadow: 0 0 5px rgba(102,126,234,0.3); }
        textarea { resize: vertical; min-height: 140px; }
        button { background: #667eea; color: white; padding: 0.8rem 1.5rem; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
        button:hover { background: #764ba2; }
        .alert { padding: 1rem; margin-bottom: 1rem; border-radius: 5px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .media-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; margin-top: 0.8rem; }
        .media-item { border: 1px solid #e5e7eb; border-radius: 6px; padding: 0.5rem; text-align: center; }
        .media-item img, .media-item video { max-width: 100%; border-radius: 4px; }
        .media-item a { display: inline-block; margin-top: 0.5rem; color: #ef4444; text-decoration: none; }
        .media-item a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo $id > 0 ? 'Editer un evenement' : 'Ajouter un evenement'; ?></h1>
        <?php echo $message; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Titre *</label>
                <input type="text" id="title" name="title" value="<?php echo $event ? htmlspecialchars($event['title']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="description">Texte / description</label>
                <textarea id="description" name="description"><?php echo $event ? htmlspecialchars($event['description']) : ''; ?></textarea>
            </div>
            <div class="form-group">
                <label for="event_date">Date</label>
                <input type="date" id="event_date" name="event_date" value="<?php echo $event ? htmlspecialchars($event['event_date']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="location">Lieu</label>
                <input type="text" id="location" name="location" value="<?php echo $event ? htmlspecialchars($event['location']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="start_time">Heure debut</label>
                <input type="time" id="start_time" name="start_time" value="<?php echo $event ? htmlspecialchars($event['start_time']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="end_time">Heure fin</label>
                <input type="time" id="end_time" name="end_time" value="<?php echo $event ? htmlspecialchars($event['end_time']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="media_files">Photos / videos / audio (multiple)</label>
                <input type="file" id="media_files" name="media_files[]" accept="image/*,video/*,audio/*" multiple>
            </div>

            <?php if (!empty($event_media)): ?>
                <div class="form-group">
                    <label>Medias existants</label>
                    <div class="media-grid">
                        <?php foreach ($event_media as $media): ?>
                            <div class="media-item">
                                <?php if ($media['media_type'] === 'image'): ?>
                                    <img src="<?php echo SITE_URL; ?>/admin/uploads/<?php echo htmlspecialchars($media['media_path']); ?>" alt="Media">
                                <?php else: ?>
                                    <video controls>
                                        <source src="<?php echo SITE_URL; ?>/admin/uploads/<?php echo htmlspecialchars($media['media_path']); ?>" type="video/mp4">
                                    </video>
                                <?php endif; ?>
                                <a href="delete_event_media.php?id=<?php echo $media['id']; ?>&event_id=<?php echo $id; ?>" onclick="return confirm('Supprimer ce media ?');">Supprimer</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="status">Statut</label>
                <select id="status" name="status">
                    <option value="active" <?php echo $event && $event['status'] === 'active' ? 'selected' : 'selected'; ?>>Actif</option>
                    <option value="inactive" <?php echo $event && $event['status'] === 'inactive' ? 'selected' : ''; ?>>Inactif</option>
                </select>
            </div>
            <button type="submit">Enregistrer</button>
            <a href="dashboard.php" style="margin-left: 1rem;">Annuler</a>
        </form>
    </div>
</body>
</html>

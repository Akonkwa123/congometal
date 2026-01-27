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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
        .icon-picker-toggle { margin-top: 0.5rem; background: #e5e7eb; color: #111827; }
        .icon-grid { margin-top: 0.6rem; display: grid; grid-template-columns: repeat(auto-fill, minmax(44px, 1fr)); gap: 0.5rem; border: 1px solid #ddd; border-radius: 6px; padding: 0.6rem; max-height: 220px; overflow: auto; }
        .icon-choice { width: 44px; height: 44px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #e5e7eb; border-radius: 8px; background: #fff; cursor: pointer; }
        .icon-choice:hover { border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,0.15); }
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
                <label for="icon">Icône</label>
                <input type="text" id="icon" name="icon" value="<?php echo $service ? htmlspecialchars($service['icon']) : '⚙️'; ?>" maxlength="50" list="icon-list" placeholder="bi-gear-fill ou emoji">
                <datalist id="icon-list">
                    <option value="bi-gear-fill"></option>
                    <option value="bi-tools"></option>
                    <option value="bi-wrench"></option>
                    <option value="bi-lightbulb"></option>
                    <option value="bi-diagram-3"></option>
                    <option value="bi-mortarboard"></option>
                    <option value="bi-search"></option>
                    <option value="bi-code-slash"></option>
                    <option value="bi-fire"></option>
                    <option value="bi-building"></option>
                    <option value="bi-cpu"></option>
                    <option value="bi-hammer"></option>
                    <option value="bi-nut"></option>
                    <option value="bi-lightning-charge"></option>
                    <option value="bi-plug"></option>
                    <option value="bi-robot"></option>
                    <option value="bi-wind"></option>
                    <option value="bi-droplet"></option>
                    <option value="bi-pipeline"></option>
                    <option value="bi-award"></option>
                    <option value="bi-shield-lock"></option>
                </datalist>
                <div id="iconPreview" style="margin-top:0.5rem;font-size:1.6rem;"></div>
                <button type="button" id="toggleIconList" class="icon-picker-toggle">Afficher la liste des icônes</button>
                <div id="iconGrid" class="icon-grid" style="display:none;">
                    <button type="button" class="icon-choice" data-value="bi-gear-fill"><i class="bi bi-gear-fill"></i></button>
                    <button type="button" class="icon-choice" data-value="bi-tools"><i class="bi bi-tools"></i></button>
                    <button type="button" class="icon-choice" data-value="bi-wrench"><i class="bi bi-wrench"></i></button>
                    <button type="button" class="icon-choice" data-value="bi-lightbulb"><i class="bi bi-lightbulb"></i></button>
                    <button type="button" class="icon-choice" data-value="bi-diagram-3"><i class="bi bi-diagram-3"></i></button>
                    <button type="button" class="icon-choice" data-value="bi-mortarboard"><i class="bi bi-mortarboard"></i></button>
                    <button type="button" class="icon-choice" data-value="bi-search"><i class="bi bi-search"></i></button>
                    <button type="button" class="icon-choice" data-value="bi-code-slash"><i class="bi bi-code-slash"></i></button>
                    <button type="button" class="icon-choice" data-value="bi-fire"><i class="bi bi-fire"></i></button>
                    <button type="button" class="icon-choice" data-value="bi-building"><i class="bi bi-building"></i></button>
                    <button type="button" class="icon-choice" data-value="bi-cpu"><i class="bi bi-cpu"></i></button>
                    <button type="button" class="icon-choice" data-value="bi-hammer"><i class="bi bi-hammer"></i></button>
                    <button type="button" class="icon-choice" data-value="bi-nut"><i class="bi bi-nut"></i></button>
                    <button type="button" class="icon-choice" data-value="bi-lightning-charge"><i class="bi bi-lightning-charge"></i></button>
                    <button type="button" class="icon-choice" data-value="bi-plug"><i class="bi bi-plug"></i></button>
                    <button type="button" class="icon-choice" data-value="bi-robot"><i class="bi bi-robot"></i></button>
                    <button type="button" class="icon-choice" data-value="bi-wind"><i class="bi bi-wind"></i></button>
                    <button type="button" class="icon-choice" data-value="bi-droplet"><i class="bi bi-droplet"></i></button>
                    <button type="button" class="icon-choice" data-value="bi-pipeline"><i class="bi bi-pipeline"></i></button>
                    <button type="button" class="icon-choice" data-value="bi-award"><i class="bi bi-award"></i></button>
                    <button type="button" class="icon-choice" data-value="bi-shield-lock"><i class="bi bi-shield-lock"></i></button>
                    <button type="button" class="icon-choice" data-value="bi-truck"><i class="bi bi-truck"></i></button>
                    <button type="button" class="icon-choice" data-value="bi-globe"><i class="bi bi-globe"></i></button>
                    <button type="button" class="icon-choice" data-value="bi-gear"><i class="bi bi-gear"></i></button>
                    <button type="button" class="icon-choice" data-value="bi-clipboard-check"><i class="bi bi-clipboard-check"></i></button>
                    <button type="button" class="icon-choice" data-value="bi-check2-circle"><i class="bi bi-check2-circle"></i></button>
                    <button type="button" class="icon-choice" data-value="bi-cash-stack"><i class="bi bi-cash-stack"></i></button>
                    <button type="button" class="icon-choice" data-value="bi-cast"><i class="bi bi-cast"></i></button>
                    <button type="button" class="icon-choice" data-value="bi-cloud-download"><i class="bi bi-cloud-download"></i></button>
                    <button type="button" class="icon-choice" data-value="bi-cloud-upload"><i class="bi bi-cloud-upload"></i></button>
                    <button type="button" class="icon-choice" data-value="bi-compass"><i class="bi bi-compass"></i></button>
                    <button type="button" class="icon-choice" data-value="bi-cone-striped"><i class="bi bi-cone-striped"></i></button>
                    <button type="button" class="icon-choice" data-value="bi-briefcase"><i class="bi bi-briefcase"></i></button>
                    <button type="button" class="icon-choice" data-value="bi-box-seam"><i class="bi bi-box-seam"></i></button>
                    <button type="button" class="icon-choice" data-value="bi-battery-charging"><i class="bi bi-battery-charging"></i></button>
                    <button type="button" class="icon-choice" data-value="bi-bezier"><i class="bi bi-bezier"></i></button>
                    <button type="button" class="icon-choice" data-value="bi-bounding-box"><i class="bi bi-bounding-box"></i></button>
                    <button type="button" class="icon-choice" data-value="bi-bug"><i class="bi bi-bug"></i></button>
                    <button type="button" class="icon-choice" data-value="bi-building-check"><i class="bi bi-building-check"></i></button>
                    <button type="button" class="icon-choice" data-value="bi-camera"><i class="bi bi-camera"></i></button>
                    <button type="button" class="icon-choice" data-value="bi-shield"><i class="bi bi-shield"></i></button>
                    <button type="button" class="icon-choice" data-value="⚙️">⚙️</button>
                    <button type="button" class="icon-choice" data-value="🔧">🔧</button>
                    <button type="button" class="icon-choice" data-value="💡">💡</button>
                </div>
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
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var input = document.getElementById('icon');
        var preview = document.getElementById('iconPreview');
        function render(val) {
            var v = (val || '').trim();
            if (!v) { v = '⚙️'; }
            if (v.startsWith('bi-')) {
                preview.innerHTML = '<i class="bi ' + v + '"></i>';
            } else {
                preview.textContent = v;
            }
        }
        render(input.value);
        input.addEventListener('input', function(){ render(input.value); });
        var toggle = document.getElementById('toggleIconList');
        var grid = document.getElementById('iconGrid');
        if (toggle && grid) {
            toggle.addEventListener('click', function(){
                grid.style.display = (grid.style.display === 'none' || grid.style.display === '') ? 'grid' : 'none';
            });
            grid.addEventListener('click', function(e){
                var btn = e.target.closest('.icon-choice');
                if (!btn) return;
                var val = btn.getAttribute('data-value') || '';
                input.value = val;
                render(val);
            });
        }
    });
    </script>
</body>
</html>

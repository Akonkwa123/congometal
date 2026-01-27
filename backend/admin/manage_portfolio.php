<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkAuth();
if (!isAdmin()) header("Location: login.php");

$message = '';
$error = '';

// Afficher le portfolio existant
$portfolio_result = $conn->query("SELECT * FROM portfolio ORDER BY id DESC");
$portfolio_items = [];
if ($portfolio_result && $portfolio_result->num_rows > 0) {
    while ($row = $portfolio_result->fetch_assoc()) {
        $portfolio_items[] = $row;
    }
}

// Traiter l'upload/création
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add' || $_POST['action'] === 'edit') {
        $title = escape_input($_POST['title'] ?? '');
        $description = escape_input($_POST['description'] ?? '');
        $category = escape_input($_POST['category'] ?? '');
        $client = escape_input($_POST['client'] ?? '');
        $project_url = escape_input($_POST['project_url'] ?? '');
        $status = escape_input($_POST['status'] ?? 'active');

        if (empty($title)) {
            $error = 'Le titre est obligatoire.';
        } else {
            if ($_POST['action'] === 'add') {
                // Ajouter nouveau projet
                $image_path = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $upload_result = uploadImage($_FILES['image'], 'portfolio');
                    if (is_array($upload_result) && isset($upload_result['error'])) {
                        $error = $upload_result['error'];
                    } else {
                        $image_path = escape_input($upload_result);
                    }
                }

                if (empty($error)) {
                    $sql = "INSERT INTO portfolio (title, description, image, category, client, project_url, status, created_at)
                            VALUES ('$title', '$description', '$image_path', '$category', '$client', '$project_url', '$status', NOW())";
                    if ($conn->query($sql)) {
                        $message = '✓ Projet ajouté avec succès!';
                        header("Refresh:1; url=manage_portfolio.php");
                    } else {
                        $error = 'Erreur lors de l\'insertion en base de données.';
                    }
                }
            } else {
                // Éditer projet existant
                $id = intval($_POST['edit_id'] ?? 0);
                if ($id > 0) {
                    $sql = "UPDATE portfolio SET title='$title', description='$description', category='$category', 
                            client='$client', project_url='$project_url', status='$status', updated_at=NOW() WHERE id=$id";
                    
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        $upload_result = uploadImage($_FILES['image'], 'portfolio');
                        if (is_array($upload_result) && isset($upload_result['error'])) {
                            $error = $upload_result['error'];
                        } else {
                            // Supprimer l'ancienne image
                            $old_result = $conn->query("SELECT image FROM portfolio WHERE id=$id");
                            if ($old_result && $old_row = $old_result->fetch_assoc()) {
                                deleteImage($old_row['image']);
                            }
                            $new_image = escape_input($upload_result);
                            $sql = "UPDATE portfolio SET image='$new_image' WHERE id=$id";
                        }
                    }

                    if (empty($error) && $conn->query($sql)) {
                        $message = '✓ Projet mis à jour avec succès!';
                        header("Refresh:1; url=manage_portfolio.php");
                    } else if (empty($error)) {
                        $error = 'Erreur lors de la mise à jour.';
                    }
                }
            }
        }
    }
}

// Supprimer un projet
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $img_result = $conn->query("SELECT image FROM portfolio WHERE id = $delete_id");
    if ($img_result && $img_row = $img_result->fetch_assoc()) {
        if (!empty($img_row['image'])) {
            deleteImage($img_row['image']);
        }
    }
    $conn->query("DELETE FROM portfolio WHERE id = $delete_id");
    $message = '✓ Projet supprimé!';
    header("Refresh:1; url=manage_portfolio.php");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Portfolio - Congometal Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --admin-primary: #2563eb;
            --admin-primary-dark: #1e40af;
            --admin-accent: #667eea;
            --admin-bg: #f3f4f6;
            --admin-surface: #ffffff;
            --admin-border: #e5e7eb;
            --admin-text: #111827;
            --admin-muted: #6b7280;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(37, 99, 235, 0.12), transparent 55%),
                radial-gradient(circle at bottom right, rgba(129, 140, 248, 0.12), transparent 55%),
                var(--admin-bg);
            min-height: 100vh;
            padding: 0;
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

        .mobile-menu-toggle {
            position: fixed;
            right: 1.25rem;
            bottom: 1.5rem;
            z-index: 1100;
            width: 48px;
            height: 48px;
            border-radius: 999px;
            display: none;
            align-items: center;
            justify-content: center;
            border: none;
            background: linear-gradient(135deg, var(--admin-primary), var(--admin-accent));
            color: #f9fafb;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.45);
            cursor: pointer;
            transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.2s ease;
        }

        .mobile-menu-toggle i {
            font-size: 1.4rem;
        }

        .mobile-menu-toggle:hover {
            transform: translateY(-1px) scale(1.03);
            box-shadow: 0 22px 48px rgba(15, 23, 42, 0.55);
        }

        .sidebar-backdrop {
            display: none;
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
            background: linear-gradient(120deg, var(--admin-primary), var(--admin-accent));
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
            color: var(--admin-muted);
        }

        .header-user strong {
            color: var(--admin-text);
        }

        .admin-page-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .admin-page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.75rem;
        }

        .admin-page-title {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            background: linear-gradient(120deg, var(--admin-primary), var(--admin-accent));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            text-decoration: none;
            font-size: 0.9rem;
            border-radius: 999px;
            padding: 0.4rem 0.9rem;
            background: rgba(15, 23, 42, 0.04);
            color: var(--admin-primary-dark);
            border: 1px solid rgba(148, 163, 184, 0.4);
        }

        .back-link:hover {
            background: rgba(15, 23, 42, 0.08);
            text-decoration: none;
        }

        .message {
            padding: 0.9rem 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            border: 1px solid transparent;
        }

        .success {
            background: #ecfdf3;
            color: #166534;
            border-color: #bbf7d0;
        }

        .error {
            background: #fef2f2;
            color: #b91c1c;
            border-color: #fecaca;
        }

        .admin-card {
            background: var(--admin-surface);
            border-radius: 18px;
            padding: 1.6rem 1.5rem;
            box-shadow: 0 14px 35px rgba(15, 23, 42, 0.12);
            border: 1px solid rgba(148, 163, 184, 0.28);
            margin-bottom: 1.75rem;
        }

        .admin-card h2 {
            font-size: 1.1rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .admin-card h2 i {
            color: var(--admin-primary);
        }

        label {
            font-size: 0.9rem;
            color: var(--admin-muted);
        }

        input[type="text"],
        input[type="url"],
        input[type="file"],
        textarea,
        select {
            width: 100%;
            padding: 0.7rem 0.9rem;
            border-radius: 999px;
            border: 1px solid var(--admin-border);
            font-size: 0.95rem;
            margin-bottom: 1rem;
            background-color: #f9fafb;
        }

        textarea {
            border-radius: 12px;
            resize: vertical;
            min-height: 110px;
        }

        input:focus,
        textarea:focus,
        select:focus {
            outline: none;
            background-color: #ffffff;
            border-color: var(--admin-primary);
            box-shadow: 0 0 0 1px rgba(37, 99, 235, 0.4), 0 0 0 6px rgba(191, 219, 254, 0.6);
        }

        .btn-primary-admin {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: linear-gradient(135deg, var(--admin-primary), var(--admin-accent));
            color: #f9fafb;
            border-radius: 999px;
            border: none;
            padding: 0.6rem 1.4rem;
            font-size: 0.9rem;
            font-weight: 600;
            box-shadow: 0 12px 30px rgba(37, 99, 235, 0.45);
        }

        .btn-primary-admin:hover {
            color: #f9fafb;
            transform: translateY(-1px);
            box-shadow: 0 16px 34px rgba(15, 23, 42, 0.18);
        }

        .two-col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .portfolio-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-top: 1.25rem;
        }

        .portfolio-item {
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.12);
            border: 1px solid rgba(148, 163, 184, 0.28);
        }

        .portfolio-item img {
            width: 100%;
            height: 190px;
            object-fit: cover;
        }

        .portfolio-item-info {
            padding: 0.95rem 1.05rem 1.1rem;
        }

        .portfolio-item-title {
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 0.35rem;
        }

        .portfolio-item-desc {
            font-size: 0.9rem;
            color: #4b5563;
            margin-bottom: 0.35rem;
        }

        .portfolio-item-actions {
            display: flex;
            gap: 0.4rem;
            margin-top: 0.75rem;
        }

        .btn-edit,
        .btn-delete {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            border-radius: 999px;
            padding: 0.35rem 0.9rem;
            font-size: 0.8rem;
            border: none;
            text-decoration: none;
        }

        .btn-edit {
            background: rgba(37, 99, 235, 0.08);
            color: var(--admin-primary-dark);
        }

        .btn-delete {
            background: rgba(220, 38, 38, 0.08);
            color: #b91c1c;
        }

        .btn-edit:hover,
        .btn-delete:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.16);
        }

        @media (max-width: 991.98px) {
            .admin-container {
                flex-direction: column;
            }

            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                width: 260px;
                max-width: 80%;
                inset-block: auto;
                height: 100vh;
                box-shadow: 0 10px 28px rgba(15, 23, 42, 0.7);
                transform: translateX(-100%);
                transition: transform 0.26s cubic-bezier(0.22, 0.61, 0.36, 1);
                z-index: 1050;
            }

            body.sidebar-open .sidebar {
                transform: translateX(0);
            }

            .sidebar-backdrop {
                display: block;
                position: fixed;
                inset: 0;
                background: rgba(15, 23, 42, 0.55);
                opacity: 0;
                pointer-events: none;
                transition: opacity 0.2s ease;
                z-index: 1040;
            }

            body.sidebar-open .sidebar-backdrop {
                opacity: 1;
                pointer-events: auto;
            }

            .main-content {
                margin-left: 0;
                padding: 1.75rem 1.25rem 2rem;
            }

            .mobile-menu-toggle {
                display: inline-flex;
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 1.5rem 1rem;
            }

            .two-col {
                grid-template-columns: 1fr;
            }

            .admin-card {
                padding: 1.25rem 1.1rem;
            }

            header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
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
                <li><a href="manage_portfolio.php" class="nav-link active"><i class="bi bi-briefcase me-2"></i><span>Gestion Portfolio</span></a></li>
                <li><a href="manage_team.php" class="nav-link"><i class="bi bi-people me-2"></i><span>Gestion de l'équipe</span></a></li>
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

            <div class="admin-page-container">
                <div class="admin-page-header">
                    <h1 class="admin-page-title">Gestion du Portfolio</h1>
                </div>

                <?php if (!empty($message)): ?>
                    <div class="message success"><i class="bi bi-check-circle-fill me-1"></i><?php echo $message; ?></div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="message error"><i class="bi bi-exclamation-triangle-fill me-1"></i><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- Add Project Form -->
                <div class="admin-card">
            <h2><i class="bi bi-plus-square"></i><span>Ajouter un nouveau projet</span></h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                
                <div class="two-col">
                    <div>
                        <label for="title">Titre du projet *</label>
                        <input type="text" id="title" name="title" required>
                    </div>
                    <div>
                        <label for="category">Catégorie</label>
                        <input type="text" id="category" name="category" placeholder="Ex: Infrastructure, Design, etc.">
                    </div>
                </div>

                <label for="description">Description détaillée *</label>
                <textarea id="description" name="description" required placeholder="Décrivez le projet..."></textarea>

                <div class="two-col">
                    <div>
                        <label for="client">Client</label>
                        <input type="text" id="client" name="client" placeholder="Nom du client">
                    </div>
                    <div>
                        <label for="project_url">URL du projet</label>
                        <input type="url" id="project_url" name="project_url" placeholder="https://...">
                    </div>
                </div>

                <label for="image">Photo du projet</label>
                <input type="file" id="image" name="image" accept="image/*" required>

                <label for="status">Statut</label>
                <select id="status" name="status">
                    <option value="active">Actif</option>
                    <option value="inactive">Inactif</option>
                </select>

                <button type="submit" class="btn-primary-admin"><i class="bi bi-check2-circle"></i><span>Ajouter le projet</span></button>
            </form>
        </div>

        <!-- Portfolio List -->
        <div class="admin-card">
            <h2><i class="bi bi-grid-3x3-gap"></i><span>Projets actuels (<?php echo count($portfolio_items); ?>)</span></h2>
            <?php if (count($portfolio_items) > 0): ?>
                <div class="portfolio-grid">
                    <?php foreach ($portfolio_items as $item): ?>
                        <div class="portfolio-item">
                            <?php if (!empty($item['image'])): ?>
                                <?php
                                $imagePath = UPLOAD_URL . ltrim(htmlspecialchars($item['image']), '/');
                                ?>
                                <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                            <?php else: ?>
                                <div style="width: 100%; height: 180px; background: #ddd; display: flex; align-items: center; justify-content: center; color: #999;">Pas d'image</div>
                            <?php endif; ?>
                            <div class="portfolio-item-info">
                                <div class="portfolio-item-title"><?php echo htmlspecialchars($item['title']); ?></div>
                                <div class="portfolio-item-desc"><?php echo htmlspecialchars(substr($item['description'], 0, 60)); ?>...</div>
                                <?php if (!empty($item['client'])): ?>
                                    <div class="portfolio-item-desc"><strong>Client:</strong> <?php echo htmlspecialchars($item['client']); ?></div>
                                <?php endif; ?>
                                <div style="font-size: 0.85rem; color: #6b7280; margin-bottom: 0.5rem;">Statut: <?php echo $item['status']; ?></div>
                                <div class="portfolio-item-actions">
                                    <a href="?edit=<?php echo $item['id']; ?>" class="btn-edit"><i class="bi bi-pencil-square"></i><span>Éditer</span></a>
                                    <a href="?delete=<?php echo $item['id']; ?>" class="btn-delete" onclick="return confirm('Êtes-vous sûr?');"><i class="bi bi-trash"></i><span>Supprimer</span></a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-muted mb-0">Aucun projet. Ajoutez-en un !</p>
            <?php endif; ?>
        </div>
            </div>
        </div>
    </div>

    <script>
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

<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkAuth();

if (!isAdmin()) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord Admin - Congometal</title>
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
            color: var(--admin-text);
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 260px;
            background: linear-gradient(145deg, var(--admin-primary-dark), #020617);
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

        .content-section {
            display: none;
        }

        .content-section.active {
            display: block;
        }

        .content-section > h2 {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--admin-text);
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .content-section > h2::before {
            content: "";
            width: 3px;
            height: 18px;
            border-radius: 999px;
            background: linear-gradient(180deg, var(--admin-primary), var(--admin-accent));
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .card {
            background: var(--admin-surface);
            padding: 1.4rem 1.5rem;
            border-radius: 16px;
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.12);
            border: 1px solid rgba(148, 163, 184, 0.3);
        }

        #dashboard .card {
            position: relative;
            overflow: hidden;
        }

        #dashboard .card::after {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at top left, rgba(129, 140, 248, 0.18), transparent 60%);
            opacity: 0;
            transition: opacity 0.25s ease;
        }

        #dashboard .card:hover::after {
            opacity: 1;
        }

        .card h3 {
            margin: 0 0 0.5rem;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--admin-primary);
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .card h3 i {
            font-size: 1.1rem;
        }

        .card .number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--admin-text);
            margin-bottom: 0.25rem;
        }

        .card p {
            margin: 0;
            font-size: 0.9rem;
            color: var(--admin-muted);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: var(--admin-surface);
            margin-top: 1.2rem;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.12);
        }

        table thead {
            background: linear-gradient(135deg, var(--admin-primary), var(--admin-primary-dark));
            color: #f9fafb;
        }

        table th,
        table td {
            padding: 0.85rem 1.1rem;
            text-align: left;
            font-size: 0.9rem;
        }

        table th {
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-size: 0.78rem;
            border-bottom: none;
        }

        table tbody tr:nth-child(even) {
            background: #f9fafb;
        }

        table tbody tr:hover {
            background: rgba(219, 234, 254, 0.7);
        }

        table tbody td {
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
        }

        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
        }

        .action-buttons a,
        .action-buttons button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            padding: 0.4rem 0.9rem;
            font-size: 0.85rem;
            border: none;
            text-decoration: none;
            cursor: pointer;
            font-weight: 500;
            transition: transform 0.15s ease, box-shadow 0.15s ease, background-color 0.2s ease, color 0.2s ease;
        }

        .btn-add {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: #f9fafb;
            padding: 0.55rem 1.3rem;
            border-radius: 999px;
            font-size: 0.9rem;
            font-weight: 600;
            border: none;
            text-decoration: none;
            box-shadow: 0 12px 32px rgba(22, 163, 74, 0.45);
            margin-bottom: 1rem;
        }

        .btn-edit {
            background: rgba(37, 99, 235, 0.08);
            color: var(--admin-primary-dark);
        }

        .btn-delete {
            background: rgba(220, 38, 38, 0.08);
            color: #b91c1c;
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--admin-primary), var(--admin-accent));
            color: #f9fafb;
            padding: 0.7rem 1.5rem;
            border-radius: 999px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            box-shadow: 0 14px 32px rgba(37, 99, 235, 0.45);
        }

        .btn-add:hover,
        .btn-submit:hover,
        .btn-edit:hover,
        .btn-delete:hover {
            transform: translateY(-1px);
            box-shadow: 0 16px 36px rgba(15, 23, 42, 0.18);
        }

        .form-group {
            margin-bottom: 1.1rem;
        }

        label {
            display: block;
            margin-bottom: 0.35rem;
            font-weight: 500;
            font-size: 0.9rem;
            color: var(--admin-muted);
        }

        input,
        textarea,
        select {
            width: 100%;
            padding: 0.7rem 0.9rem;
            border-radius: 999px;
            border: 1px solid var(--admin-border);
            font-family: inherit;
            font-size: 0.95rem;
            background-color: #f9fafb;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
        }

        textarea {
            border-radius: 12px;
            resize: vertical;
            min-height: 150px;
        }

        input:focus,
        textarea:focus,
        select:focus {
            outline: none;
            background-color: #ffffff;
            border-color: var(--admin-primary);
            box-shadow: 0 0 0 1px rgba(37, 99, 235, 0.4), 0 0 0 6px rgba(191, 219, 254, 0.6);
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .alert {
            padding: 0.9rem 1rem;
            margin-bottom: 1rem;
            border-radius: 10px;
            border: 1px solid transparent;
            font-size: 0.9rem;
        }

        .alert-success {
            background: #ecfdf3;
            border-color: #bbf7d0;
            color: #166534;
        }

        .alert-error {
            background: #fef2f2;
            border-color: #fecaca;
            color: #b91c1c;
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
            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
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

            .main-content {
                padding: 1.25rem 1rem 1.75rem;
            }

            table {
                display: block;
                width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            table thead,
            table tbody,
            table tr,
            table th,
            table td {
                white-space: nowrap;
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
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="#" onclick="showSection('dashboard')" class="nav-link active"><i class="bi bi-speedometer2 me-2"></i><span>Tableau de bord</span></a></li>
                <li><a href="#" onclick="showSection('settings')" class="nav-link"><i class="bi bi-gear me-2"></i><span>Paramètres</span></a></li>
                <li><a href="add_about_gallery.php" class="nav-link"><i class="bi bi-images me-2"></i><span>Galerie À propos</span></a></li>
                <li><a href="manage_portfolio.php" class="nav-link"><i class="bi bi-briefcase me-2"></i><span>Gestion Portfolio</span></a></li>
                <li><a href="manage_team.php" class="nav-link"><i class="bi bi-people me-2"></i><span>Gestion de l'équipe</span></a></li>
                <li><a href="#" onclick="showSection('services')" class="nav-link"><i class="bi bi-grid me-2"></i><span>Services</span></a></li>
                <li><a href="#" onclick="showSection('portfolio')" class="nav-link"><i class="bi bi-columns-gap me-2"></i><span>Portfolio</span></a></li>
                <li><a href="#" onclick="showSection('events')" class="nav-link"><i class="bi bi-calendar-event me-2"></i><span>Evenements</span></a></li>
                <li><a href="#" onclick="showSection('contacts')" class="nav-link"><i class="bi bi-envelope-open me-2"></i><span>Contacts</span></a></li>
                <li><a href="#" onclick="showSection('users')" class="nav-link"><i class="bi bi-person-badge me-2"></i><span>Utilisateurs</span></a></li>
            </ul>
            <div class="logout">
                <a href="logout.php"><i class="bi bi-box-arrow-right me-1"></i>Déconnexion</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <header>
                <h1>Congometal - Admin Dashboard</h1>
                <div class="header-user">
                    <p>Connecté en tant que: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></p>
                </div>
            </header>

            <!-- Dashboard Section -->
            <div id="dashboard" class="content-section active">
                <h2>Tableau de bord</h2>
                <div class="dashboard-grid">
                    <?php
                    $stats = [];
                    
                    // Services
                    $result = $conn->query("SELECT COUNT(*) as count FROM services WHERE status = 'active'");
                    $stats['services'] = $result->fetch_assoc()['count'];
                    
                    // Portfolio
                    $result = $conn->query("SELECT COUNT(*) as count FROM portfolio WHERE status = 'active'");
                    $stats['portfolio'] = $result->fetch_assoc()['count'];

                    // Events
                    $result = $conn->query("SELECT COUNT(*) as count FROM events WHERE status = 'active'");
                    $stats['events'] = $result->fetch_assoc()['count'];
                    
                    // New contacts
                    $result = $conn->query("SELECT COUNT(*) as count FROM contacts WHERE status = 'new'");
                    $stats['contacts'] = $result->fetch_assoc()['count'];

                    // Gallery (About)
                    $result = $conn->query("SELECT COUNT(*) as count FROM about_gallery");
                    $stats['gallery'] = $result ? ($result->fetch_assoc()['count'] ?? 0) : 0;

                    // Team (active members)
                    $result = $conn->query("SELECT COUNT(*) as count FROM team_members WHERE is_active = 1");
                    $stats['team'] = $result ? ($result->fetch_assoc()['count'] ?? 0) : 0;
                    ?>
                    <div class="card">
                        <h3><i class="bi bi-grid-3x3-gap-fill"></i><span>Services</span></h3>
                        <div class="number"><?php echo $stats['services']; ?></div>
                        <p>Services actifs</p>
                    </div>
                    <div class="card">
                        <h3><i class="bi bi-briefcase"></i><span>Portfolio</span></h3>
                        <div class="number"><?php echo $stats['portfolio']; ?></div>
                        <p>Projets</p>
                    </div>
                    <div class="card">
                        <h3><i class="bi bi-calendar-event"></i><span>Evenements</span></h3>
                        <div class="number"><?php echo $stats['events']; ?></div>
                        <p>Evenements actifs</p>
                    </div><div class="card">
                        <h3><i class="bi bi-envelope-open"></i><span>Nouveaux contacts</span></h3>
                        <div class="number"><?php echo $stats['contacts']; ?></div>
                        <p>Messages non lus</p>
                    </div>
                    <div class="card">
                        <h3><i class="bi bi-images"></i><span>Galerie</span></h3>
                        <div class="number"><?php echo $stats['gallery']; ?></div>
                        <p>Images de la galerie</p>
                    </div>
                    <div class="card">
                        <h3><i class="bi bi-people"></i><span>Équipe</span></h3>
                        <div class="number"><?php echo $stats['team']; ?></div>
                        <p>Membres actifs</p>
                    </div>
                </div>
            </div>

            <!-- Settings Section -->
            <div id="settings" class="content-section">
                <h2>Paramètres du site</h2>
                <div id="settings-message" class="alert" style="display: none; max-width: 700px;"></div>
                <form id="settings-form" method="POST" action="save_settings.php" class="card" style="max-width: 700px;" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="site_title">Titre du site</label>
                        <input type="text" id="site_title" name="site_title" value="<?php echo htmlspecialchars(getSetting('site_title', '')); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="company_name">Nom de l'entreprise</label>
                        <input type="text" id="company_name" name="company_name" value="<?php echo htmlspecialchars(getSetting('company_name', '')); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="company_logo">Logo de l'entreprise</label>
                        <?php $logo = getSetting('company_logo', ''); ?>
                        <div style="margin-bottom:10px;">
                            <img id="logo-preview" src="<?php echo $logo ? UPLOAD_URL . ltrim($logo, '/') : ''; ?>" alt="Logo" style="max-height:60px; <?php echo $logo ? '' : 'display:none;'; ?>">
                        </div>
                        <input type="file" id="company_logo" name="company_logo" accept="image/*" onchange="previewImage(this, 'logo-preview')">
                        <small>Format: PNG, JPG, SVG, max 2Mo</small>
                    </div>
                    <div class="form-group">
                        <label for="company_logo_alt">Alt text du logo (accessibilité)</label>
                        <input type="text" id="company_logo_alt" name="company_logo_alt" value="<?php echo htmlspecialchars(getSetting('company_logo_alt', '')); ?>" placeholder="Ex: Logo de Mon Entreprise">
                    </div>
                    <div class="form-group">
                        <label for="company_description">Description de l'entreprise</label>
                        <textarea id="company_description" name="company_description"><?php echo htmlspecialchars(getSetting('company_description', '')); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="company_email">Email</label>
                        <input type="email" id="company_email" name="company_email" value="<?php echo htmlspecialchars(getSetting('company_email', '')); ?>">
                    </div>
                    <div class="form-group">
                        <label for="company_phone">Téléphone</label>
                        <input type="tel" id="company_phone" name="company_phone" value="<?php echo htmlspecialchars(getSetting('company_phone', '')); ?>">
                    </div>
                    <div class="form-group">
                        <label for="company_address">Adresse</label>
                        <input type="text" id="company_address" name="company_address" value="<?php echo htmlspecialchars(getSetting('company_address', '')); ?>">
                    </div>
                    <div class="form-group">
                        <label for="company_hours">Horaires</label>
                        <textarea id="company_hours" name="company_hours"><?php echo htmlspecialchars(getSetting('company_hours', '')); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="hero_title">Titre du héros</label>
                        <input type="text" id="hero_title" name="hero_title" value="<?php echo htmlspecialchars(getSetting('hero_title', '')); ?>">
                    </div>
                    <div class="form-group">
                        <label for="hero_subtitle">Sous-titre du héros</label>
                        <input type="text" id="hero_subtitle" name="hero_subtitle" value="<?php echo htmlspecialchars(getSetting('hero_subtitle', '')); ?>">
                    </div>
                    <div class="form-group">
                        <label for="hero_background_image">Image de fond du héros</label>
                        <?php $hero_bg = getSetting('hero_background_image', ''); ?>
                        <div style="margin-bottom:10px;">
                            <img id="hero-image-preview" src="<?php echo $hero_bg ? UPLOAD_URL . ltrim($hero_bg, '/') : ''; ?>" alt="Fond héro" style="max-height:100px; <?php echo $hero_bg ? '' : 'display:none;'; ?>">
                        </div>
                        <input type="file" id="hero_background_image" name="hero_background_image" accept="image/png,image/jpeg,image/jpg,image/svg" onchange="previewImage(this, 'hero-image-preview')">
                        <small>Image de fond pour la section héro (PNG, JPG, SVG). Max 2Mo. Recommandé : format large (1920x1080 minimum)</small>
                    </div>

                    <!-- About / À propos settings -->
                    <div class="form-group">
                        <label for="about_image">Image À propos</label>
                        <?php $about_img = getSetting('about_image', ''); ?>
                        <div style="margin-bottom:10px;">
                            <img id="about-image-preview" src="<?php echo $about_img ? UPLOAD_URL . ltrim($about_img, '/') : ''; ?>" alt="<?php echo htmlspecialchars(getSetting('about_image_alt', 'À propos')); ?>" style="max-height:100px; <?php echo $about_img ? '' : 'display:none;'; ?>">
                        </div>
                        <input type="file" id="about_image" name="about_image" accept="image/*" onchange="previewImage(this, 'about-image-preview')">
                        <input type="text" id="about_image_alt" name="about_image_alt" value="<?php echo htmlspecialchars(getSetting('about_image_alt', '')); ?>" placeholder="Texte alternatif pour l'image">
                    </div>
                    <div class="form-group">
                        <label for="about_title">Titre (À propos)</label>
                        <input type="text" id="about_title" name="about_title" value="<?php echo htmlspecialchars(getSetting('about_title', '')); ?>">
                    </div>
                    <div class="form-group">
                        <label for="about_description">Description (À propos)</label>
                        <textarea id="about_description" name="about_description"><?php echo htmlspecialchars(getSetting('about_description', '')); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="about_story">Histoire de l'entreprise</label>
                        <textarea id="about_story" name="about_story" style="min-height:200px;"><?php echo htmlspecialchars(getSetting('about_story', '')); ?></textarea>
                        <small>Racontez l'histoire et les origines de votre entreprise</small>
                    </div>
                    <div class="form-group">
                        <label for="about_objectives">Objectifs</label>
                        <textarea id="about_objectives" name="about_objectives" style="min-height:150px;"><?php echo htmlspecialchars(getSetting('about_objectives', '')); ?></textarea>
                        <small>Décrivez les objectifs principaux de votre entreprise</small>
                    </div>
                    <div class="form-group">
                        <label for="about_vision">Vision</label>
                        <textarea id="about_vision" name="about_vision" style="min-height:150px;"><?php echo htmlspecialchars(getSetting('about_vision', '')); ?></textarea>
                        <small>Quelle est la vision à long terme de votre entreprise ?</small>
                    </div>
                    <div class="form-group">
                        <label for="about_mission">Mission</label>
                        <textarea id="about_mission" name="about_mission" style="min-height:150px;"><?php echo htmlspecialchars(getSetting('about_mission', '')); ?></textarea>
                        <small>Quelle est la mission de votre entreprise ?</small>
                    </div>

                    <!-- Menu Tabs: Qui sommes-nous, Politiques, Historique -->
                    <hr style="margin:1.5rem 0;">
                    <h3 style="margin-bottom:1rem;">Menu À propos (Onglets)</h3>
                    <div class="form-group">
                        <label for="tab_who_are_we">Onglet 1 : Qui sommes-nous</label>
                        <textarea id="tab_who_are_we" name="tab_who_are_we" style="min-height:200px;"><?php echo htmlspecialchars(getSetting('tab_who_are_we', '')); ?></textarea>
                        <small>Présentez votre entreprise en détail pour cet onglet</small>
                    </div>
                    <div class="form-group">
                        <label for="tab_policies">Onglet 2 : Nos Politiques</label>
                        <textarea id="tab_policies" name="tab_policies" style="min-height:200px;"><?php echo htmlspecialchars(getSetting('tab_policies', '')); ?></textarea>
                        <small>Décrivez vos politiques (qualité, environnement, RH, etc.)</small>
                    </div>
                    <div class="form-group">
                        <label for="tab_history">Onglet 3 : Historique</label>
                        <textarea id="tab_history" name="tab_history" style="min-height:200px;"><?php echo htmlspecialchars(getSetting('tab_history', '')); ?></textarea>
                        <small>Racontez l'évolution et l'historique de votre entreprise</small>
                    </div>

                    <!-- Social media links -->
                    <hr style="margin:1.5rem 0;">
                    <h3 style="margin-bottom:1rem;">Réseaux sociaux</h3>
                    <div class="form-group">
                        <label for="social_facebook">Facebook</label>
                        <input type="text" id="social_facebook" name="social_facebook" value="<?php echo htmlspecialchars(getSetting('social_facebook', '')); ?>" placeholder="https://facebook.com/">
                    </div>
                    <div class="form-group">
                        <label for="social_twitter">Twitter</label>
                        <input type="text" id="social_twitter" name="social_twitter" value="<?php echo htmlspecialchars(getSetting('social_twitter', '')); ?>" placeholder="https://twitter.com/">
                    </div>
                    <div class="form-group">
                        <label for="social_linkedin">LinkedIn</label>
                        <input type="text" id="social_linkedin" name="social_linkedin" value="<?php echo htmlspecialchars(getSetting('social_linkedin', '')); ?>" placeholder="https://linkedin.com/">
                    </div>
                    <div class="form-group">
                        <label for="social_instagram">Instagram</label>
                        <input type="text" id="social_instagram" name="social_instagram" value="<?php echo htmlspecialchars(getSetting('social_instagram', '')); ?>" placeholder="https://instagram.com/">
                    </div>
                    <div class="form-group">
                        <label for="social_whatsapp">WhatsApp</label>
                        <input type="text" id="social_whatsapp" name="social_whatsapp" value="<?php echo htmlspecialchars(getSetting('social_whatsapp', '')); ?>" placeholder="https://wa.me/243xxxxxxxxx">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn-submit" id="save-settings-btn">
                            <span class="btn-text">Enregistrer les modifications</span>
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Services Section -->
            <div id="services" class="content-section">
                <h2>Gestion des services</h2>
                <a href="add_service.php" class="btn-add"><i class="bi bi-plus-circle me-1"></i><span>Ajouter un service</span></a>
                <table>
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Description</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("SELECT * FROM services ORDER BY order_position ASC");
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                                    <td><?php echo substr(htmlspecialchars($row['description']), 0, 50) . '...'; ?></td>
                                    <td><?php echo ucfirst($row['status']); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="edit_service.php?id=<?php echo $row['id']; ?>" class="btn-edit"><i class="bi bi-pencil-square me-1"></i><span>Éditer</span></a>
                                            <a href="delete_service.php?id=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Êtes-vous sûr?');"><i class="bi bi-trash me-1"></i><span>Supprimer</span></a>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Portfolio Section -->
            <div id="portfolio" class="content-section">
                <h2>Gestion du portfolio</h2>
                <a href="add_portfolio.php" class="btn-add"><i class="bi bi-plus-circle me-1"></i><span>Ajouter un projet</span></a>
                <table>
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Client</th>
                            <th>Catégorie</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("SELECT * FROM portfolio ORDER BY created_at DESC");
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                                    <td><?php echo htmlspecialchars($row['client']); ?></td>
                                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                                    <td><?php echo ucfirst($row['status']); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="edit_portfolio.php?id=<?php echo $row['id']; ?>" class="btn-edit"><i class="bi bi-pencil-square me-1"></i><span>Éditer</span></a>
                                            <a href="delete_portfolio.php?id=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Êtes-vous sûr?');"><i class="bi bi-trash me-1"></i><span>Supprimer</span></a>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Events Section -->
            <div id="events" class="content-section">
                <h2>Gestion des evenements</h2>
                <a href="add_event.php" class="btn-add"><i class="bi bi-plus-circle me-1"></i><span>Ajouter un evenement</span></a>
                <table>
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Date</th>
                            <th>Lieu</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("SELECT * FROM events ORDER BY event_date DESC, created_at DESC");
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $date_label = !empty($row['event_date']) ? date('d/m/Y', strtotime($row['event_date'])) : '-';
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                                    <td><?php echo $date_label; ?></td>
                                    <td><?php echo htmlspecialchars($row['location']); ?></td>
                                    <td><?php echo ucfirst($row['status']); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="edit_event.php?id=<?php echo $row['id']; ?>" class="btn-edit"><i class="bi bi-pencil-square me-1"></i><span>Editer</span></a>
                                            <a href="delete_event.php?id=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Etes-vous sur ?');"><i class="bi bi-trash me-1"></i><span>Supprimer</span></a>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <!-- Contacts Section -->
            <div id="contacts" class="content-section">
                <h2>Messages de contact</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Sujet</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("SELECT * FROM contacts ORDER BY created_at DESC");
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['subject']); ?></td>
                                    <td><?php echo ucfirst($row['status']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="view_contact.php?id=<?php echo $row['id']; ?>" class="btn-edit"><i class="bi bi-eye me-1"></i><span>Voir</span></a>
                                            <a href="delete_contact.php?id=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Êtes-vous sûr?');"><i class="bi bi-trash me-1"></i><span>Supprimer</span></a>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Users Section -->
            <div id="users" class="content-section">
                <h2>Gestion des utilisateurs</h2>
                <a href="add_user.php" class="btn-add"><i class="bi bi-plus-circle me-1"></i><span>Ajouter un utilisateur</span></a>
                <table>
                    <thead>
                        <tr>
                            <th>Nom d'utilisateur</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo ucfirst($row['role']); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="edit_user.php?id=<?php echo $row['id']; ?>" class="btn-edit"><i class="bi bi-pencil-square me-1"></i><span>Éditer</span></a>
                                            <?php if ($row['id'] !== $_SESSION['user_id']): ?>
                                                <a href="delete_user.php?id=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Êtes-vous sûr?');"><i class="bi bi-trash me-1"></i><span>Supprimer</span></a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Fonction pour prévisualiser une image avant upload
        function previewImage(inputElement, previewId) {
            const previewElement = document.getElementById(previewId);
            const selectedFile = inputElement.files[0];
            
            if (selectedFile) {
                const fileReader = new FileReader();
                
                fileReader.onload = function(event) {
                    previewElement.src = event.target.result;
                    previewElement.style.display = 'block';
                };
                
                fileReader.readAsDataURL(selectedFile);
            }
        }
        
        // Gestion du formulaire en AJAX
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('settings-form');
            const messageDiv = document.getElementById('settings-message');
            const saveBtn = document.getElementById('save-settings-btn');
            
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(form);
                    const xhr = new XMLHttpRequest();
                    
                    // Afficher le spinner et désactiver le bouton
                    saveBtn.disabled = true;
                    const spinner = saveBtn.querySelector('.spinner-border');
                    const btnText = saveBtn.querySelector('.btn-text');
                    spinner.classList.remove('d-none');
                    btnText.textContent = 'Enregistrement...';
                    
                    xhr.open('POST', 'save_settings.php', true);
                    
                    xhr.onload = function() {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            
                            // Mettre à jour l'aperçu des images si nécessaire
                            if (response.logo_path) {
                                const logoPreview = document.getElementById('logo-preview');
                                if (logoPreview) {
                                    logoPreview.src = response.logo_path;
                                    logoPreview.style.display = 'block';
                                }
                            }
                            
                            if (response.about_image_path) {
                                const aboutPreview = document.getElementById('about-image-preview');
                                if (aboutPreview) {
                                    aboutPreview.src = response.about_image_path;
                                    aboutPreview.style.display = 'block';
                                }
                            }
                            
                            if (response.hero_image_path) {
                                const heroPreview = document.getElementById('hero-image-preview');
                                if (heroPreview) {
                                    heroPreview.src = response.hero_image_path;
                                    heroPreview.style.display = 'block';
                                }
                            }
                            
                            // Afficher le message
                            if (response.message) {
                                messageDiv.textContent = response.message;
                                messageDiv.style.display = 'block';
                                
                                if (response.success) {
                                    messageDiv.className = 'alert alert-success';
                                    // Masquer le message après 5 secondes
                                    setTimeout(() => {
                                        messageDiv.style.display = 'none';
                                    }, 5000);
                                } else {
                                    messageDiv.className = 'alert alert-danger';
                                }
                            }
                            
                        } catch (e) {
                            // En cas d'erreur d'analyse JSON, rediriger normalement
                            window.location.href = 'dashboard.php?success=1';
                        }
                        
                        // Réactiver le bouton
                        saveBtn.disabled = false;
                        spinner.classList.add('d-none');
                        btnText.textContent = 'Enregistrer les modifications';
                    };
                    
                    xhr.onerror = function() {
                        messageDiv.textContent = 'Une erreur est survenue lors de l\'envoi du formulaire.';
                        messageDiv.className = 'alert alert-danger';
                        messageDiv.style.display = 'block';
                        
                        // Réactiver le bouton
                        saveBtn.disabled = false;
                        spinner.classList.add('d-none');
                        btnText.textContent = 'Enregistrer les modifications';
                    };
                    
                    xhr.send(formData);
                });
            }
        });
        
        function showSection(sectionId) {
            // Masquer tous les sections
            document.querySelectorAll('.content-section').forEach(function (el) {
                el.classList.remove('active');
            });

            // Afficher la section sélectionnée
            var section = document.getElementById(sectionId);
            if (section) {
                section.classList.add('active');
            }

            // Mettre à jour le lien de navigation actif
            document.querySelectorAll('.nav-link').forEach(function (el) {
                el.classList.remove('active');
            });

            if (typeof event !== 'undefined' && event.target) {
                event.target.classList.add('active');
            }
        }

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

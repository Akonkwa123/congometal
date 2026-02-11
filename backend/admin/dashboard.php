<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkAuth();

if (!isAdmin()) {
    header("Location: login.php");
    exit();
}

function dashboardImageUrl($path) {
    $path = trim((string)$path, " \t\n\r\0\x0B\"'");
    if ($path === '') {
        return '';
    }
    if (preg_match('~^https?://~i', $path)) {
        return $path;
    }
    return UPLOAD_URL . ltrim($path, '/');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord Admin - Congometal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
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

        .dashboard-grid .card.is-active {
            box-shadow: 0 16px 36px rgba(37, 99, 235, 0.35);
            border: 1px solid rgba(37, 99, 235, 0.6);
        }


        #dashboard .card {
            position: relative;
            overflow: hidden;
            cursor: pointer;
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

        .settings-block {
            margin-top: 1.5rem;
            padding: 1.25rem 1.5rem;
            border-radius: 16px;
            border: 1px solid rgba(148, 163, 184, 0.35);
            background: #f8fafc;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
        }

        .settings-blocks {
            display: flex;
            gap: 1.25rem;
            flex-wrap: wrap;
        }

        .settings-blocks .settings-block {
            flex: 1 1 280px;
            margin-top: 0;
        }

        @media (min-width: 1100px) {
            .settings-blocks {
                flex-wrap: nowrap;
            }
        }

        @media (max-width: 992px) {
            .settings-blocks {
                flex-direction: column;
            }
        }

        .settings-block h3 {
            margin-bottom: 1rem;
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--admin-text);
        }

        .settings-block-body {
            max-height: 280px;
            overflow: hidden;
            transition: max-height 0.25s ease;
        }

        .settings-block.is-expanded .settings-block-body {
            max-height: none;
        }

        .settings-toggle {
            margin-top: 0.75rem;
            background: none;
            border: none;
            color: var(--admin-primary);
            font-weight: 600;
            padding: 0;
            cursor: pointer;
        }

        .card p {
            margin: 0;
            font-size: 0.9rem;
            color: var(--admin-muted);
        }

        .dashboard-modal {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1200;
            padding: 1.5rem;
        }

        .dashboard-modal.active {
            display: flex;
        }

        .dashboard-modal-content {
            width: min(920px, 100%);
            max-height: 85vh;
            background: #ffffff;
            border-radius: 18px;
            border: 1px solid rgba(148, 163, 184, 0.35);
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.35);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .dashboard-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid rgba(148, 163, 184, 0.35);
        }

        .dashboard-modal-body {
            padding: 1.2rem 1.5rem 1.5rem;
            overflow-y: auto;
        }

        .image-lightbox {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.75);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1300;
            padding: 1.5rem;
        }

        .image-lightbox.active {
            display: flex;
        }

        .image-lightbox img {
            max-width: min(1100px, 96vw);
            max-height: 85vh;
            border-radius: 14px;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.45);
            background: #fff;
        }

        .image-lightbox-close {
            position: absolute;
            top: 1.25rem;
            right: 1.25rem;
            border: none;
            background: rgba(255, 255, 255, 0.95);
            width: 38px;
            height: 38px;
            border-radius: 999px;
            cursor: pointer;
            font-size: 1.1rem;
        }

        .dashboard-modal-close {
            border: none;
            background: rgba(15, 23, 42, 0.08);
            border-radius: 999px;
            width: 36px;
            height: 36px;
            cursor: pointer;
        }

        .dashboard-detail-table {
            width: 100%;
            border-collapse: collapse;
        }

        .dashboard-detail-table th,
        .dashboard-detail-table td {
            padding: 0.6rem 0.5rem;
            text-align: left;
            border-bottom: 1px solid rgba(148, 163, 184, 0.25);
            font-size: 0.92rem;
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
    <script>
        window.openDashboardModal = function (key, cardEl) {
            var modal = document.getElementById('dashboard-modal');
            var modalTitle = document.getElementById('dashboard-modal-title');
            var modalBody = document.getElementById('dashboard-modal-body');
            if (!modal || !modalTitle || !modalBody) return;
            var detail = document.querySelector('#dashboard-details [data-detail="' + key + '"]');
            var titleEl = cardEl ? cardEl.querySelector('span') : null;
            modalTitle.textContent = titleEl ? titleEl.textContent : 'Détails';
            modalBody.innerHTML = detail ? detail.innerHTML : '<p>Aucun détail disponible.</p>';
            modal.classList.add('active');
            modal.setAttribute('aria-hidden', 'false');
            modal.style.display = 'flex';
        };
    </script>
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
                <li><a href="#" onclick="showSection('event-posters')" class="nav-link"><i class="bi bi-megaphone me-2"></i><span>Affiches</span></a></li>
                <li><a href="#" onclick="showSection('contacts')" class="nav-link"><i class="bi bi-envelope-open me-2"></i><span>Contacts</span></a></li>
                <li><a href="#" onclick="showSection('testimonials')" class="nav-link"><i class="bi bi-chat-quote me-2"></i><span>Témoignages</span></a></li>
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

                    // Site visits (unique per IP and day)
                    ensureSiteVisitsTable();
                    $result = $conn->query("SELECT COUNT(*) as count FROM site_visits");
                    $stats['visitors_total'] = $result ? ($result->fetch_assoc()['count'] ?? 0) : 0;
                    $result = $conn->query("SELECT COUNT(*) as count FROM site_visits WHERE visit_date = CURDATE()");
                    $stats['visitors_today'] = $result ? ($result->fetch_assoc()['count'] ?? 0) : 0;
                    $result = $conn->query("SELECT COUNT(*) as count FROM site_visits WHERE YEARWEEK(visit_date, 1) = YEARWEEK(CURDATE(), 1)");
                    $stats['visitors_week'] = $result ? ($result->fetch_assoc()['count'] ?? 0) : 0;

                    // Gallery (About)
                    $result = $conn->query("SELECT COUNT(*) as count FROM about_gallery");
                    $stats['gallery'] = $result ? ($result->fetch_assoc()['count'] ?? 0) : 0;

                    // Team (active members)
                    $result = $conn->query("SELECT COUNT(*) as count FROM team_members WHERE is_active = 1");
                    $stats['team'] = $result ? ($result->fetch_assoc()['count'] ?? 0) : 0;

                    // Event posters
                    $result = $conn->query("SELECT COUNT(*) as count FROM event_posters");
                    $stats['posters'] = $result ? ($result->fetch_assoc()['count'] ?? 0) : 0;

                    // Testimonials (support legacy column name)
                    $testimonialStatusCol = 'status';
                    $testimonialStatusType = '';
                    $cols = [];
                    $resCols = $conn->query("SHOW COLUMNS FROM testimonials");
                    if ($resCols) {
                        while ($row = $resCols->fetch_assoc()) {
                            $cols[$row['Field']] = true;
                            if ($row['Field'] === 'status' || $row['Field'] === 'statut') {
                                $testimonialStatusType = $row['Type'];
                            }
                        }
                    }
                    if (!isset($cols['status']) && isset($cols['statut'])) {
                        $testimonialStatusCol = 'statut';
                    }
                    if (!isset($cols['status']) && !isset($cols['statut'])) {
                        $conn->query("ALTER TABLE testimonials ADD COLUMN status ENUM('new','approved','rejected') DEFAULT 'new'");
                        $testimonialStatusCol = 'status';
                        $testimonialStatusType = "enum('new','approved','rejected')";
                    }

                    $testimonialStatusMode = 'workflow';
                    if ($testimonialStatusType && stripos($testimonialStatusType, 'active') !== false && stripos($testimonialStatusType, 'inactive') !== false) {
                        $testimonialStatusMode = 'active_inactive';
                    }

                    $testimonialApprovedValue = $testimonialStatusMode === 'active_inactive' ? 'active' : 'approved';
                    $testimonialRejectedValue = $testimonialStatusMode === 'active_inactive' ? 'inactive' : 'rejected';
                    $testimonialNewValue = $testimonialStatusMode === 'active_inactive' ? 'inactive' : 'new';

                    if ($testimonialStatusMode === 'active_inactive') {
                        $result = $conn->query("SELECT COUNT(*) as count FROM testimonials WHERE `$testimonialStatusCol` = 'active'");
                        $stats['testimonials_approved'] = $result ? ($result->fetch_assoc()['count'] ?? 0) : 0;
                        $result = $conn->query("SELECT COUNT(*) as count FROM testimonials WHERE `$testimonialStatusCol` = 'inactive'");
                        $stats['testimonials_rejected'] = $result ? ($result->fetch_assoc()['count'] ?? 0) : 0;
                        $stats['testimonials_new'] = 0;
                    } else {
                        $result = $conn->query("SELECT COUNT(*) as count FROM testimonials WHERE `$testimonialStatusCol` = 'new'");
                        $stats['testimonials_new'] = $result ? ($result->fetch_assoc()['count'] ?? 0) : 0;
                        $result = $conn->query("SELECT COUNT(*) as count FROM testimonials WHERE `$testimonialStatusCol` = 'approved'");
                        $stats['testimonials_approved'] = $result ? ($result->fetch_assoc()['count'] ?? 0) : 0;
                        $result = $conn->query("SELECT COUNT(*) as count FROM testimonials WHERE `$testimonialStatusCol` = 'rejected'");
                        $stats['testimonials_rejected'] = $result ? ($result->fetch_assoc()['count'] ?? 0) : 0;
                    }
                    $result = $conn->query("SELECT COUNT(*) as count FROM testimonials");
                    $stats['testimonials_total'] = $result ? ($result->fetch_assoc()['count'] ?? 0) : 0;
                    ?>
                    <div class="card" data-card="services" onclick="openDashboardModal('services', this)">
                        <h3><i class="bi bi-grid-3x3-gap-fill"></i><span>Services</span></h3>
                        <div class="number"><?php echo $stats['services']; ?></div>
                        <p>Services actifs</p>
                    </div>
                    <div class="card" data-card="portfolio" onclick="openDashboardModal('portfolio', this)">
                        <h3><i class="bi bi-briefcase"></i><span>Portfolio</span></h3>
                        <div class="number"><?php echo $stats['portfolio']; ?></div>
                        <p>Projets</p>
                    </div>
                    <div class="card" data-card="events" onclick="openDashboardModal('events', this)">
                        <h3><i class="bi bi-calendar-event"></i><span>Evenements</span></h3>
                        <div class="number"><?php echo $stats['events']; ?></div>
                        <p>Evenements actifs</p>
                    </div>
                    <div class="card" data-card="contacts" onclick="openDashboardModal('contacts', this)">
                        <h3><i class="bi bi-envelope-open"></i><span>Nouveaux contacts</span></h3>
                        <div class="number"><?php echo $stats['contacts']; ?></div>
                        <p>Messages non lus</p>
                    </div>
                    <div class="card" data-card="visitors" onclick="openDashboardModal('visitors', this)">
                        <h3><i class="bi bi-eye"></i><span>Visiteurs</span></h3>
                        <div class="number"><?php echo $stats['visitors_total']; ?></div>
                        <p>Aujourd&apos;hui: <?php echo $stats['visitors_today']; ?></p>
                        <p>Cette semaine: <?php echo $stats['visitors_week']; ?></p>
                    </div>
                    <div class="card" data-card="gallery" onclick="openDashboardModal('gallery', this)">
                        <h3><i class="bi bi-images"></i><span>Galerie</span></h3>
                        <div class="number"><?php echo $stats['gallery']; ?></div>
                        <p>Images de la galerie</p>
                    </div>
                    <div class="card" data-card="team" onclick="openDashboardModal('team', this)">
                        <h3><i class="bi bi-people"></i><span>Équipe</span></h3>
                        <div class="number"><?php echo $stats['team']; ?></div>
                        <p>Membres actifs</p>
                    </div>
                    <div class="card" data-card="posters" onclick="openDashboardModal('posters', this)">
                        <h3><i class="bi bi-megaphone"></i><span>Affiches</span></h3>
                        <div class="number"><?php echo $stats['posters']; ?></div>
                        <p>Affiches publiées</p>
                    </div>
                    <div class="card" data-card="testimonials" onclick="openDashboardModal('testimonials', this)">
                        <h3><i class="bi bi-chat-quote"></i><span>Témoignages</span></h3>
                        <div class="number"><?php echo $stats['testimonials_total']; ?></div>
                        <p>Total: <?php echo $stats['testimonials_total']; ?></p>
                        <p>Valid&eacute;s: <?php echo $stats['testimonials_approved']; ?> &middot; Rejet&eacute;s: <?php echo $stats['testimonials_rejected']; ?></p>
                    </div>
                </div>

                <div id="dashboard-details" style="display:none;">
                    <div data-detail="services">
                        <h3>Derniers services actifs</h3>
                        <div class="action-buttons" style="margin:0 0 0.8rem;">
                            <a href="add_service.php" class="btn-add"><i class="bi bi-plus-circle me-1"></i><span>Ajouter</span></a>
                        </div>
                        <table class="dashboard-detail-table">
                            <thead><tr><th>Titre</th><th>Description</th><th>Statut</th><th>Actions</th></tr></thead>
                            <tbody>
                                <?php
                                $res = $conn->query("SELECT id, title, description, status FROM services ORDER BY updated_at DESC");
                                if ($res && $res->num_rows > 0) {
                                    while ($row = $res->fetch_assoc()) {
                                        $desc = $row['description'] ?? '';
                                        echo '<tr><td>' . htmlspecialchars($row['title']) . '</td><td>' . htmlspecialchars($desc) . '</td><td>' . htmlspecialchars($row['status']) . '</td><td><a href="edit_service.php?id=' . (int)$row['id'] . '" class="btn-edit"><i class="bi bi-pencil-square me-1"></i><span>Editer</span></a> <a href="delete_service.php?id=' . (int)$row['id'] . '" class="btn-delete" onclick="return confirm(\'Êtes-vous sûr?\');"><i class="bi bi-trash me-1"></i><span>Supprimer</span></a></td></tr>';
                                    }
                                } else {
                                    echo '<tr><td colspan=\"4\">Aucun service.</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div data-detail="portfolio">
                        <h3>Derniers projets</h3>
                        <div class="action-buttons" style="margin:0 0 0.8rem;">
                            <a href="add_portfolio.php" class="btn-add"><i class="bi bi-plus-circle me-1"></i><span>Ajouter</span></a>
                            <a href="manage_portfolio.php" class="btn-edit"><i class="bi bi-collection me-1"></i><span>Gérer</span></a>
                        </div>
                        <table class="dashboard-detail-table">
                            <thead><tr><th>Titre</th><th>Catégorie</th><th>Client</th><th>Actions</th></tr></thead>
                            <tbody>
                                <?php
                                $res = $conn->query("SELECT id, title, category, client FROM portfolio ORDER BY created_at DESC");
                                if ($res && $res->num_rows > 0) {
                                    while ($row = $res->fetch_assoc()) {
                                        echo '<tr><td>' . htmlspecialchars($row['title']) . '</td><td>' . htmlspecialchars($row['category']) . '</td><td>' . htmlspecialchars($row['client']) . '</td><td><a href="edit_portfolio.php?id=' . (int)$row['id'] . '" class="btn-edit"><i class="bi bi-pencil-square me-1"></i><span>Editer</span></a> <a href="delete_portfolio.php?id=' . (int)$row['id'] . '" class="btn-delete" onclick="return confirm(\'Êtes-vous sûr?\');"><i class="bi bi-trash me-1"></i><span>Supprimer</span></a></td></tr>';
                                    }
                                } else {
                                    echo '<tr><td colspan=\"4\">Aucun projet.</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div data-detail="events">
                        <h3>Derniers événements</h3>
                        <div class="action-buttons" style="margin:0 0 0.8rem;">
                            <a href="add_event.php" class="btn-add"><i class="bi bi-plus-circle me-1"></i><span>Ajouter</span></a>
                        </div>
                        <table class="dashboard-detail-table">
                            <thead><tr><th>Titre</th><th>Date</th><th>Lieu</th><th>Statut</th><th>Actions</th></tr></thead>
                            <tbody>
                                <?php
                                $res = $conn->query("SELECT id, title, event_date, location, status FROM events ORDER BY event_date DESC, created_at DESC");
                                if ($res && $res->num_rows > 0) {
                                    while ($row = $res->fetch_assoc()) {
                                        $date = !empty($row['event_date']) ? date('d/m/Y', strtotime($row['event_date'])) : '-';
                                        echo '<tr><td>' . htmlspecialchars($row['title']) . '</td><td>' . $date . '</td><td>' . htmlspecialchars($row['location']) . '</td><td>' . htmlspecialchars($row['status']) . '</td><td><a href="edit_event.php?id=' . (int)$row['id'] . '" class="btn-edit"><i class="bi bi-pencil-square me-1"></i><span>Editer</span></a> <a href="delete_event.php?id=' . (int)$row['id'] . '" class="btn-delete" onclick="return confirm(\'Êtes-vous sûr?\');"><i class="bi bi-trash me-1"></i><span>Supprimer</span></a></td></tr>';
                                    }
                                } else {
                                    echo '<tr><td colspan=\"5\">Aucun événement.</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div data-detail="contacts">
                        <h3>Derniers contacts</h3>
                        <table class="dashboard-detail-table">
                            <thead><tr><th>Nom</th><th>Email</th><th>Sujet</th><th>Statut</th><th>Date</th><th>Actions</th></tr></thead>
                            <tbody>
                                <?php
                                $res = $conn->query("SELECT id, name, email, subject, status, created_at FROM contacts ORDER BY created_at DESC");
                                if ($res && $res->num_rows > 0) {
                                    while ($row = $res->fetch_assoc()) {
                                        echo '<tr><td>' . htmlspecialchars($row['name']) . '</td><td>' . htmlspecialchars($row['email']) . '</td><td>' . htmlspecialchars($row['subject']) . '</td><td>' . htmlspecialchars($row['status']) . '</td><td>' . date('d/m/Y H:i', strtotime($row['created_at'])) . '</td><td><a href="view_contact.php?id=' . (int)$row['id'] . '" class="btn-edit"><i class="bi bi-eye me-1"></i><span>Voir</span></a> <a href="delete_contact.php?id=' . (int)$row['id'] . '" class="btn-delete" onclick="return confirm(\'Êtes-vous sûr?\');"><i class="bi bi-trash me-1"></i><span>Supprimer</span></a></td></tr>';
                                    }
                                } else {
                                    echo '<tr><td colspan=\"6\">Aucun contact.</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div data-detail="visitors">
                        <h3>Visiteurs rÃ©cents</h3>
                        <table class="dashboard-detail-table">
                            <thead><tr><th>Date</th><th>IP</th><th>Navigateur</th></tr></thead>
                            <tbody>
                                <?php
                                ensureSiteVisitsTable();
                                $res = $conn->query("SELECT visit_date, ip, user_agent, created_at FROM site_visits ORDER BY created_at DESC LIMIT 200");
                                if ($res && $res->num_rows > 0) {
                                    while ($row = $res->fetch_assoc()) {
                                        $date = !empty($row['visit_date']) ? date('d/m/Y', strtotime($row['visit_date'])) : '-';
                                        $ua = $row['user_agent'] ?? '';
                                        echo '<tr><td>' . $date . '</td><td>' . htmlspecialchars($row['ip']) . '</td><td>' . htmlspecialchars($ua) . '</td></tr>';
                                    }
                                } else {
                                    echo '<tr><td colspan=\"3\">Aucun visiteur.</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div data-detail="gallery">
                        <h3>Dernières images (galerie)</h3>
                        <div class="action-buttons" style="margin:0 0 0.8rem;">
                            <a href="add_about_gallery.php" class="btn-add"><i class="bi bi-plus-circle me-1"></i><span>Ajouter</span></a>
                        </div>
                        <table class="dashboard-detail-table">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Texte</th>
                                    <?php
                                    $cols = [];
                                    $colRes = $conn->query("SHOW COLUMNS FROM about_gallery");
                                    if ($colRes) {
                                        while ($col = $colRes->fetch_assoc()) {
                                            $cols[$col['Field']] = true;
                                        }
                                    }
                                    $hasDesc = isset($cols['description']);
                                    if ($hasDesc) {
                                        echo '<th>Description</th>';
                                    }
                                    ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = $hasDesc
                                    ? "SELECT image_path, image_alt, description FROM about_gallery ORDER BY created_at DESC"
                                    : "SELECT image_path, image_alt FROM about_gallery ORDER BY created_at DESC";
                                $res = $conn->query($sql);
                                if ($res && $res->num_rows > 0) {
                                    while ($row = $res->fetch_assoc()) {
                                        $thumb = htmlspecialchars(dashboardImageUrl($row['image_path'] ?? ''));
                                        $desc = $row['description'] ?? '';
                                        echo '<tr><td><img src=\"' . $thumb . '\" class=\"dashboard-zoomable\" style=\"width:60px;height:45px;object-fit:cover;border-radius:6px;cursor:zoom-in;\"></td><td>' . htmlspecialchars($row['image_alt']) . '</td>';
                                        if ($hasDesc) {
                                            echo '<td>' . htmlspecialchars($desc) . '</td>';
                                        }
                                        echo '</tr>';
                                    }
                                } else {
                                    $colspan = $hasDesc ? 3 : 2;
                                    echo '<tr><td colspan=\"' . $colspan . '\">Aucune image.</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div data-detail="team">
                        <h3>Membres de l'équipe</h3>
                        <div class="action-buttons" style="margin:0 0 0.8rem;">
                            <a href="manage_team.php" class="btn-edit"><i class="bi bi-people me-1"></i><span>Gérer</span></a>
                        </div>
                        <table class="dashboard-detail-table">
                            <thead><tr><th>Nom</th><th>Poste</th><th>Email</th><th>Téléphone</th><th>Statut</th></tr></thead>
                            <tbody>
                                <?php
                                $res = $conn->query("SELECT name, position, email, phone, is_active FROM team_members ORDER BY display_order, name");
                                if ($res && $res->num_rows > 0) {
                                    while ($row = $res->fetch_assoc()) {
                                        $status = $row['is_active'] ? 'Actif' : 'Inactif';
                                        echo '<tr><td>' . htmlspecialchars($row['name']) . '</td><td>' . htmlspecialchars($row['position']) . '</td><td>' . htmlspecialchars($row['email']) . '</td><td>' . htmlspecialchars($row['phone']) . '</td><td>' . $status . '</td></tr>';
                                    }
                                } else {
                                    echo '<tr><td colspan=\"5\">Aucun membre.</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div data-detail="posters">
                        <h3>Affiches récentes</h3>
                        <div class="action-buttons" style="margin:0 0 0.8rem;">
                            <a href="#" onclick="showSection('event-posters')" class="btn-add"><i class="bi bi-plus-circle me-1"></i><span>Ajouter</span></a>
                            <a href="#" onclick="showSection('event-posters')" class="btn-edit"><i class="bi bi-megaphone me-1"></i><span>Gérer</span></a>
                        </div>
                        <table class="dashboard-detail-table">
                            <thead><tr><th>Affiche</th><th>Titre</th><th>Ordre</th><th>Date</th><th>Actions</th></tr></thead>
                            <tbody>
                                <?php
                                $res = $conn->query("SELECT id, image_path, title, order_position, created_at FROM event_posters ORDER BY created_at DESC");
                                if ($res && $res->num_rows > 0) {
                                    while ($row = $res->fetch_assoc()) {
                                        $thumb = htmlspecialchars(dashboardImageUrl($row['image_path'] ?? ''));
                                        echo '<tr><td><img src=\"' . $thumb . '\" style=\"width:60px;height:45px;object-fit:cover;border-radius:6px;\"></td><td>' . htmlspecialchars($row['title']) . '</td><td>' . (int)$row['order_position'] . '</td><td>' . date('d/m/Y H:i', strtotime($row['created_at'])) . '</td><td><a href="delete_event_poster.php?id=' . (int)$row['id'] . '" class="btn-delete" onclick="return confirm(\'Êtes-vous sûr?\');"><i class="bi bi-trash me-1"></i><span>Supprimer</span></a></td></tr>';
                                    }
                                } else {
                                    echo '<tr><td colspan=\"5\">Aucune affiche.</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div data-detail="testimonials">
                        <h3>Témoignages récents</h3>
                        <div class="action-buttons" style="margin:0 0 0.8rem;">
                            <a href="#" onclick="showSection('testimonials')" class="btn-edit"><i class="bi bi-chat-quote me-1"></i><span>Gérer</span></a>
                        </div>
                        <table class="dashboard-detail-table">
                            <thead><tr><th>Nom</th><th>Message</th><th>Statut</th><th>Date</th><th>Actions</th></tr></thead>
                            <tbody>
                                <?php
                                $res = $conn->query("SELECT id, name, message, `$testimonialStatusCol` AS status, created_at FROM testimonials ORDER BY created_at DESC");
                                if ($res && $res->num_rows > 0) {
                                    while ($row = $res->fetch_assoc()) {
                                        $msg = $row['message'] ?? '';
                                        $actions = '';
                                        if ($row['status'] !== $testimonialApprovedValue) {
                                            $actions .= '<a href="approve_testimonial.php?id=' . (int)$row['id'] . '" class="btn-edit"><i class="bi bi-check2-circle me-1"></i><span>Valider</span></a> ';
                                        }
                                        if ($row['status'] !== $testimonialRejectedValue) {
                                            $actions .= '<a href="reject_testimonial.php?id=' . (int)$row['id'] . '" class="btn-edit"><i class="bi bi-x-circle me-1"></i><span>Rejeter</span></a> ';
                                        }
                                        $actions .= '<a href="delete_testimonial.php?id=' . (int)$row['id'] . '" class="btn-delete" onclick="return confirm(\'Êtes-vous sûr?\');"><i class="bi bi-trash me-1"></i><span>Supprimer</span></a>';
                                        echo '<tr><td>' . htmlspecialchars($row['name']) . '</td><td>' . htmlspecialchars($msg) . '</td><td>' . htmlspecialchars($row['status']) . '</td><td>' . date('d/m/Y H:i', strtotime($row['created_at'])) . '</td><td>' . $actions . '</td></tr>';
                                    }
                                } else {
                                    echo '<tr><td colspan=\"5\">Aucun témoignage.</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Settings Section -->
            <div id="settings" class="content-section">
                <h2>Paramètres du site</h2>
                <div id="settings-message" class="alert" style="display: none; max-width: 700px;"></div>
                <form id="settings-form" method="POST" action="save_settings.php" class="card" style="max-width: 100%; width: 100%;" enctype="multipart/form-data">
                    <div class="settings-blocks">
                        <div class="settings-block">
                        <h3>Informations générales</h3>
                        <div class="settings-block-body">
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
                            <img id="logo-preview" src="<?php echo $logo ? htmlspecialchars(dashboardImageUrl($logo)) : ''; ?>" alt="Logo" style="max-height:60px; <?php echo $logo ? '' : 'display:none;'; ?>">
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
                        <label for="company_phone_2">Téléphone 2</label>
                        <input type="tel" id="company_phone_2" name="company_phone_2" value="<?php echo htmlspecialchars(getSetting('company_phone_2', '')); ?>">
                        </div>
                        <div class="form-group">
                        <label for="company_phone_3">Téléphone 3</label>
                        <input type="tel" id="company_phone_3" name="company_phone_3" value="<?php echo htmlspecialchars(getSetting('company_phone_3', '')); ?>">
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
                            <img id="hero-image-preview" src="<?php echo $hero_bg ? htmlspecialchars(dashboardImageUrl($hero_bg)) : ''; ?>" alt="Fond héro" style="max-height:100px; <?php echo $hero_bg ? '' : 'display:none;'; ?>">
                        </div>
                        <input type="file" id="hero_background_image" name="hero_background_image" accept="image/png,image/jpeg,image/jpg,image/svg" onchange="previewImage(this, 'hero-image-preview')">
                        <small>Image de fond pour la section héro (PNG, JPG, SVG). Max 2Mo. Recommandé : format large (1920x1080 minimum)</small>
                        </div>
                        </div>
                        <button type="button" class="settings-toggle">Voir plus</button>
                        </div>

                        <div class="settings-block">
                        <!-- About / À propos settings -->
                        <h3>À propos</h3>
                        <div class="settings-block-body">
                        <div class="form-group">
                            <label for="about_who">Qui sommes-nous</label>
                            <textarea id="about_who" name="about_who" style="min-height:180px;"><?php echo htmlspecialchars(getSetting('about_who', '')); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="about_history">Histoire</label>
                            <textarea id="about_history" name="about_history" style="min-height:180px;"><?php echo htmlspecialchars(getSetting('about_history', '')); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="about_mission">Mission</label>
                            <textarea id="about_mission" name="about_mission" style="min-height:180px;"><?php echo htmlspecialchars(getSetting('about_mission', '')); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="about_objective">Objectif</label>
                            <textarea id="about_objective" name="about_objective" style="min-height:180px;"><?php echo htmlspecialchars(getSetting('about_objective', '')); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="about_vision">Vision</label>
                            <textarea id="about_vision" name="about_vision" style="min-height:180px;"><?php echo htmlspecialchars(getSetting('about_vision', '')); ?></textarea>
                        </div>
                        </div>
                        <button type="button" class="settings-toggle">Voir plus</button>
                        </div>

                        <div class="settings-block">
                        <!-- Values / Valeurs settings -->
                        <h3>Valeurs</h3>
                        <div class="settings-block-body">
                        <div class="form-group">
                            <label for="value_1_title">Valeur 1 - Titre</label>
                            <input type="text" id="value_1_title" name="value_1_title" value="<?php echo htmlspecialchars(getSetting('value_1_title', 'Expertise')); ?>">
                        </div>
                        <div class="form-group">
                            <label for="value_1_desc">Valeur 1 - Description</label>
                            <textarea id="value_1_desc" name="value_1_desc"><?php echo htmlspecialchars(getSetting('value_1_desc', 'Une equipe qualifiee avec des annees d experience dans le secteur industriel.')); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="value_2_title">Valeur 2 - Titre</label>
                            <input type="text" id="value_2_title" name="value_2_title" value="<?php echo htmlspecialchars(getSetting('value_2_title', 'Engagement')); ?>">
                        </div>
                        <div class="form-group">
                            <label for="value_2_desc">Valeur 2 - Description</label>
                            <textarea id="value_2_desc" name="value_2_desc"><?php echo htmlspecialchars(getSetting('value_2_desc', 'Un engagement total envers la satisfaction de nos clients.')); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="value_3_title">Valeur 3 - Titre</label>
                            <input type="text" id="value_3_title" name="value_3_title" value="<?php echo htmlspecialchars(getSetting('value_3_title', 'Innovation')); ?>">
                        </div>
                        <div class="form-group">
                            <label for="value_3_desc">Valeur 3 - Description</label>
                            <textarea id="value_3_desc" name="value_3_desc"><?php echo htmlspecialchars(getSetting('value_3_desc', 'Toujours a la pointe des dernieres technologies.')); ?></textarea>
                        </div>
                        </div>
                        <button type="button" class="settings-toggle">Voir plus</button>
                        </div>

                        <div class="settings-block">
                        <!-- Social media links -->
                        <h3>Réseaux sociaux</h3>
                        <div class="settings-block-body">
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
                        </div>
                        <button type="button" class="settings-toggle">Voir plus</button>
                        </div>
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

            <!-- Event Posters Section -->
            <div id="event-posters" class="content-section">
                <h2>Affiches des événements</h2>
                <form method="POST" action="save_event_poster.php" class="card" style="max-width: 700px;" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="poster_title">Titre (optionnel)</label>
                        <input type="text" id="poster_title" name="poster_title" placeholder="Ex: Affiche conférence 2026">
                    </div>
                    <div class="form-group">
                        <label for="poster_image">Image d'affiche</label>
                        <input type="file" id="poster_image" name="poster_image" accept="image/*" required>
                        <small>PNG, JPG, SVG. Max 2Mo.</small>
                    </div>
                    <div class="form-group">
                        <label for="poster_order">Ordre d'affichage</label>
                        <input type="number" id="poster_order" name="poster_order" value="0" min="0">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn-submit">
                            <span class="btn-text">Ajouter l'affiche</span>
                        </button>
                    </div>
                </form>
                <table>
                    <thead>
                        <tr>
                            <th>Affiche</th>
                            <th>Titre</th>
                            <th>Ordre</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $posters = $conn->query("SELECT * FROM event_posters ORDER BY order_position ASC, created_at DESC");
                        if ($posters && $posters->num_rows > 0) {
                            while ($row = $posters->fetch_assoc()) {
                                ?>
                                <tr>
                                    <td>
                                        <img src="<?php echo htmlspecialchars(dashboardImageUrl($row['image_path'] ?? '')); ?>" alt="<?php echo htmlspecialchars($row['title'] ?? 'Affiche'); ?>" class="dashboard-zoomable" style="width:80px;height:60px;object-fit:cover;border-radius:8px;cursor:zoom-in;">
                                    </td>
                                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                                    <td><?php echo (int)$row['order_position']; ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="delete_event_poster.php?id=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Êtes-vous sûr?');"><i class="bi bi-trash me-1"></i><span>Supprimer</span></a>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="5">Aucune affiche pour le moment.</td>
                            </tr>
                            <?php
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

            <!-- Testimonials Section -->
            <div id="testimonials" class="content-section">
                <h2>T&eacute;moignages</h2>
                <div class="dashboard-grid" style="margin-bottom: 1rem;">
                    <div class="card" data-filter="all">
                        <h3><i class="bi bi-stack"></i><span>Total</span></h3>
                        <div class="number"><?php echo $stats['testimonials_total'] ?? 0; ?></div>
                        <p>Nombre total</p>
                    </div>
                    <div class="card" data-filter="<?php echo htmlspecialchars($testimonialApprovedValue); ?>">
                        <h3><i class="bi bi-check2-circle"></i><span>Valid&eacute;s</span></h3>
                        <div class="number"><?php echo $stats['testimonials_approved'] ?? 0; ?></div>
                        <p>T&eacute;moignages approuv&eacute;s</p>
                    </div>
                    <div class="card" data-filter="<?php echo htmlspecialchars($testimonialRejectedValue); ?>">
                        <h3><i class="bi bi-x-circle"></i><span>Nouveaux t&eacute;moignages</span></h3>
                        <div class="number"><?php echo $stats['testimonials_rejected'] ?? 0; ?></div>
                        <p>T&eacute;moignages refus&eacute;s</p>
                    </div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Message</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("SELECT id, name, email, message, `$testimonialStatusCol` AS status, created_at FROM testimonials ORDER BY created_at DESC");
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                ?>
                                <tr data-status="<?php echo htmlspecialchars($row['status']); ?>">
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars(mb_strimwidth($row['message'], 0, 70, '...')); ?></td>
                                    <td><?php echo ucfirst($row['status']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <?php if ($row['status'] !== $testimonialApprovedValue): ?>
                                                <a href="approve_testimonial.php?id=<?php echo $row['id']; ?>" class="btn-edit"><i class="bi bi-check2-circle me-1"></i><span>Valider</span></a>
                                            <?php endif; ?>
                                            <?php if ($row['status'] !== $testimonialRejectedValue): ?>
                                                <a href="reject_testimonial.php?id=<?php echo $row['id']; ?>" class="btn-edit"><i class="bi bi-x-circle me-1"></i><span>Rejeter</span></a>
                                            <?php endif; ?>
                                            <a href="delete_testimonial.php?id=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Êtes-vous sûr?');"><i class="bi bi-trash me-1"></i><span>Supprimer</span></a>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="6">Aucun témoignage pour le moment.</td>
                            </tr>
                            <?php
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

            <div id="dashboard-modal" class="dashboard-modal" aria-hidden="true">
                <div class="dashboard-modal-content" role="dialog" aria-modal="true">
                    <div class="dashboard-modal-header">
                        <h3 id="dashboard-modal-title">Détails</h3>
                        <button id="dashboard-modal-close" class="dashboard-modal-close" aria-label="Fermer">x</button>
                    </div>
                    <div id="dashboard-modal-body" class="dashboard-modal-body"></div>
                </div>
            </div>
            <div id="image-lightbox" class="image-lightbox" aria-hidden="true">
                <button id="image-lightbox-close" class="image-lightbox-close" aria-label="Fermer">x</button>
                <img id="image-lightbox-img" src="" alt="">
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

            document.querySelectorAll('.settings-block').forEach(function (block) {
                const body = block.querySelector('.settings-block-body');
                const toggle = block.querySelector('.settings-toggle');
                if (!body || !toggle) {
                    return;
                }
                toggle.addEventListener('click', function () {
                    const expanded = block.classList.toggle('is-expanded');
                    toggle.textContent = expanded ? 'Voir moins' : 'Voir plus';
                });
            });

            // Testimonials card filtering
            var testimonialsSection = document.getElementById('testimonials');
            if (testimonialsSection) {
                var cards = testimonialsSection.querySelectorAll('.dashboard-grid .card[data-filter]');
                var rows = testimonialsSection.querySelectorAll('tbody tr[data-status]');
                function applyFilter(filter) {
                    rows.forEach(function (row) {
                        if (filter === 'all') {
                            row.style.display = '';
                            return;
                        }
                        var status = row.getAttribute('data-status') || '';
                        row.style.display = status === filter ? '' : 'none';
                    });
                }
                cards.forEach(function (card) {
                    card.addEventListener('click', function () {
                        cards.forEach(function (c) { c.classList.remove('is-active'); });
                        card.classList.add('is-active');
                        var filter = card.getAttribute('data-filter') || 'all';
                        applyFilter(filter);
                    });
                });
            }


            const modal = document.getElementById('dashboard-modal');
            const modalTitle = document.getElementById('dashboard-modal-title');
            const modalBody = document.getElementById('dashboard-modal-body');
            document.querySelectorAll('.dashboard-grid .card').forEach(function (card) {
                card.addEventListener('click', function () {
                    const key = card.getAttribute('data-card');
                    if (!key || !modal || !modalTitle || !modalBody) return;
                    const detail = document.querySelector('#dashboard-details [data-detail=\"' + key + '\"]');
                    if (!detail) return;
                    const titleEl = card.querySelector('span');
                    modalTitle.textContent = titleEl ? titleEl.textContent : 'Détails';
                    modalBody.innerHTML = detail.innerHTML;
                    modal.classList.add('active');
                    modal.setAttribute('aria-hidden', 'false');
                    modal.style.display = 'flex';
                });
            });
            const modalClose = document.getElementById('dashboard-modal-close');
            if (modalClose && modal) {
                modalClose.addEventListener('click', function () {
                    modal.classList.remove('active');
                    modal.setAttribute('aria-hidden', 'true');
                    modal.style.display = 'none';
                });
            }
            if (modal) {
                modal.addEventListener('click', function (e) {
                    if (e.target === modal) {
                        modal.classList.remove('active');
                        modal.setAttribute('aria-hidden', 'true');
                        modal.style.display = 'none';
                    }
                });
            }

            const lightbox = document.getElementById('image-lightbox');
            const lightboxImg = document.getElementById('image-lightbox-img');
            const lightboxClose = document.getElementById('image-lightbox-close');
            document.addEventListener('click', function (e) {
                const target = e.target;
                if (!target || !target.classList || !target.classList.contains('dashboard-zoomable')) return;
                if (!lightbox || !lightboxImg) return;
                lightboxImg.src = target.src;
                lightboxImg.alt = target.alt || '';
                lightbox.classList.add('active');
                lightbox.setAttribute('aria-hidden', 'false');
            });
            function closeLightbox() {
                if (!lightbox) return;
                lightbox.classList.remove('active');
                lightbox.setAttribute('aria-hidden', 'true');
                if (lightboxImg) lightboxImg.src = '';
            }
            if (lightboxClose) {
                lightboxClose.addEventListener('click', closeLightbox);
            }
            if (lightbox) {
                lightbox.addEventListener('click', function (e) {
                    if (e.target === lightbox) closeLightbox();
                });
            }
        });
    </script>
</body>
</html>

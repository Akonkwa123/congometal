<?php
/**
 * Vérification complète du site Congometal
 * Accédez à cette page pour vérifier que tout est correctement installé
 */

require_once 'includes/config.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification d'installation - Congometal</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem;
            min-height: 100vh;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 2rem;
            border-radius: 10px 10px 0 0;
            text-align: center;
        }
        
        .header h1 {
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .header p {
            color: #666;
        }
        
        .content {
            background: white;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .section {
            margin-bottom: 2rem;
        }
        
        .section h2 {
            color: #333;
            margin-bottom: 1rem;
            font-size: 1.5rem;
            border-bottom: 2px solid #667eea;
            padding-bottom: 0.5rem;
        }
        
        .check-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            margin-bottom: 0.5rem;
            border-radius: 5px;
            background: #f9f9f9;
        }
        
        .check-item.success {
            background: #d4edda;
            border-left: 4px solid #28a745;
        }
        
        .check-item.warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
        }
        
        .check-item.error {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
        }
        
        .check-icon {
            font-size: 1.5rem;
            margin-right: 1rem;
            min-width: 30px;
        }
        
        .check-text {
            flex: 1;
        }
        
        .check-label {
            font-weight: bold;
            color: #333;
        }
        
        .check-detail {
            color: #666;
            font-size: 0.9rem;
            margin-top: 0.25rem;
        }
        
        .button-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }
        
        .button {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .button-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .button-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .button-secondary {
            background: #6c757d;
            color: white;
        }
        
        .button-secondary:hover {
            background: #5a6268;
        }
        
        .summary {
            background: #f0f0f0;
            padding: 1.5rem;
            border-radius: 5px;
            margin-top: 2rem;
        }
        
        .summary h3 {
            margin-bottom: 1rem;
            color: #333;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #ddd;
        }
        
        .summary-item:last-child {
            border-bottom: none;
        }
        
        .footer {
            background: white;
            padding: 1.5rem;
            border-radius: 0 0 10px 10px;
            text-align: center;
            color: #666;
            font-size: 0.9rem;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: bold;
        }
        
        .status-ok {
            background: #28a745;
            color: white;
        }
        
        .status-warning {
            background: #ffc107;
            color: #333;
        }
        
        .status-error {
            background: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Vérification d'installation</h1>
            <p>Congometal - Système de gestion de contenu</p>
        </div>
        
        <div class="content">
            <?php
            $checks = [];
            $summary = [
                'total' => 0,
                'success' => 0,
                'warning' => 0,
                'error' => 0
            ];
            
            // ===== PHP VERSION =====
            $php_version = version_compare(PHP_VERSION, '7.4.0', '>=');
            $checks[] = [
                'title' => 'Version PHP',
                'status' => $php_version ? 'success' : 'error',
                'label' => 'PHP ' . PHP_VERSION,
                'detail' => $php_version ? 'Compatible' : 'PHP 7.4 minimum requis'
            ];
            
            // ===== EXTENSIONS PHP =====
            $extensions = [
                'mysqli' => 'MySQLi',
                'pdo' => 'PDO',
                'json' => 'JSON',
                'curl' => 'cURL',
                'gd' => 'GD (Image)',
            ];
            
            foreach ($extensions as $ext => $name) {
                $loaded = extension_loaded($ext);
                $checks[] = [
                    'title' => 'Extension: ' . $name,
                    'status' => $loaded ? 'success' : 'warning',
                    'label' => $name,
                    'detail' => $loaded ? 'Chargée' : 'Non chargée (optionnel)'
                ];
            }
            
            // ===== FICHIERS ET DOSSIERS =====
            $files_to_check = [
                'includes/config.php' => 'Config PHP',
                'includes/functions.php' => 'Fonctions PHP',
                'admin/login.php' => 'Login Admin',
                'admin/dashboard.php' => 'Dashboard Admin',
                'assets/css/style.css' => 'Styles CSS',
                'assets/js/main.js' => 'Scripts JS',
                'index.php' => 'Page d\'accueil',
            ];
            
            foreach ($files_to_check as $file => $name) {
                $exists = file_exists($file);
                $checks[] = [
                    'title' => 'Fichier: ' . $name,
                    'status' => $exists ? 'success' : 'error',
                    'label' => basename($file),
                    'detail' => $exists ? 'Trouvé' : 'Manquant'
                ];
            }
            
            // ===== DOSSIERS ÉCRITURE =====
            $dirs_to_check = [
                'admin/uploads' => 'Uploads',
                'includes' => 'Configuration',
            ];
            
            foreach ($dirs_to_check as $dir => $name) {
                $exists = is_dir($dir);
                $writable = $exists && is_writable($dir);
                $checks[] = [
                    'title' => 'Dossier: ' . $name,
                    'status' => $writable ? 'success' : ($exists ? 'warning' : 'error'),
                    'label' => $dir,
                    'detail' => $writable ? 'Accessible' : ($exists ? 'Lecture seule' : 'N\'existe pas')
                ];
            }
            
            // ===== BASE DE DONNÉES =====
            $db_check = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
            $db_ok = $db_check !== false;
            $checks[] = [
                'title' => 'Base de données',
                'status' => $db_ok ? 'success' : 'error',
                'label' => DB_NAME,
                'detail' => $db_ok ? 'Connectée' : 'Erreur de connexion'
            ];
            
            if ($db_ok) {
                mysqli_close($db_check);
                
                // Vérifier les tables
                $tables_to_check = ['users', 'services', 'portfolio', 'testimonials', 'contacts', 'settings'];
                $db_check = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
                
                foreach ($tables_to_check as $table) {
                    $result = $db_check->query("SHOW TABLES LIKE '$table'");
                    $table_exists = $result && $result->num_rows > 0;
                    $checks[] = [
                        'title' => 'Table: ' . ucfirst($table),
                        'status' => $table_exists ? 'success' : 'warning',
                        'label' => $table,
                        'detail' => $table_exists ? 'Existe' : 'Créer via install.php'
                    ];
                }
                
                $db_check->close();
            }
            
            // ===== AUTRES VÉRIFICATIONS =====
            $https_ok = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
            $checks[] = [
                'title' => 'HTTPS',
                'status' => $https_ok ? 'success' : 'warning',
                'label' => $https_ok ? 'Activé' : 'Désactivé (OK en local)',
                'detail' => $https_ok ? 'Sécurisé' : 'À activer en production'
            ];
            
            // Compter les statuts
            foreach ($checks as $check) {
                $summary['total']++;
                if ($check['status'] === 'success') $summary['success']++;
                elseif ($check['status'] === 'warning') $summary['warning']++;
                else $summary['error']++;
            }
            
            // Afficher les sections
            $sections = [
                'PHP et Extensions' => ['type' => ['PHP', 'Extension']],
                'Fichiers' => ['title' => ['Fichier']],
                'Dossiers' => ['title' => ['Dossier']],
                'Base de données' => ['label' => ['users', 'services', 'portfolio', 'testimonials', 'contacts', 'settings', DB_NAME]],
                'Sécurité' => ['label' => ['Activé', 'Désactivé']],
            ];
            
            $current_section = null;
            foreach ($checks as $check) {
                $new_section = null;
                foreach ($sections as $section_name => $section_filter) {
                    foreach ($section_filter as $key => $values) {
                        foreach ($values as $value) {
                            if (strpos($check['title'], $value) !== false) {
                                $new_section = $section_name;
                                break 3;
                            }
                        }
                    }
                }
                
                if ($new_section && $new_section !== $current_section) {
                    if ($current_section !== null) echo '</div>';
                    echo '<div class="section">';
                    echo '<h2>' . $new_section . '</h2>';
                    $current_section = $new_section;
                }
                
                $icon_map = ['success' => '✅', 'warning' => '⚠️', 'error' => '❌'];
                ?>
                <div class="check-item <?php echo $check['status']; ?>">
                    <div class="check-icon"><?php echo $icon_map[$check['status']]; ?></div>
                    <div class="check-text">
                        <div class="check-label"><?php echo $check['title']; ?></div>
                        <div class="check-detail"><?php echo $check['detail']; ?></div>
                    </div>
                </div>
                <?php
            }
            
            if ($current_section !== null) echo '</div>';
            ?>
            
            <div class="summary">
                <h3>📊 Résumé</h3>
                <div class="summary-item">
                    <span>Total des vérifications:</span>
                    <strong><?php echo $summary['total']; ?></strong>
                </div>
                <div class="summary-item">
                    <span>✅ OK:</span>
                    <span class="status-badge status-ok"><?php echo $summary['success']; ?></span>
                </div>
                <div class="summary-item">
                    <span>⚠️ Avertissements:</span>
                    <span class="status-badge status-warning"><?php echo $summary['warning']; ?></span>
                </div>
                <div class="summary-item">
                    <span>❌ Erreurs:</span>
                    <span class="status-badge status-error"><?php echo $summary['error']; ?></span>
                </div>
                <div class="summary-item" style="margin-top: 1rem; font-weight: bold; border-top: 2px solid #667eea; padding-top: 1rem;">
                    <span>État global:</span>
                    <?php
                    if ($summary['error'] === 0) {
                        echo '<span class="status-badge status-ok">✓ PRÊT</span>';
                    } elseif ($summary['error'] <= 2) {
                        echo '<span class="status-badge status-warning">⚠ CONFIGURER</span>';
                    } else {
                        echo '<span class="status-badge status-error">✗ PROBLÈMES</span>';
                    }
                    ?>
                </div>
            </div>
            
            <div class="button-group">
                <?php if ($summary['error'] === 0 && $db_ok): ?>
                    <a href="sample_data.php" class="button button-secondary">📊 Ajouter données d'exemple</a>
                    <a href="admin/login.php" class="button button-primary">🔐 Aller au panel admin</a>
                    <a href="index.php" class="button button-primary">🌐 Voir le site public</a>
                <?php else: ?>
                    <a href="install.php" class="button button-primary">⚙️ Exécuter install.php</a>
                    <a href="health_check.php" class="button button-secondary">🔧 Diagnostic complet</a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="footer">
            <p>Congometal v1.0 • Vérification système • <?php echo date('d/m/Y H:i'); ?></p>
        </div>
    </div>
</body>
</html>

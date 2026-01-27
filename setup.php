<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Vérifier l'authentification
if (!isset($_SESSION['user_id'])) {
    header("Location: admin/login.php");
    exit();
}

if (!isAdmin()) {
    header("Location: admin/login.php");
    exit();
}

$message = '';
$error = '';

// Insérer des projets d'exemple
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_demo_projects'])) {
    $demo_projects = [
        [
            'title' => 'Usine de Transformation Métallurgique',
            'description' => 'Rénovation complète d\'une usine métallurgique avec installation de nouvelles chaînes de production automatisées. Travaux incluant la restructuration des bâtiments, installation électrique haute puissance, et mise en place de systèmes de sécurité modernes.',
            'category' => 'Infrastructure Industrielle',
            'client' => 'Acme Industries Congo',
            'status' => 'active'
        ],
        [
            'title' => 'Construction d\'Entrepôt Métallique',
            'description' => 'Construction d\'un entrepôt moderne de 5000m² pour le stockage de matériaux métalliques. Structure en acier, toit ondulé haute résistance, système de ventilation sophistiqué et zones de sécurité.',
            'category' => 'Bâtiment Industriel',
            'client' => 'Compagnie Métallurgique du Congo',
            'status' => 'active'
        ],
        [
            'title' => 'Pont Métallique - Route Nationale',
            'description' => 'Conception et construction d\'un pont métallique suspendu traversant le fleuve Congo. Capacité de charge 200 tonnes, longueur 250m, travaux incluant les fondations, structure en acier inoxydable et finitions.',
            'category' => 'Ouvrages d\'Art',
            'client' => 'Ministère des Transports RDC',
            'status' => 'active'
        ],
        [
            'title' => 'Système de Tuyauterie Industrielle',
            'description' => 'Installation d\'un système complet de tuyauterie industrielle pour transport de gaz et liquides. Tuyaux en acier allié de 6 pouces, raccords haute pression, système de contrôle par capteurs.',
            'category' => 'Tuyauterie',
            'client' => 'African Mining Corporation',
            'status' => 'active'
        ],
        [
            'title' => 'Toiture en Structure Métallique',
            'description' => 'Conception et réalisation d\'une toiture en structure métallique pour un centre commercial. Surface 10,000m², structure en treillis métallique, matériaux imperméables haute gamme.',
            'category' => 'Couverture Métallique',
            'client' => 'Groupe Commercial Kinshasa',
            'status' => 'active'
        ]
    ];

    foreach ($demo_projects as $project) {
        $title = $conn->real_escape_string($project['title']);
        $description = $conn->real_escape_string($project['description']);
        $category = $conn->real_escape_string($project['category']);
        $client = $conn->real_escape_string($project['client']);
        $status = $conn->real_escape_string($project['status']);

        $sql = "INSERT INTO portfolio (title, description, category, client, status, created_at) 
                VALUES ('$title', '$description', '$category', '$client', '$status', NOW())";
        
        if (!$conn->query($sql)) {
            $error = "Erreur lors de l'insertion : " . $conn->error;
            break;
        }
    }

    if (empty($error)) {
        $message = '✓ 5 projets d\'exemple ajoutés avec succès ! Allez à /admin/manage_portfolio.php pour ajouter les photos.';
    }
}

// Supprimer tous les projets de démo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_demo_projects'])) {
    $conn->query("DELETE FROM portfolio");
    $message = '✓ Tous les projets ont été supprimés.';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation & Démo - Congometal</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .container {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            max-width: 600px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }
        h1 {
            color: #333;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        h2 {
            color: #667eea;
            font-size: 1.2rem;
            margin-top: 2rem;
            margin-bottom: 1rem;
            border-bottom: 2px solid #667eea;
            padding-bottom: 0.5rem;
        }
        p {
            color: #666;
            margin-bottom: 1rem;
            line-height: 1.6;
        }
        .message {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 5px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        button {
            padding: 0.8rem;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background: #764ba2;
            transform: translateY(-2px);
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-2px);
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
        .steps {
            background: #f9f9f9;
            padding: 1.5rem;
            border-radius: 5px;
            margin: 1.5rem 0;
        }
        .steps ol {
            margin-left: 1.5rem;
            color: #555;
        }
        .steps li {
            margin-bottom: 0.8rem;
        }
        .link {
            color: #667eea;
            text-decoration: none;
            font-weight: bold;
        }
        .link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Installation & Configuration</h1>

        <?php if (!empty($message)): ?>
            <div class="message success"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="message info">
            ℹ️ Bienvenue ! Configurez votre site Congometal en quelques étapes.
        </div>

        <!-- Section 1: Database -->
        <h2>✅ 1. Base de Données</h2>
        <p>Les tables de votre base de données ont été créées automatiquement. Vous pouvez commencer à ajouter du contenu.</p>

        <!-- Section 2: Portfolio Demo -->
        <h2>📋 2. Projets de Démonstration (Portfolio)</h2>
        <p>Cliquez sur le bouton ci-dessous pour ajouter 5 projets d'exemple à votre portfolio. Vous pourrez ensuite ajouter les photos via l'interface admin.</p>
        
        <form method="POST">
            <button type="submit" name="add_demo_projects" class="btn-primary">➕ Ajouter les projets d'exemple</button>
        </form>

        <div class="steps">
            <strong>Après avoir ajouté les projets :</strong>
            <ol>
                <li>Connectez-vous à l'admin : <a href="/admin/login.php" class="link">/admin/login.php</a></li>
                <li>Allez à <strong>Gestion Portfolio</strong> dans le tableau de bord</li>
                <li>Cliquez sur <strong>Éditer</strong> sur chaque projet pour ajouter une photo</li>
                <li>Les photos s'afficheront automatiquement en slideshow sur la page d'accueil !</li>
            </ol>
        </div>

        <!-- Section 3: Admin Panel -->
        <h2>🎛️ 3. Tableau de Bord Admin</h2>
        <p>Accédez au tableau de bord admin pour gérer :</p>
        <ul style="margin: 0 0 1rem 1.5rem; color: #666;">
            <li>Paramètres du site (logo, description, etc.)</li>
            <li>Galerie À propos (avec descriptions)</li>
            <li>Portfolio (avec slideshow animé)</li>
            <li>Services, Contacts</li>
        </ul>
        <a href="/admin/dashboard.php" style="display: block; text-align: center;">
            <button style="width: 100%; background: #28a745; color: white;">✅ Aller au Tableau de Bord</button>
        </a>

        <!-- Section 4: Cleanup -->
        <h2>🧹 4. Nettoyer les Données de Démo</h2>
        <p>Si vous voulez supprimer tous les projets et recommencer :</p>
        <form method="POST">
            <button type="submit" name="clear_demo_projects" class="btn-danger" onclick="return confirm('Êtes-vous sûr? Cette action supprimera tous les projets.');">🗑️ Supprimer tous les projets</button>
        </form>

        <hr style="margin: 2rem 0; border: none; border-top: 1px solid #ddd;">

        <h2>📚 Fonctionnalités Principales</h2>
        <ul style="margin: 0 0 1rem 1.5rem; color: #666;">
            <li><strong>Portfolio Slideshow :</strong> Les projets s'affichent en carousel automatique toutes les 6 secondes</li>
            <li><strong>Descriptions Dynamiques :</strong> Chaque projet a une description complète avec client et catégorie</li>
            <li><strong>Navigation Interactive :</strong> Flèches + points pour naviguer manuellement</li>
            <li><strong>Galerie À Propos :</strong> Galerie de photos avec descriptions</li>
            <li><strong>Animations Fluides :</strong> Transitions CSS3 professionnelles</li>
        </ul>

        <p style="text-align: center; margin-top: 2rem; color: #999; font-size: 0.9rem;">
            Besoin d'aide ? Consultez la documentation ou contactez le support.
        </p>
    </div>
</body>
</html>

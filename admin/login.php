<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = escape_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Veuillez entrer le nom d\'utilisateur et le mot de passe.';
    } else {
        $sql = "SELECT id, username, password, role FROM users WHERE username = '$username'";
        $result = $conn->query($sql);

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                header("Location: dashboard.php");
                exit();
            } else {
                $error = 'Mot de passe incorrect.';
            }
        } else {
            $error = 'Utilisateur non trouvé.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Admin - Congometal</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .login-body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            background:
                radial-gradient(circle at top left, rgba(59, 130, 246, 0.35), transparent 55%),
                linear-gradient(135deg, #020617 0%, #020617 100%);
            color: #e5e7eb;
        }

        .login-shell {
            width: 100%;
            max-width: 960px;
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr);
            border-radius: 24px;
            overflow: hidden;
            background: rgba(15, 23, 42, 0.96);
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.85);
            border: 1px solid rgba(148, 163, 184, 0.45);
        }

        .login-brand-panel {
            padding: 2.5rem 2.4rem;
            background:
                radial-gradient(circle at top, rgba(56, 189, 248, 0.35), transparent 55%),
                linear-gradient(135deg, #2563eb 0%, #1e3a8a 50%, #020617 100%);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .login-brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.4rem 0.9rem;
            border-radius: 999px;
            border: 1px solid rgba(191, 219, 254, 0.8);
            background: rgba(15, 23, 42, 0.5);
            font-size: 0.8rem;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #bfdbfe;
        }

        .login-brand-title {
            margin-top: 1.8rem;
            font-size: 2rem;
            font-weight: 700;
        }

        .login-brand-text {
            margin-top: 0.9rem;
            font-size: 0.98rem;
            color: #e5e7eb;
            max-width: 340px;
        }

        .login-brand-list {
            list-style: none;
            margin-top: 2rem;
        }

        .login-brand-list li {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: #e5e7eb;
            margin-bottom: 0.65rem;
        }

        .login-brand-list li::before {
            content: '✔';
            font-size: 0.9rem;
            color: #bbf7d0;
        }

        .login-form-panel {
            padding: 2.4rem 2.3rem;
            background: #020617;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-form-header h1 {
            font-size: 1.6rem;
            margin-bottom: 0.35rem;
            color: #f9fafb;
        }

        .login-form-header p {
            font-size: 0.95rem;
            color: #9ca3af;
            margin-bottom: 1.4rem;
        }

        .login-alert {
            padding: 0.8rem 0.9rem;
            border-radius: 0.75rem;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .login-alert-error {
            background: rgba(248, 113, 113, 0.15);
            color: #fecaca;
            border: 1px solid rgba(248, 113, 113, 0.5);
        }

        .login-form {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .login-field label {
            display: block;
            margin-bottom: 0.4rem;
            font-size: 0.9rem;
            color: #e5e7eb;
            font-weight: 500;
        }

        .login-input {
            width: 100%;
            padding: 0.7rem 0.9rem;
            border-radius: 0.75rem;
            border: 1px solid #1f2937;
            background-color: #020617;
            color: #e5e7eb;
            font-size: 0.95rem;
            font-family: inherit;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease, transform 0.1s ease;
        }

        .login-input::placeholder {
            color: #6b7280;
        }

        .login-input:focus {
            outline: none;
            border-color: #2563eb;
            background-color: #020617;
            box-shadow: 0 0 0 1px rgba(37, 99, 235, 0.7);
            transform: translateY(-1px);
        }

        .login-submit {
            margin-top: 0.8rem;
            width: 100%;
            padding: 0.85rem 1rem;
            border-radius: 999px;
            border: none;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 50%, #1e3a8a 100%);
            color: #f9fafb;
            font-size: 0.98rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 16px 40px rgba(37, 99, 235, 0.55);
            transition: background 0.25s ease, transform 0.18s ease, box-shadow 0.25s ease;
        }

        .login-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 22px 50px rgba(37, 99, 235, 0.75);
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 50%, #1e293b 100%);
        }

        .login-divider {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 1.4rem 0 1.1rem;
            font-size: 0.85rem;
            color: #6b7280;
        }

        .login-divider::before,
        .login-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(55, 65, 81, 0.9), transparent);
        }

        .google-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.7rem 1rem;
            border-radius: 999px;
            background-color: #ffffff;
            color: #111827;
            border: 1px solid #e5e7eb;
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 500;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.35);
            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        }

        .google-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 16px 40px rgba(15, 23, 42, 0.6);
            border-color: #cbd5e1;
        }

        .google-logo {
            height: 18px;
            width: auto;
        }

        .google-help-text {
            font-size: 0.8rem;
            color: #9ca3af;
            margin-top: 0.75rem;
        }

        .login-back-link {
            margin-top: 1.8rem;
            text-align: center;
        }

        .login-back-link a {
            font-size: 0.9rem;
            color: #93c5fd;
            text-decoration: none;
        }

        .login-back-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .login-shell {
                max-width: 100%;
                grid-template-columns: 1fr;
            }

            .login-brand-panel {
                padding: 2rem 1.8rem;
                text-align: center;
            }

            .login-brand-text {
                margin-left: auto;
                margin-right: auto;
            }

            .login-brand-list {
                margin-top: 1.6rem;
                text-align: left;
            }

            .login-form-panel {
                padding: 2rem 1.8rem 2.3rem;
            }
        }

        @media (max-width: 480px) {
            .login-body {
                padding: 1rem;
            }

            .login-brand-title {
                font-size: 1.7rem;
            }
        }
    </style>
</head>
<body class="login-body">
    <div class="login-shell">
        <div class="login-brand-panel">
            <div class="login-brand-badge">CONGOMETAL ADMIN</div>
            <div>
                <h1 class="login-brand-title">Espace d'administration</h1>
                <p class="login-brand-text">G&eacute;rez vos contenus, vos projets et les param&egrave;tres de votre plateforme en toute s&eacute;curit&eacute;.</p>
            </div>
            <ul class="login-brand-list">
                <li>Tableau de bord clair et moderne</li>
                <li>Suivi de vos projets et r&eacute;alisations</li>
                <li>Param&eacute;trage simple de la plateforme</li>
            </ul>
        </div>
        <div class="login-form-panel">
            <div class="login-form-header">
                <h1>Connexion</h1>
                <p>Connectez-vous pour acc&eacute;der au tableau de bord d'administration.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="login-alert login-alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" class="login-form">
                <div class="login-field">
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" class="login-input" required>
                </div>
                <div class="login-field">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" class="login-input" required>
                </div>
                <button type="submit" class="login-submit">Se connecter</button>
            </form>

            <div class="login-divider"><span>ou</span></div>

            <!-- Google Sign-in -->
            <?php
            $googleAuthUrl = '';
            if (defined('GOOGLE_CLIENT_ID') && GOOGLE_CLIENT_ID !== 'YOUR_GOOGLE_CLIENT_ID') {
                $params = [
                    'client_id' => GOOGLE_CLIENT_ID,
                    'redirect_uri' => GOOGLE_REDIRECT_URI,
                    'response_type' => 'code',
                    'scope' => 'openid email profile',
                    'access_type' => 'offline',
                    'prompt' => 'select_account'
                ];
                $googleAuthUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
            }
            ?>
            <?php if ($googleAuthUrl): ?>
                <a href="<?php echo htmlspecialchars($googleAuthUrl); ?>" class="google-btn">
                    <img src="https://www.gstatic.com/devrel-devsite/prod/v.../google_logo.png" alt="Google" class="google-logo">
                    <span>Se connecter avec Google</span>
                </a>
            <?php else: ?>
                <p class="google-help-text">(Pour activer la connexion Google, configurez GOOGLE_CLIENT_ID dans <code>includes/config.php</code>).</p>
            <?php endif; ?>

            <div class="login-back-link">
                <a href="<?php echo SITE_URL; ?>">&larr; Retour au site</a>
            </div>
        </div>
    </div>
</body>
</html>

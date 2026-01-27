<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkAuth();
if (!isAdmin()) header("Location: login.php");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user = null;

if ($id > 0) {
    $result = $conn->query("SELECT * FROM users WHERE id = $id");
    $user = $result->fetch_assoc();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = escape_input($_POST['username']);
    $email = escape_input($_POST['email']);
    $role = escape_input($_POST['role']);
    $password = $_POST['password'] ?? '';

    if (empty($username)) {
        $message = '<div class="alert alert-error">Veuillez remplir tous les champs requis.</div>';
    } else {
        if ($id > 0) {
            // Mise à jour
            $sql = "UPDATE users SET username='$username', email='$email', role='$role'";
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql .= ", password='$hashed_password'";
            }
            $sql .= " WHERE id=$id";
            $message = '<div class="alert alert-success">Utilisateur mis à jour avec succès.</div>';
        } else {
            // Insertion
            if (empty($password)) {
                $message = '<div class="alert alert-error">Le mot de passe est requis pour un nouvel utilisateur.</div>';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$hashed_password', '$role')";
                $message = '<div class="alert alert-success">Utilisateur ajouté avec succès.</div>';
            }
        }

        if (isset($sql) && $conn->query($sql) === TRUE) {
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
    <title><?php echo $id > 0 ? 'Éditer' : 'Ajouter'; ?> Utilisateur - Congometal Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; }
        .container { max-width: 600px; margin: 2rem auto; padding: 2rem; background: white; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h1 { margin-bottom: 1.5rem; color: #333; }
        .form-group { margin-bottom: 1.5rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: #333; }
        input, select { width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px; font-family: inherit; font-size: 1rem; }
        input:focus, select:focus { outline: none; border-color: #667eea; box-shadow: 0 0 5px rgba(102,126,234,0.3); }
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
        <h1><?php echo $id > 0 ? 'Éditer l\'utilisateur' : 'Ajouter un utilisateur'; ?></h1>
        <?php echo $message; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">Nom d'utilisateur *</label>
                <input type="text" id="username" name="username" value="<?php echo $user ? htmlspecialchars($user['username']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo $user ? htmlspecialchars($user['email']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="password">Mot de passe <?php echo $id === 0 ? '*' : '(laisser vide pour ne pas modifier)'; ?></label>
                <input type="password" id="password" name="password" <?php echo $id === 0 ? 'required' : ''; ?>>
            </div>
            <div class="form-group">
                <label for="role">Rôle</label>
                <select id="role" name="role">
                    <option value="admin" <?php echo $user && $user['role'] === 'admin' ? 'selected' : ''; ?>>Administrateur</option>
                    <option value="user" <?php echo $user && $user['role'] === 'user' ? 'selected' : ''; ?>>Utilisateur</option>
                </select>
            </div>
            <button type="submit">Enregistrer</button>
            <a href="dashboard.php" style="margin-left: 1rem;">Annuler</a>
        </form>
    </div>
</body>
</html>

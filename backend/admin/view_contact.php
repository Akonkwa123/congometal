<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkAuth();
if (!isAdmin()) header("Location: login.php");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$contact = null;

if ($id > 0) {
    $result = $conn->query("SELECT * FROM contacts WHERE id = $id");
    $contact = $result->fetch_assoc();
    
    // Marquer comme lue
    if ($contact['status'] === 'new') {
        $conn->query("UPDATE contacts SET status = 'read' WHERE id = $id");
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voir Contact - Congometal Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; }
        .container { max-width: 600px; margin: 2rem auto; padding: 2rem; background: white; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h1 { margin-bottom: 1.5rem; color: #333; }
        .info-group { margin-bottom: 1.5rem; border-bottom: 1px solid #eee; padding-bottom: 1rem; }
        .info-label { color: #667eea; font-weight: bold; }
        .info-value { color: #333; margin-top: 0.5rem; }
        button { background: #667eea; color: white; padding: 0.8rem 1.5rem; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
        button:hover { background: #764ba2; }
        a { color: #667eea; text-decoration: none; margin-left: 1rem; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Message de contact</h1>
        
        <?php if ($contact): ?>
            <div class="info-group">
                <div class="info-label">De:</div>
                <div class="info-value"><?php echo htmlspecialchars($contact['name']); ?></div>
            </div>
            <div class="info-group">
                <div class="info-label">Email:</div>
                <div class="info-value"><a href="mailto:<?php echo htmlspecialchars($contact['email']); ?>"><?php echo htmlspecialchars($contact['email']); ?></a></div>
            </div>
            <?php if (!empty($contact['phone'])): ?>
                <div class="info-group">
                    <div class="info-label">Téléphone:</div>
                    <div class="info-value"><?php echo htmlspecialchars($contact['phone']); ?></div>
                </div>
            <?php endif; ?>
            <div class="info-group">
                <div class="info-label">Sujet:</div>
                <div class="info-value"><?php echo htmlspecialchars($contact['subject']); ?></div>
            </div>
            <div class="info-group">
                <div class="info-label">Date:</div>
                <div class="info-value"><?php echo date('d/m/Y à H:i', strtotime($contact['created_at'])); ?></div>
            </div>
            <div class="info-group">
                <div class="info-label">Message:</div>
                <div class="info-value"><?php echo nl2br(htmlspecialchars($contact['message'])); ?></div>
            </div>
            
            <button onclick="window.location.href='delete_contact.php?id=<?php echo $contact['id']; ?>' && confirm('Êtes-vous sûr?')">Supprimer</button>
            <a href="dashboard.php">Retour</a>
        <?php else: ?>
            <p>Contact non trouvé.</p>
            <a href="dashboard.php">Retour</a>
        <?php endif; ?>
    </div>
</body>
</html>

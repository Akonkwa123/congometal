<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: text/plain; charset=utf-8');

$createdCategories = [];
$createdMembers = [];
$errors = [];

// Helper: upsert-like insert for category by name (tolerant to missing columns like is_active)
function ensureCategory(mysqli $conn, string $name, string $description = '', int $display_order = 0, int $is_active = 1): ?int {
    // Check existing
    $stmt = $conn->prepare("SELECT id FROM team_categories WHERE name = ? LIMIT 1");
    if (!$stmt) return null;
    $stmt->bind_param('s', $name);
    if (!$stmt->execute()) { $stmt->close(); return null; }
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $stmt->close();
        return (int)$row['id'];
    }
    $stmt->close();

    // Detect available columns
    $hasIsActive = false;
    $hasCreatedAt = false;
    if ($r = $conn->query("SHOW COLUMNS FROM team_categories LIKE 'is_active'")) { $hasIsActive = $r->num_rows > 0; $r->free(); }
    if ($r = $conn->query("SHOW COLUMNS FROM team_categories LIKE 'created_at'")) { $hasCreatedAt = $r->num_rows > 0; $r->free(); }

    $cols = ['name','description','display_order'];
    $placeholders = ['?','?','?'];
    $types = 'ssi';
    $values = [$name, $description, $display_order];

    if ($hasIsActive) {
        $cols[] = 'is_active';
        $placeholders[] = '?';
        $types .= 'i';
        $values[] = $is_active;
    }
    if ($hasCreatedAt) {
        $cols[] = 'created_at';
        $placeholders[] = 'NOW()';
    }

    $sql = 'INSERT INTO team_categories (' . implode(', ', $cols) . ') VALUES (' . implode(', ', $placeholders) . ')';
    $stmt = $conn->prepare($sql);
    if (!$stmt) return null;
    // Bind only if we have placeholders with ?
    if (strpos($sql, '?') !== false) {
        $stmt->bind_param($types, ...$values);
    }
    if (!$stmt->execute()) { $stmt->close(); return null; }
    $id = (int)$conn->insert_id;
    $stmt->close();
    return $id;
}

// Seed categories
$categories = [
    ['name' => 'Direction générale', 'description' => 'Direction et stratégie', 'display_order' => 1],
    ['name' => 'Ingénierie', 'description' => 'Conception et R&D', 'display_order' => 2],
    ['name' => 'Production', 'description' => 'Fabrication et ateliers', 'display_order' => 3],
    ['name' => 'Support', 'description' => 'RH, Finance, IT', 'display_order' => 4],
];

$categoryMap = [];
foreach ($categories as $cat) {
    $id = ensureCategory($conn, $cat['name'], $cat['description'], $cat['display_order'], 1);
    if ($id) {
        $categoryMap[$cat['name']] = $id;
        $createdCategories[] = $cat['name'] . " (#$id)";
    } else {
        $errors[] = 'Échec catégorie: ' . $cat['name'];
    }
}

// Helper: check if member exists (by name + position)
function memberExists(mysqli $conn, string $name, string $position): bool {
    $stmt = $conn->prepare('SELECT id FROM team_members WHERE name = ? AND position = ? LIMIT 1');
    $stmt->bind_param('ss', $name, $position);
    if (!$stmt->execute()) return false;
    $res = $stmt->get_result();
    $exists = (bool)$res->fetch_assoc();
    $stmt->close();
    return $exists;
}

// Seed members
$members = [
    [
        'name' => 'Jean Mbayo',
        'position' => 'Directeur Général',
        'description' => 'Pilote la stratégie et la croissance de Congometal.',
        'email' => 'jean.mbayo@congometal.test',
        'phone' => '+243 820 000 001',
        'linkedin' => '#',
        'twitter' => '#',
        'category' => 'Direction générale',
        'display_order' => 1,
        'is_active' => 1,
        'image_path' => null,
    ],
    [
        'name' => 'Alice Kanza',
        'position' => 'Responsable Ingénierie',
        'description' => 'Supervise la conception et l’innovation des produits.',
        'email' => 'alice.kanza@congometal.test',
        'phone' => '+243 820 000 002',
        'linkedin' => '#',
        'twitter' => '#',
        'category' => 'Ingénierie',
        'display_order' => 1,
        'is_active' => 1,
        'image_path' => null,
    ],
    [
        'name' => 'Patrick Ilunga',
        'position' => 'Chef d’Atelier',
        'description' => 'Coordonne la production et la qualité en atelier.',
        'email' => 'patrick.ilunga@congometal.test',
        'phone' => '+243 820 000 003',
        'linkedin' => '#',
        'twitter' => '#',
        'category' => 'Production',
        'display_order' => 1,
        'is_active' => 1,
        'image_path' => null,
    ],
    [
        'name' => 'Sarah Mbuyi',
        'position' => 'Responsable RH',
        'description' => 'Gère les talents, la culture et le développement des équipes.',
        'email' => 'sarah.mbuyi@congometal.test',
        'phone' => '+243 820 000 004',
        'linkedin' => '#',
        'twitter' => '#',
        'category' => 'Support',
        'display_order' => 1,
        'is_active' => 1,
        'image_path' => null,
    ],
    [
        'name' => 'Kevin Tshibasu',
        'position' => 'Soudeur',
        'description' => 'Réalise les assemblages selon les normes de sécurité.',
        'email' => 'kevin.tshibasu@congometal.test',
        'phone' => '+243 820 000 005',
        'linkedin' => '#',
        'twitter' => '#',
        'category' => 'Production',
        'display_order' => 2,
        'is_active' => 1,
        'image_path' => null,
    ],
    [
        'name' => 'Lydia Kasongo',
        'position' => 'Designer Industriel',
        'description' => 'Travaille la forme et l’ergonomie des produits industriels.',
        'email' => 'lydia.kasongo@congometal.test',
        'phone' => '+243 820 000 006',
        'linkedin' => '#',
        'twitter' => '#',
        'category' => 'Ingénierie',
        'display_order' => 2,
        'is_active' => 1,
        'image_path' => null,
    ],
];

$insertStmt = $conn->prepare("INSERT INTO team_members (
    name, position, description, email, phone,
    linkedin, twitter, category_id, display_order, is_active, image_path, created_at, updated_at
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");

if (!$insertStmt) {
    $errors[] = 'Préparation INSERT membre échouée: ' . $conn->error;
} else {
    foreach ($members as $m) {
        if (memberExists($conn, $m['name'], $m['position'])) {
            continue; // skip duplicates
        }
        $catId = isset($categoryMap[$m['category']]) ? (int)$categoryMap[$m['category']] : null;
        $desc = $m['description'] ?? '';
        $img = $m['image_path'];
        $insertStmt->bind_param(
            'sssssssiiis',
            $m['name'],
            $m['position'],
            $desc,
            $m['email'],
            $m['phone'],
            $m['linkedin'],
            $m['twitter'],
            $catId,
            $m['display_order'],
            $m['is_active'],
            $img
        );
        if ($insertStmt->execute()) {
            $createdMembers[] = $m['name'] . ' (#' . $conn->insert_id . ')';
        } else {
            $errors[] = 'Échec membre: ' . $m['name'] . ' => ' . $conn->error;
        }
    }
    $insertStmt->close();
}

echo "Catégories créées/présentes: \n- " . implode("\n- ", $createdCategories) . "\n\n";
echo "Membres créés: \n- " . implode("\n- ", $createdMembers) . "\n\n";
if (!empty($errors)) {
    echo "Erreurs: \n- " . implode("\n- ", $errors) . "\n";
}

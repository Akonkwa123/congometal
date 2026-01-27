<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: text/plain; charset=utf-8');

function ensureColumn(mysqli $conn, string $table, string $column, string $definition): bool {
    $res = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    if ($res && $res->num_rows > 0) {
        $res->free();
        echo "OK: $table.$column existe déjà\n";
        return true;
    }
    if ($res) { $res->free(); }
    $sql = "ALTER TABLE `$table` ADD COLUMN `$column` $definition";
    if ($conn->query($sql)) {
        echo "ADDED: $table.$column ($definition)\n";
        return true;
    } else {
        echo "ERREUR ALTER: $table.$column -> " . $conn->error . "\n";
        return false;
    }
}

$ok = true;
$ok = ensureColumn($conn, 'team_categories', 'is_active', 'TINYINT(1) NOT NULL DEFAULT 1') && $ok;
$ok = ensureColumn($conn, 'team_categories', 'created_at', 'TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP') && $ok;

// Facultatif: s'assurer des colonnes clés côté members
$ok = ensureColumn($conn, 'team_members', 'is_active', 'TINYINT(1) NOT NULL DEFAULT 1') && $ok;
$ok = ensureColumn($conn, 'team_members', 'display_order', 'INT NOT NULL DEFAULT 0') && $ok;
$ok = ensureColumn($conn, 'team_members', 'image_path', 'VARCHAR(255) NULL') && $ok;

echo "\nStatut: " . ($ok ? 'OK' : 'AVERTISSEMENTS/ERREURS') . "\n";

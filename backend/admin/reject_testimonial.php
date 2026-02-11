<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkAuth();
if (!isAdmin()) {
    header("Location: login.php");
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id > 0) {
    // Ensure status column exists and normalize empty statuses
    $cols = [];
    $statusType = '';
    $res = $conn->query("SHOW COLUMNS FROM testimonials");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $cols[$row['Field']] = true;
            if ($row['Field'] === 'status' || $row['Field'] === 'statut') {
                $statusType = $row['Type'];
            }
        }
    }
    if (!isset($cols['status']) && !isset($cols['statut'])) {
        $conn->query("ALTER TABLE testimonials ADD COLUMN status ENUM('new','approved','rejected') DEFAULT 'new'");
        $cols['status'] = true;
        $statusType = "enum('new','approved','rejected')";
    }

    $statusCol = isset($cols['status']) ? 'status' : (isset($cols['statut']) ? 'statut' : 'status');
    $statusMode = 'workflow';
    if ($statusType && stripos($statusType, 'active') !== false && stripos($statusType, 'inactive') !== false) {
        $statusMode = 'active_inactive';
    }

    $rejectedValue = $statusMode === 'active_inactive' ? 'inactive' : 'rejected';
    $newValue = $statusMode === 'active_inactive' ? 'inactive' : 'new';

    $conn->query("UPDATE testimonials SET `$statusCol`='$newValue' WHERE `$statusCol` IS NULL OR `$statusCol`=''");
    $conn->query("UPDATE testimonials SET `$statusCol`='$rejectedValue' WHERE id=" . $id);

    // Keep both columns in sync if they both exist
    if (isset($cols['status']) && isset($cols['statut']) && $statusCol === 'status') {
        $conn->query("UPDATE testimonials SET `statut`='$rejectedValue' WHERE id=" . $id);
    }
    if (isset($cols['status']) && isset($cols['statut']) && $statusCol === 'statut') {
        $conn->query("UPDATE testimonials SET `status`='$rejectedValue' WHERE id=" . $id);
    }
}

header("Location: dashboard.php#testimonials");
exit();
?>

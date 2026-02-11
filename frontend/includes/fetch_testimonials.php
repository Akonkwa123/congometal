<?php
require_once 'config.php';
require_once 'functions.php';

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

$testimonials = [];

$testimonialStatusCol = 'status';
$testimonialStatusType = '';
$resCols = $conn->query("SHOW COLUMNS FROM testimonials");
if ($resCols) {
    while ($row = $resCols->fetch_assoc()) {
        if ($row['Field'] === 'status' || $row['Field'] === 'statut') {
            $testimonialStatusCol = $row['Field'];
            $testimonialStatusType = $row['Type'];
        }
    }
    $testimonialStatusMode = 'workflow';
    if ($testimonialStatusType && stripos($testimonialStatusType, 'active') !== false && stripos($testimonialStatusType, 'inactive') !== false) {
        $testimonialStatusMode = 'active_inactive';
    }
    $testimonialApprovedValue = $testimonialStatusMode === 'active_inactive' ? 'active' : 'approved';
    $res = $conn->query(
        "SELECT name, message, created_at FROM testimonials WHERE `$testimonialStatusCol` = '$testimonialApprovedValue' ORDER BY created_at DESC"
    );
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $testimonials[] = [
                'name' => $row['name'] ?? '',
                'message' => $row['message'] ?? '',
                'created_at' => $row['created_at'] ?? '',
            ];
        }
    }
}

echo json_encode([
    'success' => true,
    'items' => $testimonials,
]);

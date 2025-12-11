<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Simple Google OAuth2 callback handler
if (!isset($_GET['code'])) {
    header('Location: login.php');
    exit();
}

$code = $_GET['code'];

// Exchange code for tokens
$token_url = 'https://oauth2.googleapis.com/token';
$post_fields = [
    'code' => $code,
    'client_id' => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'grant_type' => 'authorization_code'
];

$ch = curl_init($token_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_fields));
$resp = curl_exec($ch);
if ($resp === false) {
    curl_close($ch);
    header('Location: login.php?error=oauth');
    exit();
}
curl_close($ch);

$data = json_decode($resp, true);
if (empty($data['access_token'])) {
    header('Location: login.php?error=oauth');
    exit();
}

$access_token = $data['access_token'];

// Get user info
$userinfo_url = 'https://openidconnect.googleapis.com/v1/userinfo';
$ch = curl_init($userinfo_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $access_token]);
$ui = curl_exec($ch);
if ($ui === false) {
    curl_close($ch);
    header('Location: login.php?error=oauth');
    exit();
}
curl_close($ch);

$profile = json_decode($ui, true);
if (empty($profile['email'])) {
    header('Location: login.php?error=oauth');
    exit();
}

// Finder or create user
$user = findOrCreateUserFromGoogle($profile);
if (!$user) {
    header('Location: login.php?error=oauth');
    exit();
}

// Login user
$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['role'] = $user['role'];

header('Location: dashboard.php');
exit();
?>

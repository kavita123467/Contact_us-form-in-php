<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Function to regenerate the captcha code
function generateCaptchaCode() {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $captcha_code = '';
    for ($i = 0; $i < 6; $i++) {
        $captcha_code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $captcha_code;
}

// Generate a new captcha code
$captcha_code = generateCaptchaCode();
$_SESSION['captcha'] = $captcha_code; // Store plain text captcha for comparison
$_SESSION['captcha_expires'] = time() + 120; // Set expiration to 2 minutes

// Return a success response
echo json_encode(['status' => 'success']);

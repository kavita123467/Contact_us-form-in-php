<?php
session_start();  // Start the session for captcha storage
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Generate a new captcha code function
function generateCaptchaCode() {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $captcha_code = '';
    for ($i = 0; $i < 6; $i++) {
        $captcha_code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $captcha_code;
}

// Check if captcha needs to be regenerated (either expired or not set)
if (!isset($_SESSION['captcha']) || time() >= $_SESSION['captcha_expires']) {
    $captcha_code = generateCaptchaCode();
    $_SESSION['captcha'] = $captcha_code;  // Store plain text captcha for comparison
    $_SESSION['captcha_expires'] = time() + 120; // Expires in 2 minutes (120 seconds)
} else {
    // Get the current captcha code (for refreshing the image if not expired)
    $captcha_code = $_SESSION['captcha'];
}

// Set the content type to PNG
header('Content-Type: image/png');

// Create a blank image with a white background
$image = imagecreatetruecolor(180, 60); // Adjust size for better display
$background_color = imagecolorallocate($image, 255, 255, 255); // White background
$text_color = imagecolorallocate($image, 0, 0, 0); // Black text
$noise_color = imagecolorallocate($image, 200, 200, 200); // Light gray for noise

// Fill the background
imagefilledrectangle($image, 0, 0, 180, 60, $background_color);

// Add random noise (dots and lines)
for ($i = 0; $i < 100; $i++) {
    imagesetpixel($image, rand(0, 180), rand(0, 60), $noise_color); // Random dots
}
for ($i = 0; $i < 10; $i++) {
    imageline($image, rand(0, 180), rand(0, 60), rand(0, 180), rand(0, 60), $noise_color); // Random lines
}

// Draw the captcha text on the image using built-in font
$font_path = __DIR__ . '/fonts/arial.ttf'; // Path to TTF font (use any desired font)

// If you have a custom font, you can use imagettftext for better fonts
if (file_exists($font_path)) {
    for ($i = 0; $i < 6; $i++) {
        $angle = rand(-30, 30); // Random text angle for better obfuscation
        $x = 20 + $i * 25;
        $y = rand(30, 50);
        imagettftext($image, 24, $angle, $x, $y, $text_color, $font_path, $captcha_code[$i]);
    }
} else {
    imagestring($image, 5, 10, 20, $captcha_code, $text_color); // Fallback to default built-in font
}

// Output the image as PNG
imagepng($image);

// Destroy the image resource to free memory
imagedestroy($image);

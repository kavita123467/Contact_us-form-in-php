<?php  
session_start();  // Start the session for captcha storage  
  
// Check if the captcha code is already stored in the session  
if (isset($_SESSION['captcha']) && isset($_SESSION['captcha_expires'])) {  
   // Check if the captcha code has expired (2 minutes)  
   if (time() < $_SESSION['captcha_expires']) {  
      // Use the existing captcha code  
      $captcha_code = $_SESSION['captcha'];  
   } else {  
      // Generate a new captcha code and store it in the session  
      $captcha_code = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyz"), 0, 6);  
      $_SESSION['captcha'] = $captcha_code;  
      $_SESSION['captcha_expires'] = time() + 120; // 2 minutes  
   }  
} else {  
   // Generate a new captcha code and store it in the session  
   $captcha_code = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyz"), 0, 6);  
   $_SESSION['captcha'] = $captcha_code;  
   $_SESSION['captcha_expires'] = time() + 120; // 2 minutes  
}  
  
// Create a captcha image  
header('Content-Type: image/png');  
$image = imagecreate(120, 40);  // Create an image of 120x40 pixels  
$background_color = imagecolorallocate($image, 255, 255, 255);  // White background  
$text_color = imagecolorallocate($image, 0, 0, 0);  // Black text  
  
// Add the captcha code to the image  
imagestring($image, 5, 10, 10, $captcha_code, $text_color);  
  
// Output the captcha image  
imagepng($image);  
imagedestroy($image);  // Destroy image to free memory  
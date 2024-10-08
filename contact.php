<?php  
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Initialize date and time variables
$date = $_POST['date'] ?? '';  
$time = $_POST['time'] ?? '';  

// Include the function for generating the captcha code
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Form</title>
    <script src="script.js"></script>  

    <!-- Timer Script -->
    <script>
        let timeLeft = 120; // 2 minutes in seconds
        const timerElement = document.getElementById('timer');
        const canvas = document.getElementById('timer-canvas');
        const ctx = canvas.getContext('2d');
        const radius = canvas.width / 2 - 10; // Circle radius
        let interval;

        function updateTimer() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            timerElement.textContent = `Time remaining: ${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;

            // Draw the circular timer
            drawCircle((timeLeft / 120) * 100); // 120 seconds is the total time

            if (timeLeft > 0) {
                timeLeft--;
            } else {
                clearInterval(interval);
                alert('Time is up! Please refresh the page to try again.');
                document.getElementById('appointment-form').reset();
                timerElement.textContent = '';
                ctx.clearRect(0, 0, canvas.width, canvas.height); // Clear the canvas
            }
        }

        function drawCircle(percentage) {
            ctx.clearRect(0, 0, canvas.width, canvas.height); // Clear the canvas
            ctx.beginPath();
            ctx.arc(radius + 10, radius + 10, radius, 0.75 * Math.PI, (2 - percentage / 100) * Math.PI + 0.75 * Math.PI); // Start at 12 o'clock position
            ctx.lineWidth = 15; // Circle thickness
            ctx.strokeStyle = '#4CAF50'; // Circle color
            ctx.stroke();
            ctx.closePath();

            // Draw inner circle
            ctx.beginPath();
            ctx.arc(radius + 10, radius + 10, radius - 5, 0, 2 * Math.PI); // Inner circle
            ctx.fillStyle = 'white'; // Inner circle color
            ctx.fill();
            ctx.closePath();
        }

        // Start the timer
        interval = setInterval(updateTimer, 1000); // Update timer every second

        function refreshCaptcha() {
            const xhr = new XMLHttpRequest();
            xhr.open("GET", "refresh_captcha.php", true); // Endpoint to refresh the captcha
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        const response = JSON.parse(xhr.responseText);
                        if (response.status === 'success') {
                            document.getElementById('captcha-image').src = 'captcha.php?' + new Date().getTime(); // Update captcha image
                            document.getElementById('captcha').value = ''; // Clear the input field
                            alert('Captcha refreshed successfully!');
                        } else {
                            alert('Failed to refresh captcha. Please try again.');
                        }
                    } else {
                        alert('Error refreshing captcha.');
                    }
                }
            };
            xhr.send();
        }
    </script>
</head>
<body>
    <form action="check_availability.php" method="post" id="appointment-form">  
        <!-- Hidden fields for date and time -->
        <input type="hidden" name="date" value="<?php echo htmlspecialchars($date); ?>">  
        <input type="hidden" name="time" value="<?php echo htmlspecialchars($time); ?>">  

        <!-- Name -->
        <label for="name">Name:</label>  
        <input type="text" id="name" name="name" required><br><br>  

        <!-- Email -->
        <label for="email">Email:</label>  
        <input type="email" id="email" name="email" required><br><br>  

        <!-- Phone -->
        <label for="phone">Phone:</label>  
        <input type="text" id="phone" name="phone" required pattern="\d{10}" placeholder="Enter 10 digit phone number"><br><br>  

        <!-- Captcha -->
        <label for="captcha">Captcha: <?php echo $_SESSION['captcha']; ?></label>
        <input type="text" id="captcha" name="captcha" required><br><br>
        <img src="captcha.php" alt="Captcha" id="captcha-image"><br><br>
        <button type="button" id="refresh-captcha" onclick="refreshCaptcha()">Refresh Captcha</button><br><br>

        <!-- Timer Display -->
        <div id="timer">Time remaining: 2:00</div>
        <canvas id="timer-canvas" width="100" height="100" style="border: 1px solid #ccc; margin-top: 10px;"></canvas>
        <br>

        <!-- City -->
        <label for="city">City:</label>  
        <input type="text" id="city" name="city" required><br><br>  

        <!-- Business -->
        <label for="business">Business:</label>  
        <input type="text" id="business" name="business"><br><br>  

        <!-- Submit Button -->
        <input type="submit" class="next-button" value="Save Appointment">  
    </form>
</body>
</html>

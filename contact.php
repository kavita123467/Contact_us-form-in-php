<?php  
$date = $_POST['date'] ?? '';  
$time = $_POST['time'] ?? '';  
?>
<script src="script.js"></script>  

<form action="check_availability.php" method="post" id="appointment-form" name="">  
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
    <button type="button" id="refresh-captcha">Refresh Captcha</button><br><br>
    

  <!-- City -->
  <label for="city">City:</label>  
  <input type="text" id="city" name="city" required><br><br>  

  <!-- Business -->
  <label for="business">Business:</label>  
  <input type="text" id="business" name="business"><br><br>  

  <!-- Submit Button -->
  <input type="submit" class="next-button" value="Save Appointment">  
</form>

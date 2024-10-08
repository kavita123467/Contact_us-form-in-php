<?php  
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Generate captcha and store in session only if it's not already set
if (!isset($_SESSION['captcha'])) {
    $_SESSION['captcha'] = rand(1000, 9999);
}

// Include PHPMailer autoload file
require 'vendor/autoload.php';

// Database credentials
$host = 'localhost';   
$db = 'contact_form_db';   
$user = 'newuser';   
$pass = 'root123';   

// Create a new MySQLi connection
$mysqli = new mysqli($host, $user, $pass, $db);  

// Check connection
if ($mysqli->connect_error) {   
    die("Connection failed: " . $mysqli->connect_error);   
}

// Retrieve form data
$date = $_POST['date'] ?? '';   
$time = $_POST['time'] ?? '';   
$timezone = 'Asia/Kolkata';  
$name = $_POST['name'] ?? '';   
$email = $_POST['email'] ?? '';   
$phone = $_POST['phone'] ?? '';   
$city = $_POST['city'] ?? '';   
$business = $_POST['business'] ?? '';   
$captcha = $_POST['captcha'] ?? '';  // User inputted captcha

// Validation
$errors = [];

// Check required fields
if (empty($date)) {   
    $errors[] = 'Date is required';   
}
if (empty($time)) {   
    $errors[] = 'Time is required';   
}
if (empty($name)) {   
    $errors[] = 'Name is required';   
}
if (empty($email)) {   
    $errors[] = 'Email is required';   
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {   
    $errors[] = 'Invalid email format';   
}
if (empty($phone)) {   
    $errors[] = 'Phone is required';   
}

// Disable past date selection
$today = new DateTime('now', new DateTimeZone($timezone));
$today_date = $today->format('Y-m-d');

if ($date < $today_date) {
    $errors[] = 'Cannot select a past date.';
}

$captcha = $_POST['captcha'] ?? '';  // Get user input CAPTCHA

// Debugging: Print user input and session-stored CAPTCHA for comparison
echo 'User input: ' . $captcha . '<br>';
echo 'Session CAPTCHA: ' . $_SESSION['captcha'] . '<br>';

// Captcha Validation
if (empty($captcha)) {  
    $errors[] = 'Captcha is required.';  
} elseif (!isset($_SESSION['captcha'])) {  
    $errors[] = 'Captcha session is not set. Please reload the page and try again.';  
} elseif (strtolower($captcha) !== strtolower($_SESSION['captcha'])) {  
    $errors[] = 'Invalid captcha. Please try again.';  
} else {  
    // Clear captcha after successful validation to prevent reuse
    unset($_SESSION['captcha']);  
}

// Check if there are any errors
if (!empty($errors)) {   
    $response = array('status' => 'error', 'message' => implode('<br>', $errors));  
    echo json_encode($response);  
    exit;   
}

// Proceed if there are no errors (valid captcha and form data)
if (empty($errors)) {  
    // Convert the selected time to IST (Indian Standard Time)
    $datetime = new DateTime($date . ' ' . $time, new DateTimeZone($timezone));  
    $datetime->setTimezone(new DateTimeZone('Asia/Kolkata'));  
    $time_in_ist = $datetime->format('H:i');  
    $date_in_ist = $datetime->format('Y-m-d');  

    // Check if the selected time slot is already booked
    $check_query = "SELECT * FROM contact_form WHERE date = ? AND time = ? AND booked = 1";  
    $check_stmt = $mysqli->prepare($check_query);  
    $check_stmt->bind_param('ss', $date_in_ist, $time_in_ist);  
    $check_stmt->execute();  
    $check_result = $check_stmt->get_result();  

    if ($check_result->num_rows > 0) {  
        // Time slot already booked
        $response = array('status' => 'error', 'message' => 'The selected time slot is already booked. Please choose another time.');  
        echo json_encode($response);  
    } else {  
        // Insert appointment into the database
        $query = "INSERT INTO contact_form (date, time, name, email, phone, city, business, booked) VALUES (?, ?, ?, ?, ?, ?, ?, 1)";  
        $stmt = $mysqli->prepare($query);  

        if ($stmt) {  
            // Bind the parameters and execute the query
            $stmt->bind_param('sssssss', $date_in_ist, $time_in_ist, $name, $email, $phone, $city, $business);  

            if ($stmt->execute()) {  
                // Appointment booked successfully
                $response = array('status' => 'success', 'message' => 'Appointment booked successfully!');  
                echo json_encode($response);

                // Send confirmation email to host
                $to = "perfectiongeeks@gmail.com"; 
                $subject = "New Appointment Request";
                $message = '
                <html>
                <body>
                <p>Dear Host,</p>
                <p>' . $name . ' has booked an appointment. Details are:</p>
                <p>Date: ' . $date_in_ist . '</p>
                <p>Time: ' . $time_in_ist . '</p>
                <p>Phone: ' . $phone . '</p>
                <p>Business: ' . $business . '</p>
                </body>
                </html>';

                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                try {
                    $mail->isSMTP();                                      
                    $mail->Host = 'smtp.gmail.com';                       
                    $mail->SMTPAuth = true;                               
                    $mail->Username = 'perfectiongeeks@gmail.com';                    
                    $mail->Password = 'odloplohkpmuyqhq';             
                    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;                                  
                    $mail->setFrom($email, $name);
                    $mail->addAddress($to);
                    $mail->isHTML(true);                                  
                    $mail->Subject = $subject;
                    $mail->Body = $message;
                    $mail->send();
                } catch (Exception $e) {
                    // Handle mail error
                    $response = array('status' => 'error', 'message' => 'Mail could not be sent.');  
                    echo json_encode($response);  
                }
            } else {  
                // Handle SQL error
                $response = array('status' => 'error', 'message' => 'Error: ' . $stmt->error);  
                echo json_encode($response);  
            }  
        } else {  
            // Handle SQL error
            $response = array('status' => 'error', 'message' => 'Error: ' . $mysqli->error);  
            echo json_encode($response);  
        }  
    }

    // Close statements and connections
    $check_stmt->close();  
    $stmt->close();  
    $mysqli->close();  
}  

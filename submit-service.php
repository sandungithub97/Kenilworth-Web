<?php
require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $name = $_POST["username"];
    $email = $_POST["email"];
    $contact = $_POST["contact"];
    $service = $_POST["service"];
    $solution = $_POST["solution"];

    // Create a new PHPMailer instance
    $mail = new PHPMailer();
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'Kenilworth.online@gmail.com';
    $mail->Password = 'mciqykfeszzotzto';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('Kenilworth.online@gmail.com', 'Kenilworth Services');
    $mail->addAddress('reachus@kenilworthinternational.com');

    $mail->isHTML(true);
    $mail->Subject = "New Client Inquiry for $service";
    $mail->Body = "Name: $name<br>Email: $email <br>Contact: $contact <br>Service: $service<br>Description : $solution";

    // Check if the email was sent successfully
    if ($mail->send()) {
        // Send success response
        echo json_encode(array("status" => "success"));
    } else {
        // Send error response
        echo json_encode(array("status" => "error", "message" => $mail->ErrorInfo));
    }
}
?>

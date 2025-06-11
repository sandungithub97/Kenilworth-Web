<?php
// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include PHPMailer files
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Failed to send email.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Honeypot field check
    if (!empty($_POST['honeypot'])) {
        $response['message'] = "Spam detected.";
        echo json_encode($response);
        exit;
    }

    // Sanitize input
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $position = filter_input(INPUT_POST, 'position', FILTER_SANITIZE_STRING);
    $mobile = filter_input(INPUT_POST, 'mobile', FILTER_SANITIZE_STRING);

    // Validate required fields
    if (empty($name) || empty($position) || empty($mobile)) {
        $response['message'] = "All fields are required.";
        echo json_encode($response);
        exit;
    }

    // File upload handling
    $cv = $_FILES['cv'];
    if ($cv['size'] > 2097152) { // 2MB in bytes
        $response['message'] = "File size exceeds 2MB limit.";
        echo json_encode($response);
        exit;
    }

    // Allowed file types
    $allowedTypes = ['application/pdf'];
    if (!in_array($cv['type'], $allowedTypes)) {
        $response['message'] = "Invalid file type. Only PDF files are allowed.";
        echo json_encode($response);
        exit;
    }

    $recaptchaSecret = '6LdgNQUqAAAAAPmDNwBl9GwOSqIydw9QkrQnPzq2'; // Replace with your actual secret key
    $recaptchaResponse = $_POST['g-recaptcha-response'];
    $recaptchaUrl = 'https://www.google.com/recaptcha/api/siteverify';

    $recaptchaVerifyResponse = file_get_contents($recaptchaUrl . '?secret=' . $recaptchaSecret . '&response=' . $recaptchaResponse);
    $recaptchaVerifyResponseData = json_decode($recaptchaVerifyResponse);

    if (!$recaptchaVerifyResponseData->success) {
        $response['message'] = 'reCAPTCHA verification failed. Please try again.';
        echo json_encode($response);
        exit;
    }

    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $uploadFile = $uploadDir . uniqid() . '_' . basename($cv['name']);

    if (!move_uploaded_file($cv['tmp_name'], $uploadFile)) {
        $response['message'] = "Failed to upload file.";
        echo json_encode($response);
        exit;
    }

   // Email sending
   $mail = new PHPMailer(true);
   try {
       $email = new PHPMailer(TRUE);
       $email->isSMTP();
       $email->SMTPDebug = 2;
       $email->SMTPAuth = TRUE;
       $email->SMTPAutoTLS = FALSE;
       $email->SMTPSecure = "tls";                                    // Set mailer to use SMTP
       $mail->Host = 'smtpout.secureserver.net';             // Specify main and backup SMTP servers
       $mail->Username = 'careers@kenilworthinternational.com'; // SMTP username
       $mail->Password = 'careerspage@0118';                 // SMTP password
       $mail->Port = 80;                                    // TCP port to connect to

       // Recipients
       $mail->setFrom('reachus@kenilworthinternational.com', 'Kenilworth International');
       $mail->addAddress('reachus@kenilworthinternational.com');     // Add a recipient

       // Attachments
       $mail->addAttachment($uploadFile);                    // Add attachments

       // Content
       $mail->isHTML(false);                                 // Set email format to plain text
       $mail->Subject = 'Job Application: ' . $position;
       $mail->Body    = "Name: $name\nPosition: $position\nMobile: $mobile";

        $mail->send();
        $response['status'] = 'success';
        $response['message'] = 'Email sent successfully.';
    } catch (Exception $e) {
        $response['message'] = "Failed to send email. Mailer Error: {$mail->ErrorInfo}";
    }
}

echo json_encode($response);
?>

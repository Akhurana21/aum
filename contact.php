<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // PHPMailer installed via Composer

if (isset($_POST['send'])) {
    $userName = isset($_POST['name']) ? trim($_POST['name']) : '';
    $userEmail = isset($_POST['email']) ? trim($_POST['email']) : '';
    $userSub = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $userMessage = isset($_POST['message']) ? trim($_POST['message']) : '';
    // Backend validation matching frontend
    $errors = [];
    // Name: letters and spaces only, min 2 chars
    if (!preg_match('/^[A-Za-z\s]{2,}$/', $userName)) {
        $errors[] = 'Invalid name. Only letters and spaces, minimum 2 characters.';
    }
    // Email: valid email
    if (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address.';
    }
    // // Phone: 10-15 digits
    // if (!preg_match('/^\d{10,15}$/', $userPhone)) {
    //     $errors[] = 'Invalid phone number. Only 10 to 15 digits allowed.';
    // }
    if (!empty($errors)) {
        header("Location: error.html?msg=" . urlencode(implode(' ', $errors)));
        exit();
    }

        $mail = new PHPMailer(true);
        try {
            // Server config
            $mail->isSMTP();
            $mail->Host       = $_ENV['SMTP_HOST'];
            $mail->SMTPAuth = $_ENV['SMTP_AUTH'];
            // $mail->SMTPAuth   = ($_ENV['SMTP_AUTH'] === 'false');
            // if ($mail->SMTPAuth) {
            //     $mail->Username   = $_ENV['SMTP_USER'];
            //     $mail->Password   = $_ENV['SMTP_PASS'];
            // }
            $mail->SMTPSecure = $_ENV['SMTP_SECURE'];
            $mail->Port       = intval($_ENV['SMTP_PORT']);
            // Email headers
            $mail->setFrom('sales@aumindustrialpackaging.com', 'AUM Industrial Packaging');
            $mail->addAddress('sales@aumindustrialpackaging.com');        // Receiver email

            // Email body
            $mail->isHTML(true);
            $mail->Subject = "Aum Enquiry: $userSub";
            $mail->Body    = "<strong>Name:</strong> $userName<br>"
                . "<strong>Email:</strong> $userEmail<br>"
                . "<strong>Message:</strong> $userMessage<br>";
            $mail->AltBody = "Name: $userName\n"
                . "Email: $userEmail\n"
                . "Message: $userMessage\n";
            $mail->send();
            header("Location: success.html");
            exit();
        } catch (Exception $e) {
            header("Location: error.html?msg=" . urlencode("Mailer Error: " . $mail->ErrorInfo));
            exit();
        }
} else {
    header("Location: error.html");
    exit();
}
// Load .env config if not already loaded
if (!isset($_ENV['RECAPTCHA_SECRET'])) {
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        require_once __DIR__ . '/vendor/autoload.php';
    }
    if (class_exists('Dotenv\\Dotenv')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();
    }
}
$secret = $_ENV['RECAPTCHA_SECRET'];
$response = $_POST["g-recaptcha-response"];
$remoteip = $_SERVER["REMOTE_ADDR"];

$verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secret&response=$response&remoteip=$remoteip");
$captcha_success = json_decode($verify);

if ($captcha_success->success) {
    echo "CAPTCHA verified. Form submitted.";
    // Continue processing...
} else {
    echo "CAPTCHA verification failed.";
}
?>

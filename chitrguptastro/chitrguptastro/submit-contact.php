<?php
ob_start();

ini_set('display_errors', '0');
error_reporting(E_ALL);

function jsonResponse(bool $status, string $message, array $extra = []): void
{
    if (ob_get_length()) {
        ob_clean();
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge(['status' => $status, 'message' => $message], $extra));
    exit;
}

function writeMailLog(string $text): void
{
    $logFile = __DIR__ . '/mail-error.log';
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $text . PHP_EOL;
    @file_put_contents($logFile, $line, FILE_APPEND);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method.');
}

require_once __DIR__ . '/admin/includes/db.php';

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$service = trim($_POST['service'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($name === '') {
    jsonResponse(false, 'Name is required.');
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(false, 'A valid email is required.');
}
if ($subject === '') {
    jsonResponse(false, 'Subject is required.');
}
if ($message === '') {
    jsonResponse(false, 'Message is required.');
}

$stmt = $conn->prepare('INSERT INTO enquiries (name, email, phone, service, subject, message, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
if (!$stmt) {
    jsonResponse(false, 'Database prepare failed: ' . $conn->error);
}

$stmt->bind_param('ssssss', $name, $email, $phone, $service, $subject, $message);
$inserted = $stmt->execute();
$insertId = (int)$stmt->insert_id;
$stmt->close();

if (!$inserted) {
    jsonResponse(false, 'Failed to save enquiry.');
}

$mailSent = false;
$mailError = '';

$phpMailerLoader = __DIR__ . '/vendor/PHPMailerAutoload.php';
if (file_exists($phpMailerLoader)) {
    require_once $phpMailerLoader;

    try {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'shivsingh4151@gmail.com';
        $mail->Password = 'zywosfshdrmfqrtz';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->SMTPAutoTLS = true;
        $mail->Timeout = 30;
        $mail->CharSet = 'UTF-8';
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
        ];

        $mail->setFrom('shivsingh4151@gmail.com', 'Website Enquiry');
        $mail->addAddress('shivsingh4151@gmail.com', 'Admin');
        // $mail->addAddress('chitraguptastrovastu@gmail.com', 'Admin');
        $mail->addReplyTo($email, $name);

        $mail->isHTML(true);
        $mail->Subject = 'New Enquiry: ' . $subject;
        $mail->Body =
            '<h3>New Contact Enquiry</h3>' .
            '<p><strong>Name:</strong> ' . htmlspecialchars($name) . '</p>' .
            '<p><strong>Email:</strong> ' . htmlspecialchars($email) . '</p>' .
            '<p><strong>Phone:</strong> ' . htmlspecialchars($phone !== '' ? $phone : '-') . '</p>' .
            '<p><strong>Service:</strong> ' . htmlspecialchars($service !== '' ? $service : '-') . '</p>' .
            '<p><strong>Subject:</strong> ' . htmlspecialchars($subject) . '</p>' .
            '<p><strong>Message:</strong><br>' . nl2br(htmlspecialchars($message)) . '</p>';

        $mail->AltBody =
            "New Contact Enquiry\n\n" .
            "Name: {$name}\n" .
            "Email: {$email}\n" .
            'Phone: ' . ($phone !== '' ? $phone : '-') . "\n" .
            'Service: ' . ($service !== '' ? $service : '-') . "\n" .
            "Subject: {$subject}\n" .
            "Message:\n{$message}";

        $mail->send();
        $mailSent = true;
    } catch (Exception $e) {
        $mailError = $e->getMessage();
        writeMailLog('PHPMailer Exception: ' . $mailError);
    } catch (Throwable $e) {
        $mailError = $e->getMessage();
        writeMailLog('Throwable: ' . $mailError);
    }
} else {
    $mailError = 'PHPMailer loader not found at vendor/PHPMailerAutoload.php.';
    writeMailLog($mailError);
}

if ($mailSent) {
    jsonResponse(true, 'Mail sent successfully.', ['id' => $insertId]);
}

jsonResponse(false, 'Mail could not be sent. Please try again.', [
    'id' => $insertId,
    'mail_warning' => $mailError
]);

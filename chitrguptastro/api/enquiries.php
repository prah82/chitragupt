<?php
declare(strict_types=1);
require_once __DIR__ . '/_bootstrap.php';
try {
    $method = api_method();
    if ($method === 'GET') {
        // Fetch all records
        $stmt = $conn->prepare('SELECT id, name, email, phone, service, subject, message, created_at FROM enquiries ORDER BY id DESC');
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($r = $res->fetch_assoc()) $rows[] = $r;
        api_response(true, 'Enquiries fetched successfully.', ['data' => $rows]);
    }
    if ($method === 'POST') {
        // Validate required fields
        $name = trim((string)($_POST['name'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $phone = trim((string)($_POST['phone'] ?? ''));
        $service = trim((string)($_POST['service'] ?? ''));
        $subject = trim((string)($_POST['subject'] ?? ''));
        $message = trim((string)($_POST['message'] ?? ''));
        if ($name === '' || $email === '' || $subject === '' || $message === '') api_response(false, 'name, email, subject, message are required.', [], 422);
        $stmt = $conn->prepare('INSERT INTO enquiries (name, email, phone, service, subject, message) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('ssssss', $name, $email, $phone, $service, $subject, $message);
        $stmt->execute();
        api_response(true, 'Enquiry created successfully.', ['id' => (int)$stmt->insert_id]);
    }
    if ($method === 'PUT') {
        $in = api_input();
        $id = (int)($in['id'] ?? 0);
        $subject = trim((string)($in['subject'] ?? ''));
        $message = trim((string)($in['message'] ?? ''));
        if ($id <= 0 || $subject === '' || $message === '') api_response(false, 'Valid id, subject, message are required.', [], 422);
        $stmt = $conn->prepare('UPDATE enquiries SET subject=?, message=? WHERE id=?');
        $stmt->bind_param('ssi', $subject, $message, $id);
        $stmt->execute();
        api_response(true, 'Enquiry updated successfully.');
    }
    if ($method === 'DELETE') {
        $in = api_input();
        $id = (int)($in['id'] ?? ($_GET['id'] ?? 0));
        if ($id <= 0) api_response(false, 'Valid id is required.', [], 422);
        $stmt = $conn->prepare('DELETE FROM enquiries WHERE id=?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        api_response(true, 'Enquiry deleted successfully.');
    }
    api_response(false, 'Method not allowed.', [], 405);
} catch (Throwable $e) { api_response(false, 'Server error: ' . $e->getMessage(), [], 500); }

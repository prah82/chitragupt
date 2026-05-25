<?php
declare(strict_types=1);
require_once __DIR__ . '/_bootstrap.php';
$uploadDir = ADMIN_UPLOADS_ABS_PATH . '/slider/';
ensure_upload_dir($uploadDir);
$maxImageSize = 8 * 1024 * 1024;
try {
    $method = api_method();
    if ($method === 'GET') {
        // Fetch all records
        $stmt = $conn->prepare('SELECT id, image FROM slider ORDER BY id DESC');
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($r = $res->fetch_assoc()) { $r['image_url'] = public_image_url('slider', $r['image']); $rows[] = $r; }
        api_response(true, 'Sliders fetched successfully.', ['data' => $rows]);
    }
    if ($method === 'POST') {
        $upload = validate_and_upload_image($_FILES['image'] ?? [], 'slider', $uploadDir, $maxImageSize, false);
        if (!$upload['ok']) api_response(false, $upload['message'], [], 422);
        $image = (string)$upload['file'];
        $stmt = $conn->prepare('INSERT INTO slider (image) VALUES (?)');
        $stmt->bind_param('s', $image);
        $stmt->execute();
        api_response(true, 'Slider created successfully.', ['id' => (int)$stmt->insert_id, 'image_url' => public_image_url('slider', $image)]);
    }
    if ($method === 'PUT') {
        api_response(false, 'Update not required for slider. Use delete + create.', [], 422);
    }
    if ($method === 'DELETE') {
        $input = api_input();
        $id = (int)($input['id'] ?? ($_GET['id'] ?? 0));
        if ($id <= 0) api_response(false, 'Valid id is required.', [], 422);
        $stmt = $conn->prepare('SELECT image FROM slider WHERE id=?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $old = $stmt->get_result()->fetch_assoc();
        $stmt = $conn->prepare('DELETE FROM slider WHERE id=?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        if (!empty($old['image'])) { $p = $uploadDir . basename((string)$old['image']); if (is_file($p)) unlink($p); }
        api_response(true, 'Slider deleted successfully.');
    }
    api_response(false, 'Method not allowed.', [], 405);
} catch (Throwable $e) { api_response(false, 'Server error: ' . $e->getMessage(), [], 500); }


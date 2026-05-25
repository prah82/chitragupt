<?php
declare(strict_types=1);
require_once __DIR__ . '/_bootstrap.php';
$uploadDir = ADMIN_UPLOADS_ABS_PATH . '/gallery/';
ensure_upload_dir($uploadDir);
$maxImageSize = 8 * 1024 * 1024;
try {
    $method = api_method();
    if ($method === 'GET') {
        // Fetch all records
        $stmt = $conn->prepare('SELECT id, image, description FROM gallery ORDER BY id DESC');
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($r = $res->fetch_assoc()) { $r['image_url'] = public_image_url('gallery', $r['image']); $rows[] = $r; }
        api_response(true, 'Gallery fetched successfully.', ['data' => $rows]);
    }
    if ($method === 'POST') {
        $description = trim((string)($_POST['description'] ?? ''));
        $upload = validate_and_upload_image($_FILES['image'] ?? [], 'gallery', $uploadDir, $maxImageSize, false);
        if (!$upload['ok']) api_response(false, $upload['message'], [], 422);
        $image = (string)$upload['file'];
        $stmt = $conn->prepare('INSERT INTO gallery (image, description) VALUES (?, ?)');
        $stmt->bind_param('ss', $image, $description);
        $stmt->execute();
        api_response(true, 'Gallery item created successfully.', ['id' => (int)$stmt->insert_id, 'image_url' => public_image_url('gallery', $image)]);
    }
    if ($method === 'PUT') {
        $in = api_input();
        $id = (int)($in['id'] ?? 0);
        $description = trim((string)($in['description'] ?? ''));
        if ($id <= 0) api_response(false, 'Valid id is required.', [], 422);
        $stmt = $conn->prepare('UPDATE gallery SET description=? WHERE id=?');
        $stmt->bind_param('si', $description, $id);
        $stmt->execute();
        api_response(true, 'Gallery item updated successfully.');
    }
    if ($method === 'DELETE') {
        $in = api_input();
        $id = (int)($in['id'] ?? ($_GET['id'] ?? 0));
        if ($id <= 0) api_response(false, 'Valid id is required.', [], 422);
        $stmt = $conn->prepare('SELECT image FROM gallery WHERE id=?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $old = $stmt->get_result()->fetch_assoc();
        $stmt = $conn->prepare('DELETE FROM gallery WHERE id=?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        if (!empty($old['image'])) { $p = $uploadDir . basename((string)$old['image']); if (is_file($p)) unlink($p); }
        api_response(true, 'Gallery item deleted successfully.');
    }
    api_response(false, 'Method not allowed.', [], 405);
} catch (Throwable $e) { api_response(false, 'Server error: ' . $e->getMessage(), [], 500); }


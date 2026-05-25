<?php
declare(strict_types=1);
require_once __DIR__ . '/_bootstrap.php';

$uploadDir = ADMIN_UPLOADS_ABS_PATH . '/service/';
ensure_upload_dir($uploadDir);
$maxImageSize = 5 * 1024 * 1024;

try {
    $method = api_method();

    if ($method === 'GET') {
        // Fetch all records
        $activeOnly = isset($_GET['active']) && $_GET['active'] === '1';
        $where = $activeOnly ? " WHERE status='active'" : '';
        $stmt = $conn->prepare('SELECT id, title, short_description, image, tags, category, status FROM service' . $where . ' ORDER BY id DESC');
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($row = $res->fetch_assoc()) {
            $row['image_url'] = public_image_url('service', $row['image']);
            $rows[] = $row;
        }
        api_response(true, 'Services fetched successfully.', ['data' => $rows]);
    }

    if ($method === 'POST') {
        // Validate required fields
        $title = trim((string)($_POST['title'] ?? ''));
        $short = trim((string)($_POST['short_description'] ?? ''));
        $tags = trim((string)($_POST['tags'] ?? ''));
        $category = trim((string)($_POST['category'] ?? 'what_we_offer'));
        $status = trim((string)($_POST['status'] ?? 'active'));
        if ($title === '' || $short === '') api_response(false, 'Title and short_description are required.', [], 422);

        $upload = validate_and_upload_image($_FILES['image'] ?? [], 'service', $uploadDir, $maxImageSize, false);
        if (!$upload['ok']) api_response(false, $upload['message'], [], 422);

        $image = (string)$upload['file'];
        $empty = null;
        $stmt = $conn->prepare('INSERT INTO service (title, short_description, full_description, duration, fees, image, tags, category, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('sssssssss', $title, $short, $empty, $empty, $empty, $image, $tags, $category, $status);
        $stmt->execute();
        api_response(true, 'Service created successfully.', ['id' => (int)$stmt->insert_id, 'image_url' => public_image_url('service', $image)]);
    }

    if ($method === 'PUT') {
        $input = api_input();
        $id = (int)($input['id'] ?? 0);
        $title = trim((string)($input['title'] ?? ''));
        $short = trim((string)($input['short_description'] ?? ''));
        $tags = trim((string)($input['tags'] ?? ''));
        $category = trim((string)($input['category'] ?? 'what_we_offer'));
        $status = trim((string)($input['status'] ?? 'active'));
        if ($id <= 0 || $title === '' || $short === '') api_response(false, 'Valid id, title and short_description are required.', [], 422);

        $stmt = $conn->prepare('UPDATE service SET title=?, short_description=?, tags=?, category=?, status=? WHERE id=?');
        $stmt->bind_param('sssssi', $title, $short, $tags, $category, $status, $id);
        $stmt->execute();
        api_response(true, 'Service updated successfully.');
    }

    if ($method === 'DELETE') {
        $input = api_input();
        $id = (int)($input['id'] ?? ($_GET['id'] ?? 0));
        if ($id <= 0) api_response(false, 'Valid id is required.', [], 422);

        $stmt = $conn->prepare('SELECT image FROM service WHERE id=?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $old = $stmt->get_result()->fetch_assoc();

        $stmt = $conn->prepare('DELETE FROM service WHERE id=?');
        $stmt->bind_param('i', $id);
        $stmt->execute();

        if (!empty($old['image'])) {
            $oldPath = $uploadDir . basename((string)$old['image']);
            if (is_file($oldPath)) unlink($oldPath); // Delete old image from folder
        }
        api_response(true, 'Service deleted successfully.');
    }

    api_response(false, 'Method not allowed.', [], 405);
} catch (Throwable $e) {
    api_response(false, 'Server error: ' . $e->getMessage(), [], 500);
}


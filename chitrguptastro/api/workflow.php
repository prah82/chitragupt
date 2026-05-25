<?php
declare(strict_types=1);
require_once __DIR__ . '/_bootstrap.php';

$uploadDir = ADMIN_UPLOADS_ABS_PATH . '/workflow/';
ensure_upload_dir($uploadDir);
$maxImageSize = 2 * 1024 * 1024;

try {
    $method = api_method();

    if ($method === 'GET') {
        // Fetch all records
        $stmt = $conn->prepare('SELECT id, image, title, description, created_at, updated_at FROM workflow ORDER BY id ASC');
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($r = $res->fetch_assoc()) {
            $r['image_url'] = public_image_url('workflow', $r['image']);
            $rows[] = $r;
        }
        api_response(true, 'Workflow fetched successfully.', ['data' => $rows]);
    }

    if ($method === 'POST') {
        // Validate required fields
        $title = trim((string)($_POST['title'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        if ($title === '' || $description === '') {
            api_response(false, 'title and description are required.', [], 422);
        }

        // Upload image safely
        $upload = validate_and_upload_image($_FILES['image'] ?? [], 'workflow', $uploadDir, $maxImageSize, false);
        if (!$upload['ok']) {
            api_response(false, $upload['message'], [], 422);
        }

        $image = (string)$upload['file'];
        $stmt = $conn->prepare('INSERT INTO workflow (image, title, description, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())');
        $stmt->bind_param('sss', $image, $title, $description);
        $stmt->execute();
        api_response(true, 'Workflow created successfully.', ['id' => (int)$stmt->insert_id, 'image_url' => public_image_url('workflow', $image)]);
    }

    if ($method === 'PUT') {
        $in = api_input();
        $id = (int)($in['id'] ?? 0);
        $title = trim((string)($in['title'] ?? ''));
        $description = trim((string)($in['description'] ?? ''));
        if ($id <= 0 || $title === '' || $description === '') {
            api_response(false, 'Valid id, title and description are required.', [], 422);
        }

        $stmt = $conn->prepare('UPDATE workflow SET title=?, description=?, updated_at=NOW() WHERE id=?');
        $stmt->bind_param('ssi', $title, $description, $id);
        $stmt->execute();
        api_response(true, 'Workflow updated successfully.');
    }

    if ($method === 'DELETE') {
        $in = api_input();
        $id = (int)($in['id'] ?? ($_GET['id'] ?? 0));
        if ($id <= 0) {
            api_response(false, 'Valid id is required.', [], 422);
        }

        $stmt = $conn->prepare('SELECT image FROM workflow WHERE id=?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $old = $stmt->get_result()->fetch_assoc();
        if (!$old) {
            api_response(false, 'Workflow record not found.', [], 404);
        }

        $stmt = $conn->prepare('DELETE FROM workflow WHERE id=?');
        $stmt->bind_param('i', $id);
        $stmt->execute();

        if (!empty($old['image'])) {
            $oldPath = $uploadDir . basename((string)$old['image']);
            if (is_file($oldPath)) {
                unlink($oldPath); // Delete old image from folder
            }
        }

        api_response(true, 'Workflow deleted successfully.');
    }

    api_response(false, 'Method not allowed.', [], 405);
} catch (Throwable $e) {
    api_response(false, 'Server error: ' . $e->getMessage(), [], 500);
}

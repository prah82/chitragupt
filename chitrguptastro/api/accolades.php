<?php
declare(strict_types=1);
require_once __DIR__ . '/_bootstrap.php';

$uploadDir = ADMIN_UPLOADS_ABS_PATH . '/accolades/';
ensure_upload_dir($uploadDir);
$maxImageSize = 5 * 1024 * 1024;

try {
    $method = api_method();

    if ($method === 'GET') {
        // Fetch all records
        $activeOnly = isset($_GET['active']) && $_GET['active'] === '1';
        $sql = 'SELECT id, title, image, tags FROM accolades ORDER BY id DESC';
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($row = $res->fetch_assoc()) {
            $row['image_url'] = public_image_url('accolades', $row['image']);
            $rows[] = $row;
        }
        api_response(true, 'Accolades fetched successfully.', ['data' => $rows, 'active_filter' => $activeOnly]);
    }

    if ($method === 'POST') {
        // Validate required fields
        $title = trim((string)($_POST['title'] ?? ''));
        $tags = trim((string)($_POST['tags'] ?? ''));
        if ($title === '') {
            api_response(false, 'Title is required.', [], 422);
        }

        // Upload image safely
        $upload = validate_and_upload_image($_FILES['image'] ?? [], 'accolade', $uploadDir, $maxImageSize, true);
        if (!$upload['ok']) {
            api_response(false, $upload['message'], [], 422);
        }

        $image = (string)$upload['file'];
        $stmt = $conn->prepare('INSERT INTO accolades (title, image, tags) VALUES (?, ?, ?)');
        $stmt->bind_param('sss', $title, $image, $tags);
        $stmt->execute();

        api_response(true, 'Accolade created successfully.', ['id' => (int)$stmt->insert_id, 'image_url' => public_image_url('accolades', $image)]);
    }

    if ($method === 'PUT') {
        $input = api_input();
        $id = (int)($input['id'] ?? 0);
        $title = trim((string)($input['title'] ?? ''));
        $tags = trim((string)($input['tags'] ?? ''));
        if ($id <= 0 || $title === '') {
            api_response(false, 'Valid id and title are required.', [], 422);
        }

        $stmt = $conn->prepare('SELECT image FROM accolades WHERE id=?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $old = $stmt->get_result()->fetch_assoc();
        if (!$old) {
            api_response(false, 'Record not found.', [], 404);
        }

        $stmt = $conn->prepare('UPDATE accolades SET title=?, tags=? WHERE id=?');
        $stmt->bind_param('ssi', $title, $tags, $id);
        $stmt->execute();
        api_response(true, 'Accolade updated successfully.');
    }

    if ($method === 'DELETE') {
        $input = api_input();
        $id = (int)($input['id'] ?? ($_GET['id'] ?? 0));
        if ($id <= 0) {
            api_response(false, 'Valid id is required.', [], 422);
        }

        $stmt = $conn->prepare('SELECT image FROM accolades WHERE id=?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $old = $stmt->get_result()->fetch_assoc();
        if (!$old) {
            api_response(false, 'Record not found.', [], 404);
        }

        $stmt = $conn->prepare('DELETE FROM accolades WHERE id=?');
        $stmt->bind_param('i', $id);
        $stmt->execute();

        if (!empty($old['image'])) {
            $oldPath = $uploadDir . basename((string)$old['image']);
            if (is_file($oldPath)) {
                unlink($oldPath); // Delete old image from folder
            }
        }

        api_response(true, 'Accolade deleted successfully.');
    }

    api_response(false, 'Method not allowed.', [], 405);
} catch (Throwable $e) {
    api_response(false, 'Server error: ' . $e->getMessage(), [], 500);
}


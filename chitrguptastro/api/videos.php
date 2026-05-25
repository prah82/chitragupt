<?php
declare(strict_types=1);
require_once __DIR__ . '/_bootstrap.php';
$uploadDir = ADMIN_UPLOADS_ABS_PATH . '/video/';
ensure_upload_dir($uploadDir);
$maxVideoSize = 150 * 1024 * 1024;
try {
    $method = api_method();
    if ($method === 'GET') {
        // Fetch all records
        $stmt = $conn->prepare('SELECT id, video, description FROM video ORDER BY id DESC');
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($r = $res->fetch_assoc()) {
            $r['video_url'] = public_image_url('video', $r['video']);
            $rows[] = $r;
        }
        api_response(true, 'Videos fetched successfully.', ['data' => $rows]);
    }
    if ($method === 'POST') {
        $description = trim((string)($_POST['description'] ?? ''));
        if (empty($_FILES['video']['name'])) api_response(false, 'Video file is required.', [], 422);
        $ext = strtolower(pathinfo(basename((string)$_FILES['video']['name']), PATHINFO_EXTENSION));
        if (!in_array($ext, ['mp4','webm','ogg','mov'], true)) api_response(false, 'Only mp4, webm, ogg, mov allowed.', [], 422);
        $size = (int)($_FILES['video']['size'] ?? 0);
        if ($size <= 0 || $size > $maxVideoSize) api_response(false, 'Video size must be <= 150 MB.', [], 422);
        $name = 'video_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        if (!move_uploaded_file((string)$_FILES['video']['tmp_name'], $uploadDir . $name)) api_response(false, 'Video upload failed.', [], 500);
        $stmt = $conn->prepare('INSERT INTO video (video, description) VALUES (?, ?)');
        $stmt->bind_param('ss', $name, $description);
        $stmt->execute();
        api_response(true, 'Video created successfully.', ['id' => (int)$stmt->insert_id, 'video_url' => public_image_url('video', $name)]);
    }
    if ($method === 'PUT') {
        $in = api_input();
        $id = (int)($in['id'] ?? 0);
        $description = trim((string)($in['description'] ?? ''));
        if ($id <= 0) api_response(false, 'Valid id is required.', [], 422);
        $stmt = $conn->prepare('UPDATE video SET description=? WHERE id=?');
        $stmt->bind_param('si', $description, $id);
        $stmt->execute();
        api_response(true, 'Video updated successfully.');
    }
    if ($method === 'DELETE') {
        $in = api_input();
        $id = (int)($in['id'] ?? ($_GET['id'] ?? 0));
        if ($id <= 0) api_response(false, 'Valid id is required.', [], 422);
        $stmt = $conn->prepare('SELECT video FROM video WHERE id=?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $old = $stmt->get_result()->fetch_assoc();
        $stmt = $conn->prepare('DELETE FROM video WHERE id=?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        if (!empty($old['video'])) { $p = $uploadDir . basename((string)$old['video']); if (is_file($p)) unlink($p); }
        api_response(true, 'Video deleted successfully.');
    }
    api_response(false, 'Method not allowed.', [], 405);
} catch (Throwable $e) { api_response(false, 'Server error: ' . $e->getMessage(), [], 500); }


<?php
// Shared API bootstrap: JSON output, DB include, request helpers, and upload validation.
declare(strict_types=1);

ob_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/app_paths.php';
require_once ADMIN_ABS_PATH . '/includes/db.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function api_response(bool $ok, string $message, array $extra = [], int $statusCode = 200): void
{
    if (ob_get_length()) {
        ob_clean(); // Return JSON response
    }
    http_response_code($statusCode);
    echo json_encode(array_merge(['ok' => $ok, 'message' => $message], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

function api_method(): string
{
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    if ($method === 'POST' && isset($_POST['_method'])) {
        return strtoupper((string)$_POST['_method']);
    }
    return $method;
}

function api_input(): array
{
    $raw = file_get_contents('php://input') ?: '';
    if ($raw === '') {
        return [];
    }

    $json = json_decode($raw, true);
    if (is_array($json)) {
        return $json;
    }

    $parsed = [];
    parse_str($raw, $parsed);
    return is_array($parsed) ? $parsed : [];
}

function ensure_upload_dir(string $dir): void
{
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

function public_image_url(string $folder, ?string $name): ?string
{
    if ($name === null || $name === '') {
        return null;
    }
    return ADMIN_UPLOADS_PUBLIC_URL . '/' . trim($folder, '/') . '/' . basename($name);
}

function validate_and_upload_image(array $file, string $prefix, string $uploadDir, int $maxSizeBytes, bool $requireSquare = false): array
{
    // Validate required fields
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return ['ok' => false, 'message' => 'Image file is required.'];
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'message' => 'Image upload failed with error code: ' . (int)$file['error']];
    }

    $originalName = basename((string)($file['name'] ?? '')); // Upload image safely
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($ext, $allowed, true)) {
        return ['ok' => false, 'message' => 'Only jpg, jpeg, png, webp allowed.'];
    }

    $size = (int)($file['size'] ?? 0);
    if ($size <= 0 || $size > $maxSizeBytes) {
        return ['ok' => false, 'message' => 'Image size exceeds limit.'];
    }

    $tmp = (string)($file['tmp_name'] ?? '');
    if (!is_uploaded_file($tmp)) {
        return ['ok' => false, 'message' => 'Invalid uploaded file source.'];
    }

    $imgInfo = @getimagesize($tmp);
    if ($imgInfo === false) {
        return ['ok' => false, 'message' => 'Invalid image file.'];
    }

    if ($requireSquare) {
        $width = (int)($imgInfo[0] ?? 0);
        $height = (int)($imgInfo[1] ?? 0);
        if ($width <= 0 || $height <= 0 || $width !== $height) {
            return ['ok' => false, 'message' => 'Accolades image must be 1:1 square ratio (example: 500x500).'];
        }
    }

    $fileName = $prefix . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext; // unique upload filename
    $target = rtrim($uploadDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;

    if (!move_uploaded_file($tmp, $target)) {
        return ['ok' => false, 'message' => 'Failed to move uploaded image.'];
    }

    return ['ok' => true, 'file' => $fileName];
}

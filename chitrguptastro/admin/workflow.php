<?php
$pageTitle = 'Workflow';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';


$uploadDir = __DIR__ . '/uploads/workflow/';
$uploadUrl = 'uploads/workflow/';
$maxFileSize = 2 * 1024 * 1024; // 2MB
$allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
$allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

function redirectWithMessage(string $type, string $message): void
{
    header('Location: workflow.php?msg_type=' . urlencode($type) . '&msg=' . urlencode($message));
    exit;
}

function removeWorkflowImage(string $fileName, string $uploadDir): void
{
    if ($fileName === '') {
        return;
    }

    $safeName = basename($fileName);
    $fullPath = $uploadDir . $safeName;
    if (is_file($fullPath)) {
        unlink($fullPath);
    }
}

function validateAndUploadImage(array $file, array $allowedExtensions, array $allowedMimes, int $maxFileSize, string $uploadDir, ?string &$errorMessage): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        $errorMessage = 'Image upload failed. Please try again.';
        return null;
    }

    if (($file['size'] ?? 0) <= 0 || ($file['size'] ?? 0) > $maxFileSize) {
        $errorMessage = 'Image size must be between 1 byte and 2 MB.';
        return null;
    }

    $originalName = $file['name'] ?? '';
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions, true)) {
        $errorMessage = 'Only JPG, JPEG, PNG, and WEBP images are allowed.';
        return null;
    }

    $tmpPath = $file['tmp_name'] ?? '';
    if (!is_uploaded_file($tmpPath)) {
        $errorMessage = 'Invalid upload source detected.';
        return null;
    }

    $imageInfo = @getimagesize($tmpPath);
    if ($imageInfo === false) {
        $errorMessage = 'Uploaded file is not a valid image.';
        return null;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $detectedMime = $finfo ? finfo_file($finfo, $tmpPath) : '';
    if ($finfo) {
        finfo_close($finfo);
    }

    if (!in_array($detectedMime, $allowedMimes, true)) {
        $errorMessage = 'Invalid image type. Only JPG, PNG, and WEBP are accepted.';
        return null;
    }

    $newFileName = 'workflow_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $extension;
    $destination = $uploadDir . $newFileName;

    if (!move_uploaded_file($tmpPath, $destination)) {
        $errorMessage = 'Failed to save uploaded image.';
        return null;
    }

    return $newFileName;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            redirectWithMessage('err', 'Invalid workflow record.');
        }

        $stmt = $conn->prepare('SELECT image FROM workflow WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) {
            redirectWithMessage('err', 'Workflow record not found.');
        }

        $stmt = $conn->prepare('DELETE FROM workflow WHERE id = ?');
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            removeWorkflowImage((string)($row['image'] ?? ''), $uploadDir);
            redirectWithMessage('ok', 'Workflow deleted successfully.');
        }

        redirectWithMessage('err', 'Failed to delete workflow.');
    }

    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $currentImage = trim($_POST['current_image'] ?? '');

        if ($title === '' || $description === '') {
            redirectWithMessage('err', 'Please fill all required fields.');
        }

        $imageError = null;
        $uploadedImage = validateAndUploadImage(
            $_FILES['image'] ?? [],
            $allowedExtensions,
            $allowedMimes,
            $maxFileSize,
            $uploadDir,
            $imageError
        );

        if ($imageError !== null) {
            redirectWithMessage('err', $imageError);
        }

        if ($id > 0) {
            $stmt = $conn->prepare('SELECT image FROM workflow WHERE id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $existing = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$existing) {
                if ($uploadedImage !== null) {
                    removeWorkflowImage($uploadedImage, $uploadDir);
                }
                redirectWithMessage('err', 'Workflow record not found.');
            }

            $oldImage = (string)($existing['image'] ?? '');
            $finalImage = $uploadedImage ?? $oldImage;

            $stmt = $conn->prepare('UPDATE workflow SET image = ?, title = ?, description = ?, updated_at = NOW() WHERE id = ?');
            $stmt->bind_param('sssi', $finalImage, $title, $description, $id);
            $ok = $stmt->execute();
            $stmt->close();

            if ($ok) {
                if ($uploadedImage !== null && $oldImage !== '' && $oldImage !== $uploadedImage) {
                    removeWorkflowImage($oldImage, $uploadDir);
                }
                redirectWithMessage('ok', 'Workflow updated successfully.');
            }

            if ($uploadedImage !== null) {
                removeWorkflowImage($uploadedImage, $uploadDir);
            }
            redirectWithMessage('err', 'Failed to update workflow.');
        }

        if ($uploadedImage === null) {
            redirectWithMessage('err', 'Image field is required.');
        }

        $stmt = $conn->prepare('INSERT INTO workflow (image, title, description, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())');
        $stmt->bind_param('sss', $uploadedImage, $title, $description);
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            redirectWithMessage('ok', 'Workflow saved successfully.');
        }

        removeWorkflowImage($uploadedImage, $uploadDir);
        redirectWithMessage('err', 'Failed to save workflow.');
    }
}

$msg = trim($_GET['msg'] ?? '');
$msgType = trim($_GET['msg_type'] ?? 'ok');
if (!in_array($msgType, ['ok', 'err'], true)) {
    $msgType = 'ok';
}

$editItem = null;
$editId = (int)($_GET['edit'] ?? 0);
if ($editId > 0) {
    $stmt = $conn->prepare('SELECT id, image, title, description FROM workflow WHERE id = ?');
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $editItem = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$list = $conn->query('SELECT id, image, title, description, created_at FROM workflow ORDER BY id DESC');

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<?php if ($msg !== ''): ?>
  <div class="alert <?php echo $msgType === 'ok' ? 'alert-success' : 'alert-danger'; ?> alert-dismissible fade show" role="alert">
    <?php echo htmlspecialchars($msg); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>

<div class="section card shadow-sm p-4 mb-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h3 class="mb-1"><?php echo $editItem ? 'Edit Workflow' : 'Add Workflow'; ?></h3>
      <p class="text-muted mb-0">Manage Workflow cards shown on frontend section.</p>
    </div>
  </div>

  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?php echo $editItem ? (int)$editItem['id'] : 0; ?>">
    <input type="hidden" name="current_image" value="<?php echo $editItem ? htmlspecialchars($editItem['image'] ?? '') : ''; ?>">

    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label for="image" class="form-label">Image <span class="text-danger">*</span></label>
        <input class="form-control" id="image" type="file" name="image" accept=".jpg,.jpeg,.png,.webp" <?php echo $editItem ? '' : 'required'; ?>>
        <div class="form-text">Accepted: JPG, JPEG, PNG, WEBP. Max: 2 MB.</div>
        <?php if ($editItem && !empty($editItem['image'])): ?>
          <div class="mt-2">
            <img class="img-thumbnail" src="<?php echo $uploadUrl . htmlspecialchars($editItem['image']); ?>" width="120" alt="Current Image">
            <div class="form-text">No new upload = existing image remains.</div>
          </div>
        <?php endif; ?>
      </div>

      <div class="col-md-6">
        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
        <input class="form-control" id="title" type="text" name="title" maxlength="255" required value="<?php echo $editItem ? htmlspecialchars($editItem['title']) : ''; ?>">
      </div>
    </div>

    <div class="mb-4">
      <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
      <textarea class="form-control" id="description" name="description" rows="3" required><?php echo $editItem ? htmlspecialchars($editItem['description']) : ''; ?></textarea>
    </div>

    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-primary"><?php echo $editItem ? 'Update Workflow' : 'Save Workflow'; ?></button>
      <?php if ($editItem): ?>
        <a class="btn btn-secondary" href="workflow.php">Cancel Edit</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<div class="section card shadow-sm p-4">
  <h3 class="mb-3">Saved Workflow List</h3>

  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th style="width:120px;">Image</th>
          <th>Title</th>
          <th>Description</th>
          <th style="width:170px;">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($list && $list->num_rows > 0): ?>
          <?php while ($row = $list->fetch_assoc()): ?>
            <tr>
              <td>
                <?php if (!empty($row['image'])): ?>
                  <img class="img-thumbnail" src="<?php echo $uploadUrl . htmlspecialchars($row['image']); ?>" width="80" alt="Workflow Image">
                <?php else: ?>
                  N/A
                <?php endif; ?>
              </td>
              <td><?php echo htmlspecialchars($row['title']); ?></td>
              <td><?php echo nl2br(htmlspecialchars($row['description'])); ?></td>
              <td>
                <div class="d-flex gap-2">
                  <a class="btn btn-sm btn-primary" href="workflow.php?edit=<?php echo (int)$row['id']; ?>">Edit</a>
                  <form method="post" onsubmit="return confirm('Delete this workflow record?');" class="m-0">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="4" class="text-center text-muted">No workflow records found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

</main></div></body></html>

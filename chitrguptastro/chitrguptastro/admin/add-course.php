<?php
$pageTitle = 'Add Course';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$msg = '';
$msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $shortDescription = trim($_POST['short_description'] ?? '');
    $tags = trim($_POST['tags'] ?? '');
    $category = trim($_POST['category'] ?? 'what_we_offer');
    $status = strtolower(trim($_POST['status'] ?? 'inactive'));

    if ($title === '' || $shortDescription === '') {
        $msg = 'Please fill all required fields.';
        $msgType = 'err';
    } elseif (!in_array($status, ['active', 'inactive'], true)) {
        $msg = 'Invalid status.';
        $msgType = 'err';
    } elseif (!in_array($category, ['what_we_offer', 'specialized'], true)) {
        $msg = 'Invalid category.';
        $msgType = 'err';
    } else {
        $imageName = null;
        if (!empty($_FILES['image']['name'])) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed, true)) {
                $msg = 'Only jpg, jpeg, png, webp allowed.';
                $msgType = 'err';
            } else {
                $safeName = 'course_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $uploadDir = __DIR__ . '/uploads/service/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $dest = $uploadDir . $safeName;
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                    $msg = 'Image upload failed.';
                    $msgType = 'err';
                } else {
                    $imageName = $safeName;
                }
            }
        }

        if ($msgType !== 'err') {
            $fullDescription = null;
            $duration = null;
            $fees = null;
            $stmt = $conn->prepare('INSERT INTO service (title, short_description, full_description, duration, fees, image, tags, category, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('sssssssss', $title, $shortDescription, $fullDescription, $duration, $fees, $imageName, $tags, $category, $status);
            if ($stmt->execute()) {
                $msg = 'Service added successfully.';
                $msgType = 'ok';
            } else {
                $msg = 'Failed to add service.';
                $msgType = 'err';
            }
            $stmt->close();
        }
    }
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<div class="section card shadow-sm p-4 mb-4">
  <?php if ($msg !== ''): ?>
    <div class="alert <?php echo $msgType === 'ok' ? 'alert-success' : 'alert-danger'; ?> alert-dismissible fade show" role="alert">
      <?php echo htmlspecialchars($msg); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>
  <h3 class="mb-3">Service Details</h3>
  <p class="text-muted mb-4">Use this form for both sections: <strong>What We Offer</strong> and <strong>Specialized Analysis</strong>.</p>
  <form method="post" enctype="multipart/form-data">
    <div class="mb-3">
      <label for="title" class="form-label">Service Title</label>
      <input type="text" class="form-control" id="title" name="title" required>
    </div>
    <div class="mb-3">
      <label for="short_description" class="form-label">Short Description</label>
      <textarea class="form-control" id="short_description" name="short_description" rows="4" required></textarea>
    </div>
    <div class="mb-3">
      <label for="tags" class="form-label">Tags (comma separated)</label>
      <input type="text" class="form-control" id="tags" name="tags" placeholder="e.g. Stress Scan, Energy Mapping, Correction Plan, Healing Guide">
    </div>
    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label for="image" class="form-label">Image Upload</label>
        <input class="form-control" type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp">
      </div>
      <div class="col-md-6">
        <label for="category" class="form-label">Category</label>
        <select class="form-select" id="category" name="category" required>
          <option value="what_we_offer">What We Offer</option>
          <option value="specialized">Specialized Analysis</option>
        </select>
      </div>
    </div>
    <div class="mb-4">
      <label for="status" class="form-label">Status</label>
      <select class="form-select" id="status" name="status" required>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
      </select>
    </div>
    <div class="d-flex flex-wrap gap-2">
      <button type="submit" class="btn btn-primary">Save Service</button>
    </div>
  </form>
</div>
</main></div></body></html>

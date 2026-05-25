<?php
$pageTitle = 'Gallery';
require_once __DIR__ . '/includes/db.php';

$msg = '';
$msgType = '';

$uploadDir = __DIR__ . '/uploads/gallery/';
$uploadUrl = 'uploads/gallery/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

/* DELETE */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int)($_POST['id'] ?? 0);

    $old = $conn->prepare("SELECT image FROM gallery WHERE id=?");
    $old->bind_param("i", $id);
    $old->execute();
    $oldData = $old->get_result()->fetch_assoc();
    $old->close();

    if (!empty($oldData['image']) && file_exists($uploadDir . $oldData['image'])) {
        unlink($uploadDir . $oldData['image']);
    }

    $stmt = $conn->prepare("DELETE FROM gallery WHERE id=?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $msg = 'Gallery item deleted successfully.';
        $msgType = 'ok';
    } else {
        $msg = 'Delete failed.';
        $msgType = 'err';
    }

    $stmt->close();
}

/* GET EDIT DATA */
$editData = null;

if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];

    $stmt = $conn->prepare("SELECT id, image, description FROM gallery WHERE id=?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $editData = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

/* ADD / UPDATE */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') !== 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $oldImage = trim($_POST['old_image'] ?? '');

    $imageName = $oldImage ?: null;

    if ($id === 0 && empty($_FILES['image']['name'])) {
        $msg = 'Please upload gallery image.';
        $msgType = 'err';
    } else {
        if (!empty($_FILES['image']['name'])) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed, true)) {
                $msg = 'Only jpg, jpeg, png, webp allowed.';
                $msgType = 'err';
            } else {
                $safeName = 'gallery_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $dest = $uploadDir . $safeName;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                    if ($oldImage && file_exists($uploadDir . $oldImage)) {
                        unlink($uploadDir . $oldImage);
                    }

                    $imageName = $safeName;
                } else {
                    $msg = 'Image upload failed.';
                    $msgType = 'err';
                }
            }
        }

        if ($msgType !== 'err') {
            if ($id > 0) {
                $stmt = $conn->prepare("UPDATE gallery SET image=?, description=? WHERE id=?");
                $stmt->bind_param("ssi", $imageName, $description, $id);
                $successMsg = 'Gallery item updated successfully.';
            } else {
                $stmt = $conn->prepare("INSERT INTO gallery (image, description) VALUES (?, ?)");
                $stmt->bind_param("ss", $imageName, $description);
                $successMsg = 'Gallery item added successfully.';
            }

            if ($stmt->execute()) {
                $msg = $successMsg;
                $msgType = 'ok';
                $editData = null;
            } else {
                $msg = 'Something went wrong.';
                $msgType = 'err';
            }

            $stmt->close();
        }
    }
}

$list = $conn->query("SELECT id, image, description FROM gallery ORDER BY id DESC");

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

    <h3 class="mb-3"><?php echo $editData ? 'Edit Gallery Image' : 'Add Gallery Image'; ?></h3>

    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $editData['id'] ?? 0; ?>">
        <input type="hidden" name="old_image" value="<?php echo htmlspecialchars($editData['image'] ?? ''); ?>">

        <div class="mb-3">
            <label class="form-label">Gallery Image</label>
            <input class="form-control" type="file" name="image" accept=".jpg,.jpeg,.png,.webp" <?php echo $editData ? '' : 'required'; ?>>
            <?php if (!empty($editData['image'])): ?>
                <div class="mt-2">
                    <img src="<?php echo $uploadUrl . htmlspecialchars($editData['image']); ?>" width="120" class="img-thumbnail">
                </div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" placeholder="Enter description" rows="3"><?php echo htmlspecialchars($editData['description'] ?? ''); ?></textarea>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <?php echo $editData ? 'Update Gallery' : 'Add Gallery'; ?>
            </button>
            <?php if ($editData): ?>
                <a href="gallery.php" class="btn btn-secondary">Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="section card shadow-sm p-4">
    <h3 class="mb-3">Gallery List</h3>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:150px;">Image</th>
                    <th>Description</th>
                    <th style="width:180px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($list && $list->num_rows > 0): ?>
                    <?php while ($row = $list->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?php if (!empty($row['image'])): ?>
                                    <img class="img-thumbnail" src="<?php echo $uploadUrl . htmlspecialchars($row['image']); ?>" width="120">
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                            <td><?php echo nl2br(htmlspecialchars($row['description'])); ?></td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a class="btn btn-sm btn-primary" href="gallery.php?edit=<?php echo (int)$row['id']; ?>">
                                        Edit
                                    </a>
                                    <form method="post" onsubmit="return confirm('Delete this gallery item?');" class="m-0">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="text-center text-muted">No gallery items found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</main>
</div>
</body>
</html>
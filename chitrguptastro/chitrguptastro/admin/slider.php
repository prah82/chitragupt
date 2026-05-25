<?php
$pageTitle = 'Add Slider Images';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';


$uploadDir = __DIR__ . '/uploads/slider/';
$uploadUrl = 'uploads/slider/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

/* AJAX ADD */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    header('Content-Type: application/json');

    if (empty($_FILES['image']['name'])) {
        echo json_encode(['status' => false, 'message' => 'Please upload slider image.']);
        exit;
    }

    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed, true)) {
        echo json_encode(['status' => false, 'message' => 'Only jpg, jpeg, png, webp allowed.']);
        exit;
    }

    $imageName = 'slider_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $dest = $uploadDir . $imageName;

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
        echo json_encode(['status' => false, 'message' => 'Image upload failed.']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO slider (image) VALUES (?)");

    if (!$stmt) {
        echo json_encode(['status' => false, 'message' => 'SQL Error: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("s", $imageName);

    if ($stmt->execute()) {
        echo json_encode([
            'status' => true,
            'message' => 'Slider image added successfully.',
            'id' => $stmt->insert_id,
            'image' => $uploadUrl . $imageName
        ]);
    } else {
        echo json_encode(['status' => false, 'message' => 'Database insert failed.']);
    }

    $stmt->close();
    exit;
}

/* AJAX DELETE */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    header('Content-Type: application/json');

    $id = (int)($_POST['id'] ?? 0);

    if ($id <= 0) {
        echo json_encode(['status' => false, 'message' => 'Invalid ID.']);
        exit;
    }

    $old = $conn->prepare("SELECT image FROM slider WHERE id=?");
    $old->bind_param("i", $id);
    $old->execute();
    $oldData = $old->get_result()->fetch_assoc();
    $old->close();

    if (!empty($oldData['image']) && file_exists($uploadDir . $oldData['image'])) {
        unlink($uploadDir . $oldData['image']);
    }

    $stmt = $conn->prepare("DELETE FROM slider WHERE id=?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => true, 'message' => 'Slider image deleted successfully.']);
    } else {
        echo json_encode(['status' => false, 'message' => 'Delete failed.']);
    }

    $stmt->close();
    exit;
}

$list = $conn->query("SELECT id, image FROM slider ORDER BY id DESC");

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="section card shadow-sm p-4 mb-4">
    <div id="ajaxMsg"></div>

    <form id="sliderForm" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add">

        <div class="mb-3">
            <label for="sliderImage" class="form-label">Slider Image</label>
            <input class="form-control" type="file" id="sliderImage" name="image" accept=".jpg,.jpeg,.png,.webp" required>
        </div>

        <button type="submit" class="btn btn-primary">Add Image</button>
    </form>
</div>

<div class="section card shadow-sm p-4">
    <h3 class="mb-3">Slider Images List</h3>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:150px;" class="slider-th">Image</th>
                    <th style="width:150px;" class="slider-th">Action</th>
                </tr>
            </thead>

        <tbody id="sliderTableBody">
            <?php if ($list && $list->num_rows > 0): ?>
                <?php while ($row = $list->fetch_assoc()): ?>
                    <tr id="row-<?php echo (int)$row['id']; ?>">
                        <td>
                            <img class="img-thumbnail"
                                 src="<?php echo $uploadUrl . htmlspecialchars($row['image']); ?>"
                                 width="120">
                        </td>

                        <td>
                            <button type="button"
                                    class="btn btn-sm btn-danger deleteBtn"
                                    data-id="<?php echo (int)$row['id']; ?>">
                                Delete
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr id="noDataRow">
                    <td colspan="2" style="text-align:center;">No slider images found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</main>
</div>

<script>
const sliderForm = document.getElementById('sliderForm');
const ajaxMsg = document.getElementById('ajaxMsg');
const sliderTableBody = document.getElementById('sliderTableBody');

function showMessage(message, status) {
    ajaxMsg.innerHTML = message;
    ajaxMsg.style.color = status ? 'green' : 'red';
    ajaxMsg.style.marginBottom = '10px';
}

sliderForm.addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(sliderForm);

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        showMessage(data.message, data.status);

        if (data.status) {
            sliderForm.reset();

            const noDataRow = document.getElementById('noDataRow');
            if (noDataRow) {
                noDataRow.remove();
            }

            const newRow = `
                <tr id="row-${data.id}">
                    <td>
                        <img class="img-thumbnail" src="${data.image}" width="120">
                    </td>
                    <td>
                        <button type="button"
                                class="btn btn-sm btn-danger deleteBtn"
                                data-id="${data.id}">
                            Delete
                        </button>
                    </td>
                </tr>
            `;

            sliderTableBody.insertAdjacentHTML('afterbegin', newRow);
        }
    })
    .catch(error => {
        showMessage('Something went wrong. Check console.', false);
        console.log(error);
    });
});

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('deleteBtn')) {
        const id = e.target.getAttribute('data-id');

        if (!confirm('Delete this slider image?')) {
            return;
        }

        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);

        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            showMessage(data.message, data.status);

            if (data.status) {
                const row = document.getElementById('row-' + id);
                if (row) {
                    row.remove();
                }
            }
        })
        .catch(error => {
            showMessage('Delete failed. Check console.', false);
            console.log(error);
        });
    }
});
</script>

</body>
</html>
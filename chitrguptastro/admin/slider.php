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

    // Size check: Max 100 MB
    if ($_FILES['image']['size'] > 100 * 1024 * 1024) {
        echo json_encode(['status' => false, 'message' => 'Image size must be less than 100 MB.']);
        exit;
    }

    // Image validation only. Ratio restriction removed because cropper will fix ratio on frontend.
    if (@getimagesize($_FILES['image']['tmp_name']) === false) {
        echo json_encode(['status' => false, 'message' => 'Invalid image file.']);
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

<style>
  #ajaxMsg {
    position: fixed !important;
    top: 85px !important;
    right: 30px !important;
    left: auto !important;
    min-width: 320px;
    max-width: 520px;
    z-index: 999999 !important;
    box-shadow: 0 12px 35px rgba(0, 0, 0, .22);
    border-radius: 10px;
  }

  @media (max-width: 768px) {
    #ajaxMsg {
      top: 80px !important;
      left: 15px !important;
      right: 15px !important;
      min-width: 0;
      max-width: none;
    }
  }
</style>
    <div id="ajaxMsg" class="alert alert-dismissible fade show" style="display:none;" role="alert"></div>

<div class="section card shadow-sm p-4 mb-4">

    <form id="sliderForm" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add">

        <div class="mb-3">
            <label for="sliderImage" class="form-label">Slider Image</label>
            <input class="form-control" type="file" id="sliderImage" name="image" accept=".jpg,.jpeg,.png,.webp" required>
            <div class="form-text text-muted mt-1">Recommended slider crop: 3131x1600 ratio (same as sample image, approx 1.96:1). Max size: 100MB. If image ratio is different, crop option will open automatically.</div>
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


<div class="modal fade" id="cropModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Fix Slider Image Ratio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <img id="cropImage" style="max-width:100%; display:block;">
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="cropBtn">Crop & Use Image</button>
            </div>
        </div>
    </div>
</div>

<script>
const sliderForm = document.getElementById('sliderForm');
const ajaxMsg = document.getElementById('ajaxMsg');
const sliderTableBody = document.getElementById('sliderTableBody');
const sliderImage = document.getElementById('sliderImage');

let msgTimeout;
function showMessage(message, status) {
    clearTimeout(msgTimeout);

    if (!message) {
        ajaxMsg.innerHTML = '';
        ajaxMsg.style.display = 'none';
        return;
    }

    ajaxMsg.innerHTML = `
        <span>${message}</span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    ajaxMsg.className = 'alert alert-dismissible fade show ' + (status ? 'alert-success' : 'alert-danger');
    ajaxMsg.style.display = 'block';

    // Same duration as service file
    msgTimeout = setTimeout(() => {
        ajaxMsg.style.display = 'none';
        ajaxMsg.innerHTML = '';
    }, 3000);
}

let cropper;
let selectedFileName = '';

const cropModalEl = document.getElementById('cropModal');
const cropImage = document.getElementById('cropImage');
const cropBtn = document.getElementById('cropBtn');

if (typeof bootstrap === 'undefined') {
    showMessage('Bootstrap JS load nahi hua. bootstrap.bundle.min.js add karo.', false);
}

if (typeof Cropper === 'undefined') {
    showMessage('Cropper JS load nahi hua. Cropper CDN/header check karo.', false);
}

const cropModal = (typeof bootstrap !== 'undefined') ? new bootstrap.Modal(cropModalEl) : null;

sliderImage.addEventListener('change', function() {
    const file = this.files[0];
    if (!file) return;

    // Check size (100MB)
    if (file.size > 100 * 1024 * 1024) {
        showMessage('Error: Image size must be less than 100 MB.', false);
        this.value = '';
        return;
    }

    selectedFileName = file.name;

    const img = new Image();
    const objectUrl = URL.createObjectURL(file);

    img.onload = function() {
        const ratio = img.width / img.height;

        // Slider target ratio: 3131:1000, based on your sample image ratio (approx 3.13:1). If already close, no need to crop.
        if (ratio >= 3.10 && ratio <= 3.16) {
            showMessage('', false);
            URL.revokeObjectURL(objectUrl);
            return;
        }

        cropImage.src = objectUrl;

        if (!cropModal || typeof Cropper === 'undefined') {
            showMessage('Cropper open nahi ho raha. Cropper/Bootstrap CDN check karo.', false);
            return;
        }

        cropModal.show();

        cropModalEl.addEventListener('shown.bs.modal', function() {
            if (cropper) cropper.destroy();

            cropper = new Cropper(cropImage, {
                aspectRatio: 1.96,
                viewMode: 2,
                autoCropArea: 2,
                responsive: true,
                background: false
            });
        }, { once: true });
    };

    img.onerror = function() {
        showMessage('Error: Invalid image file.', false);
        sliderImage.value = '';
        URL.revokeObjectURL(objectUrl);
    };

    img.src = objectUrl;
});

cropBtn.addEventListener('click', function() {
    if (!cropper) return;

    cropper.getCroppedCanvas({
        width: 3131,
        height: 1600,
        imageSmoothingQuality: 'high'
    }).toBlob(function(blob) {
        const croppedFile = new File([blob], selectedFileName.replace(/\.[^/.]+$/, '') + '.jpg', {
            type: 'image/jpeg',
            lastModified: Date.now()
        });

        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(croppedFile);
        sliderImage.files = dataTransfer.files;

        cropper.destroy();
        cropper = null;
        cropModal.hide();

        showMessage('Slider image ratio fixed successfully.', true);
    }, 'image/jpeg', 0.9);
});

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
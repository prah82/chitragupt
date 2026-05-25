<?php
ob_start();
$pageTitle = 'Add Accolade';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$uploadDir = __DIR__ . '/uploads/accolades/';
$uploadUrl = 'uploads/accolades/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

function jsonResponse(bool $ok, string $message, array $extra = []): void
{
    if (ob_get_length()) {
        ob_clean();
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge(['ok' => $ok, 'message' => $message], $extra));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['api'])) {
    $result = $conn->query("SELECT id, title, image, tags FROM accolades ORDER BY id DESC");

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $row['image_url'] = 'uploads/accolades/' . $row['image'];
        $data[] = $row;
    }

    jsonResponse(true, 'Accolades fetched successfully.', [
        'data' => $data
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action'])) {
    $action = trim($_POST['ajax_action']);

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            jsonResponse(false, 'Invalid id.');
        }

        $old = $conn->prepare('SELECT image FROM accolades WHERE id=?');
        $old->bind_param('i', $id);
        $old->execute();
        $oldData = $old->get_result()->fetch_assoc();
        $old->close();

        if (!$oldData) {
            jsonResponse(false, 'Record not found.');
        }

        $stmt = $conn->prepare('DELETE FROM accolades WHERE id=?');
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $stmt->close();

        if (!$ok) {
            jsonResponse(false, 'Delete failed.');
        }

        if (!empty($oldData['image']) && file_exists($uploadDir . $oldData['image'])) {
            unlink($uploadDir . $oldData['image']);
        }

        jsonResponse(true, 'Accolade deleted successfully.', ['id' => $id]);
    }

    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $tags = trim($_POST['tags'] ?? '');
        $oldImage = trim($_POST['old_image'] ?? '');

        if ($title === '') {
            jsonResponse(false, 'Please fill title field.');
        }

        $imageName = $oldImage !== '' ? basename($oldImage) : null;

        if ($id > 0) {
            $existing = $conn->prepare('SELECT image FROM accolades WHERE id=?');
            $existing->bind_param('i', $id);
            $existing->execute();
            $existingData = $existing->get_result()->fetch_assoc();
            $existing->close();

            if (!$existingData) {
                jsonResponse(false, 'Record not found.');
            }

            $imageName = $existingData['image'] ?? null;
            $oldImage = $existingData['image'] ?? '';
        }

        if (!empty($_FILES['image']['name'])) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed, true)) {
                jsonResponse(false, 'Only jpg, jpeg, png, webp allowed.');
            }

            $tmpPath = $_FILES['image']['tmp_name'] ?? '';
            if (!is_uploaded_file($tmpPath) || @getimagesize($tmpPath) === false) {
                jsonResponse(false, 'Invalid image file.');
            }

            if ($_FILES['image']['size'] > 1 * 1024 * 1024) {
                jsonResponse(false, 'Image size must be less than 1 MB.');
            }

            $imgInfo = @getimagesize($tmpPath);
            $width = $imgInfo[0];
            $height = $imgInfo[1];
            if ($height <= 0) {
                jsonResponse(false, 'Invalid image dimensions.');
            }

            $ratio = $width / $height;
            if ($ratio < 0.95 || $ratio > 1.05) {
                jsonResponse(false, 'Image ratio must be between 0.95 and 1.05 (Square layout). Your image ratio is ' . round($ratio, 2) . '.');
            }

            $safeName = 'accolade_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $dest = $uploadDir . $safeName;

            if (!move_uploaded_file($tmpPath, $dest)) {
                jsonResponse(false, 'Image upload failed.');
            }

            if ($oldImage && file_exists($uploadDir . $oldImage)) {
                unlink($uploadDir . $oldImage);
            }

            $imageName = $safeName;
        }

        if ($id > 0) {
            $stmt = $conn->prepare('UPDATE accolades SET title=?, image=?, tags=? WHERE id=?');
            $stmt->bind_param('sssi', $title, $imageName, $tags, $id);
            $ok = $stmt->execute();
            $stmt->close();

            if (!$ok) {
                jsonResponse(false, 'Update failed.');
            }

            jsonResponse(true, 'Accolade updated successfully.', [
                'row' => [
                    'id' => $id,
                    'title' => $title,
                    'tags' => $tags,
                    'image' => $imageName ?? ''
                ]
            ]);
        }

        if ($imageName === null) {
            jsonResponse(false, 'Image is required for new accolade.');
        }

        $stmt = $conn->prepare('INSERT INTO accolades (title, image, tags) VALUES (?, ?, ?)');
        $stmt->bind_param('sss', $title, $imageName, $tags);
        $ok = $stmt->execute();
        $newId = (int)$stmt->insert_id;
        $stmt->close();

        if (!$ok) {
            jsonResponse(false, 'Something went wrong.');
        }

        jsonResponse(true, 'Accolade added successfully.', [
            'row' => [
                'id' => $newId,
                'title' => $title,
                'tags' => $tags,
                'image' => $imageName
            ]
        ]);
    }

    jsonResponse(false, 'Invalid action.');
}

$list = $conn->query('SELECT id, title, image, tags FROM accolades ORDER BY id DESC');

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<style>
  .service-toast {
    position: fixed !important;
    top: 85px !important;
    right: 30px !important;
    left: auto !important;
    width: auto !important;
    min-width: 320px;
    max-width: 520px;
    z-index: 999999 !important;
    box-shadow: 0 12px 35px rgba(0, 0, 0, .22);
    border-radius: 10px;
  }
  @media (max-width: 768px) {
    .service-toast {
      top: 75px !important;
      right: 15px !important;
      left: 15px !important;
      min-width: auto;
      max-width: none;
    }
  }
</style>

<div id="ajaxMsg" class="service-toast" style="display:none;" role="alert"></div>

<div class="section card shadow-sm p-4 mb-4">
    <h3 id="formTitle" class="mb-3">Add Accolade</h3>

    <form id="accoladeForm" enctype="multipart/form-data">
        <input type="hidden" name="ajax_action" value="save">
        <input type="hidden" name="id" id="accoladeId" value="0">
        <input type="hidden" name="old_image" id="oldImage" value="">

        <div class="mb-3">
            <label class="form-label">Accolade Title</label>
            <input type="text" class="form-control" name="title" id="accoladeTitle" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Tags</label>
            <input type="text" class="form-control" name="tags" id="accoladeTags" placeholder="RECOGNITION, AWARD, CERTIFICATE">
        </div>

        <div class="mb-3">
            <label class="form-label">Image Upload</label>
            <input type="file" class="form-control" name="image" id="accoladeImage" accept=".jpg,.jpeg,.png,.webp" required>
            <div class="form-text text-muted mt-1">Recommended: Square image (1:1 ratio, e.g. 500x500px). Max size: 1MB. Allowed ratio: 0.95 to 1.05.</div>
            <div id="currentPreview" style="display:none; margin-top:10px;">
                <img id="previewImg" src="" width="100" class="img-thumbnail" alt="Current image">
            </div>
        </div>

        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary" id="submitBtn">Save Accolade</button>
            <button type="button" class="btn btn-secondary" id="cancelBtn" style="display:none;">Cancel</button>
        </div>
    </form>
</div>

<div class="section card shadow-sm p-4">
    <h3 class="mb-3">Accolades List</h3>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:120px;">Image</th>
                    <th>Title</th>
                    <th>Tags</th>
                    <th style="width:170px;">Action</th>
                </tr>
            </thead>

            <tbody id="accoladesBody">
                <?php if ($list && $list->num_rows > 0): ?>
                    <?php while ($row = $list->fetch_assoc()): ?>
                        <tr id="row-<?php echo (int)$row['id']; ?>"
                            data-id="<?php echo (int)$row['id']; ?>"
                            data-title="<?php echo htmlspecialchars($row['title'], ENT_QUOTES); ?>"
                            data-tags="<?php echo htmlspecialchars($row['tags'], ENT_QUOTES); ?>"
                            data-image="<?php echo htmlspecialchars($row['image'], ENT_QUOTES); ?>">
                            <td class="img-cell">
                                <?php if (!empty($row['image'])): ?>
                                    <img class="img-thumbnail" src="<?php echo $uploadUrl . htmlspecialchars($row['image']); ?>" width="80" alt="accolade">
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>

                            <td class="title-cell"><?php echo htmlspecialchars($row['title']); ?></td>
                            <td class="tags-cell"><?php echo htmlspecialchars($row['tags']); ?></td>

                            <td>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-primary editBtn" data-id="<?php echo (int)$row['id']; ?>">Edit</button>
                                    <button type="button" class="btn btn-sm btn-danger deleteBtn" data-id="<?php echo (int)$row['id']; ?>">Delete</button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr id="noDataRow">
                        <td colspan="4" class="text-center text-muted">No accolades found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</main>
</div>

<div class="modal fade" id="cropModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Fix Image Ratio</h5>
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
(function() {
    const form = document.getElementById('accoladeForm');
    const msgBox = document.getElementById('ajaxMsg');
    const body = document.getElementById('accoladesBody');

    const accoladeId = document.getElementById('accoladeId');
    const oldImage = document.getElementById('oldImage');
    const accoladeImage = document.getElementById('accoladeImage');
    const accoladeTitle = document.getElementById('accoladeTitle');
    const accoladeTags = document.getElementById('accoladeTags');
    const formTitle = document.getElementById('formTitle');
    const submitBtn = document.getElementById('submitBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const currentPreview = document.getElementById('currentPreview');
    const previewImg = document.getElementById('previewImg');

    const cropModalEl = document.getElementById('cropModal');
    const cropModal = new bootstrap.Modal(cropModalEl);
    const cropImage = document.getElementById('cropImage');
    const cropBtn = document.getElementById('cropBtn');

    let msgTimeout;
    let cropper = null;
    let selectedFileName = '';

    function esc(str) {
        return (str || '').replace(/[&<>'"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]));
    }

    function showMessage(text, ok) {
        clearTimeout(msgTimeout);

        if (!text) return;

        msgBox.style.display = 'block';
        msgBox.className = 'service-toast alert alert-dismissible fade show ' + (ok ? 'alert-success' : 'alert-danger');
        msgBox.innerHTML = esc(text) + '<button type="button" class="btn-close" aria-label="Close"></button>';

        const closeBtn = msgBox.querySelector('.btn-close');
        if (closeBtn) {
            closeBtn.onclick = function () {
                msgBox.style.display = 'none';
                msgBox.classList.remove('show');
            };
        }

        msgTimeout = setTimeout(function () {
            msgBox.style.display = 'none';
            msgBox.classList.remove('show');
        }, 3000);
    }

    function resetForm() {
        form.reset();
        accoladeId.value = '0';
        oldImage.value = '';
        accoladeImage.required = true;
        formTitle.textContent = 'Add Accolade';
        submitBtn.textContent = 'Save Accolade';
        cancelBtn.style.display = 'none';
        currentPreview.style.display = 'none';
        previewImg.src = '';
    }

    function rowHtml(r) {
        const img = r.image ? `<img class="img-thumbnail" src="uploads/accolades/${esc(r.image)}" width="80" alt="accolade">` : 'N/A';
        return `<tr id="row-${r.id}" data-id="${r.id}" data-title="${esc(r.title)}" data-tags="${esc(r.tags)}" data-image="${esc(r.image)}">
            <td class="img-cell">${img}</td>
            <td class="title-cell">${esc(r.title)}</td>
            <td class="tags-cell">${esc(r.tags)}</td>
            <td>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-primary editBtn" data-id="${r.id}">Edit</button>
                    <button type="button" class="btn btn-sm btn-danger deleteBtn" data-id="${r.id}">Delete</button>
                </div>
            </td>
        </tr>`;
    }

    accoladeImage.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;

        if (file.size > 1 * 1024 * 1024) {
            showMessage('Error: Image size must be less than 1 MB.', false);
            this.value = '';
            return;
        }

        selectedFileName = file.name;

        const img = new Image();
        const objectUrl = URL.createObjectURL(file);

        img.onload = function () {
            const ratio = img.width / img.height;

            if (ratio >= 0.95 && ratio <= 1.05) {
                URL.revokeObjectURL(objectUrl);
                return;
            }

            cropImage.src = objectUrl;
            cropModal.show();

            cropModalEl.addEventListener('shown.bs.modal', function () {
                if (cropper) cropper.destroy();

                cropper = new Cropper(cropImage, {
                    aspectRatio: 1,
                    viewMode: 1,
                    autoCropArea: 1,
                    responsive: true,
                    background: false
                });
            }, { once: true });
        };

        img.onerror = function () {
            showMessage('Error: Invalid image file.', false);
            accoladeImage.value = '';
            URL.revokeObjectURL(objectUrl);
        };

        img.src = objectUrl;
    });

    cropBtn.addEventListener('click', function () {
        if (!cropper) return;

        cropper.getCroppedCanvas({
            width: 500,
            height: 500,
            imageSmoothingQuality: 'high'
        }).toBlob(function (blob) {
            if (!blob) {
                showMessage('Error: Crop failed. Please try again.', false);
                return;
            }

            const croppedFile = new File([blob], selectedFileName, {
                type: 'image/jpeg',
                lastModified: Date.now()
            });

            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(croppedFile);
            accoladeImage.files = dataTransfer.files;

            cropper.destroy();
            cropper = null;
            cropModal.hide();

            showMessage('Image ratio fixed successfully.', true);
        }, 'image/jpeg', 0.9);
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(form);

        fetch('accolades.php', { method: 'POST', body: formData })
            .then(res => res.text())
            .then(text => {
                const data = JSON.parse(text.replace(/^\uFEFF/, '').trim());

                if (!data.ok) {
                    showMessage(data.message || 'Action failed.', false);
                    return;
                }

                const noData = document.getElementById('noDataRow');
                if (noData) noData.remove();

                if (accoladeId.value !== '0') {
                    const row = document.getElementById('row-' + data.row.id);
                    if (row) {
                        row.outerHTML = rowHtml(data.row);
                    }
                } else {
                    body.insertAdjacentHTML('afterbegin', rowHtml(data.row));
                }

                showMessage(data.message || 'Saved.', true);
                resetForm();
            })
            .catch(() => showMessage('Something went wrong.', false));
    });

    body.addEventListener('click', function(e) {
        const editBtn = e.target.closest('.editBtn');
        if (editBtn) {
            const id = editBtn.dataset.id;
            const row = document.getElementById('row-' + id);
            if (!row) return;

            accoladeId.value = id;
            oldImage.value = row.dataset.image || '';
            accoladeTitle.value = row.dataset.title || '';
            accoladeTags.value = row.dataset.tags || '';

            accoladeImage.required = false;
            formTitle.textContent = 'Edit Accolade';
            submitBtn.textContent = 'Update Accolade';
            cancelBtn.style.display = 'inline-block';

            if (row.dataset.image) {
                previewImg.src = 'uploads/accolades/' + row.dataset.image;
                currentPreview.style.display = 'block';
            } else {
                currentPreview.style.display = 'none';
            }

            document.getElementById('accoladeForm')?.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
            return;
        }

        const deleteBtn = e.target.closest('.deleteBtn');
        if (deleteBtn) {
            const id = deleteBtn.dataset.id;
            if (!confirm('Delete this accolade?')) return;

            const fd = new FormData();
            fd.append('ajax_action', 'delete');
            fd.append('id', id);

            fetch('accolades.php', { method: 'POST', body: fd })
                .then(res => res.text())
                .then(text => {
                    const data = JSON.parse(text.replace(/^\uFEFF/, '').trim());

                    if (!data.ok) {
                        showMessage(data.message || 'Delete failed.', false);
                        return;
                    }

                    document.getElementById('row-' + data.id)?.remove();

                    if (!body.querySelector('tr')) {
                        body.insertAdjacentHTML('afterbegin', '<tr id="noDataRow"><td colspan="4" class="text-center text-muted">No accolades found.</td></tr>');
                    }

                    showMessage(data.message || 'Deleted.', true);
                })
                .catch(() => showMessage('Something went wrong.', false));
        }
    });

    cancelBtn.addEventListener('click', resetForm);
})();
</script>

</body>
</html>

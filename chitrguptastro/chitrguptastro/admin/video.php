<?php
ob_start();
$pageTitle = 'Videos';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';


$uploadDir = __DIR__ . '/uploads/video/';
$uploadUrl = 'uploads/video/';
$maxVideoSize = 150 * 1024 * 1024; // 150 MB

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

function jsonResponse(bool $status, string $message, array $extra = []): void
{
    if (ob_get_length()) {
        ob_clean();
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge(['status' => $status, 'message' => $message], $extra));
    exit;
}

/* AJAX ADD */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $description = trim($_POST['description'] ?? '');
    $videoName = null;

    if (empty($_FILES['video']['name'])) {
        jsonResponse(false, 'Please upload video.');
    }

    if (!empty($_FILES['video']['name'])) {
        $allowed = ['mp4', 'webm', 'ogg', 'mov'];
        $ext = strtolower(pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION));
        $fileSize = (int)($_FILES['video']['size'] ?? 0);
        $uploadError = (int)($_FILES['video']['error'] ?? UPLOAD_ERR_OK);

        if ($uploadError !== UPLOAD_ERR_OK) {
            if ($uploadError === UPLOAD_ERR_INI_SIZE || $uploadError === UPLOAD_ERR_FORM_SIZE) {
                jsonResponse(false, 'Upload failed: file exceeds server limit. Please increase upload_max_filesize and post_max_size in php.ini.');
            }
            jsonResponse(false, 'Upload failed with error code: ' . $uploadError);
        }

        if (!in_array($ext, $allowed, true)) {
            jsonResponse(false, 'Only mp4, webm, ogg, mov allowed.');
        }
        if ($fileSize <= 0 || $fileSize > $maxVideoSize) {
            jsonResponse(false, 'Video size must be less than or equal to 150 MB.');
        }

        $safeName = 'video_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dest = $uploadDir . $safeName;

        if (!move_uploaded_file($_FILES['video']['tmp_name'], $dest)) {
            jsonResponse(false, 'Video upload failed.');
        }

        $videoName = $safeName;
    }

    $stmt = $conn->prepare("INSERT INTO video (video, description) VALUES (?, ?)");
    if (!$stmt) {
        jsonResponse(false, 'Insert query preparation failed: ' . $conn->error);
    }
    $stmt->bind_param("ss", $videoName, $description);
    $successMsg = 'Video added successfully.';

    if ($stmt->execute()) {
        jsonResponse(true, $successMsg, [
            'id' => $stmt->insert_id,
            'video' => $uploadUrl . $videoName,
            'description' => htmlspecialchars($description)
        ]);
    } else {
        jsonResponse(false, 'Database error: ' . $stmt->error);
    }

    $stmt->close();
}

/* AJAX DELETE */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int)($_POST['id'] ?? 0);

    $old = $conn->prepare("SELECT video FROM video WHERE id=?");
    if (!$old) {
        jsonResponse(false, 'Delete lookup failed: ' . $conn->error);
    }
    $old->bind_param("i", $id);
    $old->execute();
    $oldData = $old->get_result()->fetch_assoc();
    $old->close();

    if (!empty($oldData['video']) && file_exists($uploadDir . $oldData['video'])) {
        unlink($uploadDir . $oldData['video']);
    }

    $stmt = $conn->prepare("DELETE FROM video WHERE id=?");
    if (!$stmt) {
        jsonResponse(false, 'Delete query preparation failed: ' . $conn->error);
    }
    $stmt->bind_param("i", $id);
    $ok = $stmt->execute();
    if ($ok) {
        jsonResponse(true, 'Video deleted successfully.');
    }
    jsonResponse(false, 'Delete failed: ' . $stmt->error);

    $stmt->close();
}

$list = $conn->query("SELECT id, video, description FROM video ORDER BY id DESC");

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="section card shadow-sm p-4 mb-4">
    <div id="ajaxMsg" class="alert" style="display:none;" role="alert"></div>

    <h3 id="formTitle" class="mb-3">Add Video</h3>

    <form id="videoForm" enctype="multipart/form-data">
        <input type="hidden" name="action" value="save">

        <div class="mb-3">
            <label for="videoInput" class="form-label">Video Upload</label>
            <input class="form-control" type="file" name="video" id="videoInput" accept=".mp4,.webm,.ogg,.mov" required>
            <div class="form-text">Max file size: 150 MB</div>
        </div>

        <div class="mb-4">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" name="description" id="description" rows="4" placeholder="Enter description"></textarea>
        </div>

        <button type="submit" class="btn btn-primary" id="submitBtn">Add Video</button>
    </form>
</div>

<div class="section card shadow-sm p-4">
    <h3 class="mb-3">Video List</h3>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:220px;">Video</th>
                    <th>Description</th>
                    <th style="width:200px;">Action</th>
                </tr>
            </thead>

        <tbody id="videoTableBody">
            <?php if ($list && $list->num_rows > 0): ?>
                <?php while ($row = $list->fetch_assoc()): ?>
                    <tr id="row-<?php echo (int)$row['id']; ?>">
                        <td>
                            <video width="200" controls>
                                <source src="<?php echo $uploadUrl . htmlspecialchars($row['video']); ?>">
                            </video>
                        </td>

                        <td class="desc-cell"><?php echo htmlspecialchars($row['description']); ?></td>

                        <td>
                            <!-- <button type="button"
                                    class="btn-premium editBtn"
                                    data-id="<?php # echo (int)$row['id']; ?>"
                                    data-video="<?php # echo htmlspecialchars($row['video']); ?>"
                                    data-description="<?php # echo htmlspecialchars($row['description']); ?>">
                                Edit
                            </button> -->

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
                    <td colspan="3" style="text-align:center;">No videos found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</main>
</div>

<script>
const videoForm = document.getElementById('videoForm');
const ajaxMsg = document.getElementById('ajaxMsg');
const videoTableBody = document.getElementById('videoTableBody');

const videoInput = document.getElementById('videoInput');
const submitBtn = document.getElementById('submitBtn');

function showMessage(message, status) {
    ajaxMsg.style.display = 'block';
    ajaxMsg.className = 'alert ' + (status ? 'alert-success' : 'alert-danger');
    ajaxMsg.textContent = message;
    setTimeout(() => { ajaxMsg.style.display = 'none'; }, 3000);
}

function resetForm() {
    videoForm.reset();
    submitBtn.innerHTML = 'Add Video';
}

videoForm.addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(videoForm);

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(text => {
        let data;
        try {
            data = JSON.parse(text.replace(/^\uFEFF/, '').trim());
        } catch (e) {
            throw new Error(text || 'Invalid JSON response');
        }
        return data;
    })
    .then(data => {
        showMessage(data.message, data.status);

        if (data.status) {
            const noDataRow = document.getElementById('noDataRow');
            if (noDataRow) noDataRow.remove();

            const rowHtml = `
                <td>
                    <video width="200" controls>
                        <source src="${data.video}">
                    </video>
                </td>
                <td class="desc-cell">${data.description}</td>
                <td>
                    <button type="button"
                            class="btn btn-sm btn-danger deleteBtn"
                            data-id="${data.id}">
                        Delete
                    </button>
                </td>
            `;

            videoTableBody.insertAdjacentHTML('afterbegin', `<tr id="row-${data.id}">${rowHtml}</tr>`);

            resetForm();
        }
    })
    .catch(err => {
        showMessage('Something went wrong: ' + err.message, false);
        console.log(err);
    });
});

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('deleteBtn')) {
        const id = e.target.dataset.id;

        if (!confirm('Delete this video?')) return;

        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);

        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(res => res.text())
        .then(text => {
            let data;
            try {
                data = JSON.parse(text.replace(/^\uFEFF/, '').trim());
            } catch (e) {
                throw new Error(text || 'Invalid JSON response');
            }
            return data;
        })
        .then(data => {
            showMessage(data.message, data.status);

            if (data.status) {
                document.getElementById('row-' + id)?.remove();
            }
        })
        .catch(err => {
            showMessage('Something went wrong: ' + err.message, false);
            console.log(err);
        });
    }
});
</script>

</body>
</html>

<?php
ob_start();
$pageTitle = 'service';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';


$uploadDir = __DIR__ . '/uploads/service/';
$uploadUrl = 'uploads/service/';

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

function uploadServiceImage(array $file, string $uploadDir, ?string &$error): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        $error = 'Image upload failed.';
        return null;
    }

    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true)) {
        $error = 'Only jpg, jpeg, png, webp allowed.';
        return null;
    }

    $tmpPath = $file['tmp_name'] ?? '';
    if (!is_uploaded_file($tmpPath)) {
        $error = 'Invalid upload source.';
        return null;
    }

    if (@getimagesize($tmpPath) === false) {
        $error = 'Invalid image file.';
        return null;
    }

    $safeName = 'course_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $dest = $uploadDir . $safeName;

    if (!move_uploaded_file($tmpPath, $dest)) {
        $error = 'Image upload failed.';
        return null;
    }

    return $safeName;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action'])) {
    $action = trim($_POST['ajax_action']);

    if ($action === 'toggle_status') {
        $id = (int)($_POST['id'] ?? 0);
        $status = strtolower(trim($_POST['status'] ?? 'inactive'));
        if ($id <= 0 || !in_array($status, ['active', 'inactive'], true)) {
            jsonResponse(false, 'Invalid request');
        }

        $stmt = $conn->prepare('UPDATE service SET status = ? WHERE id = ?');
        $stmt->bind_param('si', $status, $id);
        $ok = $stmt->execute();
        $stmt->close();

        if (!$ok) {
            jsonResponse(false, 'Status update failed');
        }

        jsonResponse(true, 'Status updated.', ['status' => $status]);
    }

    if ($action === 'delete_course') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            jsonResponse(false, 'Invalid course id.');
        }

        $stmt = $conn->prepare('SELECT image FROM service WHERE id=?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $course = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$course) {
            jsonResponse(false, 'Course not found.');
        }

        $stmt = $conn->prepare('DELETE FROM service WHERE id=?');
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $stmt->close();

        if (!$ok) {
            jsonResponse(false, 'Delete failed.');
        }

        if (!empty($course['image'])) {
            $file = $uploadDir . basename($course['image']);
            if (is_file($file)) {
                unlink($file);
            }
        }

        jsonResponse(true, 'Course deleted.', ['id' => $id]);
    }

    if ($action === 'save_course') {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $shortDescription = trim($_POST['short_description'] ?? '');
        $tags = trim($_POST['tags'] ?? '');
        $category = trim($_POST['category'] ?? 'what_we_offer');
        $status = strtolower(trim($_POST['status'] ?? 'inactive'));

        if ($title === '' || $shortDescription === '') {
            jsonResponse(false, 'Please fill all required fields.');
        }
        if (!in_array($status, ['active', 'inactive'], true)) {
            jsonResponse(false, 'Invalid status.');
        }
        if (!in_array($category, ['what_we_offer', 'specialized'], true)) {
            jsonResponse(false, 'Invalid category.');
        }

        $imageError = null;
        $uploadedImage = uploadServiceImage($_FILES['image'] ?? [], $uploadDir, $imageError);
        if ($imageError !== null) {
            jsonResponse(false, $imageError);
        }

        if ($id > 0) {
            $stmt = $conn->prepare('SELECT image FROM service WHERE id=?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $oldRow = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$oldRow) {
                if ($uploadedImage !== null) {
                    @unlink($uploadDir . $uploadedImage);
                }
                jsonResponse(false, 'Course not found.');
            }

            $oldImage = (string)($oldRow['image'] ?? '');
            $finalImage = $uploadedImage ?? $oldImage;

            $stmt = $conn->prepare('UPDATE service SET title=?, short_description=?, tags=?, category=?, status=?, image=? WHERE id=?');
            $stmt->bind_param('ssssssi', $title, $shortDescription, $tags, $category, $status, $finalImage, $id);
            $ok = $stmt->execute();
            $stmt->close();

            if (!$ok) {
                if ($uploadedImage !== null) {
                    @unlink($uploadDir . $uploadedImage);
                }
                jsonResponse(false, 'Update failed.');
            }

            if ($uploadedImage !== null && $oldImage !== '' && $oldImage !== $uploadedImage) {
                $oldPath = $uploadDir . basename($oldImage);
                if (is_file($oldPath)) {
                    @unlink($oldPath);
                }
            }

            jsonResponse(true, 'Course updated.', [
                'id' => $id,
                'row' => [
                    'id' => $id,
                    'title' => $title,
                    'short_description' => $shortDescription,
                    'tags' => $tags,
                    'category' => $category,
                    'status' => $status,
                    'image' => $finalImage,
                    'category_text' => $category === 'specialized' ? 'Specialized Analysis' : 'What We Offer'
                ]
            ]);
        }

        if ($uploadedImage === null) {
            jsonResponse(false, 'Image is required for new course.');
        }

        $fullDescription = null;
        $duration = null;
        $fees = null;
        $stmt = $conn->prepare('INSERT INTO service (title, short_description, full_description, duration, fees, image, tags, category, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('sssssssss', $title, $shortDescription, $fullDescription, $duration, $fees, $uploadedImage, $tags, $category, $status);
        $ok = $stmt->execute();
        $newId = (int)$stmt->insert_id;
        $stmt->close();

        if (!$ok) {
            @unlink($uploadDir . $uploadedImage);
            jsonResponse(false, 'Failed to add course.');
        }

        jsonResponse(true, 'Course added successfully.', [
            'id' => $newId,
            'row' => [
                'id' => $newId,
                'title' => $title,
                'short_description' => $shortDescription,
                'tags' => $tags,
                'category' => $category,
                'status' => $status,
                'image' => $uploadedImage,
                'category_text' => $category === 'specialized' ? 'Specialized Analysis' : 'What We Offer'
            ]
        ]);
    }

    jsonResponse(false, 'Invalid action.');
}

$service = $conn->query('SELECT * FROM service ORDER BY id DESC');

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<style>
  .switch {
    position: relative;
    display: inline-block;
    width: 52px;
    height: 28px;
    margin: 0;
    vertical-align: middle;
  }
  .switch input {
    opacity: 0;
    width: 0;
    height: 0;
  }
  .switch .slider {
    position: absolute;
    inset: 0;
    cursor: pointer;
    background: #cbd5e1;
    border: 1px solid #b8c2cf;
    border-radius: 999px;
    transition: background-color 0.2s ease, border-color 0.2s ease;
  }
  .switch .slider::before {
    content: "";
    position: absolute;
    width: 22px;
    height: 22px;
    left: 2px;
    top: 2px;
    background: #ffffff;
    border-radius: 50%;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    transition: transform 0.2s ease;
  }
  .switch input:checked + .slider {
    background: #22c55e;
    border-color: #16a34a;
  }
  .switch input:checked + .slider::before {
    transform: translateX(24px);
  }
  .switch input:focus-visible + .slider {
    outline: 3px solid rgba(34, 197, 94, 0.35);
    outline-offset: 2px;
  }
  .status-pill {
    display: inline-flex;
    align-items: center;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    line-height: 1;
  }
  .status-active {
    background: #dcfce7;
    color: #166534;
  }
  .status-inactive {
    background: #fee2e2;
    color: #991b1b;
  }
</style>

<div id="serviceMsg" class="alert alert-info" style="display:none;" role="alert"></div>

<div class="section card shadow-sm p-4 mb-4">
  <h3 id="serviceFormTitle">Add Service</h3>
  <p class="text-muted mb-4">Use this form for both sections: <strong>What We Offer</strong> and <strong>Specialized Analysis</strong>.</p>
  <form id="serviceForm" enctype="multipart/form-data">
    <input type="hidden" name="ajax_action" value="save_course">
    <input type="hidden" name="id" id="serviceId" value="0">
    <input type="hidden" name="current_image" id="currentImage" value="">

    <div class="mb-3">
      <label for="title" class="form-label">Service Title</label>
      <input type="text" class="form-control" name="title" id="title" required>
    </div>
    <div class="mb-3">
      <label for="short_description" class="form-label">Short Description</label>
      <textarea class="form-control" name="short_description" id="short_description" rows="4" required></textarea>
    </div>
    <div class="mb-3">
      <label for="tags" class="form-label">Tags (comma separated)</label>
      <input type="text" class="form-control" name="tags" id="tags" placeholder="e.g. Stress Scan, Energy Mapping, Correction Plan, Healing Guide">
    </div>
    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label for="image" class="form-label">Image Upload</label>
        <input class="form-control" type="file" name="image" id="image" accept=".jpg,.jpeg,.png,.webp">
        <div class="form-text">New course ke liye image required hai. Edit me optional.</div>
      </div>
      <div class="col-md-6">
        <label for="category" class="form-label">Category</label>
        <select class="form-select" name="category" id="category" required>
          <option value="what_we_offer">What We Offer</option>
          <option value="specialized">Specialized Analysis</option>
        </select>
      </div>
    </div>
    <div class="mb-4">
      <label class="form-label d-block">Status</label>
      <div class="d-flex align-items-center gap-2">
        <label class="switch mb-0">
          <input type="checkbox" id="statusToggleForm" checked>
          <span class="slider"></span>
        </label>
        <span class="status-pill status-active" id="statusToggleFormLabel">Active</span>
      </div>
      <input type="hidden" name="status" id="status" value="active">
    </div>
    <div class="d-flex flex-wrap gap-2">
      <button type="submit" class="btn btn-primary" id="saveBtn">Save Service</button>
      <button type="button" class="btn btn-secondary" id="cancelEditBtn" style="display:none;">Cancel Edit</button>
    </div>
  </form>
</div>

<div class="section table-wrap card shadow-sm p-4">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light"><tr><th style="width: 150px;">Image</th><th>Title</th><th>Category</th><th>Status</th><th style="width: 180px;">Action</th></tr></thead>
      <tbody id="serviceTableBody">
      <?php while ($row = $service->fetch_assoc()): ?>
      <tr id="row-<?php echo (int)$row['id']; ?>" data-id="<?php echo (int)$row['id']; ?>" data-title="<?php echo htmlspecialchars($row['title'], ENT_QUOTES); ?>" data-short_description="<?php echo htmlspecialchars($row['short_description'] ?? '', ENT_QUOTES); ?>" data-tags="<?php echo htmlspecialchars($row['tags'] ?? '', ENT_QUOTES); ?>" data-category="<?php echo htmlspecialchars($row['category'] ?? 'what_we_offer', ENT_QUOTES); ?>" data-status="<?php echo htmlspecialchars($row['status'] ?? 'inactive', ENT_QUOTES); ?>" data-image="<?php echo htmlspecialchars($row['image'] ?? '', ENT_QUOTES); ?>">
        <td class="img-cell"><?php if (!empty($row['image'])): ?><img class="img-thumbnail" src="uploads/service/<?php echo htmlspecialchars($row['image']); ?>" width="120" alt="course"><?php else: ?>N/A<?php endif; ?></td>
        <td class="title-cell"><?php echo htmlspecialchars($row['title']); ?></td>
        <td class="category-cell"><?php echo htmlspecialchars(($row['category'] ?? 'what_we_offer') === 'specialized' ? 'Specialized Analysis' : 'What We Offer'); ?></td>
        <td>
          <label class="switch">
            <input type="checkbox" class="status-toggle" data-id="<?php echo (int)$row['id']; ?>" <?php echo $row['status'] === 'active' ? 'checked' : ''; ?>>
            <span class="slider"></span>
          </label>
          <span class="status-pill <?php echo $row['status'] === 'active' ? 'status-active' : 'status-inactive'; ?>" id="status-text-<?php echo (int)$row['id']; ?>"><?php echo ucfirst(htmlspecialchars($row['status'])); ?></span>
        </td>
        <td class="actions">
          <div class="d-flex gap-2">
            <button class="btn btn-sm btn-primary edit-btn" type="button" data-id="<?php echo (int)$row['id']; ?>">Edit</button>
            <button class="btn btn-sm btn-danger delete-btn" type="button" data-id="<?php echo (int)$row['id']; ?>">Delete</button>
          </div>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<script>
(function() {
  var form = document.getElementById('serviceForm');
  var tableBody = document.getElementById('serviceTableBody');
  var msgBox = document.getElementById('serviceMsg');
  var serviceId = document.getElementById('serviceId');
  var formTitle = document.getElementById('serviceFormTitle');
  var saveBtn = document.getElementById('saveBtn');
  var cancelEditBtn = document.getElementById('cancelEditBtn');
  var statusToggleForm = document.getElementById('statusToggleForm');
  var statusToggleFormLabel = document.getElementById('statusToggleFormLabel');

  function syncFormStatusUi(status) {
    var isActive = status === 'active';
    document.getElementById('status').value = isActive ? 'active' : 'inactive';
    statusToggleForm.checked = isActive;
    statusToggleFormLabel.textContent = isActive ? 'Active' : 'Inactive';
    statusToggleFormLabel.classList.remove('status-active', 'status-inactive');
    statusToggleFormLabel.classList.add(isActive ? 'status-active' : 'status-inactive');
  }

  function showMsg(text, ok) {
    msgBox.style.display = 'block';
    msgBox.className = 'alert ' + (ok ? 'alert-success' : 'alert-danger') + ' mb-4';
    msgBox.textContent = text;
    setTimeout(function(){ msgBox.style.display = 'none'; }, 3000);
  }

  function escapeHtml(str) {
    return (str || '').replace(/[&<>'"]/g, function(c) {
      return {'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c];
    });
  }

  function resetForm() {
    form.reset();
    serviceId.value = '0';
    document.getElementById('currentImage').value = '';
    formTitle.textContent = 'Add Service';
    saveBtn.textContent = 'Save Service';
    cancelEditBtn.style.display = 'none';
    syncFormStatusUi('active');
  }

  function buildRow(row) {
    var checked = row.status === 'active' ? 'checked' : '';
    var pillClass = row.status === 'active' ? 'status-active' : 'status-inactive';
    var statusText = row.status.charAt(0).toUpperCase() + row.status.slice(1);
    var imgHtml = row.image ? '<img class="img-thumbnail" src="uploads/service/' + escapeHtml(row.image) + '" width="120" alt="course">' : 'N/A';

    return '<tr id="row-' + row.id + '" data-id="' + row.id + '" data-title="' + escapeHtml(row.title) + '" data-short_description="' + escapeHtml(row.short_description) + '" data-tags="' + escapeHtml(row.tags) + '" data-category="' + escapeHtml(row.category) + '" data-status="' + escapeHtml(row.status) + '" data-image="' + escapeHtml(row.image) + '">' +
      '<td class="img-cell">' + imgHtml + '</td>' +
      '<td class="title-cell">' + escapeHtml(row.title) + '</td>' +
      '<td class="category-cell">' + escapeHtml(row.category_text) + '</td>' +
      '<td>' +
        '<label class="switch">' +
          '<input type="checkbox" class="status-toggle" data-id="' + row.id + '" ' + checked + '>' +
          '<span class="slider"></span>' +
        '</label>' +
        '<span class="status-pill ' + pillClass + '" id="status-text-' + row.id + '">' + statusText + '</span>' +
      '</td>' +
      '<td class="actions">' +
        '<div class="d-flex gap-2">' +
          '<button class="btn btn-sm btn-primary edit-btn" type="button" data-id="' + row.id + '">Edit</button>' +
          '<button class="btn btn-sm btn-danger delete-btn" type="button" data-id="' + row.id + '">Delete</button>' +
        '</div>' +
      '</td>' +
    '</tr>';
  }

  form.addEventListener('submit', function(e) {
    e.preventDefault();
    var fd = new FormData(form);

    fetch('service.php', { method: 'POST', body: fd })
      .then(function(res) { return res.text(); })
      .then(function(text) {
        var data = JSON.parse(text.replace(/^\uFEFF/, '').trim());
        if (!data.ok) {
          showMsg(data.message || 'Action failed.', false);
          return;
        }

        if (serviceId.value === '0') {
          tableBody.insertAdjacentHTML('afterbegin', buildRow(data.row));
        } else {
          var target = document.getElementById('row-' + data.id);
          if (target) {
            target.outerHTML = buildRow(data.row);
          }
        }

        showMsg(data.message || 'Saved.', true);
        resetForm();
      })
      .catch(function() {
        showMsg('AJAX / JSON error.', false);
      });
  });

  cancelEditBtn.addEventListener('click', resetForm);
  statusToggleForm.addEventListener('change', function() {
    syncFormStatusUi(statusToggleForm.checked ? 'active' : 'inactive');
  });

  tableBody.addEventListener('click', function(e) {
    var editBtn = e.target.closest('.edit-btn');
    if (editBtn) {
      var id = editBtn.getAttribute('data-id');
      var row = document.getElementById('row-' + id);
      if (!row) return;

      serviceId.value = id;
      document.getElementById('title').value = row.getAttribute('data-title') || '';
      document.getElementById('short_description').value = row.getAttribute('data-short_description') || '';
      document.getElementById('tags').value = row.getAttribute('data-tags') || '';
      document.getElementById('category').value = row.getAttribute('data-category') || 'what_we_offer';
      syncFormStatusUi(row.getAttribute('data-status') || 'inactive');
      document.getElementById('currentImage').value = row.getAttribute('data-image') || '';

      formTitle.textContent = 'Edit Service #' + id;
      saveBtn.textContent = 'Update Service';
      cancelEditBtn.style.display = 'inline-block';
      var formCard = document.getElementById('serviceFormTitle');
      if (formCard) {
        formCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
      } else {
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
      setTimeout(function() {
        document.getElementById('title').focus();
      }, 250);
      return;
    }

    var deleteBtn = e.target.closest('.delete-btn');
    if (deleteBtn) {
      var deleteId = deleteBtn.getAttribute('data-id');
      if (!confirm('Delete this course?')) return;

      var body = new URLSearchParams();
      body.append('ajax_action', 'delete_course');
      body.append('id', deleteId);

      fetch('service.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body.toString()
      })
      .then(function(res) { return res.text(); })
      .then(function(text) {
        var data = JSON.parse(text.replace(/^\uFEFF/, '').trim());
        if (!data.ok) {
          showMsg(data.message || 'Delete failed.', false);
          return;
        }
        var rowEl = document.getElementById('row-' + data.id);
        if (rowEl) rowEl.remove();
        showMsg(data.message || 'Deleted.', true);
      })
      .catch(function() {
        showMsg('AJAX / JSON error.', false);
      });
    }
  });

  tableBody.addEventListener('change', function(e) {
    var toggle = e.target.closest('.status-toggle');
    if (!toggle) return;

    var id = toggle.getAttribute('data-id');
    var nextStatus = toggle.checked ? 'active' : 'inactive';
    toggle.disabled = true;

    var body = new URLSearchParams();
    body.append('ajax_action', 'toggle_status');
    body.append('id', id);
    body.append('status', nextStatus);

    fetch('service.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: body.toString()
    })
    .then(function(res) { return res.text(); })
    .then(function(text) {
      var data = JSON.parse(text.replace(/^\uFEFF/, '').trim());
      if (!data.ok) {
        toggle.checked = !toggle.checked;
        showMsg(data.message || 'Status update failed', false);
        return;
      }

      var statusText = document.getElementById('status-text-' + id);
      if (statusText) {
        statusText.textContent = data.status.charAt(0).toUpperCase() + data.status.slice(1);
        statusText.classList.remove('status-active', 'status-inactive');
        statusText.classList.add(data.status === 'active' ? 'status-active' : 'status-inactive');
      }

      var row = document.getElementById('row-' + id);
      if (row) {
        row.setAttribute('data-status', data.status);
      }
    })
    .catch(function() {
      toggle.checked = !toggle.checked;
      showMsg('AJAX / JSON error.', false);
    })
    .finally(function() {
      toggle.disabled = false;
    });
  });
})();
</script>
</main></div></body></html>

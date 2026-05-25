<?php
$pageTitle = 'Enquiries';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';


$msg = '';
$msgType = 'ok';

$tableCheck = $conn->query("SHOW TABLES LIKE 'enquiries'");
$tableExists = $tableCheck && $tableCheck->num_rows > 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if (!$tableExists) {
        $msg = 'enquiries table not found. Please run SQL setup first.';
        $msgType = 'err';
    } elseif ($id <= 0) {
        $msg = 'Invalid enquiry id.';
        $msgType = 'err';
    } else {
        $stmt = $conn->prepare('DELETE FROM enquiries WHERE id = ?');
        if (!$stmt) {
            $msg = 'Delete query failed: ' . $conn->error;
            $msgType = 'err';
        } else {
            $stmt->bind_param('i', $id);
            if ($stmt->execute()) {
                $msg = 'Enquiry deleted successfully.';
                $msgType = 'ok';
            } else {
                $msg = 'Failed to delete enquiry.';
                $msgType = 'err';
            }
            $stmt->close();
        }
    }
}

$list = null;
if ($tableExists) {
    $list = $conn->query('SELECT id, name, email, phone, service, subject, message, created_at FROM enquiries ORDER BY id DESC');
}

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
  <h3 class="mb-3">Enquiries</h3>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th style="width:70px;">ID</th>
          <th>Name</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Service</th>
          <th>Subject</th>
          <th>Message</th>
          <th style="width:180px;">Created At</th>
          <th style="width:100px;">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$tableExists): ?>
          <tr><td colspan="9" class="text-center text-muted">`enquiries` table not found. Run the SQL query first.</td></tr>
        <?php elseif ($list && $list->num_rows > 0): ?>
          <?php while ($row = $list->fetch_assoc()): ?>
            <tr>
              <td><?php echo (int)$row['id']; ?></td>
              <td><?php echo htmlspecialchars($row['name']); ?></td>
              <td><?php echo htmlspecialchars($row['email']); ?></td>
              <td><?php echo htmlspecialchars($row['phone'] ?? ''); ?></td>
              <td><?php echo htmlspecialchars($row['service'] ?? ''); ?></td>
              <td><?php echo htmlspecialchars($row['subject']); ?></td>
              <td><?php echo nl2br(htmlspecialchars($row['message'])); ?></td>
              <td><?php echo htmlspecialchars($row['created_at']); ?></td>
              <td>
                <form method="post" onsubmit="return confirm('Delete this enquiry?');" class="m-0">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                  <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="9" class="text-center text-muted">No enquiries found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

</main></div></body></html>

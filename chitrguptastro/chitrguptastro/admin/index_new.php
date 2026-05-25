<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/db.php';

// Get statistics
$totalservice = (int)($conn->query("SELECT COUNT(*) AS c FROM service")->fetch_assoc()['c'] ?? 0);
$activeservice = (int)($conn->query("SELECT COUNT(*) AS c FROM service WHERE status='active'")->fetch_assoc()['c'] ?? 0);
$inactiveservice = (int)($conn->query("SELECT COUNT(*) AS c FROM service WHERE status='inactive'")->fetch_assoc()['c'] ?? 0);

$totalLeads = null;
$checkLeadTable = $conn->query("SHOW TABLES LIKE 'enquiries'");
if ($checkLeadTable && $checkLeadTable->num_rows > 0) {
    $totalLeads = (int)($conn->query("SELECT COUNT(*) AS c FROM enquiries")->fetch_assoc()['c'] ?? 0);
} else {
    $checkLeadTable = $conn->query("SHOW TABLES LIKE 'leads'");
    if ($checkLeadTable && $checkLeadTable->num_rows > 0) {
        $totalLeads = (int)($conn->query("SELECT COUNT(*) AS c FROM leads")->fetch_assoc()['c'] ?? 0);
    }
}

include __DIR__ . '/includes/header_new.php';
include __DIR__ . '/includes/sidebar_new.php';
?>

<div class="container-fluid">
  <!-- Dashboard Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h2 class="h3 mb-1">Welcome to Dashboard</h2>
      <p class="text-muted mb-0">Here's an overview of your admin panel</p>
    </div>
    <div>
      <span class="badge bg-success">System Online</span>
    </div>
  </div>

  <!-- Statistics Cards -->
  <div class="cards">
    <div class="card">
      <h3><i class="bi bi-briefcase me-2"></i>Total Services</h3>
      <p><?php echo $totalservice; ?></p>
      <small class="text-muted d-block mt-2">Services managed</small>
    </div>

    <div class="card">
      <h3><i class="bi bi-check-circle me-2"></i>Active Services</h3>
      <p><?php echo $activeservice; ?></p>
      <small class="text-muted d-block mt-2">Currently active</small>
    </div>

    <div class="card">
      <h3><i class="bi bi-x-circle me-2"></i>Inactive Services</h3>
      <p><?php echo $inactiveservice; ?></p>
      <small class="text-muted d-block mt-2">Not active</small>
    </div>

    <div class="card">
      <h3><i class="bi bi-chat-dots me-2"></i>Total Enquiries</h3>
      <p><?php echo $totalLeads === null ? 'N/A' : $totalLeads; ?></p>
      <small class="text-muted d-block mt-2">Customer inquiries</small>
    </div>
  </div>

  <!-- Recent Activity Section -->
  <div class="row mt-4">
    <div class="col-md-8">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
          <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Quick Actions</h5>
        </div>
        <div class="card-body">
          <div class="list-group">
            <a href="service.php" class="list-group-item list-group-item-action border-0">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="mb-1">Manage Services</h6>
                  <small class="text-muted">Add, edit, or remove services</small>
                </div>
                <i class="bi bi-chevron-right text-primary"></i>
              </div>
            </a>
            <a href="gallery.php" class="list-group-item list-group-item-action border-0">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="mb-1">Gallery Management</h6>
                  <small class="text-muted">Upload and organize gallery images</small>
                </div>
                <i class="bi bi-chevron-right text-primary"></i>
              </div>
            </a>
            <a href="enquiries.php" class="list-group-item list-group-item-action border-0">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="mb-1">View Enquiries</h6>
                  <small class="text-muted">Check customer inquiries and messages</small>
                </div>
                <i class="bi bi-chevron-right text-primary"></i>
              </div>
            </a>
            <a href="slider.php" class="list-group-item list-group-item-action border-0">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="mb-1">Update Sliders</h6>
                  <small class="text-muted">Manage homepage slider images</small>
                </div>
                <i class="bi bi-chevron-right text-primary"></i>
              </div>
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- System Info Section -->
    <div class="col-md-4">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
          <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>System Info</h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <small class="text-muted d-block">PHP Version</small>
            <strong><?php echo phpversion(); ?></strong>
          </div>
          <div class="mb-3">
            <small class="text-muted d-block">Server Time</small>
            <strong><?php echo date('Y-m-d H:i:s'); ?></strong>
          </div>
          <div class="mb-3">
            <small class="text-muted d-block">Database Status</small>
            <span class="badge bg-success">Connected</span>
          </div>
          <hr>
          <small class="text-muted">Admin Panel v2.0 - Modern Design</small>
        </div>
      </div>
    </div>
  </div>
</div>

</main>
</div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</html>

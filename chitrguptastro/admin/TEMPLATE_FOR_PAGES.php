<?php
/**
 * TEMPLATE FOR MODERNIZING EXISTING ADMIN PAGES
 * 
 * This template shows how to update existing admin pages (service.php, gallery.php, etc.)
 * to use the new modern design system with proper Bootstrap 5 styling.
 * 
 * Copy this structure and adapt it to your existing pages.
 */

$pageTitle = 'Page Title Here'; // Change this for each page
require_once __DIR__ . '/includes/db.php';

// Your existing PHP logic goes here...

include __DIR__ . '/includes/header.php';      // Changed from header_new.php to header.php
include __DIR__ . '/includes/sidebar.php';    // Changed from sidebar_new.php to sidebar.php

?>

<!-- ALERT MESSAGES SECTION -->
<?php if (isset($_GET['success'])): ?>
  <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
    <i class="bi bi-check-circle me-2"></i>
    <strong>Success!</strong> Your changes have been saved successfully.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
  <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
    <i class="bi bi-exclamation-circle me-2"></i>
    <strong>Error!</strong> Something went wrong. Please try again.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<!-- MAIN CONTENT CONTAINER -->
<div class="container-fluid">
  
  <!-- PAGE HEADER -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h2 class="h3 mb-1">Your Page Title</h2>
      <p class="text-muted mb-0">Description of this page goes here</p>
    </div>
    <div>
      <button class="btn btn-primary" onclick="openModal()">
        <i class="bi bi-plus-lg me-2"></i>Add New
      </button>
    </div>
  </div>

  <!-- FILTERS/SEARCH BAR (Optional) -->
  <div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-6">
          <input type="text" class="form-control" placeholder="Search...">
        </div>
        <div class="col-md-3">
          <select class="form-select">
            <option selected>All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
        <div class="col-md-3">
          <button class="btn btn-outline-primary w-100">
            <i class="bi bi-funnel me-2"></i>Filter
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- DATA TABLE -->
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom">
      <h5 class="mb-0">Items List</h5>
    </div>
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Status</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <!-- Your table rows go here -->
          <tr>
            <td>#001</td>
            <td>Sample Item</td>
            <td><span class="badge bg-success">Active</span></td>
            <td>2024-01-15</td>
            <td>
              <button class="btn btn-sm btn-outline-primary" title="Edit">
                <i class="bi bi-pencil"></i>
              </button>
              <button class="btn btn-sm btn-outline-danger" title="Delete">
                <i class="bi bi-trash"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="card-footer bg-white">
      <nav>
        <ul class="pagination mb-0 justify-content-end">
          <li class="page-item"><a class="page-link" href="#">Previous</a></li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item"><a class="page-link" href="#">Next</a></li>
        </ul>
      </nav>
    </div>
  </div>

</div>

<!-- MODAL TEMPLATE FOR ADD/EDIT -->
<div class="modal fade" id="itemModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add/Edit Item</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="itemForm">
          <div class="mb-3">
            <label for="itemName" class="form-label">Name</label>
            <input type="text" class="form-control" id="itemName" required>
          </div>
          <div class="mb-3">
            <label for="itemStatus" class="form-label">Status</label>
            <select class="form-select" id="itemStatus" required>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="itemDescription" class="form-label">Description</label>
            <textarea class="form-control" id="itemDescription" rows="4"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" onclick="saveItem()">Save Changes</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  function openModal() {
    const modal = new bootstrap.Modal(document.getElementById('itemModal'));
    modal.show();
  }

  function saveItem() {
    const form = document.getElementById('itemForm');
    if (form.checkValidity() === false) {
      form.classList.add('was-validated');
      return;
    }
    // Submit your data here
    console.log('Saving item...');
  }

  // Add more JavaScript functions as needed
</script>

</main>
</div>
</body>
</html>

<?php
/**
 * MODERN ADMIN PANEL - COMPONENT SHOWCASE
 * 
 * This page demonstrates all the available components and styling
 * in the modern redesigned admin panel. Use this as a reference
 * when building new pages or updating existing ones.
 */

$pageTitle = 'Component Showcase';
require_once __DIR__ . '/includes/db.php';

// Mock data for demonstration
$stats = [
    ['title' => 'Total Services', 'value' => '42', 'icon' => 'briefcase', 'color' => 'primary'],
    ['title' => 'Active Services', 'value' => '38', 'icon' => 'check-circle', 'color' => 'success'],
    ['title' => 'Inactive Services', 'value' => '4', 'icon' => 'x-circle', 'color' => 'warning'],
    ['title' => 'Total Enquiries', 'value' => '156', 'icon' => 'chat-dots', 'color' => 'info'],
];

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h2 class="h3 mb-1">Component Showcase</h2>
      <p class="text-muted mb-0">Modern admin panel components and patterns</p>
    </div>
  </div>

  <!-- ==================== ALERTS SECTION ==================== -->
  <div class="mb-4">
    <h4 class="mb-3">
      <i class="bi bi-info-circle me-2"></i>Alerts & Messages
    </h4>
    
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="bi bi-check-circle me-2"></i>
      <strong>Success!</strong> Your action was completed successfully.
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>

    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="bi bi-exclamation-circle me-2"></i>
      <strong>Error!</strong> Something went wrong. Please try again.
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>

    <div class="alert alert-warning alert-dismissible fade show" role="alert">
      <i class="bi bi-exclamation-triangle me-2"></i>
      <strong>Warning!</strong> Please review this important information.
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>

    <div class="alert alert-info alert-dismissible fade show" role="alert">
      <i class="bi bi-info-circle me-2"></i>
      <strong>Info:</strong> Here's some useful information for you.
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  </div>

  <!-- ==================== BUTTONS SECTION ==================== -->
  <div class="mb-4">
    <h4 class="mb-3">
      <i class="bi bi-cursor-fill me-2"></i>Buttons
    </h4>
    
    <div class="card border-0 shadow-sm p-3">
      <div class="d-flex gap-2 flex-wrap">
        <button class="btn btn-primary">
          <i class="bi bi-plus-lg me-2"></i>Primary Button
        </button>
        <button class="btn btn-outline-primary">Outline Primary</button>
        <button class="btn btn-success">
          <i class="bi bi-check-lg me-2"></i>Success Button
        </button>
        <button class="btn btn-danger">
          <i class="bi bi-trash me-2"></i>Danger Button
        </button>
        <button class="btn btn-warning">Warning Button</button>
        <button class="btn btn-info">Info Button</button>
        <button class="btn btn-secondary">Secondary</button>
        <button class="btn btn-link">Link Button</button>
      </div>
    </div>
  </div>

  <!-- ==================== STATISTICS CARDS ==================== -->
  <div class="mb-4">
    <h4 class="mb-3">
      <i class="bi bi-speedometer2 me-2"></i>Statistics Cards
    </h4>
    
    <div class="row g-4">
      <?php foreach ($stats as $stat): ?>
        <div class="col-md-6 col-lg-3">
          <div class="card border-0 shadow-sm p-4 h-100">
            <h5 class="text-<?php echo $stat['color']; ?> mb-3">
              <i class="bi bi-<?php echo $stat['icon']; ?> me-2"></i>
              <?php echo $stat['title']; ?>
            </h5>
            <h2 class="display-6 fw-bold mb-0"><?php echo $stat['value']; ?></h2>
            <small class="text-muted d-block mt-2">Last updated today</small>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- ==================== FORMS SECTION ==================== -->
  <div class="mb-4">
    <h4 class="mb-3">
      <i class="bi bi-input-cursor me-2"></i>Forms & Inputs
    </h4>
    
    <div class="card border-0 shadow-sm p-3">
      <form>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="formName" class="form-label">Full Name</label>
            <input type="text" class="form-control" id="formName" placeholder="Enter your name">
          </div>
          <div class="col-md-6 mb-3">
            <label for="formEmail" class="form-label">Email Address</label>
            <input type="email" class="form-control" id="formEmail" placeholder="Enter your email">
          </div>
        </div>

        <div class="mb-3">
          <label for="formCategory" class="form-label">Category</label>
          <select class="form-select" id="formCategory">
            <option selected>Choose category...</option>
            <option value="1">Service</option>
            <option value="2">Product</option>
            <option value="3">Event</option>
          </select>
        </div>

        <div class="mb-3">
          <label for="formMessage" class="form-label">Message</label>
          <textarea class="form-control" id="formMessage" rows="4" placeholder="Enter your message"></textarea>
        </div>

        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" id="formAgree">
          <label class="form-check-label" for="formAgree">
            I agree to the terms and conditions
          </label>
        </div>

        <button type="submit" class="btn btn-primary">
          <i class="bi bi-send me-2"></i>Submit
        </button>
        <button type="reset" class="btn btn-outline-secondary ms-2">Reset</button>
      </form>
    </div>
  </div>

  <!-- ==================== BADGES & LABELS ==================== -->
  <div class="mb-4">
    <h4 class="mb-3">
      <i class="bi bi-tags me-2"></i>Badges & Labels
    </h4>
    
    <div class="card border-0 shadow-sm p-3">
      <div class="d-flex gap-2 flex-wrap">
        <span class="badge bg-primary">Primary</span>
        <span class="badge bg-success">Success</span>
        <span class="badge bg-danger">Danger</span>
        <span class="badge bg-warning text-dark">Warning</span>
        <span class="badge bg-info">Info</span>
        <span class="badge bg-secondary">Secondary</span>
        <span class="badge bg-light text-dark">Light</span>
        <span class="badge bg-dark">Dark</span>
      </div>
    </div>
  </div>

  <!-- ==================== TABLES SECTION ==================== -->
  <div class="mb-4">
    <h4 class="mb-3">
      <i class="bi bi-table me-2"></i>Tables
    </h4>
    
    <div class="card border-0 shadow-sm">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th>#</th>
              <th>Service Name</th>
              <th>Category</th>
              <th>Status</th>
              <th>Date Added</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>1</td>
              <td>Astrology Reading</td>
              <td>Services</td>
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
            <tr>
              <td>2</td>
              <td>Horoscope Report</td>
              <td>Reports</td>
              <td><span class="badge bg-success">Active</span></td>
              <td>2024-01-10</td>
              <td>
                <button class="btn btn-sm btn-outline-primary" title="Edit">
                  <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" title="Delete">
                  <i class="bi bi-trash"></i>
                </button>
              </td>
            </tr>
            <tr>
              <td>3</td>
              <td>Vedic Consultation</td>
              <td>Services</td>
              <td><span class="badge bg-warning text-dark">Pending</span></td>
              <td>2024-01-08</td>
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
    </div>
  </div>

  <!-- ==================== GRID LAYOUT ==================== -->
  <div class="mb-4">
    <h4 class="mb-3">
      <i class="bi bi-columns-gap me-2"></i>Grid Layout Examples
    </h4>
    
    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <div class="card border-0 shadow-sm p-3 text-center">
          <i class="bi bi-layout-split" style="font-size: 2rem; color: #667eea;"></i>
          <p class="mt-2 mb-0">50% Width Column</p>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card border-0 shadow-sm p-3 text-center">
          <i class="bi bi-layout-split" style="font-size: 2rem; color: #667eea;"></i>
          <p class="mt-2 mb-0">50% Width Column</p>
        </div>
      </div>
    </div>

    <div class="row g-3">
      <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm p-3 text-center">
          <i class="bi bi-columns" style="font-size: 2rem; color: #667eea;"></i>
          <p class="mt-2 mb-0">25%</p>
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm p-3 text-center">
          <i class="bi bi-columns" style="font-size: 2rem; color: #667eea;"></i>
          <p class="mt-2 mb-0">25%</p>
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm p-3 text-center">
          <i class="bi bi-columns" style="font-size: 2rem; color: #667eea;"></i>
          <p class="mt-2 mb-0">25%</p>
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm p-3 text-center">
          <i class="bi bi-columns" style="font-size: 2rem; color: #667eea;"></i>
          <p class="mt-2 mb-0">25%</p>
        </div>
      </div>
    </div>
  </div>

  <!-- ==================== CARDS & PANELS ==================== -->
  <div class="mb-4">
    <h4 class="mb-3">
      <i class="bi bi-credit-card me-2"></i>Card Variations
    </h4>
    
    <div class="row g-3">
      <div class="col-md-4">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white border-bottom">
            <h5 class="mb-0">Card Header</h5>
          </div>
          <div class="card-body">
            <p>This is a card with header, body, and footer. Use this pattern for organizing content.</p>
          </div>
          <div class="card-footer bg-white border-top">
            <button class="btn btn-sm btn-primary">Action</button>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card border-0 shadow-sm border-top border-4" style="border-top-color: #48bb78 !important;">
          <div class="card-body">
            <h5 class="card-title">
              <i class="bi bi-check-circle me-2" style="color: #48bb78;"></i>Success Card
            </h5>
            <p class="card-text">Card with colored top border and icon</p>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
          <div class="card-body">
            <h5 class="card-title">
              <i class="bi bi-star-fill me-2"></i>Gradient Card
            </h5>
            <p class="card-text">Card with gradient background for highlights</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ==================== MODALS ==================== -->
  <div class="mb-4">
    <h4 class="mb-3">
      <i class="bi bi-window me-2"></i>Modals & Dialogs
    </h4>
    
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
      <i class="bi bi-window me-2"></i>Open Modal Example
    </button>
  </div>

  <!-- Modal Example -->
  <div class="modal fade" id="exampleModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="bi bi-info-circle me-2"></i>Modal Title
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p>This is a modern modal dialog. It can contain forms, messages, or any content you need.</p>
          <form>
            <div class="mb-3">
              <label for="modalInput" class="form-label">Input Field</label>
              <input type="text" class="form-control" id="modalInput">
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary">Save Changes</button>
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

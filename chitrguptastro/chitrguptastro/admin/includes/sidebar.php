<?php 
$current = basename($_SERVER['PHP_SELF']); 
$adminBrand = 'Chitra Gupta Admin';
?>
<!-- Modern Sidebar Navigation -->
<aside class="sidebar bg-dark">
  <div class="sidebar-header">
    <div class="brand">
      <i class="bi bi-gear-fill"></i>
      <span><?php echo htmlspecialchars($adminBrand); ?></span>
    </div>
    <button type="button" class="btn-close btn-close-white d-md-none" onclick="document.querySelector('.sidebar').classList.remove('show')"></button>
  </div>

  <nav class="sidebar-nav">
    <!-- Dashboard -->
    <a href="index.php" class="nav-link <?php echo $current === 'index.php' ? 'active' : ''; ?>">
      <i class="bi bi-speedometer2"></i>
      <span>Dashboard</span>
    </a>

    <!-- Content Management Section -->
    <div class="nav-section">
      <div class="nav-section-title">Content</div>
      <a href="service.php" class="nav-link <?php echo $current === 'service.php' ? 'active' : ''; ?>">
        <i class="bi bi-briefcase"></i>
        <span>Services</span>
      </a>
      <a href="slider.php" class="nav-link <?php echo $current === 'slider.php' ? 'active' : ''; ?>">
        <i class="bi bi-images"></i>
        <span>Sliders</span>
      </a>
      <a href="gallery.php" class="nav-link <?php echo $current === 'gallery.php' ? 'active' : ''; ?>">
        <i class="bi bi-collection"></i>
        <span>Gallery</span>
      </a>
      <a href="video.php" class="nav-link <?php echo $current === 'video.php' ? 'active' : ''; ?>">
        <i class="bi bi-play-circle"></i>
        <span>Videos</span>
      </a>
    </div>

    <!-- Management Section -->
    <div class="nav-section">
      <div class="nav-section-title">Manage</div>
      <a href="workflow.php" class="nav-link <?php echo $current === 'workflow.php' ? 'active' : ''; ?>">
        <i class="bi bi-diagram-3"></i>
        <span>Workflow</span>
      </a>
      <a href="accolades.php" class="nav-link <?php echo $current === 'accolades.php' ? 'active' : ''; ?>">
        <i class="bi bi-award"></i>
        <span>Awards</span>
      </a>
      <a href="enquiries.php" class="nav-link <?php echo $current === 'enquiries.php' ? 'active' : ''; ?>">
        <i class="bi bi-chat-dots"></i>
        <span>Enquiries</span>
      </a>
    </div>
  </nav>

  <div class="sidebar-footer">
    <a href="logout.php" class="btn btn-outline-light btn-sm w-100 text-decoration-none" data-no-spa="true">
      <i class="bi bi-box-arrow-right"></i> Logout
    </a>
  </div>
</aside>

<!-- Top Header Bar -->
<header class="topbar">
  <div class="topbar-left">
    <button type="button" class="btn btn-link text-white d-md-none" onclick="document.querySelector('.sidebar').classList.add('show')">
      <i class="bi bi-list fs-5"></i>
    </button>
  </div>

  <div class="topbar-center">
    <h1 class="page-title mb-0"><?php echo htmlspecialchars($pageTitle); ?></h1>
  </div>

  <div class="topbar-right">
    <div class="user-profile">
      <i class="bi bi-person-badge"></i>
      <span class="user-name">Admin</span>
    </div>
  </div>
</header>

<!-- Main Content Area -->
<main class="main-content">

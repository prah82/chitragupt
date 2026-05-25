<?php
if (!isset($pageTitle)) {
    $pageTitle = 'Admin Panel';
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo htmlspecialchars($pageTitle); ?></title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <!-- Custom Admin CSS -->
  <link rel="stylesheet" href="assets/css/admin-modern.css">
  <link rel="stylesheet" href="assets/css/workflow.css">
  <style>
    :root {
      --primary-color: #667eea;
      --secondary-color: #764ba2;
      --success-color: #48bb78;
      --danger-color: #f56565;
      --warning-color: #ed8936;
      --info-color: #4299e1;
      --dark-bg: #1a202c;
      --light-bg: #f7fafc;
      --border-color: #e2e8f0;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: var(--light-bg);
      color: #2d3748;
    }

    .admin-shell {
      display: flex;
      min-height: 100vh;
      background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    }
  </style>
  
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.5.13/dist/cropper.min.css" crossorigin="anonymous">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.8/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.5.13/dist/cropper.min.js" crossorigin="anonymous"></script>
</head>
<body>
<div class="admin-shell">
<script>
document.addEventListener('DOMContentLoaded', function () {
  function initAlerts() {
    var alerts = document.querySelectorAll('.alert');
    alerts.forEach(function (el) {
      setTimeout(function () {
        el.style.transition = 'opacity 0.3s ease';
        el.style.opacity = '0';
        setTimeout(function () {
          el.style.display = 'none';
        }, 300);
      }, 3000);
    });
  }

  function executeInlineScripts(scope) {
    var scripts = scope.querySelectorAll('script');
    scripts.forEach(function (oldScript) {
      var newScript = document.createElement('script');
      if (oldScript.src) {
        newScript.src = oldScript.src;
      } else {
        newScript.textContent = oldScript.textContent;
      }
      Array.from(oldScript.attributes).forEach(function (attr) {
        if (attr.name !== 'src') {
          newScript.setAttribute(attr.name, attr.value);
        }
      });
      oldScript.parentNode.replaceChild(newScript, oldScript);
    });
  }

  function setActiveSidebarLink(url) {
    var currentPath = new URL(url, window.location.origin).pathname.toLowerCase();
    document.querySelectorAll('.nav-link').forEach(function (a) {
      var hrefPath = new URL(a.getAttribute('href'), window.location.origin).pathname.toLowerCase();
      a.classList.toggle('active', hrefPath === currentPath);
    });
  }

  async function loadAdminPage(url, push) {
    try {
      var res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      var html = await res.text();
      var parser = new DOMParser();
      var doc = parser.parseFromString(html, 'text/html');
      var newMain = doc.querySelector('.main-content');
      var curMain = document.querySelector('.main-content');
      if (!newMain || !curMain) {
        window.location.href = url;
        return;
      }

      curMain.innerHTML = newMain.innerHTML;
      document.title = doc.title || document.title;
      
      var newPageTitle = doc.querySelector('.page-title');
      var curPageTitle = document.querySelector('.page-title');
      if (newPageTitle && curPageTitle) {
        curPageTitle.innerHTML = newPageTitle.innerHTML;
      }

      setActiveSidebarLink(url);
      executeInlineScripts(curMain);
      initAlerts();

      if (push) {
        history.pushState({ adminSpa: true, url: url }, '', url);
      }
    } catch (e) {
      window.location.href = url;
    }
  }

  function closeSidebarOnMobile() {
    var sidebar = document.querySelector('.sidebar');
    if (sidebar && sidebar.classList.contains('show')) {
      sidebar.classList.remove('show');
    }
  }

  document.querySelectorAll('.nav-link').forEach(function (link) {
    link.addEventListener('click', function (e) {
      var href = link.getAttribute('href');
      if (!href || href.indexOf('http') === 0 || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) {
        return;
      }
      e.preventDefault();
      closeSidebarOnMobile();
      loadAdminPage(href, true);
    });
  });

  window.addEventListener('popstate', function () {
    loadAdminPage(location.href, false);
  });

  initAlerts();
});
</script>


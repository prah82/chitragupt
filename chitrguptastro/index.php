<?php
require_once __DIR__ . '/config/app_paths.php';
$frontendservice = [];
$whatWeOfferservice = [];
$specializedservice = [];
$frontendDbPath = ADMIN_ABS_PATH . '/includes/db.php';
if (file_exists($frontendDbPath)) {
    include $frontendDbPath;
    if (isset($conn) && $conn instanceof mysqli) {
        $tableCheck = $conn->query("SHOW TABLES LIKE 'service'");
        if ($tableCheck && $tableCheck->num_rows > 0) {
            $columnCheck = $conn->query("SHOW COLUMNS FROM service LIKE 'category'");
            $hasCategory = $columnCheck && $columnCheck->num_rows > 0;

            $stmt = $conn->prepare("SELECT title, short_description, image, tags, category FROM service WHERE status = ? ORDER BY id DESC");
            $active = 'active';
            $stmt->bind_param('s', $active);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    $frontendservice[] = $row;
                    $courseCategory = $hasCategory ? ($row['category'] ?? 'what_we_offer') : 'what_we_offer';
                    if ($courseCategory === 'specialized') {
                        $specializedservice[] = $row;
                    } else {
                        $whatWeOfferservice[] = $row;
                    }
                }
            }
            $stmt->close();
        }
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="ChitraguptAstroVastu - Scientific Astro Vastu Guidance" />
  <title>ChitraguptAstroVastu</title>
  <link rel="icon" type="image/png" href="./logos/astro vastu.png" />
  <link rel="apple-touch-icon" href="./logos/astro vastu.png" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="./css/style.css" />

 
</head>
<body>
<?php require_once ADMIN_ABS_PATH . '/includes/db.php'; ?>
<!-- ══════════════════════════════════════════════
     NAVBAR
══════════════════════════════════════════════ -->
<nav class="navbar" id="navbar">
  <div class="nav-inner">
    <div class="nav-logo" onclick="showPage('home')">
      <div class="logo-icon"><img src="./logos/astro vastu.png" alt=""></div>
      <span class="logo-text"><em>ChitraguptAstroVastu</em></span>
    </div>
    <ul class="nav-links">
      <li><a onclick="showPage('home')" data-page="home" class="active">Home</a></li>
      <li><a onclick="showPage('about')" data-page="about">About</a></li>
      <li><a onclick="showPage('services')" data-page="services">Services</a></li>
      <li><a onclick="showPage('achievements')" data-page="achievements">Achievements</a></li>
      <li class="nav-dropdown">
        <a class="nav-dropdown-trigger" onclick="showPage('gallery','photos')" data-page="gallery">Gallery <i class="fa-solid fa-chevron-down"></i></a>
        <div class="nav-submenu">
          <a onclick="showPage('gallery','photos')">Photos <i class="fa-regular fa-images"></i></a>
          <a onclick="showPage('gallery','videos')">Videos <i class="fa-solid fa-circle-play"></i></a>
        </div>
      </li>
      <li><a onclick="showPage('contact')" data-page="contact">Contact Us</a></li>
    </ul>
    <div class="nav-cta">
      <button class="btn btn-primary btn-sm nav-book" onclick="showPage('books')">Order Books</button>
      <button class="hamburger" id="hamburger" aria-label="Toggle menu">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
</nav>

<!-- Mobile Nav -->
<nav class="mobile-nav" id="mobileNav">
  <a onclick="showPage('home');closeMobileNav()">Home</a>
  <a onclick="showPage('about');closeMobileNav()">About</a>
  <a onclick="showPage('services');closeMobileNav()">Services</a>
  <a onclick="showPage('achievements');closeMobileNav()">Achievements</a>
  <a onclick="showPage('gallery','photos');closeMobileNav()">Gallery Photos</a>
  <a onclick="showPage('gallery','videos');closeMobileNav()">Gallery Videos</a>
  <a onclick="showPage('books');closeMobileNav()" class="nav-book">Books</a>
</nav>

<!-- ══════════════════════════════════════════════
     PAGE: HOME
══════════════════════════════════════════════ -->
<div class="page active" id="page-home">

  <!-- Hero -->
<section class="hero">
  <div class="hero-slider" id="heroSlider">
    <?php
require_once __DIR__ . '/config/app_paths.php';
    $sliders = $conn->query("SELECT image FROM slider ORDER BY id DESC");
    $active = true;
    ?>

    <?php if ($sliders && $sliders->num_rows > 0): ?>
      <?php while ($slide = $sliders->fetch_assoc()): ?>
        <div class="hero-slide <?php echo $active ? 'active' : ''; ?>"
             style="background-image:url('admin/uploads/slider/<?php echo htmlspecialchars($slide['image']); ?>')">
        </div>
        <?php $active = false; ?>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="hero-slide active" style="background-image:url('./hero section/bg1.png')"></div>
    <?php endif; ?>
  </div>

  <div class="hero-grid"></div>
  <div class="hero-overlay"></div>
  <div class="hero-slider-dots" id="heroDots"></div>
</section>

  <section class="section home-intro">
    <div class="container">
      <div class="home-intro-card fi">
        <div class="home-intro-copy">
          <h1>Geopathic Stress Analysis and <em>Healing</em></h1>
          <p>We provide practical astro-vastu solutions using scientific methods, aura analysis, and geopathic stress detection to improve health, harmony, and success.</p>
          <div class="home-intro-actions">
            <button class="btn btn-primary" onclick="showPage('services')">Our Services</button>
            <button class="btn btn-outline" onclick="showPage('about')">About Institute</button>
          </div>
          <div class="home-intro-stats">
            <div class="stat-item"><h3><span data-count="25">25</span>+</h3><span>Years of Service</span></div>
            <div class="hero-stat-divider"></div>
            <div class="stat-item"><h3><span data-count="500">500</span>+</h3><span>Consultations</span></div>
            <div class="hero-stat-divider"></div>
            <div class="stat-item"><h3><span data-count="98">98</span>%</h3><span>Success Rate</span></div>
          </div>
        </div>
        <div class="home-intro-media">
          <div class="home-intro-video home-intro-youtube" onclick="playHomeIntroVideo(this)" role="button" tabindex="0" aria-label="Play YouTube video" data-video-id="vCQzJ-Mat08">
            <img src="https://img.youtube.com/vi/vCQzJ-Mat08/maxresdefault.jpg" alt="YouTube video thumbnail">
            <span class="video-play">▶</span>
            <div class="video-alt-link">Video not loading? <a href="https://youtu.be/vCQzJ-Mat08" target="_blank" rel="noopener noreferrer">Open on YouTube</a>.</div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- Services Preview -->
  <section class="section services special-analysis">
    <div class="container">
      <div class="sec-header fi">
        <span class="label">Our Expertise</span>
        <h2>Astro Vastu Services</h2>
        <p>Scientific and Vedic consultation services for personal, family, and business wellbeing.</p>
      </div>
      <div class="srv-grid">
        <?php if (!empty($frontendservice)): ?>
          <?php foreach ($frontendservice as $course): ?>
            <div class="srv-card fi">
              <div style="display:flex;justify-content:center;align-items:center;width:100%;margin-bottom:1.1rem">
                <?php if (!empty($course['image']) && file_exists(__DIR__ . '/admin/uploads/service/' . $course['image'])): ?>
                  <img src="./admin/uploads/service/<?php echo htmlspecialchars($course['image']); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" class="img-fluid d-block" style="max-width:110px;border-radius:10px;">
                <?php else: ?>
                  <img src="./logos/astro vastu.png" alt="<?php echo htmlspecialchars($course['title']); ?>" class="img-fluid d-block" style="max-width:88px;">
                <?php endif; ?>
              </div>
              <h3><?php echo htmlspecialchars($course['title']); ?></h3>
              <p><?php echo htmlspecialchars($course['short_description']); ?></p>
              <span class="srv-link" onclick="showPage('services')">Learn more ?</span>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="srv-card fi">
            <h3>service will be shown here</h3>
            <p>Add active service from admin panel to display them in this section.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- About Preview -->
  <section class="section" style="background:var(--white)">
    <div class="container">
      <div class="about-grid">
        <div class="about-visual fi">
          <div class="about-img about-logo"><img src="./logos/Shree Chitragupt Institute of Astro Studies and Research.png" alt="Shree Chitragupt Institute of Astro Studies and Research logo"></div>
          <div class="about-badge"><div class="num">25+</div><div class="lbl">Years of<br>Trusted Service</div></div>
        </div>
        <div class="fi">
          <span class="label">About Us</span>
          <h2 style="color:var(--n9);margin-bottom:1rem;font-size:clamp(1.9rem,2.3vw,2.5rem)">Shree Chitragupt Institute of Astro Studies and Research</h2>
          <p>The institute offers solutions and healing services with the help of geopathic analysis and Vedic astrological methods.</p>
          <p style="margin-top:1rem">Dr. Rajesh Srivastav started this initiative to help people through a combination of scientific and Vedic methodologies.</p>
          <div class="about-feats">
            <div class="about-feat"><div class="feat-chk">✓</div><p><strong>Scientific Methods</strong> - practical diagnosis through aura and geopathic tools.</p></div>
            <div class="about-feat"><div class="feat-chk">✓</div><p><strong>Consultation Reach</strong> - support for clients in India and abroad.</p></div>
            <div class="about-feat"><div class="feat-chk">✓</div><p><strong>International Recognition</strong> - honored by reputed astro foundations.</p></div>
          </div>
          <button class="btn btn-dark" style="margin-top:2rem" onclick="showPage('about')">Read Full Profile</button>
        </div>
      </div>
    </div>
  </section>

  <!-- Testimonials -->
  <section class="section testimonials">
    <div class="container">
      <div class="sec-header fi">
        <span class="label">Testimonials</span>
        <h2>What People Say About Us</h2>
        <p>Experiences shared by clients after astro-vastu consultation and healing guidance.</p>
      </div>
      <div class="test-grid">
        <div class="test-card fi"><div class="test-stars">★ ★ ★ ★ ★</div><p>"My life changed completely after consultation. I feel more energetic and positive."</p><div class="test-author"><div class="auth-av">RK</div><div><div class="auth-name">Amit Sharma</div><div class="auth-role">Client</div></div></div></div>
        <div class="test-card fi"><div class="test-stars">★ ★ ★ ★ ★</div><p>"Accurate analysis and practical remedies helped my family resolve long-standing issues."</p><div class="test-author"><div class="auth-av">SP</div><div><div class="auth-name">Neha Gupta</div><div class="auth-role">Client</div></div></div></div>
        <div class="test-card fi"><div class="test-stars">★ ★ ★ ★ ★</div><p>"Dr. Rajesh explained every remedy clearly. Results were visible within weeks."</p><div class="test-author"><div class="auth-av">AM</div><div><div class="auth-name">Rohit Verma</div><div class="auth-role">Client</div></div></div></div>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="cta-banner section-sm">
    <div class="container">
      <div class="cta-inner fi">
        <div class="cta-text"><h2>Ready to Improve Your Energy and Life?</h2><p>Book a scientific astro-vastu consultation and start your positive transformation.</p></div>
        <div class="cta-actions">
          <button class="btn btn-dark" onclick="showPage('contact')">Book Consultation</button>
          <button class="btn btn-outline" onclick="showPage('services')">Our Services</button>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer"><div class="container"><div class="footer-grid">
    <div class="footer-brand"><div class="nav-logo" onclick="showPage('home')" style="cursor:pointer"><div class="logo-icon"><img src="./logos/astro vastu.png" alt=""></div><span class="logo-text"><em>ChitraguptAstroVastu</em></span></div><p>Scientific Astro Vastu solutions for health, harmony, relationships, and success.</p><div class="social-links"><a class="social-a" href="https://www.facebook.com/ChitraguptAstroVaastu/" target="_blank"><i class="fab fa-facebook-f"></i></a><a class="social-a" href="https://www.instagram.com/chitraguptastrovastu/" target="_blank"><i class="fab fa-instagram"></i></a><a class="social-a" href="https://www.youtube.com/@chitraguptastrovastugeopat2656" target="_blank"><i class="fab fa-youtube"></i></a></div></div>
    <div class="footer-col"><h4>Company</h4><ul><li><a onclick="showPage('about')">About Us</a></li><li><a onclick="showPage('achievements')">Achievements</a></li><li><a onclick="showPage('gallery')">Gallery</a></li><li><a onclick="showPage('contact')">Contact</a></li></ul></div>
    <div class="footer-col"><h4>Services</h4><ul><li><a onclick="showPage('services')">Geopathic Stress</a></li><li><a onclick="showPage('services')">Grah Dosh</a></li><li><a onclick="showPage('services')">Distance Analysis</a></li><li><a onclick="showPage('services')">Gemstone Guidance</a></li></ul></div>
    <div class="footer-col"><h4>Contact</h4><ul><li><a href="mailto:chitraguptastrovastu@gmail.com">chitraguptastrovastu@gmail.com</a></li><li><a class="footer-whatsapp" href="https://wa.me/919929426997" target="_blank" rel="noopener noreferrer">+91-9929426997</a></li></ul></div>
  </div><div class="footer-bottom"><p>&copy; 2017-2026 Shree Chitragupt Institute Of Astro Studies and Research</p><p>Developed by <a class="footer-dev-link" href="https://euonusit.com/" target="_blank" rel="noopener noreferrer">EUONUS IT</a></p></div></div></footer>
</div>

<!-- ══════════════════════════════════════════════
     PAGE: ABOUT
══════════════════════════════════════════════ -->
<div class="page" id="page-about">
  <section class="page-hero"><div class="container page-hero-inner"><div class="breadcrumb"><a onclick="showPage('home')">Home</a><span>></span><span>About</span></div><h1>About Us</h1><p>Accept the self, accept others, and accept the world.</p></div></section>

  <section class="section" style="background:var(--white)">
    <div class="container">
      <div class="about-grid">
        <div class="about-visual fi"><div class="about-img" style="font-size:7rem"><img src="./images/home.jpg" alt=""></div><div class="about-badge"><div class="num">1999</div><div class="lbl">Year<br>Founded</div></div></div>
        <div class="fi"><span class="label">Our Story</span><h2 style="color:var(--n9);margin-bottom:1rem">Who We Are</h2><p>The Chitragupt Institute of Astro Studies and Research offers solutions and healing services through geopathic analysis and Vedic astrology. Dr. Rajesh Srivastav started this initiative to help people using a practical blend of scientific and Vedic methodologies.</p><p style="margin-top:1rem">The institute provides guidance for health, relationships, career, and family wellbeing through aura analysis, vastu consultation, and remedial astrology.</p><p style="margin-top:1rem">We also offer geopathic and vastu products including yantra, gemstones, energy pyramids, crystals, and related healing tools.</p><button class="btn btn-primary" style="margin-top:2rem" onclick="showPage('contact')">Book Consultation</button></div>
      </div>
    </div>
  </section>

  <section class="section section-card" style="background:var(--s1)">
    <div class="container">
      <div class="sec-header fi"><span class="label">Leadership</span><h2>Message from Our Chairman</h2><p>Guidance and vision of Dr. Rajesh Srivastav.</p></div>
      <div class="chairman-card fi">
        <div class="chairman-photo"><img src="./images/Chairman.png" alt="Dr. Rajesh Srivastav"></div>
        <div class="chairman-info">
          <span class="label">Chairman & Founder</span>
          <h2>Dr. Rajesh Srivastav</h2>
          <div class="role">Doctor of Vastu Shastra - Ph.D</div>
          <blockquote>"Scientific methods with Vedic wisdom can transform health, harmony, and success in life."</blockquote>
          <p>Dr. Rajesh Srivastav is a Doctorate in Vastu Shastra, Gold Medalist, and Life Member of International Astro Foundation (India). He has been recognized internationally for research and consultation work.</p>
          <p>As an Advance Remedial Expert, Scientific Astro Vastu Expert-cum-Healer, Aura Expert, and Geopathic Consultant, he has guided thousands of individuals and businesses.</p>
          <div class="about-feats" style="margin-top:1rem">
            <div class="about-feat"><div class="feat-chk">✓</div><p>Gold Medal for Research Work of Geopathic Stress</p></div>
            <div class="about-feat"><div class="feat-chk">✓</div><p>Life Member, International Astro Foundation</p></div>
          </div>
        </div>
      </div>    
    </div>
  </section>

  <section class="section services">
    <div class="container">
      <div class="sec-header fi"><span class="label">What Drives Us</span><h2>Our Mission, Vision and Values</h2><p>The principles and purpose that guide everything we do.</p></div>
      <div class="srv-grid">
        <div class="srv-card fi"><div class="srv-icon" ><img src="./logos/Our Mission.png" alt=""></div><h3>Our Mission</h3><p>To deliver scientific astro-vastu solutions that improve wellbeing, relationships, and life direction with practical and ethical guidance.</p></div>
        <div class="srv-card fi"><div class="srv-icon" style="margin:0 auto 1rem"><img src="./logos/Our Vision.png" alt=""></div><h3>Our Vision</h3><p>To make reliable astro-vastu consultation accessible so people can live healthier, balanced, and successful lives.</p></div>
        <div class="srv-card fi"><div class="srv-icon" style="margin:0 0 1rem auto"><img src="./logos/Our Values.png" alt=""></div><h3>Our Values</h3><p>Integrity, clarity, service, and responsibility guide every consultation, analysis, and recommendation we provide.</p></div>
      </div>
    </div>
  </section>

  <section class="section" style="background:var(--n9);position:relative;overflow:hidden">
    <div style="position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.025) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.025) 1px,transparent 1px);background-size:60px 60px;pointer-events:none"></div>
    <div class="container" style="position:relative;z-index:2">
      <div class="sec-header fi"><span class="label">Our Impact</span><h2 style="color:white">25 Years of Measurable Impact</h2><p style="color:rgba(255,255,255,.6)">Numbers that reflect real change in real communities.</p></div>
      <div class="ach-grid">
        <div class="ach-card fi"><div class="ach-num"><span data-count="1000">1000</span>+</div><div class="ach-lbl">Consultations</div></div>
        <div class="ach-card fi"><div class="ach-num"><span data-count="48">48</span></div><div class="ach-lbl">Districts Covered</div></div>
        <div class="ach-card fi"><div class="ach-num"><span data-count="7">7</span>+</div><div class="ach-lbl">countries Covered</div></div>
        <div class="ach-card fi"><div class="ach-num"><span data-count="120">120</span>+</div><div class="ach-lbl">Expert Team Members</div></div>
        <div class="ach-card fi"><div class="ach-num"><span data-count="98">98</span>%</div><div class="ach-lbl">Positive Outcomes</div></div>
      </div>
    </div>
  </section>

  
  <!-- CTA -->
  <section class="cta-banner section-sm">
    <div class="container">
      <div class="cta-inner fi">
        <div class="cta-text"><h2>Ready to Improve Your Energy and Life?</h2><p>Book a scientific astro-vastu consultation and start your positive transformation.</p></div>
        <div class="cta-actions">
          <button class="btn btn-dark" onclick="showPage('contact')">Book Consultation</button>
          <button class="btn btn-outline" onclick="showPage('services')">Our Services</button>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer"><div class="container"><div class="footer-grid">
    <div class="footer-brand"><div class="nav-logo" onclick="showPage('home')" style="cursor:pointer"><div class="logo-icon"><img src="./logos/astro vastu.png" alt=""></div><span class="logo-text"><em>ChitraguptAstroVastu</em></span></div><p>Scientific Astro Vastu solutions for health, harmony, relationships, and success.</p><div class="social-links"><a class="social-a" href="https://www.facebook.com/ChitraguptAstroVaastu/" target="_blank"><i class="fab fa-facebook-f"></i></a><a class="social-a" href="https://www.instagram.com/chitraguptastrovastu/" target="_blank"><i class="fab fa-instagram"></i></a><a class="social-a" href="https://www.youtube.com/@chitraguptastrovastugeopat2656" target="_blank"><i class="fab fa-youtube"></i></a></div></div>
    <div class="footer-col"><h4>Company</h4><ul><li><a onclick="showPage('about')">About Us</a></li><li><a onclick="showPage('achievements')">Achievements</a></li><li><a onclick="showPage('gallery')">Gallery</a></li><li><a onclick="showPage('contact')">Contact</a></li></ul></div>
    <div class="footer-col"><h4>Services</h4><ul><li><a onclick="showPage('services')">Geopathic Stress</a></li><li><a onclick="showPage('services')">Grah Dosh</a></li><li><a onclick="showPage('services')">Distance Analysis</a></li><li><a onclick="showPage('services')">Gemstone Guidance</a></li></ul></div>
    <div class="footer-col"><h4>Contact</h4><ul><li><a href="mailto:chitraguptastrovastu@gmail.com">chitraguptastrovastu@gmail.com</a></li><li><a class="footer-whatsapp" href="https://wa.me/919929426997" target="_blank" rel="noopener noreferrer">+91-9929426997</a></li></ul></div>
  </div><div class="footer-bottom"><p>&copy; 2017-2026 Shree Chitragupt Institute Of Astro Studies and Research</p><p>Developed by <a class="footer-dev-link" href="https://euonusit.com/" target="_blank" rel="noopener noreferrer">EUONUS IT</a></p></div></div></footer>
</div>
<!-- ══════════════════════════════════════════════
     PAGE: SERVICES
══════════════════════════════════════════════ -->
<div class="page" id="page-services">
  <section class="page-hero"><div class="container page-hero-inner"><div class="breadcrumb"><a onclick="showPage('home')">Home</a><span>></span><span>Services</span></div><h1>Our Services</h1><p>Scientific astro-vastu services designed to identify and resolve energy imbalances in life, home, and business.</p></div></section>

  <section class="section" style="background:var(--white)">
    <div class="container">
      <div class="sec-header fi"><span class="label">What We Offer</span><h2>Holistic Astro Vastu Solutions</h2><p>Each service is designed to create practical and measurable improvement in wellbeing and life outcomes.</p></div>
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:2rem">
        <?php if (!empty($whatWeOfferservice)): ?>
          <?php foreach ($whatWeOfferservice as $course): ?>
            <div class="srv-full fi">
              <div class="srv-icon" style="margin:0 auto 2rem">
                <?php if (!empty($course['image']) && file_exists(__DIR__ . '/admin/uploads/service/' . $course['image'])): ?>
                  <img src="./admin/uploads/service/<?php echo htmlspecialchars($course['image']); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" class="img-fluid d-block" style="max-width:110px;">
                <?php else: ?>
                  <img src="./logos/astro vastu.png" alt="<?php echo htmlspecialchars($course['title']); ?>" class="img-fluid d-block" style="max-width:80px;">
                <?php endif; ?>
              </div>
              <h3 style="color:var(--n9);margin-bottom:.75rem"><?php echo htmlspecialchars($course['title']); ?></h3>
              <p style="font-size:.925rem;margin-bottom:1rem"><?php echo htmlspecialchars($course['short_description']); ?></p>
              <?php
require_once __DIR__ . '/config/app_paths.php';
                $tags = array_filter(array_map('trim', explode(',', (string)($course['tags'] ?? ''))));
              ?>
              <?php if (!empty($tags)): ?>
                <div class="srv-tags">
                  <?php foreach ($tags as $tag): ?>
                    <span class="srv-tag"><?php echo htmlspecialchars($tag); ?></span>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="srv-full fi"><h3 style="color:var(--n9);margin-bottom:.75rem">No services found</h3><p style="font-size:.925rem;margin-bottom:1rem">Add active service in category "What We Offer" from admin panel.</p></div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <section class="section services">
    <div class="container">
      <div class="sec-header fi"><span class="label">Specialized Analysis</span><h2>Paranormal, Industrial & Agriculture Energy Analysis Services</h2><p>Focused diagnostic services for deeper energy, environment, business, and land related concerns.</p></div>
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:2rem">
        <?php if (!empty($specializedservice)): ?>
          <?php foreach ($specializedservice as $course): ?>
            <div class="srv-full fi">
              <div class="srv-icon" style="margin:0 auto 2rem">
                <?php if (!empty($course['image']) && file_exists(__DIR__ . '/admin/uploads/service/' . $course['image'])): ?>
                  <img src="./admin/uploads/service/<?php echo htmlspecialchars($course['image']); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" class="img-fluid d-block" style="max-width:110px;">
                <?php else: ?>
                  <img src="./logos/astro vastu.png" alt="<?php echo htmlspecialchars($course['title']); ?>" class="img-fluid d-block" style="max-width:80px;">
                <?php endif; ?>
              </div>
              <h3 style="color:var(--n9);margin-bottom:.75rem"><?php echo htmlspecialchars($course['title']); ?></h3>
              <p style="font-size:.925rem;margin-bottom:1rem"><?php echo htmlspecialchars($course['short_description']); ?></p>
              <?php
require_once __DIR__ . '/config/app_paths.php';
                $tags = array_filter(array_map('trim', explode(',', (string)($course['tags'] ?? ''))));
              ?>
              <?php if (!empty($tags)): ?>
                <div class="srv-tags">
                  <?php foreach ($tags as $tag): ?>
                    <span class="srv-tag"><?php echo htmlspecialchars($tag); ?></span>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="srv-full fi"><h3 style="color:var(--n9);margin-bottom:.75rem">No specialized services found</h3><p style="font-size:.925rem;margin-bottom:1rem">Add active service in category "Specialized Analysis" from admin panel.</p></div>
        <?php endif; ?>
      </div>
    </div>
  </section>




<?php
require_once __DIR__ . '/config/app_paths.php';
$workflowItems = $conn->query("SELECT image, title, description FROM workflow ORDER BY id ASC");
?>

<section class="section services">
  <div class="container">
    <div class="sec-header fi">
      <span class="label">How We Work</span>
      <h2>Our Workflow</h2>
      <p>A structured approach that ensures quality, transparency, and lasting results every time.</p>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1.5rem" id="workflowGrid">
      <?php if ($workflowItems && $workflowItems->num_rows > 0): ?>
        <?php $step = 1; ?>
        <?php while ($row = $workflowItems->fetch_assoc()): ?>
          <div class="srv-card fi" style="text-align:center;padding:2rem 1.5rem">
            
            <div class="srv-icon" style="margin:0 auto 1rem">
              <img 
                src="admin/uploads/workflow/<?php echo htmlspecialchars($row['image']); ?>" 
                alt="<?php echo htmlspecialchars($row['title']); ?>" 
                class="img-fluid mx-auto d-block" 
                style="max-width:110px;"
              >
            </div>

            <div style="font-size:.75rem;font-weight:700;color:var(--g6);letter-spacing:.08em;text-transform:uppercase;margin-bottom:.5rem">
              Step <?php echo str_pad($step, 2, '0', STR_PAD_LEFT); ?>
            </div>

            <h4 style="color:var(--n9);margin-bottom:.5rem">
              <?php echo htmlspecialchars($row['title']); ?>
            </h4>

            <p style="font-size:.875rem">
              <?php echo htmlspecialchars($row['description']); ?>
            </p>
          </div>
          <?php $step++; ?>
        <?php endwhile; ?>
      <?php else: ?>
        <p style="text-align:center;width:100%;">No workflow added yet.</p>
      <?php endif; ?>
    </div>
  </div>
</section>





  <!-- CTA -->
  <section class="cta-banner section-sm">
    <div class="container">
      <div class="cta-inner fi">
        <div class="cta-text"><h2>Ready to Improve Your Energy and Life?</h2><p>Book a scientific astro-vastu consultation and start your positive transformation.</p></div>
        <div class="cta-actions">
          <button class="btn btn-dark" onclick="showPage('contact')">Book Consultation</button>
          <button class="btn btn-outline" onclick="showPage('services')">Our Services</button>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer"><div class="container"><div class="footer-grid">
    <div class="footer-brand"><div class="nav-logo" onclick="showPage('home')" style="cursor:pointer"><div class="logo-icon"><img src="./logos/astro vastu.png" alt=""></div><span class="logo-text"><em>ChitraguptAstroVastu</em></span></div><p>Scientific Astro Vastu solutions for health, harmony, relationships, and success.</p><div class="social-links"><a class="social-a" href="https://www.facebook.com/ChitraguptAstroVaastu/" target="_blank"><i class="fab fa-facebook-f"></i></a><a class="social-a" href="https://www.instagram.com/chitraguptastrovastu/" target="_blank"><i class="fab fa-instagram"></i></a><a class="social-a" href="https://www.youtube.com/@chitraguptastrovastugeopat2656" target="_blank"><i class="fab fa-youtube"></i></a></div></div>
    <div class="footer-col"><h4>Company</h4><ul><li><a onclick="showPage('about')">About Us</a></li><li><a onclick="showPage('achievements')">Achievements</a></li><li><a onclick="showPage('gallery')">Gallery</a></li><li><a onclick="showPage('contact')">Contact</a></li></ul></div>
    <div class="footer-col"><h4>Services</h4><ul><li><a onclick="showPage('services')">Geopathic Stress</a></li><li><a onclick="showPage('services')">Grah Dosh</a></li><li><a onclick="showPage('services')">Distance Analysis</a></li><li><a onclick="showPage('services')">Gemstone Guidance</a></li></ul></div>
    <div class="footer-col"><h4>Contact</h4><ul><li><a href="mailto:chitraguptastrovastu@gmail.com">chitraguptastrovastu@gmail.com</a></li><li><a class="footer-whatsapp" href="https://wa.me/919929426997" target="_blank" rel="noopener noreferrer">+91-9929426997</a></li></ul></div>
  </div><div class="footer-bottom"><p>&copy; 2017-2026 Shree Chitragupt Institute Of Astro Studies and Research</p><p>Developed by <a class="footer-dev-link" href="https://euonusit.com/" target="_blank" rel="noopener noreferrer">EUONUS IT</a></p></div></div></footer>
</div>
<!-- ══════════════════════════════════════════════
     PAGE: ACHIEVEMENTS
══════════════════════════════════════════════ -->
<div class="page" id="page-achievements">
  <section class="page-hero"><div class="container page-hero-inner"><div class="breadcrumb"><a onclick="showPage('home')">Home</a><span>></span><span>Achievements</span></div><h1>Our Achievements</h1><p>Major recognitions, awards, and milestones of Shree Chitragupt Institute of Astro Studies and Research.</p></div></section>

  
  <section class="section" style="background:var(--s1)">
    <div class="container">
      <div class="sec-header fi"><span class="label">Our Journey</span><h2>Milestones Through the Years</h2><p>Key recognitions and achievements of the institute.</p></div>
      <div class="timeline">
        <div class="tl-item fi"><div class="tl-content"><div class="tl-year">2012</div><h4>Diploma in Astrology</h4><p>Diploma in Astrology from Indian Institute in Vedic Sciences (IIVSc.), Jaipur.</p></div><div class="tl-dot">🏅</div><div class="tl-empty"></div></div>
        <div class="tl-item fi"><div class="tl-empty"></div><div class="tl-dot">🏆</div><div class="tl-content"><div class="tl-year">2012</div><h4>All India Topper</h4><p>Got 1st rank in Astrological Research on the subject â€œVivah Jyotish ke sandarbh me Mangal Dosh ke prabhavon ka Jyotisheeya Adhdhyaaâ€.</p></div></div>
        <div class="tl-item fi"><div class="tl-content"><div class="tl-year">2014</div><h4>Jyotish Samman</h4><p>Honored on the occasion of Akhil Bhartiya Second Jyotish Mahasammelan.</p></div><div class="tl-dot">✨</div><div class="tl-empty"></div></div>
        <div class="tl-item fi"><div class="tl-empty"></div><div class="tl-dot">🤝</div><div class="tl-content"><div class="tl-year">2014</div><h4>Institute Established</h4><p>Established â€œShree Chitragupt Institute of Astro Studies and Researchâ€.</p></div></div>
        <div class="tl-item fi"><div class="tl-content"><div class="tl-year">2014</div><h4>Advance Remedial Expert Award</h4><p>Awarded by Future Spot for outstanding contribution in remedial astro sciences.</p></div><div class="tl-dot">⭐</div><div class="tl-empty"></div></div>
        <div class="tl-item fi"><div class="tl-empty"></div><div class="tl-dot">🌍</div><div class="tl-content"><div class="tl-year">2014</div><h4>Scientific Astro Vastu Expert & Healer</h4><p>Received recognition as Scientific Astro Vastu Expert and Healer.</p></div></div>
        <div class="tl-item fi"><div class="tl-content"><div class="tl-year">2014</div><h4>Vishishth Jyotish Samman</h4><p>Honored with Vishishth Jyotish Samman for excellence in astrology.</p></div><div class="tl-dot">📜</div><div class="tl-empty"></div></div>
        <div class="tl-item fi"><div class="tl-empty"></div><div class="tl-dot">🎖️</div><div class="tl-content"><div class="tl-year">2014</div><h4>Life Membership</h4><p>Life Membership awarded by International Astro Foundation.</p></div></div>
        <div class="tl-item fi"><div class="tl-content"><div class="tl-year">2015</div><h4>Star Astrologer Award</h4><p>Recognized with Star Astrologer Award 2015 with 5 Star Rating.</p></div><div class="tl-dot">🎓</div><div class="tl-empty"></div></div>
        <div class="tl-item fi"><div class="tl-empty"></div><div class="tl-dot">🥇</div><div class="tl-content"><div class="tl-year">2015</div><h4>Indian Achiever Award</h4><p>Honored at International Astro Seminar in June 2015.</p></div></div>
        <div class="tl-item fi"><div class="tl-content"><div class="tl-year">2015</div><h4>Abhivadan Patram</h4><p>Received Abhivadan Patram in June 2015 for dedicated service.</p></div><div class="tl-dot">🏅</div><div class="tl-empty"></div></div>
        <div class="tl-item fi"><div class="tl-empty"></div><div class="tl-dot">🏆</div><div class="tl-content"><div class="tl-year">2015</div><h4>Indo-Thai Achievers Award</h4><p>Received Indo-Thai Achievers Award at Bangkok on 21 December 2015.</p></div></div>
        <div class="tl-item fi"><div class="tl-content"><div class="tl-year">2016</div><h4>Honorary Doctorate</h4><p>Honorary Doctorate of Vastu Shastra - Geopathic Stress, awarded in June 2016.</p></div><div class="tl-dot">✨</div><div class="tl-empty"></div></div>
        <div class="tl-item fi"><div class="tl-empty"></div><div class="tl-dot">🤝</div><div class="tl-content"><div class="tl-year">2017</div><h4>Gold Medal Recognition</h4><p>Gold Medal for Research Work of Geopathic Stress on 01 January 2017.</p></div></div>
        <div class="tl-item fi"><div class="tl-empty"></div><div class="tl-dot">⭐</div><div class="tl-content"><div class="tl-year">2018</div><h4>10th Asiad literature festival (2018)</h4><p></p></div></div>
        <div class="tl-item fi"><div class="tl-empty"></div><div class="tl-dot">🌍</div><div class="tl-content"><div class="tl-year">2019</div><h4>Captain of Pyramid Vastu (2019)</h4><p></p></div></div>
        <div class="tl-item fi"><div class="tl-empty"></div><div class="tl-dot">📜</div><div class="tl-content"><div class="tl-year">2026</div><h4>Lifetime Achievement Award (2026)</h4><p></p></div></div>
      </div>
    </div>
  </section>
 
  <section class="section" style="background:var(--white)">
  <div class="container">
    <div class="sec-header fi">
      <span class="label">Recognition</span>
      <h2>Awards and Honors</h2>
      <p>Major recognitions and certifications received by the institute.</p>
    </div>

    <div class="award-grid">
      <?php
require_once __DIR__ . '/config/app_paths.php';
      $awards = $conn->query("SELECT title, image, tags FROM accolades ORDER BY id DESC");
      ?>

      <?php if ($awards && $awards->num_rows > 0): ?>
        <?php while ($award = $awards->fetch_assoc()): ?>
          <div class="award-card fi">
            <div class="award-icon">
              <?php if (!empty($award['image'])): ?>
                <img src="admin/uploads/accolades/<?php echo htmlspecialchars($award['image']); ?>" alt="<?php echo htmlspecialchars($award['title']); ?>">
              <?php endif; ?>
            </div>

            <p class="award-title">
              <?php echo htmlspecialchars($award['title']); ?>
            </p>

            <span class="award-year">
              <?php echo htmlspecialchars($award['tags']); ?>
            </span>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p>No awards found.</p>
      <?php endif; ?>
    </div>
  </div>
</section>
  
  <section class="section" style="background:var(--white)">
    <div class="container">
      <div class="sec-header fi"><span class="label">By the Numbers</span><h2>Impact at a Glance</h2><p>Measurable outcomes that demonstrate the scale and depth of our work.</p></div>
      <div class="ach-grid">
        <div class="ach-card fi"><div class="ach-num"><span data-count="1000">1000</span>+</div><div class="ach-lbl">Consultations</div></div>
        <div class="ach-card fi"><div class="ach-num"><span data-count="7">7</span>+</div><div class="ach-lbl">countries Served</div></div>
        <div class="ach-card fi"><div class="ach-num"><span data-count="48">48</span></div><div class="ach-lbl">Cities Served</div></div>
        <div class="ach-card fi"><div class="ach-num"><span data-count="25">25</span>+</div><div class="ach-lbl">Years of Service</div></div>
        <div class="ach-card fi"><div class="ach-num"><span data-count="120">120</span>+</div><div class="ach-lbl">Experts on Team</div></div>
        <div class="ach-card fi"><div class="ach-num"><span data-count="18">18</span></div><div class="ach-lbl">National Awards Won</div></div>
        <div class="ach-card fi"><div class="ach-num"><span data-count="98">98</span>%</div><div class="ach-lbl">Positive Outcomes Rate</div></div>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="cta-banner section-sm">
    <div class="container">
      <div class="cta-inner fi">
        <div class="cta-text"><h2>Ready to Improve Your Energy and Life?</h2><p>Book a scientific astro-vastu consultation and start your positive transformation.</p></div>
        <div class="cta-actions">
          <button class="btn btn-dark" onclick="showPage('contact')">Book Consultation</button>
          <button class="btn btn-outline" onclick="showPage('services')">Our Services </button>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer"><div class="container"><div class="footer-grid">
    <div class="footer-brand"><div class="nav-logo" onclick="showPage('home')" style="cursor:pointer"><div class="logo-icon"><img src="./logos/astro vastu.png" alt=""></div><span class="logo-text"><em>ChitraguptAstroVastu</em></span></div><p>Scientific Astro Vastu solutions for health, harmony, relationships, and success.</p><div class="social-links"><a class="social-a" href="https://www.facebook.com/ChitraguptAstroVaastu/" target="_blank"><i class="fab fa-facebook-f"></i></a><a class="social-a" href="https://www.instagram.com/chitraguptastrovastu/" target="_blank"><i class="fab fa-instagram"></i></a><a class="social-a" href="https://www.youtube.com/@chitraguptastrovastugeopat2656" target="_blank"><i class="fab fa-youtube"></i></a></div></div>
    <div class="footer-col"><h4>Company</h4><ul><li><a onclick="showPage('about')">About Us</a></li><li><a onclick="showPage('achievements')">Achievements</a></li><li><a onclick="showPage('gallery')">Gallery</a></li><li><a onclick="showPage('contact')">Contact</a></li></ul></div>
    <div class="footer-col"><h4>Services</h4><ul><li><a onclick="showPage('services')">Geopathic Stress</a></li><li><a onclick="showPage('services')">Grah Dosh</a></li><li><a onclick="showPage('services')">Distance Analysis</a></li><li><a onclick="showPage('services')">Gemstone Guidance</a></li></ul></div>
    <div class="footer-col"><h4>Contact</h4><ul><li><a href="mailto:chitraguptastrovastu@gmail.com">chitraguptastrovastu@gmail.com</a></li><li><a class="footer-whatsapp" href="https://wa.me/919929426997" target="_blank" rel="noopener noreferrer">+91-9929426997</a></li></ul></div>
  </div><div class="footer-bottom"><p>&copy; 2017-2026 Shree Chitragupt Institute Of Astro Studies and Research</p><p>Developed by <a class="footer-dev-link" href="https://euonusit.com/" target="_blank" rel="noopener noreferrer">EUONUS IT</a></p></div></div></footer>
</div>

<!-- ══════════════════════════════════════════════
     PAGE: GALLERY
══════════════════════════════════════════════ -->
<div class="page" id="page-gallery">
  <section class="page-hero">
    <div class="container page-hero-inner">
      <div class="breadcrumb"><a onclick="showPage('home')">Home</a><span>></span><span>Gallery</span></div>
      <h1>Gallery and Media</h1>
      <p>A visual journey through institute events, consultations, seminars, and awareness programs.</p>
    </div>
  </section>

  <section class="section" style="background:var(--s1)">
    <div class="container">
      <div class="sec-header fi">
        <span class="label">Media Library</span>
        <h2>Photos and Videos</h2>
        <p>Choose the media type you want to view.</p>
      </div>

      <div class="gallery-tabs fi" role="tablist" aria-label="Gallery media tabs">
        <button class="gallery-tab active" id="galleryTabPhotos" data-tab="photos" type="button" onclick="showGallerySection('photos')" role="tab" aria-controls="galleryPhotos"><i class="fa-solid fa-camera-retro"></i><span>Photos</span></button>
        <button class="gallery-tab" id="galleryTabVideos" data-tab="videos" type="button" onclick="showGallerySection('videos')" role="tab" aria-controls="galleryVideos"><i class="fa-solid fa-film"></i><span>Videos</span></button>
      </div>

     <div class="gallery-panel active" id="galleryPhotos" data-gallery-panel="photos">
  <div class="gal-grid">

    <?php
require_once __DIR__ . '/config/app_paths.php';
    $gallery = $conn->query("SELECT image, description FROM gallery ORDER BY id DESC");
    ?>

    <?php if ($gallery && $gallery->num_rows > 0): ?>
      <?php while ($row = $gallery->fetch_assoc()): ?>

        <?php
require_once __DIR__ . '/config/app_paths.php';
        $imagePath = 'admin/uploads/gallery/' . $row['image'];
        $description = $row['description'] ?? '';
        ?>

        <div class="gal-item fi"
             onclick="openLightbox(
               '',
               '<?php echo htmlspecialchars($description, ENT_QUOTES); ?>',
               'linear-gradient(135deg,#0f2040,#C49A3C)',
               '<?php echo htmlspecialchars($imagePath, ENT_QUOTES); ?>'
             )">

          <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Gallery Image">

          <div class="gal-overlay">
            <?php echo htmlspecialchars($description); ?>
          </div>

        </div>

      <?php endwhile; ?>
    <?php else: ?>
      <p>No gallery images found.</p>
    <?php endif; ?>

  </div>
</div>




      <div class="gallery-panel" id="galleryVideos" data-gallery-panel="videos">
        <div class="gallery-video-grid">
        <?php
require_once __DIR__ . '/config/app_paths.php';
$videos = $conn->query("SELECT video, description FROM video ORDER BY id DESC");
?>

<?php if ($videos && $videos->num_rows > 0): ?>
  <?php while ($row = $videos->fetch_assoc()): ?>

    <?php
require_once __DIR__ . '/config/app_paths.php';
    $videoPath = 'admin/uploads/video/' . $row['video'];
    $description = $row['description'] ?? '';
    ?>

    <article class="gallery-video-card fi">
      <div class="gallery-video-thumb local-video"
           onclick="openVideoLightbox('<?php echo htmlspecialchars($videoPath, ENT_QUOTES); ?>', '')"
           role="button"
           tabindex="0"
           aria-label="Play local video">

        <video class="local-video-player" preload="metadata">
          <source src="<?php echo htmlspecialchars($videoPath); ?>" type="video/mp4">
        </video>

        <span class="video-play">▶</span>
      </div>

      <div class="gallery-video-body">
        <h3><?php echo htmlspecialchars($description); ?></h3>

       

        <div class="gallery-video-actions">
          <button class="btn btn-dark btn-sm"
                  onclick="downloadVideo('<?php echo htmlspecialchars($videoPath, ENT_QUOTES); ?>')">
            Download
          </button>
        </div>
      </div>
    </article>

  <?php endwhile; ?>
<?php else: ?>
  <p>No videos found.</p>
<?php endif; ?>
<!-- 
          <article class="gallery-video-card fi">
            <div class="gallery-video-thumb home-intro-youtube" onclick="playGalleryVideo(this)" role="button" tabindex="0" aria-label="Play Geopathic Stress video" data-video-id="h1hT3Q7RDPY">
              <img src="https://www.youtube.com/@chitraguptastrovastugeopat2656" alt="Geopathic Stress video thumbnail">
              <span class="video-play">▶</span>
            </div>
            <div class="gallery-video-body">
              <h3>Understanding Geopathic Stress</h3>
              <p>Scientific explanation of geopathic stress and its impact on human health and well-being fixed.</p>
              <div class="gallery-video-actions">
                <a class="btn btn-dark btn-sm" href="https://www.youtube.com/@chitraguptastrovastugeopat2656" target="_blank" rel="noopener noreferrer">Open Video</a>
              </div>
            </div>
          </article>

          <article class="gallery-video-card fi">
            <div class="gallery-video-thumb home-intro-youtube" onclick="playGalleryVideo(this)" role="button" tabindex="0" aria-label="Play Vastu consultation video" data-video-id="KvQkYp5Tz-g">
              <img src="https://img.youtube.com/vi/KvQkYp5Tz-g/maxresdefault.jpg" alt="Vastu consultation video thumbnail">
              <span class="video-play">▶</span>
            </div>
            <div class="gallery-video-body">
              <h3>Vastu Consultation Process</h3>
              <p>Learn about our comprehensive Vastu consultation and analysis methodology for homes and offices.</p>
              <div class="gallery-video-actions">
                <a class="btn btn-dark btn-sm" href="https://youtu.be/KvQkYp5Tz-g" target="_blank" rel="noopener noreferrer">Open Video</a>
              </div>
            </div>
          </article>

          <article class="gallery-video-card fi">
            <div class="gallery-video-thumb home-intro-youtube" onclick="playGalleryVideo(this)" role="button" tabindex="0" aria-label="Play astro vastu seminar video" data-video-id="x8R0QJxKzWI">
              <img src="https://img.youtube.com/vi/x8R0QJxKzWI/maxresdefault.jpg" alt="Astro Vastu seminar video thumbnail">
              <span class="video-play">▶</span>
            </div>
            <div class="gallery-video-body">
              <h3>National Astro Vastu Seminar</h3>
              <p>Highlights from the annual seminar featuring expert talks and case studies from across India.</p>
              <div class="gallery-video-actions">
                <a class="btn btn-dark btn-sm" href="https://youtu.be/x8R0QJxKzWI" target="_blank" rel="noopener noreferrer">Open Video</a>
              </div>
            </div>
          </article> -->

       <article class="gallery-video-card fi">
            <div class="gallery-video-thumb home-intro-youtube" onclick="window.open('https://www.youtube.com/@chitraguptastrovastugeopat2656','_blank')" role="button" tabindex="0" aria-label="Open YouTube channel">
              <img src="./images/youtube.png" alt="ChitraguptAstroVastu YouTube channel">
              <span class="video-play">▶</span>
            </div>
            <div class="gallery-video-body">
              <h3>Channel Library</h3>
              <p>Explore more seminar, event, and consultation clips on the official channel.</p>
              <div class="gallery-video-actions">
                <a class="btn btn-primary btn-sm" href="https://www.youtube.com/@chitraguptastrovastugeopat2656" target="_blank" rel="noopener noreferrer">Visit Channel</a>
              </div>
            </div>
          </article>
        </div>
      </div>
    </div>
  </section>
  

  <!-- <section class="section" style="background:var(--white)">
    <div class="container">
      <div class="sec-header fi"><span class="label">Events & Training</span><h2>Workshops, Conferences & Community Programs</h2><p>Empowering people is at the heart of what we do — here's what it looks like in practice.</p></div>
      <div class="gal-grid">
        <div class="gal-item fi" style="background:linear-gradient(135deg,#8B662D,#0a1628)" onclick="openLightbox('🎓','Environmental Law Workshop — Delhi, 2023','linear-gradient(135deg,#8B662D,#0a1628)')">🎓<div class="gal-overlay">Environmental Law Workshop — Delhi, 2023</div></div>
        <div class="gal-item fi" style="background:linear-gradient(135deg,#C49A3C,#162d55)" onclick="openLightbox('🎤','National Astro Conference, 2022','linear-gradient(135deg,#C49A3C,#162d55)')">🎤<div class="gal-overlay">National Astro Conference, 2022</div></div>
        <div class="gal-item fi" style="background:linear-gradient(135deg,#E8D5B7,#0a1628)" onclick="openLightbox('👥','Village Capacity Building — 200 Participants','linear-gradient(135deg,#E8D5B7,#0a1628)')">👥<div class="gal-overlay">Village Capacity Building — 200 Participants</div></div>
        <div class="gal-item fi" style="background:linear-gradient(135deg,#A67C35,#0f2040)" onclick="openLightbox('📊','Research Symposium — Jaipur, 2023','linear-gradient(135deg,#A67C35,#0f2040)')">📊<div class="gal-overlay">Research Symposium — Jaipur, 2023</div></div>
        <div class="gal-item fi" style="background:linear-gradient(135deg,#C49A3C,#1e3a6e)" onclick="openLightbox('🧪','Soil Health Training — Farmers Field School','linear-gradient(135deg,#C49A3C,#1e3a6e)')">🧪<div class="gal-overlay">Soil Health Training — Farmers Field School</div></div>
        <div class="gal-item fi" style="background:linear-gradient(135deg,#8B662D,#162d55)" onclick="openLightbox('🤝','Government Partnership Launch — 2021','linear-gradient(135deg,#8B662D,#162d55)')">🤝<div class="gal-overlay">Government Partnership Launch — 2021</div></div>
      </div>
    </div>
  </section> -->

  <!-- CTA -->
  <section class="cta-banner section-sm">
    <div class="container">
      <div class="cta-inner fi">
        <div class="cta-text"><h2>Ready to Improve Your Energy and Life?</h2><p>Book a scientific astro-vastu consultation and start your positive transformation.</p></div>
        <div class="cta-actions">
          <button class="btn btn-dark" onclick="showPage('contact')">Book Consultation</button>
          <button class="btn btn-outline" onclick="showPage('services')">Our Services</button>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer"><div class="container"><div class="footer-grid">
    <div class="footer-brand"><div class="nav-logo" onclick="showPage('home')" style="cursor:pointer"><div class="logo-icon"><img src="./logos/astro vastu.png" alt=""></div><span class="logo-text"><em>ChitraguptAstroVastu</em></span></div><p>Scientific Astro Vastu solutions for health, harmony, relationships, and success.</p><div class="social-links"><a class="social-a" href="https://www.facebook.com/ChitraguptAstroVaastu/" target="_blank"><i class="fab fa-facebook-f"></i></a><a class="social-a" href="https://www.instagram.com/chitraguptastrovastu/" target="_blank"><i class="fab fa-instagram"></i></a><a class="social-a" href="https://www.youtube.com/@chitraguptastrovastugeopat2656" target="_blank"><i class="fab fa-youtube"></i></a></div></div>
    <div class="footer-col"><h4>Company</h4><ul><li><a onclick="showPage('about')">About Us</a></li><li><a onclick="showPage('achievements')">Achievements</a></li><li><a onclick="showPage('gallery')">Gallery</a></li><li><a onclick="showPage('contact')">Contact</a></li></ul></div>
    <div class="footer-col"><h4>Services</h4><ul><li><a onclick="showPage('services')">Geopathic Stress</a></li><li><a onclick="showPage('services')">Grah Dosh</a></li><li><a onclick="showPage('services')">Distance Analysis</a></li><li><a onclick="showPage('services')">Gemstone Guidance</a></li></ul></div>
    <div class="footer-col"><h4>Contact</h4><ul><li><a href="mailto:chitraguptastrovastu@gmail.com">chitraguptastrovastu@gmail.com</a></li><li><a class="footer-whatsapp" href="https://wa.me/919929426997" target="_blank" rel="noopener noreferrer">+91-9929426997</a></li></ul></div>
  </div><div class="footer-bottom"><p>&copy; 2017-2026 Shree Chitragupt Institute Of Astro Studies and Research</p><p>Developed by <a class="footer-dev-link" href="https://euonusit.com/" target="_blank" rel="noopener noreferrer">EUONUS IT</a></p></div></div></footer>
</div>

<!-- ══════════════════════════════════════════════
     PAGE: BOOKS
══════════════════════════════════════════════ -->
<div class="page" id="page-books">
  <section class="page-hero"><div class="container page-hero-inner"><div class="breadcrumb"><a onclick="showPage('home')">Home</a><span>></span><span>Books</span></div><h1>Our Publications</h1><p>Comprehensive books on astro-vastu, geopathic stress, and scientific guidance for better living.</p></div></section>

  <section class="section" style="background:var(--white)">
    <div class="container">
      <div class="sec-header fi"><span class="label">Knowledge Base</span><h2>Featured Book</h2><p>Detailed resources on astrology, Vastu Shastra, geopathic stress analysis, and remedial measures.</p></div>
      <div class="book-card fi">
        <div class="book-cover">
          <img src="./images/book.png" alt="Geopathic Stress: The Hidden Threat cover">
        </div>
        <div class="book-content">
          <h3>Geopathic Stress: The Hidden Threat</h3>
          <p>The cover page of my book effectively illustrates the impact of “Geopathic Stress.” The red and yellow energy lines shown beneath the house represent harmful radiation emerging from the Earth. This can influence the health and behavior of people living above. The inclusion of a compass and Vastu tools suggests that this invisible phenomenon can be identified and addressed through both scientific and Vastu perspectives. Overall, the image highlights a research-based solution.</p>
          <p>The globe and map on the cover indicate that geopathic stress is a global issue, not limited to any one location. The flowing water layers beneath the ground symbolize underground water streams, which are often a primary cause of this stress. Additionally, the distressed figures in the image reflect how these energies can affect mental peace and interpersonal relationships. Through this book, I aim to present a blend of in-depth research and modern solutions. How successful this effort has been, I leave it to the judgment of readers and fellow researchers.</p>
          <div class="srv-tags"><span class="srv-tag">Geopathic Stress</span><span class="srv-tag">Detection Methods</span><span class="srv-tag">Remedies</span><span class="srv-tag">Case Studies</span></div>
        </div>
      </div>
    </div>
  </section>

  <section class="section" style="background:var(--s1)">
    <div class="container">
      <div class="sec-header fi"><span class="label">Why Our Books?</span><h2>Benefits of This Publication</h2><p>Combining ancient wisdom with modern scientific research for practical applications.</p></div>
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1.5rem" id="workflowGrid">
        <div class="srv-card fi" style="text-align:center"><div class="srv-icon" style="margin:0 auto 1rem">✍️</div><h4 style="color:var(--n9);margin-bottom:.75rem">Expert Research</h4><p style="font-size:.875rem">Authored by Dr. Rajesh Srivastav, a doctorate in Vastu Shastra with over 25 years of research experience.</p></div>
        <div class="srv-card fi" style="text-align:center"><div class="srv-icon" style="margin:0 auto 1rem">🔬</div><h4 style="color:var(--n9);margin-bottom:.75rem">Scientific Approach</h4><p style="font-size:.875rem">A blend of traditional knowledge and modern scientific analysis for practical, measurable results.</p></div>
        <div class="srv-card fi" style="text-align:center"><div class="srv-icon" style="margin:0 auto 1rem">📚</div><h4 style="color:var(--n9);margin-bottom:.75rem">Practical Guidance</h4><p style="font-size:.875rem">Real-world case studies, step-by-step remedies, and actionable solutions you can implement immediately.</p></div>
        <div class="srv-card fi" style="text-align:center"><div class="srv-icon" style="margin:0 auto 1rem">💡</div><h4 style="color:var(--n9);margin-bottom:.75rem">Easy to Understand</h4><p style="font-size:.875rem">Complex concepts explained in simple language, with illustrations, charts, and practical examples.</p></div>
      </div>
    </div>
  </section>
  

  <!-- CTA -->
  <section class="cta-banner section-sm">
    <div class="container">
      <div class="cta-inner fi">
        <div class="cta-text"><h2>Ready to Improve Your Energy and Life?</h2><p>Book a scientific astro-vastu consultation and start your positive transformation.</p></div>
        <div class="cta-actions">
          <button class="btn btn-dark" onclick="showPage('contact')">Book Consultation</button>
          <button class="btn btn-outline" onclick="showPage('services')">Our Services </button>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer"><div class="container"><div class="footer-grid">
    <div class="footer-brand"><div class="nav-logo" onclick="showPage('home')" style="cursor:pointer"><div class="logo-icon"><img src="./logos/astro vastu.png" alt=""></div><span class="logo-text"><em>ChitraguptAstroVastu</em></span></div><p>Scientific Astro Vastu solutions for health, harmony, relationships, and success.</p><div class="social-links"><a class="social-a" href="https://www.facebook.com/ChitraguptAstroVaastu/" target="_blank"><i class="fab fa-facebook-f"></i></a><a class="social-a" href="https://www.instagram.com/chitraguptastrovastu/" target="_blank"><i class="fab fa-instagram"></i></a><a class="social-a" href="https://www.youtube.com/@chitraguptastrovastugeopat2656" target="_blank"><i class="fab fa-youtube"></i></a></div></div>
    <div class="footer-col"><h4>Company</h4><ul><li><a onclick="showPage('about')">About Us</a></li><li><a onclick="showPage('achievements')">Achievements</a></li><li><a onclick="showPage('gallery')">Gallery</a></li><li><a onclick="showPage('contact')">Contact</a></li></ul></div>
    <div class="footer-col"><h4>Services</h4><ul><li><a onclick="showPage('services')">Geopathic Stress</a></li><li><a onclick="showPage('services')">Grah Dosh</a></li><li><a onclick="showPage('services')">Distance Analysis</a></li><li><a onclick="showPage('services')">Gemstone Guidance</a></li></ul></div>
    <div class="footer-col"><h4>Contact</h4><ul><li><a href="mailto:chitraguptastrovastu@gmail.com">chitraguptastrovastu@gmail.com</a></li><li><a class="footer-whatsapp" href="https://wa.me/919929426997" target="_blank" rel="noopener noreferrer">+91-9929426997</a></li></ul></div>
  </div><div class="footer-bottom"><p>&copy; 2017-2026 Shree Chitragupt Institute Of Astro Studies and Research</p><p>Developed by <a class="footer-dev-link" href="https://euonusit.com/" target="_blank" rel="noopener noreferrer">EUONUS IT</a></p></div></div></footer>
</div>

<!-- Floating WhatsApp Button -->
<a href="https://wa.me/919929426997" 
   class="floating-whatsapp" 
   target="_blank">
   
   <img src="https://cdn-icons-png.flaticon.com/512/733/733585.png" 
        alt="WhatsApp">
</a>

<style>
.floating-whatsapp{
    position: fixed;
    bottom: 150px;
    right: 20px;
    width: 43px;
    height: 43px;
    z-index: 9999;
    padding:9px;
    border-radius: 50%;
    overflow: hidden;
    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    animation: pulse 1.5s infinite;
}

.floating-whatsapp img{
    width: 100%;
    height: 100%;
    object-fit: cover;
}

@keyframes pulse{
    0%{
        transform: scale(1);
    }
    50%{
        transform: scale(1.1);
    }
    100%{
        transform: scale(1);
    }
}
</style>

<!-- ══════════════════════════════════════════════
     PAGE: CONTACT
══════════════════════════════════════════════ -->
<div class="page" id="page-contact">
  <section class="page-hero"><div class="container page-hero-inner"><div class="breadcrumb"><a onclick="showPage('home')">Home</a><span>></span><span>Contact</span></div><h1>Contact Us</h1><p>Contact Shree Chitragupt Institute of Astro Studies and Research for consultation and guidance.</p></div></section>

  <section class="section" style="background:var(--white)">
    <div class="container">
      <div class="contact-grid">
        <div class="fi">
          <span class="label">Contact Details</span>
          <h3>Main and Jaipur Office</h3>
          <p style="margin:1rem 0 1.5rem;font-size:.925rem">Reach us by call or WhatsApp and share your concern for guided remedies.</p>
          <div class="ci-items">
            <div class="ci-item"><div class="ci-icon"><i class="fas fa-location-dot"></i></div><div><h4>Main Office</h4><p>3/132, "Green House"<br>Housing Board Colony, Sawai Madhopur (Raj.) - 322021</p></div></div>
            <div class="ci-item"><div class="ci-icon"><i class="fas fa-phone"></i></div><div><h4>Phone</h4><p><a href="tel:+919929426997" style="color:inherit;text-decoration:none">+91-9929426997</a></p></div></div>
          </div>
          <div><h4 style="font-size:.85rem;font-weight:700;color:var(--n8);text-transform:uppercase;letter-spacing:.08em;margin-bottom:1rem">Follow Us</h4><div class="social-links" style="gap:1rem"><a class="social-a" href="https://www.facebook.com/ChitraguptAstroVaastu/" target="_blank" rel="noopener noreferrer" style="background:#1877f2;color:white"><i class="fab fa-facebook-f"></i></a><a class="social-a" href="https://www.instagram.com/chitraguptastrovastu/" target="_blank" rel="noopener noreferrer" style="background:linear-gradient(135deg,#f58529 0%,#dd2a7b 50%,#8134af 100%);color:white"><i class="fab fa-instagram"></i></a><a class="social-a" href="https://www.youtube.com/@chitraguptastrovastugeopat2656" target="_blank" rel="noopener noreferrer" style="background:#ff0000;color:white"><i class="fab fa-youtube"></i></a></div></div>
          <div class="map-box">
            <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d57377.876621341995!2d76.2829663!3d25.996975!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x396e25d6bc5429c1%3A0x39e45c3b3b997da9!2sShree%20Chitragupt%20Institute%20Of%20Astro%20Studies%20and%20Research!5e0!3m2!1sen!2sin!4v1777019580827!5m2!1sen!2sin" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
          </div>
        </div>

        <div class="form-wrap fi">
          <h3>Send Your Message</h3>
          <p>Share your details and our team will contact you soon.</p>
          <form id="contactForm" novalidate>
            <div class="form-row">
              <div class="form-group"><label>Full Name *</label><input name="name" type="text" id="c-name" placeholder="e.g. Priya Sharma" /><div class="f-err">Please enter your full name.</div></div>
              <div class="form-group"><label>Email Address *</label><input name="email" type="email" id="c-email" placeholder="e.g. priya@example.com" /><div class="f-err">Please enter a valid email.</div></div>
            </div>
            <div class="form-row">
              <div class="form-group"><label>Phone Number</label><input name="phone" type="tel" id="c-phone" placeholder="+91-8619129494" /></div>
              <div class="form-group"><label>Service Interested In</label><select name="service" id="c-service"><option value="">Select a service...</option><option>Analysis of Geopathic Stress</option><option>Grah Dosh Analysis</option><option>Distance Analysis</option><option>Gemstone Recommendation</option><option>Detecting Negative Impacts</option><option>Chakra Analysis and Healing</option><option>Yoga Analysis</option><option>Aura Scanner Guidance</option><option>Paranormal Energy & Dosh Analysis</option><option>Kaal Sarp Dosh Analysis</option><option>Pitr Dosh Analysis</option><option>Pishach Dosh Analysis</option><option>Mangal Dosh Analysis</option><option>Planetary Energy Imbalance Detection</option><option>Marriage Compatibility Energy Matching</option><option>Negative Energy Detection (Home / Office)</option><option>Factory Energy & Vastu Analysis</	option><Option>Agriculture Land Energy Analysis</Option><Option>Spiritual Protection Guidance</Option><Option>Business & Career Blockage Analysis</Option></select></div>
            </div>
            <div class="form-group"><label>Subject *</label><input name="subject" type="text" id="c-subject" placeholder="e.g. Consultation Request" /><div class="f-err">Please enter a subject.</div></div>
            <div class="form-group"><label>Message *</label><textarea name="message" id="c-message" placeholder="Write your concern or question..."></textarea><div class="f-err">Please write your message.</div></div>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Send Message</button>
            <div id="formSuccess" class="f-ok"></div>
            <div id="formError" class="f-err" style="margin-top:10px;"></div>
          </form>
        </div>
      </div>
    </div>
  </section>


  <!-- CTA -->
  <section class="cta-banner section-sm">
    <div class="container">
      <div class="cta-inner fi">
        <div class="cta-text"><h2>Ready to Improve Your Energy and Life?</h2><p>Book a scientific astro-vastu consultation and start your positive transformation.</p></div>
        <div class="cta-actions">
          <button class="btn btn-dark" onclick="showPage('contact')">Book Consultation</button>
          <button class="btn btn-outline" onclick="showPage('services')">Our Services </button>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer"><div class="container"><div class="footer-grid">
    <div class="footer-brand"><div class="nav-logo" onclick="showPage('home')" style="cursor:pointer"><div class="logo-icon"><img src="./logos/astro vastu.png" alt=""></div><span class="logo-text"><em>ChitraguptAstroVastu</em></span></div><p>Scientific Astro Vastu solutions for health, harmony, relationships, and success.</p><div class="social-links"><a class="social-a" href="https://www.facebook.com/ChitraguptAstroVaastu/" target="_blank"><i class="fab fa-facebook-f"></i></a><a class="social-a" href="https://www.instagram.com/chitraguptastrovastu/" target="_blank"><i class="fab fa-instagram"></i></a><a class="social-a" href="https://www.youtube.com/@chitraguptastrovastugeopat2656" target="_blank"><i class="fab fa-youtube"></i></a></div></div>
    <div class="footer-col"><h4>Company</h4><ul><li><a onclick="showPage('about')">About Us</a></li><li><a onclick="showPage('achievements')">Achievements</a></li><li><a onclick="showPage('gallery')">Gallery</a></li><li><a onclick="showPage('contact')">Contact</a></li></ul></div>
    <div class="footer-col"><h4>Services</h4><ul><li><a onclick="showPage('services')">Geopathic Stress</a></li><li><a onclick="showPage('services')">Grah Dosh</a></li><li><a onclick="showPage('services')">Distance Analysis</a></li><li><a onclick="showPage('services')">Gemstone Guidance</a></li></ul></div>
    <div class="footer-col"><h4>Contact</h4><ul><li><a href="mailto:chitraguptastrovastu@gmail.com">chitraguptastrovastu@gmail.com</a></li><li><a class="footer-whatsapp" href="https://wa.me/919929426997" target="_blank" rel="noopener noreferrer">+91-9929426997</a></li></ul></div>
  </div><div class="footer-bottom"><p>&copy; 2017-2026 Shree Chitragupt Institute Of Astro Studies and Research</p><p>Developed by <a class="footer-dev-link" href="https://euonusit.com/" target="_blank" rel="noopener noreferrer">EUONUS IT</a></p></div></div></footer>
</div>

<!-- ══ LIGHTBOX ══ -->
<div id="lightbox" onclick="if(event.target===this)closeLightbox()">
  <div id="lb-box">
    <button id="lb-close" onclick="closeLightbox()">✕</button>
    <span id="lb-emoji" style="font-size:5rem"></span>
  </div>
</div>

<!-- ══ VIDEO LIGHTBOX ══ -->
<div id="videoLightbox" onclick="if(event.target===this)closeVideoLightbox()">
  <div id="vlb-box">
    <button id="vlb-close" onclick="closeVideoLightbox()">✕</button>
    <video id="vlb-video" controls preload="metadata">
      <source id="vlb-source" src="" type="video/mp4">
      Your browser does not support the video tag.
    </video>
  </div>
</div>

<!-- ══ CHAT BOT ══ -->
<!-- <div class="chatbot" id="chatbot">
  <div class="chat-window" role="dialog" aria-labelledby="chatTitle" aria-hidden="true">
    <div class="chat-head">
      <div class="chat-brand">
        <div class="chat-avatar"><i class="fa-solid fa-hands-praying"></i></div>
        <div>
          <div class="chat-title" id="chatTitle">Office Assistant</div>
          <div class="chat-status">Usually replies in a minute</div>
        </div>
      </div>
      <button class="chat-close" type="button" onclick="toggleChat(false)" aria-label="Close chat"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="chat-messages" id="chatMessages" aria-live="polite"></div>
    <div class="chat-quick" id="chatQuick">
      <button class="chat-chip" type="button" data-msg="I want to book a consultation">Book Consultation</button>
      <button class="chat-chip" type="button" data-msg="Tell me about your services">Services</button>
      <button class="chat-chip" type="button" data-msg="What are your contact details?">Contact Details</button>
    </div>
    <form class="chat-form" id="chatForm">
      <input class="chat-input" id="chatInput" type="text" placeholder="Type your question..." autocomplete="off" />
      <button class="chat-send" type="submit" aria-label="Send message"><i class="fa-solid fa-paper-plane"></i></button>
    </form>
  </div>
  <button class="chat-launcher" type="button" onclick="toggleChat()" aria-label="Open chat">
    <i class="fa-solid fa-comments"></i>
    <span class="chat-badge"></span>
  </button>
</div> -->

<!-- ══════════════════════════════════════════════
     JAVASCRIPT
══════════════════════════════════════════════ -->
<script>
  /* ── Page Navigation ── */
  function showPage(name, section = '') {
    document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
    const pg = document.getElementById('page-' + name);
    if (pg) {
      pg.classList.add('active');
      window.location.hash = section ? `${name}-${section}` : name;
      window.scrollTo({ top: 0, behavior: 'smooth' });
      // Update nav active state
      document.querySelectorAll('.nav-links a').forEach(a => {
        a.classList.toggle('active', a.dataset.page === name);
      });
      document.querySelectorAll('.nav-dropdown').forEach(item => {
        item.classList.toggle('active', name === 'gallery');
      });
      closeMobileNav();
      if (name === 'gallery') {
        setTimeout(() => showGallerySection(section || 'photos', false), 0);
      }
      // Re-run observers for new page
      setTimeout(initObserver, 80);
      // Re-run counters
      setTimeout(initCounters, 120);
      setTimeout(updateChatbotVisibility, 80);
    }
  }

  /* ── Load page from URL hash ── */
  function loadPageFromHash() {
    const hash = window.location.hash.slice(1) || 'home';
    const [pageName, sectionName] = hash.split('-');
    const pg = document.getElementById('page-' + pageName);
    if (pg) {
      showPage(pageName, sectionName || '');
    } else {
      showPage('home');
    }
  }

  /* ── Handle hash changes ── */
  window.addEventListener('hashchange', loadPageFromHash);

  /* ── Navbar scroll ── */
  const navbar = document.getElementById('navbar');
  function updateNavbarScrollState() {
    navbar.classList.toggle('scrolled', window.scrollY > 12);
  }
  window.addEventListener('scroll', () => {
    updateNavbarScrollState();
    updateChatbotVisibility();
  }, { passive: true });
  updateNavbarScrollState();

  /* ── Mobile Menu ── */
  const hamburger = document.getElementById('hamburger');
  const mobileNav = document.getElementById('mobileNav');
  hamburger.addEventListener('click', () => {
    const open = mobileNav.classList.toggle('open');
    hamburger.classList.toggle('open', open);
    document.body.style.overflow = open ? 'hidden' : '';
  });
  function closeMobileNav() {
    mobileNav.classList.remove('open');
    hamburger.classList.remove('open');
    document.body.style.overflow = '';
  }

  /* ── Gallery Tabs ── */
  function showGallerySection(section = 'photos', updateHash = true) {
    const normalized = section === 'videos' ? 'videos' : 'photos';
    const photosPanel = document.getElementById('galleryPhotos');
    const videosPanel = document.getElementById('galleryVideos');
    const photosTab = document.getElementById('galleryTabPhotos');
    const videosTab = document.getElementById('galleryTabVideos');

    if (photosPanel && videosPanel && photosTab && videosTab) {
      photosPanel.classList.toggle('active', normalized === 'photos');
      videosPanel.classList.toggle('active', normalized === 'videos');
      photosTab.classList.toggle('active', normalized === 'photos');
      videosTab.classList.toggle('active', normalized === 'videos');

      if (updateHash) {
        window.location.hash = `gallery-${normalized}`;
      }
      setTimeout(initObserver, 60);
    }
  }

  function updateChatbotVisibility() {
    if (!chatbot) return;
    const currentPage = window.location.hash.slice(1) || 'home';
    const hero = document.querySelector('#page-home .hero');
    const onHomeHero = currentPage === 'home' && hero && window.scrollY < (hero.offsetHeight - 110);
    chatbot.classList.toggle('hero-hidden', !!onHomeHero);
    if (onHomeHero) toggleChat(false);
  }

  /* ── Scroll Fade-In ── */
  function initObserver() {
    const els = document.querySelectorAll('.page.active .fi:not(.vis)');
    if (!('IntersectionObserver' in window)) {
      els.forEach(el => el.classList.add('vis'));
      return;
    }
    const io = new IntersectionObserver((entries) => {
      entries.forEach((e, i) => {
        if (e.isIntersecting) {
          const delay = Number(e.target.dataset.delay || 0);
          setTimeout(() => e.target.classList.add('vis'), delay);
          io.unobserve(e.target);
        }
      });
    }, { threshold: 0.1 });

    // Stagger grid children
    document.querySelectorAll('.page.active .srv-grid, .page.active .test-grid, .page.active .ach-grid, .page.active .gal-grid, .page.active .gallery-video-grid').forEach(grid => {
      [...grid.children].forEach((child, i) => {
        if (!child.classList.contains('fi')) {
          child.classList.add('fi');
        }
        child.dataset.delay = i * 70;
      });
    });

    document.querySelectorAll('.page.active .fi:not(.vis)').forEach(el => io.observe(el));
  }

  /* ── Animated Counters ── */
  const counted = new WeakSet();
  function initCounters() {
    const els = document.querySelectorAll('.page.active [data-count]');
    if (!('IntersectionObserver' in window)) {
      els.forEach(el => { el.textContent = el.dataset.count; });
      return;
    }
    const io2 = new IntersectionObserver((entries) => {
      entries.forEach(e => {
        if (!e.isIntersecting || counted.has(e.target)) return;
        counted.add(e.target);
        const end = parseInt(e.target.dataset.count, 10);
        let cur = 0;
        const inc = end / (1800 / 16);
        const tick = setInterval(() => {
          cur = Math.min(cur + inc, end);
          e.target.textContent = Math.floor(cur);
          if (cur >= end) clearInterval(tick);
        }, 16);
        io2.unobserve(e.target);
      });
    }, { threshold: 0.5 });
    els.forEach(el => io2.observe(el));
  }

  /* ── Contact Form ── */
  // Load saved form data from localStorage
  function loadFormData() {
    const saved = localStorage.getItem('contactFormData');
    if (saved) {
      const data = JSON.parse(saved);
      if (data.name) document.getElementById('c-name').value = data.name;
      if (data.email) document.getElementById('c-email').value = data.email;
      if (data.phone) document.getElementById('c-phone').value = data.phone;
      if (data.service) document.getElementById('c-service').value = data.service;
      if (data.subject) document.getElementById('c-subject').value = data.subject;
      if (data.message) document.getElementById('c-message').value = data.message;
    }
  }
  
  // Save form data to localStorage
  function saveFormData() {
    const data = {
      name: document.getElementById('c-name').value,
      email: document.getElementById('c-email').value,
      phone: document.getElementById('c-phone').value,
      service: document.getElementById('c-service').value,
      subject: document.getElementById('c-subject').value,
      message: document.getElementById('c-message').value,
    };
    localStorage.setItem('contactFormData', JSON.stringify(data));
  }
  
  // Load data when page loads
  loadFormData();
  
  // Save form data on every input change
  ['c-name', 'c-email', 'c-phone', 'c-service', 'c-subject', 'c-message'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('input', saveFormData);
    if (el) el.addEventListener('change', saveFormData);
  });
  
  document.addEventListener('submit', async (e) => {
    if (e.target.id !== 'contactForm') return;
    e.preventDefault();

    const form = e.target;
    const fields = [
      { el: document.getElementById('c-name'), check: v => v.trim().length > 0 },
      { el: document.getElementById('c-email'), check: v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim()) },
      { el: document.getElementById('c-subject'), check: v => v.trim().length > 0 },
      { el: document.getElementById('c-message'), check: v => v.trim().length > 0 },
    ];

    let valid = true;
    fields.forEach(f => {
      const ok = f.check(f.el.value || '');
      f.el.classList.toggle('err', !ok);
      const errEl = f.el.nextElementSibling;
      if (errEl && errEl.classList.contains('f-err')) errEl.classList.toggle('show', !ok);
      if (!ok) valid = false;
    });

    const successBox = document.getElementById('formSuccess');
    const errorBox = document.getElementById('formError');
    successBox.textContent = '';
    errorBox.textContent = '';
    successBox.classList.remove('show');
    errorBox.classList.remove('show');

    if (!valid) return;

    const btn = form.querySelector('[type="submit"]');
    const oldText = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Sending...';

    try {
      const formData = new FormData(form);
      const res = await fetch('submit-contact.php', { method: 'POST', body: formData });
      const text = await res.text();
      let data;
      try {
        data = JSON.parse(text.replace(/^\uFEFF/, '').trim());
      } catch (parseErr) {
        throw new Error('Invalid JSON response');
      }

      if (data.status) {
        successBox.textContent = data.message || 'Thank you! Your enquiry has been submitted successfully.';
        successBox.classList.add('show');
        localStorage.removeItem('contactFormData');
        form.reset();
      } else {
        errorBox.textContent = data.message || 'Unable to submit enquiry.';
        errorBox.classList.add('show');
      }
    } catch (err) {
      errorBox.textContent = 'Server error. Please try again.';
      errorBox.classList.add('show');
      console.log(err);
    } finally {
      btn.disabled = false;
      btn.textContent = oldText || 'Send Message';
    }
  });

  // Live clear errors
  document.addEventListener('input', (e) => {
    if (e.target.classList.contains('err')) {
      e.target.classList.remove('err');
      const errEl = e.target.nextElementSibling;
      if (errEl && errEl.classList.contains('f-err')) errEl.classList.remove('show');
    }
  });

  /* ── Chat Bot ── */
  const chatbot = document.getElementById('chatbot');
  const chatWindow = chatbot ? chatbot.querySelector('.chat-window') : null;
  const chatMessages = document.getElementById('chatMessages');
  const chatForm = document.getElementById('chatForm');
  const chatInput = document.getElementById('chatInput');
  const chatQuick = document.getElementById('chatQuick');
  let chatStarted = false;
  let typingTimer = null;

  const chatReplies = [
    {
      keys: ['consult', 'appointment', 'session', 'milna', 'consultation'],
      text: 'Aap consultation book karna chahte hain to bas naam, mobile number, city aur concern bhej dijiye. Main aapko next step bata deta hoon.'
    },
    {
      keys: ['service', 'services', 'vastu', 'astro', 'grah', 'dosh', 'gemstone', 'chakra', 'aura', 'geopathic'],
      text: 'Main aapko services ka short version bata deta hoon: Astro-Vastu, Geopathic Stress, Grah Dosh, Aura-Chakra guidance, gemstone aur remote consultation.'
    },
    {
      keys: ['contact', 'phone', 'number', 'call', 'mobile', 'address', 'location'],
      text: 'Contact number +91-9929426997 hai. Office Sawai Madhopur, Rajasthan me hai. Chahein to yahin detail bhej dijiye, main aage guide kar deta hoon.'
    },
    {
      keys: ['fee', 'fees', 'price', 'cost', 'charge', 'payment'],
      text: 'Fees ka depend service aur consultation type par hota hai. Agar aap apni need bata dein, main sahi range samjha dunga.'
    },
    {
      keys: ['hello', 'hi', 'namaste', 'hey'],
      text: 'Namaste. Main yahan help ke liye hoon. Aap consultation, services, fees ya contact details me se kisi bhi cheez par pooch sakte hain.'
    }
  ];
  const consultationFlow = {
    active: false,
    name: '',
    phone: '',
    city: '',
    concern: ''
  };

  function toggleChat(forceOpen) {
    if (!chatbot || !chatWindow) return;
    const open = typeof forceOpen === 'boolean' ? forceOpen : !chatbot.classList.contains('open');
    chatbot.classList.toggle('open', open);
    chatWindow.setAttribute('aria-hidden', String(!open));
    if (open) {
      if (!chatStarted) {
        chatStarted = true;
        addChatMessage('bot', 'Namaste. Main office ki taraf se help kar raha hoon. Aapko kis cheez me guidance chahiye?');
      }
      setTimeout(() => chatInput && chatInput.focus(), 120);
    }
  }

  function addChatMessage(type, text) {
    if (!chatMessages) return;
    const msg = document.createElement('div');
    msg.className = `chat-msg ${type}`;
    const p = document.createElement('p');
    p.textContent = text;
    msg.appendChild(p);
    chatMessages.appendChild(msg);
    chatMessages.scrollTop = chatMessages.scrollHeight;
  }

  function showTyping() {
    if (typingTimer) clearTimeout(typingTimer);
    hideTyping();
    const msg = document.createElement('div');
    msg.className = 'chat-msg bot typing';
    msg.id = 'chatTyping';
    const dots = document.createElement('div');
    dots.className = 'typing-dots';
    dots.innerHTML = '<span></span><span></span><span></span>';
    msg.appendChild(dots);
    chatMessages.appendChild(msg);
    chatMessages.scrollTop = chatMessages.scrollHeight;
  }

  function hideTyping() {
    const typing = document.getElementById('chatTyping');
    if (typing) typing.remove();
  }

  function pickReply(replies) {
    return replies[Math.floor(Math.random() * replies.length)];
  }

  function extractContactData(message) {
    const text = message.trim();
    const lower = text.toLowerCase();
    const phoneMatch = text.match(/(?:\+91[-\s]?)?[6-9]\d{9}/);
    const cityHints = ['jaipur', 'sawai madhopur', 'madhopur', 'delhi', 'mumbai', 'indore', 'ajmer', 'rajasthan', 'up', 'mp'];
    const cityMatch = cityHints.find(city => lower.includes(city));
    const words = text.split(/\s+/).filter(Boolean);
    const nameParts = words.filter(word => !/\d/.test(word) && !cityHints.includes(word.toLowerCase()) && !['book', 'booking', 'consult', 'consultation', 'appointment', 'session', 'call', 'visit'].includes(word.toLowerCase()));
    const shortText = nameParts.length >= 2 && nameParts.length <= 4 && !phoneMatch && !cityMatch;

    return {
      name: shortText ? nameParts.slice(0, 3).join(' ') : '',
      phone: phoneMatch ? phoneMatch[0] : '',
      city: cityMatch || '',
      concern: /book|consult|appointment|call|visit|help|problem|issue/.test(lower) ? text : ''
    };
  }

  function summarizeConsultation() {
    const missing = [];
    if (!consultationFlow.name) missing.push('naam');
    if (!consultationFlow.phone) missing.push('mobile number');
    if (!consultationFlow.city) missing.push('city');
    if (!consultationFlow.concern) missing.push('concern');

    if (!missing.length) {
      const summary = [
        `Name: ${consultationFlow.name}`,
        `Phone: ${consultationFlow.phone}`,
        `City: ${consultationFlow.city}`,
        `Concern: ${consultationFlow.concern}`
      ].join(' | ');
      consultationFlow.active = false;
      return `Perfect. Mujhe details mil gayi hain: ${summary}. Ab main consultation ke liye aage ka step suggest kar deta hoon.`;
    }

    const nextAsk = missing.length === 1
      ? missing[0]
      : missing.slice(0, 2).join(' aur ');
    consultationFlow.active = true;
    return `Theek hai. Abhi mujhe ${nextAsk} chahiye. Aap ek hi message me sab bhej sakte hain.`;
  }

  function getBotReply(message) {
    const normalized = message.toLowerCase();

    if (consultationFlow.active || /book|consult|appointment|session|call|visit/.test(normalized)) {
      const extracted = extractContactData(message);
      if (extracted.name && !consultationFlow.name) consultationFlow.name = extracted.name;
      if (extracted.phone && !consultationFlow.phone) consultationFlow.phone = extracted.phone;
      if (extracted.city && !consultationFlow.city) consultationFlow.city = extracted.city;
      if (extracted.concern && !consultationFlow.concern) consultationFlow.concern = extracted.concern;

      if (/book|consult|appointment|session|call|visit/.test(normalized)) {
        consultationFlow.active = true;
      }

      return summarizeConsultation();
    }

    const match = chatReplies.find(item => item.keys.some(key => normalized.includes(key)));
    if (match) return match.text;
    if (normalized.includes('book') || normalized.includes('call') || normalized.includes('visit')) {
      return pickReply([
        'Theek hai, aap chahein to naam aur number bhej dijiye. Main aage ka simplest step bata deta hoon.',
        'Bilkul, booking ke liye basic details share kar dijiye. Uske baad main aapko next action bataunga.'
      ]);
    }
    return pickReply([
      'Aap thoda aur detail me bata den to main better help kar paunga.',
      'Samajh gaya. Bas apni requirement thodi clearly likh dijiye, main uske hisaab se guide karta hoon.',
      'Theek hai. Agar aap chahen to apna naam, city aur issue bhej dijiye.'
    ]);
  }

  function sendChatMessage(message) {
    const clean = message.trim();
    if (!clean || !chatInput || !chatMessages) return;
    addChatMessage('user', clean);
    chatInput.value = '';
    showTyping();
    const delay = 700 + Math.min(clean.length * 18, 1200);
    typingTimer = setTimeout(() => {
      hideTyping();
      typingTimer = null;
      addChatMessage('bot', getBotReply(clean));
    }, delay);
  }

  if (chatForm) {
    chatForm.addEventListener('submit', (e) => {
      e.preventDefault();
      if (!chatInput) return;
      sendChatMessage(chatInput.value);
    });
  }

  if (chatQuick) {
    chatQuick.addEventListener('click', (e) => {
      const btn = e.target.closest('[data-msg]');
      if (!btn) return;
      sendChatMessage(btn.dataset.msg);
    });
  }

  /* ── Lightbox ── */
  function openLightbox(emoji, label, bg, imgUrl) {
    const lb = document.getElementById('lightbox');
    const box = document.getElementById('lb-box');
    const em = document.getElementById('lb-emoji');
    let img = document.getElementById('lb-img');
    
    // Clear previous content
    if (img) img.remove();
    em.textContent = '';
    
    // If imgUrl is provided, show image instead of emoji
    if (imgUrl) {
      img = document.createElement('img');
      img.id = 'lb-img';
      img.src = imgUrl;
      img.alt = label;
      box.appendChild(img);
      box.style.background = 'transparent';
    } else {
      em.textContent = emoji;
      box.style.background = bg;
    }
    
    lb.classList.add('show');
    document.body.style.overflow = 'hidden';
  }
  function closeLightbox() {
    document.getElementById('lightbox').classList.remove('show');
    document.body.style.overflow = '';
  }
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeLightbox(); });

  /* ── Video Lightbox ── */
  function openVideoLightbox(videoSrc, posterSrc) {
    const vlb = document.getElementById('videoLightbox');
    const video = document.getElementById('vlb-video');
    const source = document.getElementById('vlb-source');

    source.src = videoSrc;
    if (posterSrc) {
      video.poster = posterSrc;
    }
    video.load(); // Reload the video with new source

    vlb.classList.add('show');
    document.body.style.overflow = 'hidden';
  }

  function closeVideoLightbox() {
    const vlb = document.getElementById('videoLightbox');
    const video = document.getElementById('vlb-video');

    vlb.classList.remove('show');
    video.pause();
    document.body.style.overflow = '';
  }
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeVideoLightbox(); });

  function playHomeIntroVideo(card) {
    if (!card || card.dataset.playing === 'true') return;
    const videoId = card.dataset.videoId || 'vCQzJ-Mat08';
    card.dataset.playing = 'true';
    card.innerHTML = `
      <iframe width="100%" height="500"
        src="https://www.youtube-nocookie.com/embed/${videoId}?rel=0&modestbranding=1&autoplay=1"
        title="YouTube video"
        frameborder="0"
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen"
        allowfullscreen>
      </iframe>
      <div class="video-alt-link">If the video does not load here, <a href="https://youtu.be/${videoId}" target="_blank" rel="noopener noreferrer">open it on YouTube</a>.</div>
    `;
  }

  function playGalleryVideo(card) {
    playHomeIntroVideo(card);
  }

  /* ── Hero Slider ── */
  function initHeroSlider() {
    const slider = document.getElementById('heroSlider');
    const dotsWrap = document.getElementById('heroDots');
    if (!slider || !dotsWrap) return;

    const slides = Array.from(slider.querySelectorAll('.hero-slide'));
    if (!slides.length) return;

    let current = 0;
    let timerId = null;

    const dots = slides.map((_, idx) => {
      const b = document.createElement('button');
      b.className = 'hero-dot';
      b.type = 'button';
      b.setAttribute('aria-label', `Show slide ${idx + 1}`);
      b.addEventListener('click', () => {
        showSlide(idx);
        restartAuto();
      });
      dotsWrap.appendChild(b);
      return b;
    });

    function showSlide(index) {
      current = (index + slides.length) % slides.length;
      slides.forEach((slide, i) => slide.classList.toggle('active', i === current));
      dots.forEach((dot, i) => dot.classList.toggle('active', i === current));
    }

    function restartAuto() {
      if (timerId) clearInterval(timerId);
      timerId = setInterval(() => showSlide(current + 1), 4200);
    }

    showSlide(0);
    restartAuto();
  }

  /* ── Init on Load ── */
  function escHtml(v) {
    return String(v || '').replace(/[&<>"']/g, function(ch){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch]; });
  }

  async function loadSlidersApi() {
    const slider = document.querySelector('#page-home #heroSlider');
    if (!slider) return;
    try {
      const res = await fetch('./api/sliders.php');
      const json = await res.json();
      if (!json.ok || !Array.isArray(json.data) || !json.data.length) return;
      slider.innerHTML = json.data.map((s, i) => `<div class="hero-slide ${i === 0 ? 'active' : ''}" style="background-image:url('${escHtml(s.image_url)}')"></div>`).join('');
      const dotsWrap = document.getElementById('heroDots');
      if (dotsWrap) dotsWrap.innerHTML = '';
      initHeroSlider();
    } catch (e) { console.error('Slider API failed', e); }
  }

  async function loadServicesApi() {
    const home = document.querySelector('#page-home .srv-grid');
    const serviceGrids = document.querySelectorAll('#page-services .container > div[style*="grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:2rem"]');
    if (!home || serviceGrids.length < 2) return;
    const what = serviceGrids[0];
    const spec = serviceGrids[1];

    try {
      const res = await fetch('./api/services.php?active=1');
      const json = await res.json();
      if (!json.ok || !Array.isArray(json.data)) return;
      const rows = json.data;
      const whatRows = rows.filter(r => (r.category || 'what_we_offer') !== 'specialized');
      const specRows = rows.filter(r => (r.category || '') === 'specialized');

      home.innerHTML = rows.length ? rows.map(r => `<div class="srv-card fi"><div style="display:flex;justify-content:center;align-items:center;width:100%;margin-bottom:1.1rem"><img src="${escHtml(r.image_url || './logos/astro vastu.png')}" alt="${escHtml(r.title)}" class="img-fluid d-block" style="max-width:110px;border-radius:10px;"></div><h3>${escHtml(r.title)}</h3><p>${escHtml(r.short_description)}</p><span class="srv-link" onclick="showPage('services')">Learn more ?</span></div>`).join('') : '<div class="srv-card fi"><h3>No services found</h3></div>';

      function renderFull(list, emptyText) {
        if (!list.length) return `<div class="srv-full fi"><h3 style="color:var(--n9);margin-bottom:.75rem">${emptyText}</h3></div>`;
        return list.map(r => {
          const tags = String(r.tags || '').split(',').map(t => t.trim()).filter(Boolean);
          return `<div class="srv-full fi"><div class="srv-icon" style="margin:0 auto 2rem"><img src="${escHtml(r.image_url || './logos/astro vastu.png')}" alt="${escHtml(r.title)}" class="img-fluid d-block" style="max-width:110px;"></div><h3 style="color:var(--n9);margin-bottom:.75rem">${escHtml(r.title)}</h3><p style="font-size:.925rem;margin-bottom:1rem">${escHtml(r.short_description)}</p>${tags.length ? `<div class="srv-tags">${tags.map(t => `<span class="srv-tag">${escHtml(t)}</span>`).join('')}</div>` : ''}</div>`;
        }).join('');
      }

      what.innerHTML = renderFull(whatRows, 'No services found');
      spec.innerHTML = renderFull(specRows, 'No specialized services found');
      initObserver();
    } catch (e) { console.error('Services API failed', e); }
  }

  async function loadAccoladesApi() {
    const grid = document.querySelector('#page-achievements .award-grid');
    if (!grid) return;
    try {
      const res = await fetch('./api/accolades.php');
      const json = await res.json();
      if (!json.ok || !Array.isArray(json.data) || !json.data.length) return;
      grid.innerHTML = json.data.map(a => `<div class="award-card fi"><div class="award-icon">${a.image_url ? `<img src="${escHtml(a.image_url)}" alt="${escHtml(a.title)}">` : ''}</div><p class="award-title">${escHtml(a.title)}</p><span class="award-year">${escHtml(a.tags)}</span></div>`).join('');
      initObserver();
    } catch (e) { console.error('Accolades API failed', e); }
  }

  async function loadGalleryApi() {
    const pGrid = document.querySelector('#galleryPhotos .gal-grid');
    const vGrid = document.querySelector('#galleryVideos .gallery-video-grid');
    if (!pGrid || !vGrid) return;
    const channelCard = `<article class="gallery-video-card fi">
            <div class="gallery-video-thumb home-intro-youtube" onclick="window.open('https://www.youtube.com/@chitraguptastrovastugeopat2656','_blank')" role="button" tabindex="0" aria-label="Open YouTube channel">
              <img src="./images/youtube.png" alt="ChitraguptAstroVastu YouTube channel">
              <span class="video-play">▶</span>
            </div>
            <div class="gallery-video-body">
              <h3>Channel Library</h3>
              <p>Explore more seminar, event, and consultation clips on the official channel.</p>
              <div class="gallery-video-actions">
                <a class="btn btn-primary btn-sm" href="https://www.youtube.com/@chitraguptastrovastugeopat2656" target="_blank" rel="noopener noreferrer">Visit Channel</a>
              </div>
            </div>
          </article>`;
    try {
      const [gp, gv] = await Promise.all([fetch('./api/gallery.php'), fetch('./api/videos.php')]);
      const photos = await gp.json();
      const videos = await gv.json();
      if (photos.ok && Array.isArray(photos.data)) {
        pGrid.innerHTML = photos.data.length ? photos.data.map(g => `<div class="gal-item fi" onclick="openLightbox('', '${escHtml(g.description)}', 'linear-gradient(135deg,#0f2040,#C49A3C)', '${escHtml(g.image_url)}')"><img src="${escHtml(g.image_url)}" alt="Gallery Image"><div class="gal-overlay">${escHtml(g.description)}</div></div>`).join('') : '<p>No gallery images found.</p>';
      }
      if (videos.ok && Array.isArray(videos.data)) {
        vGrid.innerHTML = videos.data.length ? videos.data.map(v => `<article class="gallery-video-card fi"><div class="gallery-video-thumb local-video" onclick="openVideoLightbox('${escHtml(v.video_url)}','')" role="button" tabindex="0" aria-label="Play local video"><video class="local-video-player" preload="metadata"><source src="${escHtml(v.video_url)}" type="video/mp4"></video><span class="video-play">▶</span></div><div class="gallery-video-body"><h3>${escHtml(v.description)}</h3><div class="gallery-video-actions"><button class="btn btn-dark btn-sm" onclick="downloadVideo('${escHtml(v.video_url)}')">Download</button></div></div></article>`).join('') : '<p>No videos found.</p>';
      } else {
        vGrid.innerHTML = '<p>No videos found.</p>';
      }
      vGrid.innerHTML += channelCard;
      initObserver();
    } catch (e) { console.error('Gallery API failed', e); vGrid.innerHTML += channelCard; }
  }

  async function loadWorkflowApi() {
    const grid = document.querySelector('#page-services #workflowGrid');
    if (!grid) return;
    try {
      const res = await fetch('./api/workflow.php');
      const json = await res.json();
      if (!json.ok || !Array.isArray(json.data)) return;
      grid.innerHTML = json.data.length ? json.data.map((w, i) => `<div class="srv-card fi" style="text-align:center;padding:2rem 1.5rem"><div class="srv-icon" style="margin:0 auto 1rem"><img src="${escHtml(w.image_url || './logos/astro vastu.png')}" alt="${escHtml(w.title)}" class="img-fluid mx-auto d-block" style="max-width:110px;"></div><div style="font-size:.75rem;font-weight:700;color:var(--g6);letter-spacing:.08em;text-transform:uppercase;margin-bottom:.5rem">Step ${String(i + 1).padStart(2,'0')}</div><h4 style="color:var(--n9);margin-bottom:.5rem">${escHtml(w.title)}</h4><p style="font-size:.875rem">${escHtml(w.description)}</p></div>`).join('') : '<p style="text-align:center;width:100%;">No workflow added yet.</p>';
      initObserver();
    } catch (e) { console.error('Workflow API failed', e); }
  }

  loadPageFromHash();
  initHeroSlider();
  loadSlidersApi();
  loadServicesApi();
  loadAccoladesApi();
  loadGalleryApi();
  loadWorkflowApi();
  initObserver();
  initCounters();
  updateChatbotVisibility();</script>


</body>
</html>


















